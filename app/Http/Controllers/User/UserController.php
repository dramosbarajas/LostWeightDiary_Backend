<?php

namespace App\Http\Controllers\User;

use App\Events\recoverPassword;
use App\User;
use Carbon\Carbon;
use App\PassRecover;
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
            //Comprobamos si el usuario no ha sido verificado, enviamos nuevamente el correo.
            if (!Auth::user()->isVerified) {
                $usuario = Auth::user();
                $usuario->verification_token = User::generateToken();
                $usuario->save();
                event(new UserRegistered($usuario));
                return $this->errorResponse("Parece que no has confirmado tu registro, te hemos enviado nuevamente un correo electrónico.", 200);
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
    public function forgotPassword($correo)
    {
        $usuario = User::where('email', $correo)->firstOrFail();
        $passRecover = new PassRecover();
        $passRecover->user_id = $usuario->id;
        $passRecover->token_pass = str_random(100);
        $passRecover->save();
        $evento = ['email' => $usuario->email, 'name' => $usuario->name, $passRecover->token_password];
        //Lanzamos el evento con el token para cambiar la contraseña 
        event(new recoverPassword($usuario, $passRecover));
        return $this->successResponse("Te hemos enviado un correo electrónico para recuperar tu cuenta.", 200);
    }

    /***
     * 
     * 
     */
    public function recoverPassword(Request $request)
    {
        $reglas = [
            'key' => 'required',
            'old_password' => 'required',
            'password' => 'required|min:6|confirmed',
        ];

        $this->validate($request, $reglas);
        $filaCambioClave = PassRecover::where('token_pass', $request->key)->first();
        $usuario = User::find($filaCambioClave->user_id);
        if (!Hash::check($request->old_password, $usuario->password)) {
            return $this->errorResponse("La contraseña antigua no es correcta", 401);
        } else {
            if ($request->old_password == $request->password) {
                return $this->errorResponse("La contraseña ya ha sido utilizada", 401);
            }
            $usuario->password = Hash::make($request->password);
            $filaCambioClave->changed_on = Carbon::now();
            $filaCambioClave->token_pass = null;
            $filaCambioClave->save();
            $usuario->save();
            return $this->successResponse("Cambio de contraseña realizado", 200);
        }
    }

    /**
     * verifyToken
     *
     * @param  mixed $token
     * @return void
     */
    public function verifyToken($token)
    {
        $user = User::where('verification_token', $token)->firstOrFail();
        $user->isVerified = User::USUARIO_VERIFICADO;
        $user->verification_token = null;
        $user->email_verified_at = Carbon::now();
        $user->save();
        return $this->showMessage('La cuenta ha sido verificada');
    }

    /**
     * logout
     *
     * @return void
     */
    public function logout()
    {
        $user = Auth::user()->token();
        $user->revoke();
        return $this->showMessage('Usuario desconectado correctamente.');
    }
}
