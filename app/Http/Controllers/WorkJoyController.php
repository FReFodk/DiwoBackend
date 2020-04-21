<?php

namespace App\Http\Controllers;

use App\WorkJoy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use App\User;
use Carbon\Carbon;


class WorkJoyController extends Controller
{
    public function addWorkJoy(Request $request)
    {
            $validator = Validator::make($request->all(), [
            'user_id' => 'required|string|max:255',
            'review_date' => 'required|string|max:255',
            'review' => 'required|string|max:255',
        ]);

        if($validator->fails()){
                return response()->json($validator->errors()->toJson(), 400);
        }

        $insert = WorkJoy::create([
            'user_id' => $request->get('user_id'),
            'review_date' => $request->get('review_date'),
            'review' => $request->get('review'),
            'last_review_date' => $request->get('last_review_date'),
        ]);
        $insertedId = $insert->id;
        $user = JWTAuth::parseToken()->authenticate();
        /*$where = ['user_id' => $user->user_id];
        $work_data = WorkJoy::where($where)->get();*/
        $last_inserted_id = $insertedId; 
        $status = 200;
        $user_id = $request->get('user_id');
        $where = array('user_id' => $user_id);
        $user1 = user::where($where)->first();
        $now = Carbon::now();
        $user1['last_added_workjoy'] = $now;
        $user1->save();

        return response()->json(compact('last_inserted_id','status'));
    }
    public function getlastAddedWorkJoy()
    {
            $user = JWTAuth::parseToken()->authenticate();
            $where = ['user_id' => $user->user_id];
            $workjoy_data = WorkJoy::where($where)->orderBy('id', 'DESC')->limit(1)->get();
            $status = 200;
            return response()->json(compact('workjoy_data','status'));
    }
    public function getLatestWorkJoy()
    {
            $user = JWTAuth::parseToken()->authenticate();
            $where = ['user_id' => $user->user_id];
            $workjoy_data = WorkJoy::where($where)->orderBy('id', 'DESC')->limit(3)->get();
            $status = 200;
            return response()->json(compact('workjoy_data','status'));
    }

    public function getLastRecordsWorkJoy()
    {
            $user = JWTAuth::parseToken()->authenticate();
            $where = ['user_id' => $user->user_id];
            $workjoy_data = WorkJoy::where($where)->orderBy('id', 'DESC')->limit(6)->get();
            $status = 200;
            return response()->json(compact('workjoy_data','status'));
    }

    public function updateCommentsWorkJoy(Request $request)
    {
          $validator = Validator::make($request->all(), [
            'id' => 'required|string|max:255',
            'comments' => 'required|string|max:255',
            
        ]);

         if($validator->fails()){
                return response()->json($validator->errors()->toJson(), 400);
        }
        $workjoy = WorkJoy::findOrFail($request->get('id'));
        $workjoy->comments = $request->get('comments');
        $workjoy->save();
        $user = JWTAuth::parseToken()->authenticate();
        $where = ['user_id' => $user->user_id];
        $work_data = WorkJoy::where($where)->get();
        $status = 200;
        return response()->json(compact('work_data','status'));

    }    
}
