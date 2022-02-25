<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Repositories\CurrencyInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Models\CurrencySold;
use App\Models\Stock;
use DB;

class CurrencyController extends Controller
{
    protected $currency;
    public function __construct(CurrencyInterface $currency)
    {
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
            $path = '/admin/currencies';
            $data = $this->currency->getAllWithParam($parameter, $path);
            foreach ($data as $key => $d) {
                $data[$key]['rate'] = $d->baseRate->rate;
                $data[$key]['stock'] = $d->stock;
            }
            

            if (count($data) == 0) {
                return response()->json([
                    "status" => "404",
                    "message" => "No record found"
                ], 404);
            }
            else{
                foreach($data as $key=>$currency){
                    $data[$key]['attributes'] = json_decode($currency['attributes']);
                    $data[$key]['pages'] = json_decode($currency['pages']);
                }
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
                'message' => 'The Currency created successfully.'
            ], 200);

        } catch (\Exception $ex) {
            Log::error('Currency Create', [
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


    public function getCurrencyBySlug($slug)
    {
        try {
            $data = $this->currency->getSpecificBySlug($slug);
            
            $data['rate'] = $data->baseRate;

            return response()->json([
                'status' => '200',
                "payload" => $data
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

    public function buyCurrency(Request $request){
        try {
            $this->validate($request, [
                "currency_slug" => "required|string",
                "amount" => "required",
                "name" => "string|required",
                "email" => "required|email",
                "id" => "required|numeric",
            ]);
        } catch (\Exception $ex) {
            Log::error("Currency List Display", [
                "status" => "422",
                "message" => $ex->errors(),
                "request" => $request->all()
            ]);
            return response()->json([
                "status" => "422",
                "errors" => $ex->errors()
            ], 422);
        }

        $data['slug'] = $request->currency_slug;
        $data['amount'] = $request->amount;
        $data['name'] = $request->name;
        $data['email'] = $request->email;
        $data['id'] = $request->id;

        $currency = $this->currency->getSpecificBySlug($data['slug']);
        
        try{
            if($currency->stock){
                $quantity_calculated = $data['amount'] / $currency->baseRate->rate;
                if($currency->stock->quantity >= $quantity_calculated){
                    try {
                        $sold['currency_id'] =$currency->id;
                        $sold['email'] = $data['email'];
                        $sold['quantity'] = $quantity_calculated;
                        $sold['rate'] = $currency->baseRate->rate;
                        $sold['amount'] = $data['amount'];
                        $sold['user_id'] = $data['id'];
    
                        // currency sold
                        CurrencySold::create($sold);
    
                        //stock update
                        $newstock['quantity'] = $currency->stock->quantity-$quantity_calculated;
                        $newstock['currency_id']  = $currency->id;
                        $stock = Stock::find($currency->stock->id);
                        $stock->update($newstock);
    
                        $purchased['quantity'] = $quantity_calculated;
                        $purchased['currency_name'] = $currency->name;
                        $purchased['slug'] = $data['slug'];
                        $purchased['rate'] = $sold['rate'];
                        $purchased['amount'] = $sold['amount'];
                        $purchased['email'] = $data['email'];
    
    
                        return response()->json([
                            'status' => '200',
                            "payload" => $purchased,
                            'message' => $currency->name .' '. 'purchased Successfully'
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
            }
        }
        catch (\Exception $ex) {
            Log::error("Currency List Display", [
                "status" => "422",
                "message" => $ex->errors(),
                "request" => $request->all()
            ]);
            return response()->json([
                "status" => "422",
                "errors" => $ex->errors()
            ], 422);
        }
    }

    public function boughtCurrency($id){
        try {
            $boughtCurrencies = CurrencySold::where('user_id', $id)
            ->groupBy('currency_id')
            ->selectRaw("SUM(quantity) as total_quantity")
            ->selectRaw("SUM(amount) as total_amount")
            ->get();
            
            $uniqueCurrencies = CurrencySold::where('user_id', $id)->get()->unique('currency_id');
            $data = [];

            if(count($boughtCurrencies)>0){
                $i=0;
                foreach($uniqueCurrencies as $bought){
                    $data[$i]['quantity'] = $boughtCurrencies[$i]->total_quantity;
                    $data[$i]['rate'] = $bought->rate;
                    $data[$i]['amount'] = $boughtCurrencies[$i]->total_amount;
                    $currency = $this->currency->find($bought->currency_id);
                    $data[$i]['currency_name']= $currency->name;
                    $data[$i]['symbol']= $currency->symbol;
                    $data[$i]['slug']= $currency->slug;
                    $i++;
                }
            }
            
            return response()->json([
                'status' => '200',
                "payload" => $data
            ]);
        } catch (ModelNotFoundException $ex) {
            return response()->json([
                'status' => '404',
                'message' => "User has no bought stock"
            ], 404);
        } catch (\Exception $ex) {
            Log::error('Currency View', [
                'status' => '500',
                'message' => serialize($ex->getMessage())
            ]);

            return response()->json([
                'status' => '500',
                'message' => "Something went wrong",
            ], 500);
        }
    }
}
