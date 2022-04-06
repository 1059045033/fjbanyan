<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginUserRequest;
use App\Http\Requests\RegisterUserRequest;
use App\Models\User;
use GuzzleHttp\Client as Guzzle;
use Illuminate\Http\Request;

class RegisterController extends Controller
{
    private $http;
    private $form_params;


    public function __construct(Guzzle $http)
    {
        $this->http = $http;
        $this->form_params = config('auth.form_params');
    }

    public function register(RegisterUserRequest $request)
    {
        $user = User::create([
            'name'  => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password)
        ]);

        $this->form_params['username'] = $user->email;
        $this->form_params['password'] = $request->password;
        $response = $this->http->post(config('app.url').'/oauth/token',[
            'form_params' => $this->form_params,
        ]);

        $token = json_decode((string) $response->getBody(),true);
        return response()->json([
            'token'=>$token
        ],201);
    }

    public function login(LoginUserRequest $request)
    {
        $this->form_params['username'] = $request->username;
        $this->form_params['password'] = $request->password;
        $response = $this->http->post(config('app.url').'/oauth/token',[
            'form_params' => $this->form_params,
        ]);

        $token = json_decode((string) $response->getBody(),true);
        return response()->json([
            'token'=>$token
        ],200);
    }
}
