<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
class AuthController extends Controller
{
    public function logout( Request $request ) {

       $token = $request->header('Authorization');

        try {
            JWTAuth::parseToken()->invalidate( $token );

            return response()->json( [
                'status'   => 200,
                'message' => trans( 'auth.logged_out' )
            ] );
        } catch ( TokenExpiredException $exception ) {
            return response()->json( [
                'status'   => 401,
                'message' => trans( 'auth.token.expired' )

            ], 401 );
        } catch ( TokenInvalidException $exception ) {
            return response()->json( [
                'status'   => 401,
                'message' => trans( 'auth.token.invalid' )
            ], 401 );

        } catch ( JWTException $exception ) {
            return response()->json( [
                'status'   => 500,
                'message' => trans( 'auth.token.missing' )
            ], 500 );
        }
    }
}
