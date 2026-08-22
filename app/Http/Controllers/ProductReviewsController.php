<?php

namespace App\Http\Controllers;

use App\Models\ProductReviews;
use App\Models\Customer;
use Illuminate\Http\Request;

class ProductReviewsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        
            $productid =  $request['productid'];
            $userid =  $request['userid'];
            $review =  $request['review'];
            $rating =  $request['rating'];
            $ary = array(
                            "product_id"=>$productid,
                            "customer_id"=>$userid,
                            "rating"=>$rating,
                            "review"=>$review,"status"=>"1"
                        );
            ProductReviews::insert($ary);
                 $custmr = Customer::where("id",$userid)->first();
                 $rw["customername"] = $custmr->name;
                 $rw["customerimage"] = $custmr->image;
                 $rw["review"] = $review;
                 $rw["rating"] = $rating;
                 $rw["date"] = date("Y-m-d H:i:s");
                
             return json_encode(["status"=>true,"code"=>100,"msg"=>"Successfully Added","data"=>$rw]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ProductReviews  $productReviews
     * @return \Illuminate\Http\Response
     */
    public function show(ProductReviews $productReviews)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\ProductReviews  $productReviews
     * @return \Illuminate\Http\Response
     */
    public function edit(ProductReviews $productReviews)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\ProductReviews  $productReviews
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ProductReviews $productReviews)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ProductReviews  $productReviews
     * @return \Illuminate\Http\Response
     */
    public function destroy(ProductReviews $productReviews)
    {
        //
    }
}
