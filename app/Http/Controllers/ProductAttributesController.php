<?php

namespace App\Http\Controllers;

use App\Models\ProductAttributes;
use Illuminate\Http\Request;
use DB;
class ProductAttributesController extends Controller
{
    
 public function updateattributeprice(Request $request){
     $id=$request->aid;
     $price=$request->price;
     $up = ProductAttributes::where("id",$id)->update(["price"=>$price]);
     return json_encode(["status"=>true]);
 }
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
        $color = $request->colorid;
        $packing = $request->packingid;
        $price = $request->price;
        $proid = $request->productid;
        //  `product_attributes`(`id`, `product_id`, `color`, `quantity`, `oldprice`, `price`
        $pray = array(
                        "product_id"=>$proid,
                        "color"=>$color,
                        "quantity"=>$packing,
                        "oldprice"=>$price,
                        "price"=>$price
                    );
        ProductAttributes::insert($pray);
            return back()->withErrors(["Successfully Added"]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ProductAttributes  $productAttributes
     * @return \Illuminate\Http\Response
     */
    public function show(ProductAttributes $productAttributes)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\ProductAttributes  $productAttributes
     * @return \Illuminate\Http\Response
     */
    public function edit(ProductAttributes $productAttributes)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\ProductAttributes  $productAttributes
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ProductAttributes $productAttributes)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ProductAttributes  $productAttributes
     * @return \Illuminate\Http\Response
     */
    public function destroy(ProductAttributes $productAttributes)
    {
        //
    }
}
