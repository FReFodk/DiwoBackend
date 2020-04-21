<?php

namespace App\Http\Controllers;

use App\SocialKapital;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use JWTAuth;
use Carbon\Carbon;
use Tymon\JWTAuth\Exceptions\JWTException;
use App\User;

class SocialKapitalController extends Controller
{
    public function addSocialKapital(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|string|max:255',
            'review_date' => 'required|string|max:255',
              //'review' => 'required|string|max:255',
            'comment' => 'max:255',
            'question1' => 'required|string|max:255',
            'question2' => 'required|string|max:255',
            'question3' => 'required|string|max:255',
            'question4' => 'required|string|max:255',
            'question5' => 'required|string|max:255',
        ]);

        if($validator->fails()){
                return response()->json($validator->errors()->toJson(), 400);
        }

        $insert = SocialKapital::create([
            'user_id' => $request->get('user_id'),
            'review_date' => $request->get('review_date'),
            //'review' => $request->get('review'),
            'comment' => $request->get('comment'),
            'question1' => $request->get('question1'),
            'question2' => $request->get('question2'),
            'question3' => $request->get('question3'),
            'question4' => $request->get('question4'),
            'question5' => $request->get('question5'),
            'last_review_date' => $request->get('last_review_date'),
        ]);

        $user = JWTAuth::parseToken()->authenticate();
        $where = ['user_id' => $user->user_id];
        $where1 = array('user_id' => $user->user_id);
        $user1 = user::where($where1)->first();
        $now = Carbon::now();
        $user1['last_added_social_kapital'] = $now;
        $user1->save();
        $kapital_data = SocialKapital::where($where)->get();
        $status = 200;

        return response()->json(compact('kapital_data','status'));
    }
    public function getlastAddedSocialkapital()
    {
            $user = JWTAuth::parseToken()->authenticate();
            $where = ['user_id' => $user->user_id];
            $kapital_data = SocialKapital::where($where)->orderBy('id', 'DESC')->limit(1)->get();
            $status = 200;
            return response()->json(compact('kapital_data','status'));
    }

    public function getLatestSocialkapital()
    {
            $user = JWTAuth::parseToken()->authenticate();
            $where = ['user_id' => $user->user_id];
            $kapital_data = SocialKapital::where($where)->orderBy('id', 'DESC')->limit(3)->get();
            $status = 200;
            return response()->json(compact('kapital_data','status'));
    }
}
