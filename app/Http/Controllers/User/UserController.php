<?php

namespace App\Http\Controllers\User;

use App\User;
use Illuminate\Http\Request;
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
        $campos['verified'] = User::USUARIO_NO_VERIFICADO;
        $campos['tokenVerified'] = User::generateToken(); //Generamos el token de verificación

        $usuario = User::create($campos);
        return $this->showOne($usuario, 201);
    }

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');
        $credentials = [
            'email' => $request->email,
            'password' => $request->password,
            'isVerified' => true,
            'deleted_at' => null
        ];
        if (Auth::attempt($credentials)) {
            return $this->errorResponse("Autenticación correcta", 200);
        }

        return $this->errorResponse("Se ha producido un error en la autenticación", 500);
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
}
