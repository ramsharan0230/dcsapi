<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Repositories\StockInterface;
use App\Repositories\CurrencyInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Response;

class StockController extends Controller
{
    protected $stock;
    protected $currency;
    public function __construct(StockInterface $stock, CurrencyInterface $currency)
    {
        $this->stock = $stock;
        $this->currency = $currency;
    }


    public function index(Request $request)
    {
        try {
            $sortBy = $request->get("sort_by", "desc");
            $sortField = $request->get("sort_field");
            $limit = $request->get("limit");

            try {
                $this->validate($request, [
                    "filter_field" => "sometimes|string",
                    "filter_value" => "required_with:filter_field|string",
                    "q" => "sometimes",
                ]);
            } catch (\Exception $ex) {
                Log::error("Currency List Display", [
                    "status" => "422",
                    "message" => serialize($ex->response->original),
                    "request" => $request->all()
                ]);
                return response()->json([
                    "status" => "422",
                    "errors" => $ex->response->original
                ], 422);
            }

            try {
                $this->validate($request, [
                    "limit" => "required|integer|min:1"
                ]);
            } catch (\Exception $ex) {
                $limit = 10;
            }
            $parameter = $request->all();
            $parameter["sort_by"] = $sortBy;
            $parameter["sort_field"] = $sortField;
            $parameter["limit"] = $limit;
            $path = '/admin/stocks';
            $data = $this->stock->getAllWithParam($parameter, $path);
            

            if (count($data) == 0) {
                return response()->json([
                    "status" => "404",
                    "message" => "No record found"
                ], 404);
            }

            return response()->json([
                "status" => "200",
                "payload" => $data
            ], 200);
        } catch (\Exception $ex) {
            Log::error("Currency List Display", [
                'status' => "500",
                'message' => serialize($ex->getMessage()),
            ]);
            return response()->json([
                "status" => "500",
                "message" => "Something went wrong"
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $this->validate($request, [
                'currency_id' => 'required|numeric',
                'quantity' => 'required|numeric',
                'publish' => 'required|in:0,1',
            ]);
        } catch (\Exception $ex) {
            return response()->json([
                "status" => "422",
                "errors" => $ex->errors()
            ], 422);
        }

        try {

            $data = $request->except(['publish', 'currency_id']);
            $data['publish'] = $request->publish?1:0;

            $currency= $this->currency->find($request->currency_id);
            $data['currency_id']=$request->currency_id;

            if($currency){
                $stock = $currency->stock;
                if(!$stock){
                    $this->stock->create($data);
                }else{
                    $stock = $this->stock->getStockByCurrency($request->currency_id);
                    $data['quantity'] = $stock->quantity + $request->quantity;
                    $this->stock->update($stock->id, $data);
                }
               
            }else{
                return response()->json([
                    'status' => '404',
                    'message' => "Requested Currency could not be found"
                ], 404);
            }

            return response()->json([
                'status' => '200',
                'message' => 'The Currency created successfully.'
            ], 200);

        }catch (ModelNotFoundException $ex) {
                return response()->json([
                    'status' => '404',
                    'message' => "Requested Currency could not be found"
                ], 404);
        } 
        catch (\Exception $ex) {
            Log::error('Currency Create', [
                'status' => '500',
                'message' => $ex->getMessage()
            ]);

            return response()->json([
                'status' => '500',
                'message' =>'Something went wrong'
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $stock = $this->stock->getSpecificById($id);

            return response()->json([
                'status' => '200',
                "payload" => $stock
            ]);
        } catch (ModelNotFoundException $ex) {
            return response()->json([
                'status' => '404',
                'message' => "Requested stock could not be found"
            ], 404);
        } catch (\Exception $ex) {
            Log::error('Stock View', [
                'status' => '500',
                'message' => serialize($ex->getMessage())
            ]);

            return response()->json([
                'status' => '500',
                'message' => "Something went wrong"
            ], 500);
        }
    }

    /**
     * Remove the specified bun$bundle from bun$bundle table.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $stock = $this->stock->delete($id);

            return response()->json([
                'status' => '200',
                'message' => "Stock deleted successfully"
            ]);
        } catch (ModelNotFoundException  $ex) {

            return response()->json([
                'status' => '404',
                'message' => "Requested Stock could not be found"
            ], 404);

        } catch (\Exception $ex) {
            
            Log::error('Stock Delete', [
                'status' => '500',
                'message' => serialize($ex->getMessage())
            ]);

            return response()->json([
                'status' => '500',
                'message' => "Something went wrong"
            ], 500);
        }
    }
}
