<?php

namespace App\Http\Controllers\User;

use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\ApiController;

class UserController extends ApiController
{
    /***
     * 
     * 
     */

    public function register(request $request)
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
    /***
     * 
     * 
     */
    public function login()
    {
        return $this->successResponse("hola", 200);
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
