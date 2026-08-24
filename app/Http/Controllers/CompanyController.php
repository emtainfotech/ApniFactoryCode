<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\User;
use Illuminate\Http\Request;
use Auth;
use DB;
use Illuminate\Support\Facades\Hash;

use App\Models\CompanyAuditLog;

class CompanyController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
     public function updateImage(Request $request)
    {
        // 1. Validate the incoming file
        $request->validate([
            'profile_picture' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048', // max 2MB
        ]);

        if ($request->hasFile('profile_picture')) {
            $file = $request->file('profile_picture');
            $filename = time() . '_' . $file->getClientOriginalName();
            
            // Move file to storage/app/public/product directory
            $file->move('storage/app/public/product', $filename);
            $photoPath = 'product/' . $filename;

            // Update authenticated user record
            $user = User::find(Auth::id());
            $oldPhoto = $user->profilephoto;
            $user->profilephoto = $photoPath;
            $user->save();

            // Log change
            $company = Company::where('user_id', $user->id)->first();
            if ($company) {
                CompanyAuditLog::logChange(
                    $company->id,
                    'profile_update',
                    'Profile Photo Updated',
                    "User {$user->name} updated their profile picture.",
                    ['old_photo' => $oldPhoto],
                    ['new_photo' => $photoPath]
                );
            }

            return response()->json([
                'success'   => true,
                'message'   => 'Profile picture updated successfully!',
                'image_url' => asset('storage/app/public/' . $photoPath)
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'No image file uploaded.'
        ], 400);
    }

        $user = Auth::user(); // or Auth::guard('seller')->user() depending on your setup
        
        if ($request->hasFile('profile_picture')) {
            $file = $request->file('profile_picture');
            
            // Generate a unique name
            $filename = time() . '_' . $file->getClientOriginalName();
            
            // 2. Save the file to public/uploads/profiles directory
            $file->move('storage/app/public/product', $filename);
            // Optional: Delete old image from server if it exists
            if ($user->profilephoto && file_exists('storage/app/public/product/' . $user->profilephoto)) {
                @unlink('storage/app/public/product/' . $user->profilephoto);
            }

            // 3. Update the database column
            $user->profilephoto = 'product/'.$filename;
            $user->save();
            $rt = Company::where('user_id',Auth::user()->id)->update(["photo"=>'product/'.$filename]);
            // 4. Return json response back to the page
            return response()->json([
                'success' => true,
                'image_url' => asset('storage/app/public/product/' . $filename)
            ]);
        }

        return response()->json(['success' => false, 'message' => 'No file uploaded.'], 400);
    }

     public function  update_restrictedcity(Request $request){
            $city = json_encode($request->get('city'));
            $rt = Company::where('user_id',Auth::user()->id)->update(["restricted_city"=>$city]);
              return back()->withErrors(["Cities Successfully Updated"]);
      }
      public function  restrictedcity(){
     $rt = Company::where('user_id',Auth::user()->id)->first();
     if(!empty($rt->restricted_city)){    $cityy = json_decode($rt->restricted_city,true); }else{$citty = '';}
            $html='';
       $onlystate = DB::table("india_pincode")->select('state')->groupBy('state')->orderBy("state", "asc")->get();
           foreach($onlystate as $key=>$st){
            $html .= '<div class="accordion-item">
    <h2 class="accordion-header" id="headingOne">
      <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne'.$key.'"  aria-controls="collapseOne'.$key.'">
       '.$st->state.' </button>
    </h2>
    <div id="collapseOne'.$key.'" class="accordion-collapse collapse " aria-labelledby="headingOne" data-bs-parent="#accordionExample">
      <div class="accordion-body">';
                 $city = DB::table("india_pincode")->select('city')->where('state',$st->state)->groupBy('city')->get();
                 foreach($city as $ct){
                     if(!empty($rt->restricted_city)){  
                         if (in_array($ct->city, $cityy))  {  $ch = 'checked';  }else  {  $ch = '';  }
                     }else  {  $ch = '';  }
                      $html .= '<li><input type="checkbox" name="city[]" value="'.$ct->city.'" class="allcitycheck'.$key.'" '.$ch.'>'.$ct->city.'</li>';
                 }
                 $html .=  ' </div>
    </div>
  </div>';
           }
          $data['html'] = $html;
        $data['title'] = 'Restricted Cities';
        return view('seller.citynotallow',$data);
      }
     public function profile_sellerview()
    {  
        $data['detail'] = Company::where('user_id',Auth::user()->id)->first();
        $data['title'] = 'Company Profile';
        return view('seller.profile',$data);
    }
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
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Company  $company
     * @return \Illuminate\Http\Response
     */
    public function show(Company $company)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Company  $company
     * @return \Illuminate\Http\Response
     */
    public function edit(Company $company)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Company  $company
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Company $company)
    {
        $user = Auth::user();
        $detail = Company::where('user_id', $user->id)->firstOrFail();

        if($request->action == 'minordervalue'){
            $oldMin = $detail->minordervalue;
            $newMin = $request->input('minvalue');
            $detail->update(["minordervalue" => $newMin]);

            CompanyAuditLog::logChange(
                $detail->id,
                'min_order_value_change',
                'Minimum Order Value Updated',
                "Changed minimum order threshold from ₹" . number_format((float)$oldMin, 2) . " to ₹" . number_format((float)$newMin, 2),
                ['old_minordervalue' => $oldMin],
                ['new_minordervalue' => $newMin]
            );

            return redirect()->back()->withErrors(['Minimum order value updated successfully!']);
        }

        if($request->action == 'passwordupdate'){
             $oldpassword =  $request->currentpassword;
             $newpassword =  $request->newpassword;

            if (!Hash::check($request->currentpassword, $user->password)) {
                return back()->withErrors(['current_password' => 'The provided password does not match our records.']);
            }

            $user->update([
                'password' => Hash::make($request->newpassword)
            ]);

            CompanyAuditLog::logChange(
                $detail->id,
                'profile_update',
                'Password Changed',
                "Seller account password was updated successfully."
            );

            return back()->withErrors('Password updated successfully!');
        }

        // Handle Banner Upload
        if ($request->input('action') === 'update_banner') {
            $request->validate([
                'banner_image' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048', // 2MB Max
            ]);
            if ($request->hasFile('banner_image')) {
                $file = $request->file('banner_image');
                $filename = time() . '_' . $file->getClientOriginalName();
                $file->move('storage/app/public/product', $filename);

                $oldBanner = $detail->photo;
                if ($detail->photo && file_exists('storage/app/public/product/' . $detail->photo)) {
                    @unlink('storage/app/public/product/' . $detail->photo);
                }
                $detail->photo = 'product/'.$filename;
                $detail->save();

                CompanyAuditLog::logChange(
                    $detail->id,
                    'banner_update',
                    'Store Banner Updated',
                    "Seller uploaded a new store banner.",
                    ['old_banner' => $oldBanner],
                    ['new_banner' => 'product/'.$filename]
                );

                return redirect()->back()->withErrors('Banner updated successfully!');
            }
        }

        // Handle Logo Upload
        if ($request->input('action') === 'update_logo') {
            $request->validate([
                'company_logo' => 'required|image|mimes:jpeg,png,jpg,webp|max:1024', // 1MB Max
            ]);

            if ($request->hasFile('company_logo')) {
                $file = $request->file('company_logo');
                $filename = time() . '_' . $file->getClientOriginalName();
                $file->move('storage/app/public/product', $filename);

                $oldLogo = $detail->logo;
                if ($detail->logo && file_exists('storage/app/public/product/' . $detail->logo)) {
                    @unlink('storage/app/public/product/' . $detail->logo);
                }
                $detail->logo = 'product/'.$filename;
                $detail->save();
                User::where("id", $user->id)->update(["profilephoto" => 'product/'.$filename]);

                CompanyAuditLog::logChange(
                    $detail->id,
                    'profile_update',
                    'Company Logo Updated',
                    "Seller updated their brand logo.",
                    ['old_logo' => $oldLogo],
                    ['new_logo' => 'product/'.$filename]
                );

                return redirect()->back()->withErrors('Logo updated successfully!');
            }
        }

        return redirect()->back()->withErrors('Invalid action.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Company  $company
     * @return \Illuminate\Http\Response
     */
    public function destroy(Company $company)
    {
        //
    }
}
