<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\ApiController;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class UserController extends ApiController
{
    /***
     * 
     * 
     */

    public function register()
    {
        return $this->errorResponse("hola", 200);
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
