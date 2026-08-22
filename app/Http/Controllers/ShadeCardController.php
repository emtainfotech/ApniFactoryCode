<?php

namespace App\Http\Controllers;

use App\Models\ShadeCard;
use Illuminate\Http\Request;
use Auth;

class ShadeCardController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
         $data['list'] = ShadeCard::where('status','!=','1')->get();
         $data['title'] = 'Shade Cards';
        return view('seller.managecolor',$data);
    }
  public function sendrequesttoadmin(Request $request)
    {
        $name = $request->name;
        $hexacode = $request->hexacode;
        $addby = Auth::user()->id;
        $status = '0';$images='';
        $file = $request->file('image');
        if(!empty($file)){
                 $fname=$file->getClientOriginalName();
            $file->move('storage/app/public/shadecard',$fname);
            $images='shadecard/'.$fname;
        }
            $check = ShadeCard::where("user_id",$addby)->where("name",$name)->first();
            if(empty($check)){
            $array = array(
                            "hexcode"=>$hexacode,
                            "name"=>$name,
                            "status"=>$status,
                            "adminmsg"=>'',
                            "user_id"=>$addby,
                            "image"=>$images);
            ShadeCard::insert($array);     
        }
        return back()->withErrors(["Request Successfully Send"]);
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
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ShadeCard  $shadeCard
     * @return \Illuminate\Http\Response
     */
    public function show(ShadeCard $shadeCard)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\ShadeCard  $shadeCard
     * @return \Illuminate\Http\Response
     */
    public function edit(ShadeCard $shadeCard)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\ShadeCard  $shadeCard
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ShadeCard $shadeCard)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ShadeCard  $shadeCard
     * @return \Illuminate\Http\Response
     */
    public function destroy(ShadeCard $shadeCard)
    {
        //
    }
}
