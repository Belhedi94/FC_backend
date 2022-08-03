<?php

namespace App\Http\Controllers;

use App\Http\Helpers;
use App\Http\ResponseMessages;
use Illuminate\Http\Request;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Gate;
use App\Models\User;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Storage;
use App\Rules\MobileNumberRule;
use Symfony\Component\HttpFoundation\Response;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return UserResource::collection(User::all())->response()->setStatusCode(200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $user = Helpers::doesItExist(User::class, $id);
        if ($user)
            return (new UserResource(User::findOrfail($id)))->response()->setStatusCode(200);

        return response()->json([
            'message' => ResponseMessages::NOT_FOUND
        ], Response::HTTP_NOT_FOUND);
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $user = Helpers::doesItExist(User::class, $id);
        if ($user) {
            if (! Gate::allows('update-user', $id)) {
                return response()->json([
                    'message' => ResponseMessages::FORBIDDEN
                ], Response::HTTP_FORBIDDEN);
            }

            $request['mobile_number'] = Helpers::normalizeMobileNumber($request->mobile_number);
            $fields = $request->validate([
                'full_name' => 'required|max:30',
                'email' => ['required', 'email', Rule::unique('users')->ignore($id)],
                'password' => ['required', 'confirmed',
                    Password::min(8)
                        ->letters()
                        ->mixedCase()
                        ->numbers()
                        ->symbols()->uncompromised()],
                'mobile_number' => ['required', new MobileNumberRule, Rule::unique('users')->ignore($id)],
                'role_id' => Rule::in([1, 2, 3, 4]),
            ]);

            $fields['password'] = bcrypt($fields['password']);
            if(isset($request['is_active'])) {
                $fields['is_active'] = 1;
            }
            $user->update($fields);


            return (new UserResource($user))->response()->setStatusCode(200);
        }

        return response()->json([
            'message' => ResponseMessages::NOT_FOUND
        ], Response::HTTP_NOT_FOUND);


    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id) {
        $user = Helpers::doesItExist(User::class, $id);
        if (isset($user)) {
            User::destroy($id);
            return response()->json([
                'message' => ResponseMessages::SUCCESSFULLY_DELETED
            ], Response::HTTP_OK);
        }

        return response()->json([
            'message' => ResponseMessages::FORBIDDEN
        ], Response::HTTP_FORBIDDEN);


    }

    public function confirmUserByPhone(Request $request) {
        $user = User::findOrFail($request->id);
        $user->is_active = 1;
        $user->save();
        return (new UserResource($user))->response()->setStatusCode(201);

    }




}
