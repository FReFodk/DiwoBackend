<?php

namespace App\Http\Controllers;
use App\Role;
use Illuminate\Http\Request;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class RoleController extends Controller
{
    public function getRoles()
    {
            $roles = Role::all();
            $status = 200;
			return response()->json(compact('roles','status'));
    }
}
