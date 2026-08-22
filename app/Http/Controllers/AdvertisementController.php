<?php

namespace App\Http\Controllers;

use App\Models\Advertisement;
use Illuminate\Http\Request;
use Auth;
class AdvertisementController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
         $data['list'] = Advertisement::where('user_id',Auth::user()->id)->get();
         $data['title'] = 'Advertisement';
        return view('seller.advertisement',$data);
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
        $name = $request->name;
        $content = $request->content;
        $user_id = Auth::user()->id;
        $status = '0';
        $file = $request->file('image');
          $fname=$file->getClientOriginalName();
            $file->move('storage/app/public/slider',$fname);
            $images=$fname;
            $check = Advertisement::where("user_id",$user_id)->where("name",$name)->first();
            if(empty($check)){
            $array = array(
                            "content"=>$content,
                            "name"=>$name,
                            "status"=>$status,
                            "adminmsg"=>'',
                            "user_id"=>$user_id,
                            "file"=>'slider/'.$images
                            );
            Advertisement::insert($array);        
            return back()->withErrors(["Request Successfully Send"]);
        }else{ return back()->withErrors(["Request Already Exist"]);}
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Advertisement  $advertisement
     * @return \Illuminate\Http\Response
     */
    public function show(Advertisement $advertisement)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Advertisement  $advertisement
     * @return \Illuminate\Http\Response
     */
    public function edit(Advertisement $advertisement)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Advertisement  $advertisement
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Advertisement $advertisement)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Advertisement  $advertisement
     * @return \Illuminate\Http\Response
     */
    public function destroy(Advertisement $advertisement)
    {
        $advertisement->delete();
        return redirect()->back()->withErrors(["Successfully Deleted"]);
    }
}
