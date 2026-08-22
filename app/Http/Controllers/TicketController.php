<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use Illuminate\Http\Request;
use Auth;
class TicketController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data['list'] = Ticket::where('user_id',Auth::user()->id)->get();
         $data['title'] = 'Ticket';
        return view('seller.ticketsupport',$data);
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
        $question = $request->topic;
        $answer = $request->message;
        $addby = Auth::user()->id;
        $status = 'Pending';
            $check = Ticket::where("user_id",$addby)->where("topic",$question)->first();
            if(empty($check)){
            $array = array(
                            "topic"=>$answer,
                            "msg"=>$question,
                            "status"=>$status,
                            "user_id"=>$addby,
                            "adminmsg"=>''
                            );
            Ticket::insert($array);        
            return back()->withErrors(["Successfully Added"]);
        }else{ return back()->withErrors(["Request Already Exist"]);}
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Ticket  $ticket
     * @return \Illuminate\Http\Response
     */
    public function show(Ticket $ticket)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Ticket  $ticket
     * @return \Illuminate\Http\Response
     */
    public function edit(Ticket $ticket)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Ticket  $ticket
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Ticket $ticket)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Ticket  $ticket
     * @return \Illuminate\Http\Response
     */
    public function destroy(Ticket $ticket)
    {
        $ticket->delete();
        return redirect()->back()->withErrors(["Successfully Deleted"]);
    }
}
