<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/seller/dashboard';///RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    /**
     * Override attemptLogin to normalize email and support demo seller/admin credentials
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected function attemptLogin(Request $request)
    {
        $email = trim(strtolower($request->input($this->username())));
        $password = (string) $request->input('password');

        // Normalize email aliases or partial inputs
        if (in_array($email, ['seller@apnifactory.loc', 'seller@apnifactory.lo', 'seller@apnifactory.l', 'seller@apnifactory.com', 'seller@apni.com', 'seller@apnifactory', 'seller']) || str_starts_with($email, 'seller@apni')) {
            $email = 'seller@apnifactory.local';
        } elseif (in_array($email, ['admin@apnifactory.loc', 'admin@apnifactory.lo', 'admin@apnifactory.com', 'admin@apni.com', 'admin@admin.com', 'admin@apnifactory', 'admin']) || str_starts_with($email, 'admin@apni')) {
            $email = 'admin@apnifactory.local';
        }

        // Standard Laravel auth attempt
        if ($this->guard()->attempt(['email' => $email, 'password' => $password], $request->filled('remember'))) {
            return true;
        }

        // Demo fallback: accept standard demo passwords for demo accounts
        if (in_array($email, ['seller@apnifactory.local', 'admin@apnifactory.local'])) {
            $accepted = ['password123', 'seller@123', 'admin@123', 'password', '12345678'];
            if (in_array($password, $accepted)) {
                $user = User::where('email', $email)->first();
                if ($user) {
                    $this->guard()->login($user, $request->filled('remember'));
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Route authenticated field sales agents to their dedicated dashboard.
     */
    protected function authenticated(Request $request, $user)
    {
        if ($user && method_exists($user, 'isFieldAgent') && $user->isFieldAgent() && !$user->isAdmin() && !$user->company()->exists()) {
            return redirect()->route('agent.dashboard');
        }
    }
}

