<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Company;
use App\Models\Category;
use Illuminate\Http\Request;
use Auth;
use DB;
class BrandController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
     public function __construct()
    {
        $this->middleware('auth')->except('logout');
    }
  public function updateImage(Request $request)
    {
        // 1. Validate incoming data
        $request->validate([
           // 'old_image_name' => 'required|string',
            'brand_image'    => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);
        
        if ($request->hasFile('brand_image')){
            $file = $request->file('brand_image');
            $fname=rand(10,99999).$file->getClientOriginalName();
            $file->move('storage/app/public/brand',$fname);
            $images='brand/'.$fname;
            
        }
        if ($request->hasFile('brand_image') and $request->old_image_name!='') {
            // 2. Check if any records exist with this old image name
        $matchingBrandsCount = Brand::where('image', $request->old_image_name)->count();
        if ($matchingBrandsCount === 0) {
            return redirect()->back()->with('error', 'No database records match the previous image name.');
        }
            // 5. Mass update ALL records matching the old image name with the new one
            Brand::where('image', $request->old_image_name)->where('user_id', Auth::user()->id)->update([
                'image' => $images
            ]);
            
            return back()->withErrors(["Successfully updated {$matchingBrandsCount} matching entries with the new image!"]);
        }else{
            // Batch update every row linked to this specific previous filename
                $updatedRowsCount = Brand::where('mid',$request->mid)->where('user_id', Auth::user()->id)->where('image', '')->update([
                    'image' => $images
                ]);
               
                return back()->withErrors(["Successfully updated with the new image!"]);
        }

                return back()->withErrors([ 'No file uploaded.']);
    }
    public function index(Request $request)
    {
       
        $data['title'] = 'Brand';
        // $query = Brand::where('user_id',Auth::user()->id);
        // if ($request->filled('brand')) {    $query = $query->where("name", $request->input('brand'));           }
        // if ($request->filled('srchcategory')) {    $query = $query->where("category_id", $request->input('srchcategory'));           }
        //      $data['list'] = $query->orderby('name','asc')->get();
        $maincategory = Company::select('maincategory_id')->where('user_id',Auth::user()->id)->first();
        $data['maincatid'] = $maincategory->maincategory_id;
        $data['categorylist'] = Category::where('status',1)->where('maincategory_id',$maincategory->maincategory_id)->orderby('name','asc')->get();
        $data['list'] = Brand::select('mid','user_id','name','image','status','adminresponse',DB::raw('GROUP_CONCAT(category_id) as categories'))
                    ->where('user_id',Auth::user()->id)
                    ->groupBy('mid','user_id','name','image','status','adminresponse')->get();
           // dd($data['list']);     
        return view('seller.brand',$data);
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
        $mid = $request->maincategory;
        $name = $request->name;
        $addby = Auth::user()->id;  
        $cmp = Company::where("user_id",$addby)->first();
        $cmpid = $cmp->id;
        $status = '1';
        $cid = $request->subcat;
        $nt=0;
        $trade = $request->tno;
        $file = $request->file('image');
                 $fname=$file->getClientOriginalName();
            $file->move('storage/app/public/brand',$fname);
            $images='brand/'.$fname;
        foreach($request->category as $cat){
            $check = Brand::where("user_id",$addby)->where("name",$name)->where("category_id",$cat)->first();
            if(empty($check)){
            $array = array("category_id"=>$cat,
                            "mid"=>$mid,
                            "name"=>$name,
                            "company_id"=>$cmpid,
                            "status"=>$status,
                            "user_id"=>$addby,
                            "image"=>$images,
                            "trademarkno"=>$trade,
                            "type"=>"Processing");
            Brand::insert($array);        
            }else{ $nt++; }
        }
        return back()->withErrors(["Successfully Added"]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Brand  $brand
     * @return \Illuminate\Http\Response
     */
    public function show(Brand $brand)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Brand  $brand
     * @return \Illuminate\Http\Response
     */
    public function edit(Brand $brand)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Brand  $brand
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Brand $brand)
    {
        $id = $request->upid;
        $status = $request->changestatus;
        $st = Brand::where("id",$id)->update(["status"=>$status]);
        return redirect()->back()->withErrors(["Successfully Updated"]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Brand  $brand
     * @return \Illuminate\Http\Response
     */
    public function destroy(Brand $brand)
    {
        $brand->delete();
        return redirect()->back()->withErrors(["Successfully Deleted"]);
    }
}
