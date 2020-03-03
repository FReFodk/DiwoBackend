<?php

namespace App\Http\Controllers;
use App\Team;
use Illuminate\Http\Request;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class TeamController extends Controller
{
    public function getTeam()
    {
        $roles = Team::all();
        $status = 200;
        return response()->json(compact('roles','status'));
    }
}
