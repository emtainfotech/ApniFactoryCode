<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use App\Models\User;
use App\Models\Company;
use App\Models\MainCategory;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

use App\Helper\Helper;
class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
   // protected $redirectTo = RouteServiceProvider::HOME;
protected function redirectTo()
{
    if (auth()->user()->id == 1) {
        return '/admin/dashboard';
    }
    return '/seller/dashboard';
}
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }
    
      public function showRegistrationForm()
    {
        // Fetch main categories from database
        $mainCategories = MainCategory::where('status', '1')->orderBy('name')->get();
        
        return view('auth.register', compact('mainCategories'));
    }

    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => ['required', 'string', 'max:255', 'unique:users'],
            'cmpname' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'mobile' => ['required', 'string', 'min:10'], // Adjust regex if specific format needed
            ///'crnno' => ['required', 'string', 'max:50'],
            'gstno' => ['required', 'string', 'min:15', 'max:15'], // GST is usually 15 chars
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'city' => ['required', 'string', 'max:100'],
            'terms' => ['required', 'accepted'], // Enforces terms checkbox
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\Models\User
     */
    protected function create(array $data)
    {
        // 1. Create the User
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);
        
        // 2. Create the Company associated with the user
        if($data['crnno']==''){$crn='NA';}else{$crn=$data['crnno'];}
        $cmpary = array(
            'name' => $data['cmpname'],
            'email' => $data['email'],
            'mobile' => $data['mobile'],
            'crn' => $crn,
            'gst' => $data['gstno'],
            'city' => $data['city'],
            'user_id' => $user->id,
            'status' => '1',
            'minordervalue' => '9999',
            'maincategory_id'=>$data['category_id']
        );
        
        Company::create($cmpary); // Assuming you use create() with Mass Assignment (fillable in model) or use insert()
        $msg = 'Welcome To Apnifactory Family with your Login Id: '.$data['email']. ' and Password :'.$data['password'].'at '.now();
        Helper::addnotificationindb($user->id,'seller','Welcome To Apnifactory',$msg);
        return $user;
    }
}

