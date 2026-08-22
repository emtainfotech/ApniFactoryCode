<?php

namespace App\Http\Controllers;

use App\Models\faq;
use Illuminate\Http\Request;
use Auth;
class FaqController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
     
      public function sellerview()
    {
        $data['list'] = faq::where('user_id',Auth::user()->id)->get();
         $data['title'] = 'FAQ';
        return view('seller.faq',$data);
    } 
    
    public function index()
    {
       $data = faq::where("status","1")->get();
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
        
        $question = $request->question;
        $answer = $request->answer;
        $user_id = Auth::user()->id;
        $status = '1';
            $check = faq::where("user_id",$addby)->where("question",$question)->first();
            if(empty($check)){
            $array = array(
                            "answer"=>$answer,
                            "question"=>$question,
                            "status"=>$status,
                            "user_id"=>$addby
                            );
            faq::insert($array);        
            return back()->withErrors(["Successfully Added"]);
        }else{ return back()->withErrors(["Request Already Exist"]);}
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\faq  $faq
     * @return \Illuminate\Http\Response
     */
    public function show(faq $faq)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\faq  $faq
     * @return \Illuminate\Http\Response
     */
    public function edit(faq $faq)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\faq  $faq
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, faq $faq)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\faq  $faq
     * @return \Illuminate\Http\Response
     */
    public function destroy(faq $faq)
    {
        $faq->delete();
        return redirect()->back()->withErrors(["Successfully Deleted"]);
    }
}
