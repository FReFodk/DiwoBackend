<?php

    namespace App\Http\Controllers;

    use App\User;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\Hash;
    use Illuminate\Support\Facades\Validator;
    use JWTAuth;
    use Tymon\JWTAuth\Exceptions\JWTException;
     use Mail;
     use DateTime;
     use Carbon\Carbon;
    class UserController extends Controller
    {
        public function authenticate(Request $request)
        {
            $credentials = $request->only('email', 'password');

            try {
                if (! $token = JWTAuth::attempt($credentials)) {
                    return response()->json(['error' => 'invalid_credentials','status' => 400], 400);
                }
            } catch (JWTException $e) {
                return response()->json(['error' => 'could_not_create_token','status' => 500], 500);
            }
            $where = array('email' => $request->email);
            
            
            $user = user::where($where)->get();
            $user1 = user::find($where)->first();
            $now = Carbon::now();
            
            $user1->is_prev_logged_id = $now->format('Y-m-d H:i:s');
            $user1->save();
            $status = 200;
            return response()->json(compact('user','token','status'));
        }

        public function register(Request $request)
        {
                $validator = Validator::make($request->all(), [
                'first_name' => 'required|string|max:255',
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|confirmed',
            ]);

            if($validator->fails()){
                    return response()->json($validator->errors()->toJson(), 400);
            }

            $user = User::create([
                'role_id' => $request->get('role_id'),
                'team_id' => $request->get('team_id'),
                'first_name' => $request->get('first_name'),
                'last_name' => $request->get('last_name'),
                'email' => $request->get('email'),
                'password' => Hash::make($request->get('password')),
                'hours_hired' => $request->get('hours_hired'),
            ]);
            $status = 200;

            //$token = JWTAuth::fromUser($user);

            //return response()->json(compact('user','token'),201);
            return response()->json(compact('user','status'),201);
        }

        public function getAuthenticatedUser()
        {
           try {

            if (! $user = JWTAuth::parseToken()->authenticate()) {
                    return response()->json(['error' => 'user_not_found' ,'status' => 404], 404);
            }

            } catch (Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {

                    return response()->json(['error' => 'token_expired' ,'status' => $e->getStatusCode()], $e->getStatusCode());

            } catch (Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {

                    return response()->json(['error' => 'token_invalid' ,'status' => $e->getStatusCode()], $e->getStatusCode());

            } catch (Tymon\JWTAuth\Exceptions\JWTException $e) {

                    return response()->json(['error' => 'token_absent' ,'status' => $e->getStatusCode()], $e->getStatusCode());

            }
            $status = 200;
            return response()->json(compact('user','status'));
        }

         public function getUserInfoCp(){
            echo 2;die;
         }
         public function getUserInfo()
         {
            $user = JWTAuth::user();
            $user_id = $user->user_id;
            $users = \DB::table('users')
                ->join('role', 'users.role_id', '=', 'role.id')
                ->join('team', 'users.team_id', '=', 'team.id')
                ->select('users.user_id','users.first_name','users.last_name','users.email','users.hours_hired', 'role.role_name', 'team.team_name')
                ->where('users.user_id', '=', $user_id)
                ->get();
             $status = 200;
            return response()->json(compact('users','status'));
        }

        public function getAllUserInfo()
         {            
            $user = JWTAuth::user();
            $user_id = $user->user_id;
            $user = \DB::table('users')->select('team_id')->where('user_id', '=', $user_id)->get();
           // echo "user=".$user_id; exit;
            //print_r($user[0]->team_id); exit;
            $user_teams=\DB::table('users')->select('user_id')->where('team_id', '=', $user[0]->team_id)->where('user_id', '!=', $user_id)->get();
            //$idCats = array_column($user_teams, 'user_id');
            //$catIds = array_map(create_function('$user_teams', 'return $user_teams->id;'), $objects);
           
            $usr_list=array();
            foreach ($user_teams as $user) {
                 //var_dump($user->user_id);
                $usr_list[]=$user->user_id;
                
            }
             //echo "<pre>"; print_r($usr_list); exit;
             $users = \DB::table('users')
                ->join('role', 'users.role_id', '=', 'role.id')
                ->join('team', 'users.team_id', '=', 'team.id')
                ->select('users.user_id','users.first_name','users.last_name', 'role.role_name', 'team.team_name')  
                 //->where('users.user_id', '=', $usr)   
                 ->whereIn('users.user_id', $usr_list)    
                ->get();
            //print_r($user_team_id); exit;
            /*$users = \DB::table('users')
                ->join('role', 'users.role_id', '=', 'role.id')
                ->join('team', 'users.team_id', '=', 'team.id')
                ->select('users.user_id','users.first_name','users.last_name', 'role.role_name', 'team.team_name')  
                 ->where('users.user_id', '!=', $user_id)       
                ->get();*/
             $status = 200;
            return response()->json(compact('users','status'));
        }

  
        public function forgetPassword(Request $request)
        {
            try {                
            
                $validator = Validator::make($request->all(), [               
                    'email' => 'required|email|max:255',               
                ]);

                if($validator->fails()){
                        return response()->json($validator->errors()->toJson(), 400);
                }
                $email=$request->get('email');
                $user_data = \DB::table('users')->select('first_name')->where('email', $email)->first();            
                $exists = \DB::table('users')->where('email', $email)->first();

                if($exists) {
                    //$new_password = Hash::make(str_random(8));
                    $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%";
                    $new_password = substr( str_shuffle( $chars ), 0, 8 );               
                    $data = [
                                           'password' => $new_password,
                                           'name' => $user_data->first_name,

                                        ];
                    //$data = array('password'=>$new_password);
                    Mail::send('password',["data1"=>$data], function($message) use ($email) {
                           // $message->to("bg@logixbuilt.com")->subject('New password');
                            $message->to($email)->subject('New password');
                            $message->from('support@diwo.nu','Diwo');
                        }); 
                    if(count(Mail::failures()) >0) {
                       echo "There was one or more failures. They were: <br />";
                       foreach(Mail::failures as $email) {
                           echo " - $email <br />";
                        }
                    } else {
                        $password_new= Hash::make($new_password);
                       \DB::update('update users set password = ? where email = ?',[$password_new,$email]);
                        $message="New password sent to your Email Id";
                        $status = 200;
                    }  
                     
                    
                } else {
                     $status = 404;
                     $message="Invalid email Id,please try again.";
                }
                return response()->json(compact('message','status'));
            }
            catch (\Exception $e) {
                return response($e);
            }

        }

    }