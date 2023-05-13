<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\PasswordResetLinkSent;
use App\PasswordReset;
use App\Student;
use App\User;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Str;

class PasswordResetController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset requests
    | and uses a simple trait to include this behavior. You're free to
    | explore this trait and override any methods you wish to tweak.
    |
    */

    use ResetsPasswords;

    /**
     * Where to redirect users after resetting their password.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    protected function reset()
    {
        $this->validatePasswordReset();

        $user = User::where('email', request('email'))->first();
        if (!$user) {
            flash('This email address is not registered on School Direct Student.')->error();
            return Redirect::to(config('app.url') . "password-reset");
        }

        $reset_password = PasswordReset::where('email', request('email'))->where('token', request('token'))->first();
        if (!$reset_password) {
            flash('This password reset token does not exist.')->error();
            return Redirect::to(config('app.url') . "password-reset");
        }

        if (strcmp(request('password'), request('password_confirmation')) !== 0) {
            flash('Passwords do not match.')->error();
            return Redirect::to(config('app.url') . "password-reset");
        }

        $user->password = Hash::make(request('password'));

        $user->save();

        PasswordReset::where('email', $user->email)->delete();

        flash('Your password has been successfully reset!')->success();
        return Redirect::to(config('app.url') . "password-reset");
    }

    protected function show($token)
    {
        return view('password.show', compact('token'));
    }

    protected function sendLink()
    {
        $user = User::where('email', request('email'))->first();

        if(!$user){
            return response()->json(array(
                'status' => 0 // user not found
            ));
        }
        else {
            $created_password_reset = PasswordReset::forceCreate([
                'email' => request('email'),
                'token' => Str::random(40),
            ]);
            ini_set('max_execution_time', 300); // 5 minutes
            Mail::to($created_password_reset->email)->send(
                new PasswordResetLinkSent($created_password_reset)
            );

            return response()->json(array(
                'status' => 1 // Password reset link successfully sent
            ));
        }
    }

    public function validatePasswordReset()
    {
        return request()->validate([
            'email'=> 'required|email',
            'password'=> 'required|min:6|confirmed'
        ]);
    }
}
