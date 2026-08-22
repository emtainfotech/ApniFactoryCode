<?php

namespace App\Http\Controllers;

use App\Models\Page;
use Illuminate\Http\Request;
use Auth;
use App\Models\Company;
use App\Models\Customer;
use DB;
use App\Helper\helper;
use App\Models\Profile;
use App\Models\order;
class PageController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
       public function termconditionpg_onregisterpage()
    {
             $data["data"] = Page::where("slug",'TermsCondition')->first();
             return view('TermsCondition',$data);
       }
     public function helpnoforapp()
    {
        $data = DB::table("profiles")->where("attribute","Whatsapp")->where("viewon","app")->select("value")->first();
        return json_encode(["status"=>true,"code"=>100,"msg"=>"Successfully Show","data"=>$data]);
      
    }
      public function pageslist_forappview()
    {
        $data = Page::where("status",1)->select("id","name","slug")->get();
        return json_encode(["status"=>true,"code"=>100,"msg"=>"Successfully Show","data"=>$data]);
    }
        public function sellertransections(Request $request)
    {
            //  $data['list']  = DB::table("transections")->where("user_id",Auth::user()->id)->get();
                $data['title'] = 'Transections';
                $query = DB::table("transections")->where("user_id",Auth::user()->id);
                        if ($request->filled('from_date')) {
                            $query->where('created_at', '>=', $request->from_date);
                        }
                        if ($request->filled('to_date')) {
                            $query->where('created_at', '<=', $request->to_date);
                        }
                        if ($request->filled('order_no')) {
                            $query->where('order_no', $request->order_no);
                        }
                        if ($request->filled('txn_id')) {
                            $query->where('txnid', $request->txn_id);
                        }
                        if ($request->filled('status')) {
                            $query->where('status', $request->status);
                        }
                         $data['list']  = $query->get(); // Adjust the pagination as needed
                        

                return view('seller.transection',$data);
    }
         public function creditnotes_details(Request $request,$id)
    {  
            $credit = DB::table("wallet")->where("id",$id)->first();
             $order = order::where("id",$credit->order_id)->first();
            $cartattribut = DB::table("orderdetail")->where("order_id",$order->id)->get();
            $data['title'] = 'Credit Note Details';
            $data['order'] = $order;
            $data['orderdetail'] = $cartattribut;
            $data['profile'] = Profile::where("viewon",'Invoice')->get();
            $data['seller'] = Company::where("user_id",Auth::user()->id)->first();
            $data['buyer'] = Customer::where("id",$order->customer_id)->first();
            $data['track'] = DB::table("order_tracks")->where("order_id",$order->id)->first();
            $data['laststatus'] = DB::table("order_status")->where("order_id",$order->id)->orderby("id","desc")->first();
             $coupon_code = json_decode($data['order']->coupon_code); 
            $data['creditnote']  =$credit;
                return view('seller.creditnotedetail',$data);
    }
       public function creditnotesofseller()
    {
             $data['list']  = DB::table("wallet")->where("user_id",Auth::user()->id)->get();
             $balance = DB::table("wallet")->where("user_id",Auth::user()->id)->orderby("id","desc")->first();
             if(empty($balance)){$curblnce = 0;}else{$curblnce =$balance->balance;}
                $data['title'] = 'Credit';
                $data['currentbalance'] = $curblnce;
                return view('seller.creditnotes',$data);
    }
     
      public function helppageofseller()
    {
             $data['list']  = Profile::where("viewon",'Sellerpanel')->get();
                $data['title'] = 'Help';
                return view('seller.help',$data);
    }
    public function pagesforapp()
    {
        $name=$_GET['name'];
             $data = Page::where("slug",$name)->first();
        return json_encode(["status"=>true,"code"=>100,"msg"=>"Successfully Show","name"=>$data->name,"data"=>$data->description]);
    }
      public function sellerview($name)
    {
         $pg = Page::where('user_id',Auth::user()->id)->where('slug',$name)->first();
        $data['list'] = $pg;
        if(!empty($pg)){$data['logslist'] = DB::table("logsofpages")->where("page_id",$pg->id)->get();}else{$data['logslist'] = '';}
         $data['title'] = $name;
        return view('seller.page',$data);
    } 
    
    public function addrupdatepage(Request $request)
    {
        $name = Page::where('user_id',Auth::user()->id)->where('slug',$request->name)->first();
        if(empty($name)){
            $ary = array("name"=>$request->name,"slug"=>$request->name,"user_id"=>Auth::user()->id,"description"=>$request->description,"status"=>"1");
            $id=Page::insertGetId($ary);
            $pgname = $request->name;
        }else{
            $ary = array("description"=>$request->description);
            Page::where("id",$name->id)->update($ary);
            $id = $name->id;
            $pgname = $name->name;
        }
        DB::table("logsofpages")->insert(["page_id"=>$id,"user_id"=>Auth::user()->id,"updateon"=>date("Y-m-d"),"pagename"=>$pgname]);
         return back()->withErrors(["Successfully Updated"]);
    }
    
    public function index($pid='')
    {
        if(!empty($pid)){
             $data = Page::where("status","1")->where("id",$pid)->get();
        }else{
        $data = Page::where("status","1")->get();
        }
        return json_encode(["status"=>true,"code"=>100,"msg"=>"Successfully Show","data"=>$data]);
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
     * @param  \App\Models\Page  $page
     * @return \Illuminate\Http\Response
     */
    public function show(Page $page)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Page  $page
     * @return \Illuminate\Http\Response
     */
    public function edit(Page $page)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Page  $page
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Page $page)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Page  $page
     * @return \Illuminate\Http\Response
     */
    public function destroy(Page $page)
    {
        //
    }
}
