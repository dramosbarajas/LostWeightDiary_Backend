<?php

namespace App\Http\Controllers\User;

use App\User;
use Carbon\Carbon;
use App\PassRecover;
use Illuminate\Http\Request;
use App\Events\UserRegistered;
use App\Events\recoverPassword;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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

        //Reglas de validación
        $reglas = [
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6|confirmed',
            'age' => 'required|integer|min:10|max:99'
        ];

        //Validamos los datos del request 
        $this->validate($request, $reglas);

        //Recuperamos los campos de la petición y seteamos los valores.
        $campos = $request->all();
        $campos['password'] = Hash::make($campos['password']); // Hash de la contraseña
        $campos['isVerified'] = User::USUARIO_NO_VERIFICADO;
        $campos['verification_token'] = User::generateToken(); //Generamos el token de verificación
        //Guardamos el nuevo usuario 
        $usuario = User::create($campos);
        //Lanzamos el evento con el token de verificación de la cuenta
        event(new UserRegistered($usuario));

        return $this->showOne($usuario, 201);
    }

    /**
     * login
     *
     * @param  mixed $request
     * @return void
     */
    public function login(Request $request)
    {
        //Reglas de validación
        $reglas = [
            'email' => 'required|email',
            'password' => 'required|min:6',
        ];

        //Validamos los datos del request 
        $this->validate($request, $reglas);

        //Array que utilizaremos para validar la autenticación del usuario 
        $credentials = [
            'email' => $request->email,
            'password' => $request->password,
            'deleted_at' => null
        ];
        //Comprobamos la autenticación del usuario
        if (Auth::attempt($credentials)) {
            //Comprobamos si el usuario no ha sido verificado, enviamos nuevamente el correo.
            if (!Auth::user()->isVerified) {
                $usuario = Auth::user();
                $usuario->verification_token = User::generateToken();
                $usuario->save();
                event(new UserRegistered($usuario));
                return $this->errorResponse("Parece que no has confirmado tu registro, te hemos enviado nuevamente un correo electrónico.", 200);
            }

            //Generamos el token de acceso 
            $user = Auth::user();
            $tokenResult = $user->createToken('Personal Access Token');
            $token = $tokenResult->token;
            $token->save();
            return $this->successResponse([
                'access_token' => $tokenResult->accessToken,
                'token_type' => 'Bearer',
                'User' => $user->name
            ], 200);
        } else {
            return $this->errorResponse("Autenticación incorrecta", 401);
        }
    }

    /**
     * forgotPassword
     * Metodo para que un usuario registrado pueda solicitar un reseteo de contraseña.
     * @param  mixed $correo
     * @return void
     */
    public function forgotPassword($correo)
    {
        //Recibimos el correo y consultamos con base de datos 
        $usuario = User::where('email', $correo)->firstOrFail();

        //Creamos una nueva instancia para generar los valores del cambio de contraseña
        $passRecover = new PassRecover();
        $passRecover->user_id = $usuario->id; //Almacenamos el usuario que lo solicita.
        $passRecover->token_pass = str_random(100); //Generamos un token para el cambio
        $passRecover->save();

        //Lanzamos el evento con el token para cambiar la contraseña y retornamos la respuesta
        event(new recoverPassword($usuario, $passRecover));
        return $this->successResponse("Te hemos enviado un correo electrónico para recuperar tu cuenta.", 200);
    }


    /**
     * recoverPassword
     *
     * @param  mixed $request
     * @return void
     */
    public function recoverPassword(Request $request)
    {
        //Validamos los datos que recibimos
        $reglas = [
            'key' => 'required',
            'password' => 'required|min:6|confirmed',
        ];

        $this->validate($request, $reglas);

        //Recuperamos a que usuario corresponde el token 
        $filaCambioClave = PassRecover::where('token_pass', $request->key)->firstOrFail();
        $usuario = User::find($filaCambioClave->user_id);
        //Comprobamos si la contraseña de cambio no ha sido nunca utilizada para esa cuenta
        if (Hash::check($request->password, $usuario->password)) {
            return $this->errorResponse("Esta contraseña ya ha sido utilizada con anterioridad", 401);
        } else {
            $usuario->password = Hash::make($request->password); //Ciframos la contraseña
            $filaCambioClave->changed_on = Carbon::now(); //Huella fecha del cambio
            $filaCambioClave->token_pass = null; //Eliminamos el token por seguridad
            //Guardamos los cambios
            $filaCambioClave->save();
            $usuario->save();

            //Revocamos todos los token de sesion del usuario
            $this->revokeAllTokensUser($usuario);
            //Retornamos la respuesta 
            return $this->successResponse("Cambio de contraseña realizado", 200);
        }
    }

    /**
     * verifyToken
     * Función para verificar la cuenta de un usuario.
     * @param  mixed $token
     * @return void
     */
    public function verifyToken($token)
    {
        //Buscamos el usuario con el token recibido en la petición 
        $user = User::where('verification_token', $token)->firstOrFail();
        //Setemos los valores 
        $user->isVerified = User::USUARIO_VERIFICADO;
        $user->verification_token = null;
        $user->email_verified_at = Carbon::now();
        $user->save();
        //Retornamos la respuesta
        return $this->showMessage('La cuenta ha sido verificada');
    }

    /**
     * logout
     * Desconexión de un usuario
     * @return void
     */
    public function logout()
    {
        $user = Auth::user()->token(); //Recuperamos el token del usuario activo
        $user->revoke(); //Revocamos
        return $this->showMessage('Usuario desconectado correctamente.', 204); //Retornamos
    }

    /**
     * changePassword
     * Logica para el cambio de contraseña de un usuario autenticado
     * @param  mixed $request
     * @return void
     */
    public function changePassword(Request $request)
    {
        //Validamos los datos recibidos.
        $reglas = [
            'old_password' => 'required',
            'password' => 'required|min:6|confirmed',
        ];

        $this->validate($request, $reglas);
        //Recuperamos la instancia del usuario
        $usuario = Auth::user();
        //Comprobamos si la contraseña antigua es correcta para permitir el cambio.
        if (!Hash::check($request->old_password, $usuario->password)) {
            return $this->errorResponse("La contraseña antigua no es correcta", 401);
        } else {
            //Comprobamos que la nueva contraseña no haya sido utilizada nunca.
            if ($request->old_password == $request->password) {
                return $this->errorResponse("No puedes utilizar la misma contraseña que la actual", 401);
            }
            $usuario->password = Hash::make($request->password); //Ciframos y guardamos
            $usuario->save();
            //Revocamos todos los token del usuario que realiza el cambio 
            $this->revokeAllTokensUser($usuario);
            return $this->successResponse("Cambio de contraseña realizado", 200);
        }
    }

    public function getUserDetails()
    {

        $usuario = User::where('id', Auth::id())->with('measures')->get();
        return $this->showAll($usuario, 200);
    }

    /**
     * revokeAllTokensUser
     * Revoca todos los tokens de un determinado usuario
     * @param  mixed $user
     * @return void
     */
    private function revokeAllTokensUser(User $user)
    {
        $userTokens = $user->tokens;
        foreach ($userTokens as $token) {
            $token->revoke();
        }
        return true;
    }
}
