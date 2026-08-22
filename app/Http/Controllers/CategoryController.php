<?php

namespace App\Http\Controllers;


use App\Models\Category;
use Illuminate\Http\Request;
use Auth;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
         $data['list'] = Category::where('adminstatus','!=','Approved')->get();
         $data['title'] = 'Category';
        return view('seller.category',$data);
    }
  public function sendrequesttoadmin(Request $request)
    {
        $mid = $request->maincategory;
        $name = $request->name;
        $hexacode = $request->hexacode;
        $addby = Auth::user()->id;
        $status = '0';
        $file = $request->file('image');
          $fname=$file->getClientOriginalName();
            $file->move('storage/app/public/category',$fname);
            $images=$fname;
            $check = Category::where("addby",$addby)->where("name",$name)->first();
            if(empty($check)){
            $array = array("maincategory_id"=>$mid,
                            "title"=>$name,
                            "name"=>$name,
                            "status"=>$status,
                            "adminmsg"=>'',
                            "addby"=>$addby,
                            "image"=>'category/'.$images,
                            "adminstatus"=>'pending'
                            );
            Category::insert($array);        
            return back()->withErrors(["Request Successfully Send"]);
        }else{ return back()->withErrors(["Request Already Exist"]);}
       
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
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
