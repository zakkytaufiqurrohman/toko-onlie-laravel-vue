<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Province;
use App\City;
use App\Http\Resources\Provinces as provinceResourceCollection;
use App\Http\Resources\Cities as cityResourceCollection;
use Illuminate\Support\Facades\Auth;
use App\Book;
use App\Order;
use App\BookOrder;
use DB;

class ShopController extends Controller
{
    //
    public function provinces() {
        return new provinceResourceCollection(Province::get());
    }

    public function cities() {
        return new cityResourceCollection(City::get());
    }
    // shipping
    public function shipping(Request $request) {
        $user = auth::user();
        $status = '';
        $message = '';
        $data = null;
        $code = 200;
        if($user) {
            $this->validate($request, [
                'name' => 'required',
                'address' => 'required',
                'phone' => 'required',
                'province_id' => 'required',
                'city_id' => 'required'
            ]);
            $user->name = $request->name;
            $user->address = $request->address;
            $user->phone = $request->phone;
            $user->province_id = $request->province_id;
            $user->city_id = $request->city_id;
            if( $user->save()) {
                $status = "success";
                $message = "update success";
                $data = $user->toArray();
            }
            else {
                $message = "update failed";
            }
        }
        else {
            $message = " user not Found";
        }
        return response()->json([
            'status' => $status,
            'message' => $message,
            'data' => $data
        ], $code);
    }

    public function couriers() 
    {
        $couries = [
            ['id' => 'jne', 'text' => 'JNE'],
            ['id' => 'tiki', 'text' => 'TIKI'],
            ['id' => 'pos', 'text' => 'POS'],
        ];

        return response()->json([
            'status' => 'success',
            'message' => 'couries',
            'data' => $couries
        ], 200);
    }
    // cek cart from client with database
    public function services(Request $request)
    {
        $status = "error";
        $message = "";
        $data = [];
        $this->validate($request, [
            'courier' => 'required',
            'carts' => 'required'
        ]);

        $user = Auth::user();
        if ($user) {
            $destination = $user->city_id;
            if ($user->city_id > 0) {
                // kode 153 kode untuk wilayah asal pengiriman yaitu jakarta
                $origin = 153;
                $courier = $request->courier;
                $carts = $request->carts;
                $carts = json_decode($carts, true); //json to array
                //validasi data belanja
                $validCart = $this->validateCart($carts);
                $data['safe_carts'] = $validCart['safe_carts'];
                $data['total'] = $validCart['total'];
                $quantity_different = $data['total']['quantity_before']<>$data['total']['quantity']; //

                $weight = $validCart['total']['weight'] * 1000;
                if ($weight > 0) 
                {
                    $parameter = [
                        "origin" => $origin,
                        "destination" => $destination,
                        "weight" => $weight,
                        "courier" => $courier
                    ];
                    // cek ongkir ke api rajaongkir melalui getService()
                    $respon_service = $this->getServices($parameter);
                    if ($respon_service['error'] == null) {
                        $services = [];
                        $response = json_decode($respon_service['response']); //json to array
                        $costs = $response->rajaongkir->results[0]->costs;
                        foreach ($costs as $cost) {
                            $service_name = $cost->service;
                            $service_cost = $cost->cost[0]->value;
                            $service_estimation = str_replace('hari', '', trim($cost->cost[0]->etd));
                            $services[] = [
                                'service' => $service_name,
                                'cost' => $service_cost,
                                'estimation' => $service_estimation,
                                'resume' => $service_name . ' [ Rp.'.number_format($service_cost).', Etd: '.$cost->cost[0]->etd.' day(s)] '
                            ];
                        }
                        // response
                        if (count($services) > 0) {
                            $data['services'] = $services;
                            $status = "success";
                            $message = "getting services success";
                        }
                        else {
                            $message = "courrier services unavailable";
                        }

                        // jika jumlah beli berbeda dengan jumlah stok //tampilkan warning
                        if ($quantity_different) {
                            $status = "warning";
                            $message = "check cart data".$message;
                        }
                    }
                    else {
                        $message = "cURL error #".$respon_service['error'];
                    }
                }
                else {
                    $message = "weight invalid";
                }
            }
            else {
                $message = "destination not set";
            }
        }
        else {
            $message = "user not found";
        }

        return response()->json([
            'status' => $status,
            'message' => $message,
            'data' => $data
        ], 200);
    }
    // validasi data belanja
    // cek harga ,total dari user
    protected function validateCart($carts)
    {
        $safe_carts = [];
        $total = [
            'quantity_before' => 0,
            'quantity' => 0,
            'price' => 0,
            'weight' => 0
        ];
        $idx = 0;

        foreach($carts as $cart) {
            $id = (int)$cart['id'];
            $quantity = (int)$cart['quantity'];
            $total['quantity_before'] += $quantity;
            $book = Book::find($id);
            if ($book) {
                if($book->stock > 0) {
                    $safe_carts[$idx]['id'] = $book->id;
                    $safe_carts[$idx]['title'] = $book->title;
                    $safe_carts[$idx]['cover'] = $book->cover;
                    $safe_carts[$idx]['price'] = $book->price;
                    $safe_carts[$idx]['weight'] = $book->weight;

                    if ($book->stock < $quantity) { //jika jumlah yang di pesan melebihi stok buku maka
                        $quantity = (int) $book->stock; //jumlah yang dipesan disamakan dengan stok
                    }

                    $safe_carts[$idx]['quantity'] = $quantity;

                    $total['quantity'] += $quantity; //total jumlah yang di pesan di hitung kembali
                    $total['price'] += $book->price * $quantity; //total price dihitung kembali
                    $total['weight'] += $book->weight * $quantity; // total berat dihitung kembali
                    $idx++;
                }
                else {
                    continue;
                }
            }
        }

        return [
            'safe_carts' => $safe_carts,
            'total' => $total,
        ];
    }
    // fungsi untuk get api rajaongkir
    protected function getServices($data)
    {
        $url_cost = "https://api.rajaongkir.com/starter/cost";
        $key="85d9f98f2bf0327db08324ec64c6e155";
        $postdata = http_build_query($data);
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $url_cost,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $postdata,
            CURLOPT_HTTPHEADER => [
                "content-type: application/x-www-form-urlencoded",
                "key: ".$key
            ],
        ]);
        $response = curl_exec($curl);
        $error = curl_error($curl);
        curl_close($curl);
        return [
            'error' => $error,
            'response' => $response,
        ];
    }
    // 
    public function payment(Request $request) 
    {
        $this->validate($request,[
            'courier' => 'required',
            'service' => 'required',
            'carts' => 'required'
        ]);

        $error = 0;
        $status = "error";
        $message = "";
        $data = [];
        $user = Auth::user();
        if ($user) {

            DB::beginTransaction();
            try {
                // prepare
                $origin = 153; //base pengiriman:yaitu jaksel;
                $destination = $user->city_id;
                if ($destination <= 0) {
                    $error ++;
                }
                $courier = $request->courier;
                $service = $request->service;
                $carts = json_decode($request->carts, true);
    
                // create order
                $order = new Order; 
                $order->user_id = $user->id;
                $order->total_price = 0;
                $order->invoice_number = date('YmdHis');
                $order->courier_service = $courier.'-'.$service;
                $order->status = 'SUBMIT';
                if($order->save()){
                    $total_price = 0;
                    $total_weight = 0;
                    foreach ($carts as $cart) {
                        $id = (int)$cart['id'];
                        $quantity = (int)$cart['quantity'];
                        $book = Book::find($id);
                        if ($book) {
                            if ($book->stock >= $quantity) {
                                $total_price += $book->price * $quantity;
                                $total_weight += $book->weight * $quantity;
                                // create book order 
                                $book_order = new BookOrder;
                                $book_order->book_id = $book->id;
                                $book_order->order_id = $order->id;
                                $book_order->quantity = $quantity;
                                // - stock
                                if ($book_order->save()) {
                                    $book->stock = $book->stock - $quantity;
                                    $book->save();
                                }
                            }
                            else {
                                $error ++;
                                throw new \Exception('stock habis');
                            }
                        }
                        else {
                            $error ++;
                            throw new \Exception('Buku tidak ditemukan 404');
                        }
                    }
                }
    
                $weight = $total_weight * 1000; //to gram
                if ($weight <= 0) {
                    $error ++;
                    throw new \Exception('berat kosong');
                }
                // prepare to cek ongkir
                $data = [
                    "origin" => $origin,
                    "destination" => $destination,
                    "weight" => $weight,
                    "courier" => $courier
                ];
    
                //call getService to cek ongkir
                $data_cost = $this->getServices($data);
                if ($data_cost['error']) {
                    $error ++;
                    throw new \Exception('Courier tidak ditemukan');
                }
                // todo dd this variable
                $response = json_decode($data_cost['response']);
                $costs = $response->rajaongkir->results[0]->costs;
                $service_cost = 0;
                foreach ($costs as $cost) {
                    $service_name = $cost->service;
                    if ($service == $service_name) {
                        $service_cost = $cost->cost[0]->value;
                        break;
                    }
                }
    
                if ($service_cost <= 0) {
                    $error ++;
                    throw new Exception('cost null');
                }
                $totalBill = 0;
                $totalBill = $total_price * $service_cost;
    
                // update total bill/price pada tabel order
                $order->total_price = $totalBill;
                if ($order->save()) {
                    if ($error == 0) {
                        DB::commit();
                        $status = 'success';
                        $message = 'Transaction Success';
                        $data = [
                            'order_id' => $order->id,
                            'total_bill' => $totalBill,
                            'invoice_number' => $order->invoice_number
                        ];
                    }
                }
                else {
                    $message = 'error'.$error;
                }
    
            } catch (\Exception $e) {
                $message = $e->getMessage();
                DB::rollback();
            }
        }
        else {
            $message = 'user tidak ditemukan';
        }
        return response()->json([
            'status' => $status,
            'message' => $message,
            'data' => $data,
        ], 200);
    }

    public function myOrder()
    {
        // dd('abc');
        $user = Auth::user();
        $status = "error";
        $message = '';
        $data = [];
        if ($user) {
            $temp = Order::where('user_id',$user->id)->orderBy('id','DESC')->get();
            $message = 'My order';
            $status = "success";
            $data = $temp;
        }
        else {
            $message = 'user not found';
        }

        return Response()->json([
            'status' => $status,
            'message' => $message,
            'data' => $data
        ]);
    }
}
