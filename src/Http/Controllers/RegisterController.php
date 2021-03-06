<?php

namespace FRohlfing\Auth\Http\Controllers;

use App\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\RegistersUsers;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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
    protected $redirectTo;

    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        if (!config('auth.registration') && !is_console()) {
            throw new NotFoundHttpException;
        }

        $this->middleware('guest');

        $this->redirectTo = config('auth.redirect_to_after_register', '');
    }

    /**
     * Show the application registration form.
     *
     * @return \Illuminate\Http\Response
     */
    public function showRegistrationForm()
    {
        return view('auth::register');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        $keys = ['name', 'email', 'password'];
        if (!config('auth.hide_username')) {
            $keys[] = 'username';
        }

        $rules = array_only(User::rules(), $keys);

        return Validator::make($data, $rules);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param array $data
     * @return \App\User
     */
    protected function create(array $data)
    {
        $user = new User;
        $user->name       = $data['name'];
        $user->username   = !config('auth.hide_username') ? $data['username'] : uniqid('u');
        $user->email      = $data['email'];
        $user->password   = bcrypt($data['password']);
        $user->role       = config('auth.roles.0');
        $user->api_token  = str_unique_random(60);
        $user->rate_limit = config('auth.rate_limit');

        $user->save();

        return $user;
    }

    /**
     * The user has been registered.
     *
     * @param \Illuminate\Http\Request $request
     * @param User $user
     */
    protected function registered(/** @noinspection PhpUnusedParameterInspection */ Request $request, $user)
    {
        $user->sendEmailVerificationNotification();
    }
}
