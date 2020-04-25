<?php

namespace App\Http\Controllers\User;

use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Events\UserRegistered;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\ApiController;

class UserController extends ApiController
{


    /**
     * register
     * Logica para el registro de un usuario
     * @param  mixed $request
     * @return void
     */
    public function register(Request $request)
    {
        //TODO 
        // cambiar el tamaño para pro de password

        $reglas = [
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6|confirmed',
            'age' => 'required|integer|min:10|max:99'
        ];

        $this->validate($request, $reglas);

        $campos = $request->all();
        $campos['password'] = Hash::make($campos['password']); // Hash de la contraseña
        $campos['isVerified'] = User::USUARIO_NO_VERIFICADO;
        $campos['verification_token'] = User::generateToken(); //Generamos el token de verificación

        $usuario = User::create($campos);

        event(new UserRegistered($usuario));

        return $this->showOne($usuario, 201);
    }

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');
        $credentials = [
            'email' => $request->email,
            'password' => $request->password,
            'deleted_at' => null
        ];
        if (Auth::attempt($credentials)) {
            //Comprobamos si el usuario ya fue verificado.
            if (!Auth::user()->isVerified) {
                //TODO 
                //Reenviamos el correo de la verificación.

                return $this->errorResponse("Autenticación correcta", 200);
            }

            //Generar el token
            $user = $request->user();
            $tokenResult = $user->createToken('Personal Access Token');
            $token = $tokenResult->token;
            $token->save();
            return $this->successResponse([
                'access_token' => $tokenResult->accessToken,
                'token_type' => 'Bearer',
                'User' => $user->name
            ], 200);
        }
    }
    /***
     * 
     * 
     */
    public function forgotPasword()
    {
    }

    /***
     * 
     * 
     */
    public function recoverPasword()
    {
    }

    public function verifyToken($token)
    {
        $user = User::where('verification_token', $token)->firstOrFail();
        $user->isVerified = User::USUARIO_VERIFICADO;
        $user->verification_token = null;
        $user->email_verified_at = Carbon::now();
        $user->save();
        return $this->showMessage('La cuenta ha sido verificada');
    }
}
