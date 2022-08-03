<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Http\Resources\UserResource;

class TwoFactorAuth extends Controller
{
    public function store() {
        $user = auth()->user();
        $user->is_active = 1;
        $user->save();
        $user->generateTwoFactorCode();
//        $user->sendSmsNotification($user->two_factor_code);
        return response()->json(['message' => 'A text message has been sent with a verification code to your phone']);
    }

    public function verify(Request $request) {
        $user = auth()->user();
        if ($request->code == $user->two_factor_code) {
            $user->resetTwoFactorCode();
            return response()->json([
                'message' => 'Successfully Verified',
                'user' => new UserResource($user),
            ], 201);
        }

        return response()->json(['message' => 'Invalid code'], 400);

    }

}
