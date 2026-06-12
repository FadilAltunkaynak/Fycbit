<?php

namespace Modules\KnowledgeBase\Http\Controllers\User;

use App\Http\Requests\Login;
use App\User;
use Carbon\Carbon;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use App\Model\UserVerificationCode;
use App\Http\Services\AuthService;
use App\Http\Services\Logger;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Session;

class LoginController extends Controller
{
    public $service;
    public $logger;
    public function __construct()
    {
        $this->service = new AuthService;
        $this->logger = new Logger();
    }

    public function login()
    {
        if (Auth::user()) {
            if (Auth::user()->role == USER_ROLE_USER) {
                return redirect()->route('support_dashboard');
            } else {
                Auth::logout();
                return view('knowledgebase::user.auth.login');
            }
        }
        return view('knowledgebase::user.auth.login');
    }

    public function loginProcess(Login $request)
    {
        try {
            $user = User::where('email', $request->email)->first();

            if (!empty($user)) {
                if($user->role == USER_ROLE_USER) {
                    if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
                        $token = $user->createToken($request->email)->accessToken;
                        //Check email verification
                        if ($user->status == STATUS_SUCCESS) {
                            $data['success'] = true;
                            $data['message'] = __('Login successful');

                            return redirect()->route('support_dashboard')->with(['success' => $data['message']]);
                        } elseif ($user->status == STATUS_SUSPENDED) {
                            $data['email_verified'] = $user->is_verified;
                            $data['success'] = false;
                            $data['message'] = __("Your account has been suspended. please contact support team to active again");
                            Auth::logout();
                            return back()->with(['dismiss' => $data['message']]);
                        } elseif ($user->status == STATUS_DELETED) {
                            $data['email_verified'] = $user->is_verified;
                            $data['success'] = false;
                            $data['message'] = __("Your account has been deleted. please contact support team to active again");
                            Auth::logout();
                            return back()->with(['dismiss' => $data['message']]);
                        } elseif ($user->status == STATUS_PENDING) {
                            $data['email_verified'] = $user->is_verified;
                            $data['success'] = false;
                            $data['message'] = __("Your account has been pending for admin approval. please contact support team to active again");
                            Auth::logout();
                            return back()->with(['dismiss' => $data['message']]);
                        }
                    } else {
                        $data['success'] = false;
                        $data['message'] = __("Email or Password doesn't match");
                        return back()->with(['dismiss' => $data['message']]);
                    }
                } else {
                    $data['success'] = false;
                    $data['message'] = __("You have no login access");
                    Auth::logout();
                    return back()->with(['dismiss' => $data['message']]);
                }
            } else {
                $data['success'] = false;
                $data['message'] = __("You have no account,please register new account");
                return back()->with(['dismiss' => $data['message']]);
            }
        } catch (\Exception $e) {
            $this->logger->log('signIn', $e->getMessage());
            $response = ['success' => false, 'message' => __('Something went wrong'), 'data' =>[]];
            return back()->with(['dismiss' => $response['message']]);
        }
    }

    public function logout()
    {
        Session::flush();
        Cookie::queue(Cookie::forget('accesstokenvalue'));
        Auth::logout();

        return redirect()->route('knowledgebase_user_index')->with('success', __('Logout successful'));
    }
}
