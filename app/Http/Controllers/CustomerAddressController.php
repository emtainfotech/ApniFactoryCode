<?php

namespace App\Http\Controllers;

use App\Models\CustomerAddress;
use Illuminate\Http\Request;

class CustomerAddressController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
                $rmn = CustomerAddress::where('customer_id',$request["customer_id"])->get();
             return response()->json(["status"=>true,"code"=>100,"msg"=>"Successfully Show","data"=>$rmn]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $memberid =  $request['customer_id'];
        $name =  $request['name'];
        $landmark1 =  $request['landmark1'];
        $landmark2 =  $request['landmark2'];
        $city =  $request['city'];
        $state =  $request['state'];
        $country =  $request['country'];
        $pincode =  $request['pincode'];
        $phoneno =  $request['phoneno'];
        $location =  $request['location'];
        $identityname =  $request['identityname'];
        $type =  $request['type'];
        $data = array(
                "customer_id"=>$memberid,
                "name"=>$name,
                "landmark1"=>$landmark1,
                "landmark2"=>$landmark2,
                "city"=>$city,
                "state"=>$state,
                "country"=>$country,
                "pincode"=>$pincode,
                "phoneno"=>$phoneno,
                "location"=>$location,
                "identityname"=>$identityname,
                "type"=>$type
            );
        $insrt = CustomerAddress::insertGetId($data);
        $dataw = CustomerAddress::where("id",$insrt)->first();
        return response()->json(["status"=>true,"code"=>100,"msg"=>"Successfully Added","data"=>$dataw]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\CustomerAddress  $customerAddress
     * @return \Illuminate\Http\Response
     */
    public function show(CustomerAddress $id)
    {
             return response()->json(["status"=>true,"code"=>100,"msg"=>"Successfully Show","data"=>$id]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\CustomerAddress  $customerAddress
     * @return \Illuminate\Http\Response
     */
    public function edit(CustomerAddress $customerAddress)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\CustomerAddress  $customerAddress
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
         $memberid =  $request['customer_id'];
        $name =  $request['name'];
        $landmark1 =  $request['landmark1'];
        $landmark2 =  $request['landmark2'];
        $city =  $request['city'];
        $state =  $request['state'];
        $country =  $request['country'];
        $pincode =  $request['pincode'];
        $phoneno =  $request['phoneno'];
        $location =  $request['location'];
        $identityname =  $request['identityname'];
        $data = array(
                "customer_id"=>$memberid,
                "name"=>$name,
                "landmark1"=>$landmark1,
                "landmark2"=>$landmark2,
                "city"=>$city,
                "state"=>$state,
                "country"=>$country,
                "pincode"=>$pincode,
                "phoneno"=>$phoneno,
                "location"=>$location,
                "identityname"=>$identityname,
            );
        $insrt = CustomerAddress::where("id",$id)->update($data);
        return response()->json(["status"=>true,"code"=>100,"msg"=>"Successfully Updated","data"=>$data]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\CustomerAddress  $customerAddress
     * @return \Illuminate\Http\Response
     */
    public function destroy(CustomerAddress $id)
    {
         $id->delete();
         return response()->json(["status"=>true,"code"=>100,"msg"=>"Successfully Deleted"]);
    }
}
