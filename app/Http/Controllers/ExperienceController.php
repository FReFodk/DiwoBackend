<?php

namespace App\Http\Controllers;
use App\Experience;
use App\ExperienceLikes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class ExperienceController extends Controller
{
    public function getUserExperience()
    {
            /*$user = JWTAuth::user();
            $user_id = $user->user_id;
            $experience = \DB::table("experience as e")
                ->select("e.*",
                        \DB::raw("(select count(*) from experience_likes where expr_id = e.id and user_id = ".$user_id.") as user_likes"),"u.user_name","u.team_name"
                       )
                ->leftJoin('view_user_details as u', 'e.user_id', '=', 'u.user_id')
                ->get();*/

             $user = JWTAuth::user();
            $user_id = $user->user_id;
            $user = \DB::table('users')->select('team_id')->where('user_id', '=', $user_id)->get();
            $user_teams=\DB::table('users')->select('user_id')->where('team_id', '=', $user[0]->team_id)->get();
            //$usr_list=array();
            foreach ($user_teams as $user) {
                 //var_dump($user->user_id);
                $usr_list[]=$user->user_id;
                
            }
         
            $experience = \DB::table("experience as e")
                ->select("e.*",
                        \DB::raw("(select count(*) from experience_likes where expr_id = e.id ) as user_likes"),"u.user_name","u.team_name"
                       )
                
                ->whereIn('e.user_id', $usr_list)
                ->leftJoin('view_user_details as u', 'e.user_id', '=', 'u.user_id')               
                ->get();
             $status = 200;
			return response()->json(compact('experience','status'));
    }
    public function addLikes($id = 0)
    {
            $user = JWTAuth::parseToken()->authenticate();
            $where = ['user_id' => $user->user_id, 'expr_id' => $id];
            $likes_data = ExperienceLikes::where($where)->get()->count();
            
            if ($likes_data > 0) {
                return response()->json(['already_liked'], 404);
            }
    		$exp_data = Experience::find($id);
    		if (empty($exp_data)) {
                    return response()->json(['data_not_found'], 404);
            }
            $update = ['total_likes' => ($exp_data->total_likes + 1)];
        	Experience::where('id',$id)->update($update);

        	$likes = Experience::find($id);
        	$total_likes = $likes->total_likes;
            $exp_likes = ExperienceLikes::create([
                'user_id' => $user->user_id,
                'expr_id' => $id,
            ]);
            $status = 200;
            return response()->json(compact('total_likes','status'));
    }
     public function disLikes($id = 0)
    {

            $user = JWTAuth::parseToken()->authenticate();
            $where = ['user_id' => $user->user_id, 'expr_id' => $id];
            $likes_data = ExperienceLikes::where($where)->get()->count();
            
            if ($likes_data <= 0) {
                return response()->json(['data_not_found'], 404);
            }
            $exp_data = Experience::find($id);
            $update = ['total_likes' => ($exp_data->total_likes - 1)];
            Experience::where('id',$id)->update($update);

            $where = ['user_id' => $user->user_id, 'expr_id' => $id];
            $res=ExperienceLikes::where($where)->delete();

            $status = 200;
            return response()->json(compact('status'));
    }

    public function addExperience(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|string|max:255',
            'experience' => 'required|string|max:255',
            'date' => 'required',
               


        ]);

        if($validator->fails()){
                return response()->json($validator->errors()->toJson(), 400);
        }
        if(empty($request->get('total_likes'))) {
            $total_likes=0;
        } else {
            $total_likes=$request->get('total_likes');
        }
        $insert = Experience::create([
            'user_id' => $request->get('user_id'),
            'experience' => $request->get('experience'),            
            'date' => $request->get('date'),
            'total_likes' => $total_likes,           
            
        ]);

        $user = JWTAuth::parseToken()->authenticate();
        $where = ['user_id' => $user->user_id];
        $exp_data = Experience::where($where)->get();
        $status = 200;

        return response()->json(compact('exp_data','status'));
    }

    public function authenticatedUserExperience()
    {
            $user = JWTAuth::user();
            $user_id = $user->user_id;
            $experience = \DB::table("experience as e")
                ->select("e.*",
                        \DB::raw("(select count(*) from experience_likes where expr_id = e.id and user_id = ".$user_id.") as user_likes"),"u.user_name","u.team_name"
                       )
                ->leftJoin('view_user_details as u', 'e.user_id', '=', 'u.user_id')
                 ->where('e.user_id', '=', $user_id)
                ->orderBy('e.id','desc')->get();
             $status = 200;
            return response()->json(compact('experience','status'));
    }
    public function getSingleExperience(Request $request)
   {
   	 	$user = JWTAuth::user();
        $user_id = $user->user_id;
        $experience_id = $request->experience_id;
        $validator = Validator::make($request->all(), [
	        'experience_id' => 'required',
	    ]);
	    if($validator->fails()){
	        return response()->json($validator->errors()->toJson(), 400);
	    }
        $experience_details = \DB::table("experience as e")
            ->select("e.*",
                    \DB::raw("(select count(*) from experience_likes where expr_id = e.id and user_id = ".$user_id.") as user_likes"),"u.user_name","u.team_name"
                   )
            ->where('e.id','=',$experience_id)
            ->where('e.user_id','=',$user_id)
            ->leftJoin('view_user_details as u', 'e.user_id', '=', 'u.user_id')
            ->get();
        $status = 200;
        return response()->json(compact('experience_details','status'));
   }     
}
