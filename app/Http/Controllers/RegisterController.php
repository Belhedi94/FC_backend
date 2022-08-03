<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use App\Http\Helpers;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Validation\Rules\Password;
use App\Rules\MobileNumberRule;
use Illuminate\Support\Facades\Auth;



class RegisterController extends Controller
{
    public function register(Request $request) {
        $request['mobile_number'] = Helpers::normalizeMobileNumber($request->mobile_number);
        $fields = $request->validate([
            'full_name' => 'required|max:50',
            'email' => 'required|unique:users,email|email',
            'password' => ['required',
                Password::min(8)
                    ->letters()
                    ->mixedCase()
                    ->numbers()
                    ->symbols()->uncompromised()],
            'mobile_number' => ['required', new MobileNumberRule, 'unique:users,mobile_number'],
        ]);

        $role = 4;
        $user = User::create([
            'full_name' => $fields['full_name'],
            'email' => $fields['email'],
            'password' => bcrypt($fields['password']),
            'mobile_number' => $fields['mobile_number'],
            'is_admin' => false,
            'role_id' => $role,
            'is_active' => false

        ]);

        //event(new Registered($user));

        $token = $user->createToken('myapptoken')->plainTextToken;
        $response = [
            'message' => 'The user is successfully registred',
            'data' => new UserResource($user),
            'token' => $token
        ];
        Auth::login($user);

        return response($response, 201);
    }

}
