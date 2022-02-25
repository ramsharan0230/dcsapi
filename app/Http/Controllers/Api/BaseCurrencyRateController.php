<?php

namespace App\Models\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Repositories\BaseCurrencyRateInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class BaseCurrencyRateController extends Model
{
    protected $currency;
    public function __construct(BaseCurrencyRateInterface $currency_rate)
    {
        $this->currency_rate = $currency_rate;
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
            $path = '/admin/currencies';
            $data = $this->currency_rate->getAllWithParam($parameter, $path);
            

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
                'name' => 'required|max:199',
                'symbol' => 'required|max:20|unique:currencies',
                'publish' => 'required|in:0,1',
            ]);
        } catch (\Exception $ex) {
            return response()->json([
                "status" => "422",
                "errors" => $ex->response->original
            ], 422);
        }

        try {

            $data = $request->except('publish');
            $data['publish'] = $request->publish?1:0;

            $this->currency->create($data);
            return response()->json([
                'status' => '200',
                'message' => 'The Currencycreated successfully.'
            ], 200);

        } catch (\Exception $ex) {
            Log::error('CurrencyCreate', [
                'status' => '500',
                'message' => serialize($ex->getMessage())
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
            $currency = $this->currency->getSpecificById($id);

            return response()->json([
                'status' => '200',
                "payload" => $currency
            ]);
        } catch (ModelNotFoundException $ex) {
            return response()->json([
                'status' => '404',
                'message' => "Requested Currency could not be found"
            ], 404);
        } catch (\Exception $ex) {
            Log::error('Currency View', [
                'status' => '500',
                'message' => serialize($ex->getMessage())
            ]);

            return response()->json([
                'status' => '500',
                'message' => "Something went wrong"
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $this->validate($request, [
                'name' => 'required|max:199',
                'symbol' => 'required|max:20|unique:currencies,symbol,' . $id . ",id",
                'publish' => 'required|in:0,1',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => '422',
                'message' => $e->response->original
            ], 422);
        }
        try {
            $data = $request->except('publish');
            $data['publish'] = $request->publish?1:0;
            
            $this->currency->update($id, $data);

            return response()->json([
                'status' => '200',
                'message' => 'Currency Updated successfully'
            ]);
        } catch (ModelNotFoundException  $ex) {
            return response()->json([
                'status' => '404',
                'message' => "Requested Currency could not be found"
            ], 404);
        } catch (\Exception $ex) {
            Log::error('Currency Update', [
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
            $currency = $this->currency->delete($id);

            return response()->json([
                'status' => '200',
                'message' => "Currency deleted successfully"
            ]);
        } catch (ModelNotFoundException  $ex) {

            return response()->json([
                'status' => '404',
                'message' => "Requested Currency could not be found"
            ], 404);

        } catch (\Exception $ex) {
            
            Log::error('Currency Delete', [
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
