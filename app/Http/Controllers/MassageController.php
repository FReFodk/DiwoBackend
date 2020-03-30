<?php

namespace App\Http\Controllers;
use App\Messages;
use App\User;
use App\Push_notification;
use App\ExperienceLikes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use DB;

class MassageController extends Controller
{
   public function getUserMessages()
   {
      $user = JWTAuth::user();
        $user_id = $user->user_id;
        $messages = Messages::where(['sender_id'=>$user_id])->orWhere(['receiver_id'=>$user_id])->latest()->get();
        $messages_array = [];
        $old_message_count  = 0;
        if(!empty($messages)){
          $old_message_count = $messages->count();
          foreach($messages as $msg){
           // print_r($msg->receiver->first_name);
            $m = $msg;
            if($user_id==$m->sender_id){
              $m->message_type = 'sent';
            }
            if($user_id==$m->receiver_id){
              $m->message_type = 'received';
            }
           //$m->message_type = 'sent';
            $user_sender = \DB::table('users')->select(array('first_name', 'last_name'))->where('user_id', '=', $m->sender_id)->first();
            $user_receiver = \DB::table('users')->select(array('first_name', 'last_name'))->where('user_id', '=', $m->receiver_id)->first();
            $m->sender_name =$user_sender->first_name.' '.$user_sender->last_name;
            $m->receiver_name =$user_receiver->first_name.' '.$user_receiver->last_name;
            $msg->sender->first_name;
            $msg->receiver->first_name;

            unset($m->receiver);
            unset($m->sender);
            //$m->sender_lastname = $msg->sender->last_name;
            $messages_array[] = $m;
          }
        }

        //die;
        $status = 200;
        return response()->json(compact('messages_array','old_message_count','status'));
   }
   public function getLastMessageReadStatus()
   {
      $user = JWTAuth::user();
        $user_id = $user->user_id;
        $last_messages = Messages::where(['receiver_id'=>$user_id])->where('is_read','=',0)->latest()->get();


        $read_status = false;
        $unread_messages  = [];
        if(!empty($last_messages)){
          foreach($last_messages as $message){
            $message->read_status = false;
            if($message->is_read == 1){
              $message->read_status = true;
            }
            $unread_messages[] =$message;
        }
        }
        $status = 200;
        $length = count($last_messages);
        return response()->json(compact('unread_messages','status','length'));
    }
   public function checkForNewMessages(Request $request)
   {
      $old_message_count = $request->old_message_count;
      $user = JWTAuth::user();
        $user_id = $user->user_id;
        $messages_count = Messages::where(['sender_id'=>$user_id])->orWhere(['receiver_id'=>$user_id])->latest()->count();
        if($messages_count != $old_message_count){
          $msg = "New messages found.";
          $new_message_count = $messages_count;
        }
        else{
          $msg = "No any new messages found.";
          $new_message_count = $old_message_count;
        }
        $status = 200;
        return response()->json(compact('msg','new_message_count','status'));
   }
   public function getSingleUserMessages($user_id)
   {
        $user = JWTAuth::parseToken()->authenticate();
       // $user_id = $user->user_id;
        $messages = Messages::where(['sender_id'=>$user_id])->orWhere(['receiver_id'=>$user_id])->latest()->get();
        $messages_array = [];
        if(!empty($messages)){
          foreach($messages as $msg){
           // print_r($msg->receiver->first_name);
            $m = $msg;
            if($user_id==$m->sender_id){
              $m->message_type = 'sent';
            }
            if($user_id==$m->receiver_id){
              $m->message_type = 'received';
            }
           //$m->message_type = 'sent';
            $m->sender_name = $msg->sender->first_name.' '.$msg->sender->last_name;
            $m->receiver_name = $msg->receiver->first_name.' '.$msg->sender->receiver;
            $msg->sender->first_name;
            $msg->receiver->first_name;

            unset($m->receiver);
            unset($m->sender);
            //$m->sender_lastname = $msg->sender->last_name;
            $messages_array[] = $m;
          }
        }

        //die;
        $status = 200;
        return response()->json(compact('messages_array','status'));
   }
   public function sendMessage(Request $request)
   {
    
      $user = JWTAuth::parseToken()->authenticate();
        $user_id = $user->user_id;
        $validator = Validator::make($request->all(), [
          'receiver_id' => 'required',
          'title' => 'required',
          'message' => 'required'
        ]);

        if($validator->fails()){
                return response()->json($validator->errors()->toJson(), 400);
        }
        $receivers_data = [];
        $receivers = [];
        if(!empty($request->get('receiver_id')))
        {
          $receiver_ids = $request->get('receiver_id');
          $receivers = explode(",", $receiver_ids);
        }
        if(is_array($receivers) && !empty($receivers)) 
        {
          $url = "https://fcm.googleapis.com/fcm/send";
          $serverKey = "AAAABD8iU50:APA91bGrx1Xe66WiNnWL0sYpmk_6ZK3TYaFsg2Vw5sT66Jz_ksjPTdkxe24XJhAhnRDqWV7_QKOo1ugkALfGpRgTzKwSQPuMQjPmxdlSxmZRTirG6kFoUYGBY_pOedfLzC9fudimC0dt";
          
            if(count($receivers))
            {
              foreach($receivers as $receiver_id){

                $insert = Messages::create([
                    'sender_id' => $user_id,
                    'receiver_id' => $receiver_id,
                    'title' => $request->get('title'),
                  'message' => $request->get('message')
                ]);
                $receivers_data[] = $insert;
              }
              foreach ($receivers as $key => $value) 
              {
                $push_data = Push_notification::where("user_id",$value)->get();
                $push_id = $push_data->pluck("push_id")->toArray();
                $user_badge = DB::table('messages')->where("receiver_id",$value)->where('is_read','0')->count();
                $token = $push_id;
                $title = $request->get('title');
              $body = $request->get('message');
              $notification = array('title' =>$title , 'body' => $body,'content_available' => true,'sound' => 'default','priority' => 'high','badge' =>$user_badge);
              $data = array('title' =>$title , 'body' => $body,'content_available' => true,'sound' => 'default','priority' => 'high','badge' => $user_badge);
              $arrayToSend = array('registration_ids' => $token, 'notification' => $notification,'data' => $data);
              $json = json_encode($arrayToSend);$headers = array();
              $headers[] = 'Content-Type: application/json';
              $headers[] = 'Authorization: key='. $serverKey;
              $ch = curl_init();
              curl_setopt($ch, CURLOPT_URL, $url);
              curl_setopt($ch, CURLOPT_CUSTOMREQUEST,"POST");
              curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
              curl_setopt($ch, CURLOPT_HTTPHEADER,$headers);
              curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
              $response = curl_exec($ch);
              if ($response === FALSE) 
              {
              }
          }
        }
          
        }

        $message_data = Messages::where(['sender_id'=>$user_id])->get();
        $status = 200;

        return response()->json(compact('receivers_data','status'));
   }
   public function deleteSingleUserMessage(Request $request){
      $user = JWTAuth::parseToken()->authenticate();
        $user_id = $user->user_id;
        $validator = Validator::make($request->all(), [
          'message_id' => 'required',
        ]);

        if($validator->fails()){
                return response()->json($validator->errors()->toJson(), 400);
        }
        $message_id = $request->message_id;
        $message = Messages::where(['id'=>$message_id])->where(function ($query) use ($user_id){
              $query->where('sender_id', '=', $user_id)
                    ->orWhere('receiver_id', '=', $user_id);
          })->first();
        if(!empty($message)){
          $message->delete();
          $msg = 'Message deleted.';
        }
        else{
          $msg = 'Message not found.';
        }
        $status = 200;
        return response()->json(compact('msg','status'));
   }
   public function getSingleUserMessage(Request $request){
      $user = JWTAuth::parseToken()->authenticate();
        $user_id = $user->user_id;
        $validator = Validator::make($request->all(), [
          'message_id' => 'required',
        ]);
        $message_details = [];
        if($validator->fails()){
                return response()->json($validator->errors()->toJson(), 400);
        }
        $message_id = $request->message_id;
        $message = Messages::where(['id'=>$message_id])->where(function ($query) use ($user_id){
              $query->where('sender_id', '=', $user_id)
                    ->orWhere('receiver_id', '=', $user_id);
          })->first();
        if(!empty($message)){
          $message_details['title'] = $message->title;
          $message_details['message'] = $message->message;
          $msg = 'Message Found.';
        }
        else{
          $msg = 'Message not found.';
        }
        $status = 200;
        return response()->json(compact('message_details','msg','status'));
   }
   public function readSingleMessage(Request $request)
   {
      $user = JWTAuth::parseToken()->authenticate();
      $user_id = $user->user_id;
      $validator = Validator::make($request->all(), [
        'message_id' => 'required',
      ]);
      if($validator->fails()){
              return response()->json($validator->errors()->toJson(), 400);
      }
      $message_id = $request->message_id;
      $message_details = [];
      $message = Messages::where(['id'=>$message_id,'receiver_id'=>$user_id])->first();
      if(!empty($message))
      { 
          $message_details = $message;
          $message->is_read = 1;
          $message->save();
          $msg = "Message has been read.";        
      } 
      else{
        $msg = "Requested message not found.";
      }
      $status = 200;
      return response()->json(compact('msg','status'));
   }
   public function add_notification_data(Request $request)
   {
      try
      {
        $data = DB::table('push_notifications')->where("udid",$request->get("udid"))->get();
        if(!count($data))
        {
          $insert = Push_notification::insert($request->all());
          return response()->json([
                     'status' => 'success',
                     'data' => "Insert successful!!"
                 ],201);
        }
        else
        {
          $update = Push_notification::find($data[0]->id);
          $update->push_id = $request->get("push_id");
          $update->save();
          return response()->json([
                     'status' => 'fail',
                     'data' => "Alredy Addded"
                 ],201);
        }
      }
      catch(\Exception $e)
        {
            return response()->json([
                    'status' => 'fail',
                    'message' => $e->getMessage()
                ],201);
        }
   }
   public function edit_notification_data(Request $request)
   {
      $user = JWTAuth::parseToken()->authenticate();
      try
      {
        $data = Push_notification::where("udid",$request->get("udid"))->get();
        if(count($data))
        {
          $update = Push_notification::find($data[0]->id);
          $update->user_id =$user->user_id;
          if($update->save())
          {
            return response()->json([
                      'status' => 'success',
                      'data' => "Update successful!!"
                  ],201);
          }
          else
          {
            return response()->json([
                      'status' => 'fail',
                      'data' => "Not update"
                  ],201); 
          }
        }
        else
        {
          return response()->json([
                      'status' => 'fail',
                      'data' => "No data found"
                  ],201); 
        }
      }
      catch(\Exception $e)
        {
            return response()->json([
                    'status' => 'fail',
                    'message' => $e->getMessage()
                ],201);
        }
   }
   public function delete_notification_data(Request $request)
   {
      try
      {
        $data = Push_notification::where("udid",$request->get("udid"))->get();
        if(count($data))
        {
            $user_data = Push_notification::find($data[0]->id);
            $user_data->user_id = null;
            $user_data->save();
            return response()->json([
                    'status' => 200,
                    'message' => 'Delete success'
                ],201);
        }
      }
      catch(\Exception $e)
        {
            return response()->json([
                    'status' => 'fail',
                    'message' => $e->getMessage()
                ],201);
        }
   }
   public function update_badge_data(Request $request)
   {

      $user = JWTAuth::parseToken()->authenticate();
      try
      {
        $data = Messages::where("receiver_id",$user->user_id)->where("is_read","0")->count();
        /*return response()->json($data);
        die;*/
          $url = "https://fcm.googleapis.com/fcm/send";
            $serverKey = "AAAABD8iU50:APA91bGrx1Xe66WiNnWL0sYpmk_6ZK3TYaFsg2Vw5sT66Jz_ksjPTdkxe24XJhAhnRDqWV7_QKOo1ugkALfGpRgTzKwSQPuMQjPmxdlSxmZRTirG6kFoUYGBY_pOedfLzC9fudimC0dt";
            $push_data = Push_notification::where("user_id",$user->user_id)->get();

              if(count($push_data))
              {
                foreach ($push_data as $key => $value) 
                {
                  $user_badge = DB::table('messages')->where("receiver_id",$value->user_id)->where('is_read','0')->count();
                  $token[] = $value->push_id;
                  $title = $request->get('title');
                $body = $request->get('message');
                $notification = array('badge' =>$user_badge);
                $data = array('badge' => $user_badge);
                $arrayToSend = array('registration_ids' => $token, 'notification' => $notification,'data' => $data);
                $json = json_encode($arrayToSend);$headers = array();
                $headers[] = 'Content-Type: application/json';
                $headers[] = 'Authorization: key='. $serverKey;
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST,"POST");
                curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
                curl_setopt($ch, CURLOPT_HTTPHEADER,$headers);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                $response = curl_exec($ch);
                if ($response === FALSE) 
                {
                  die('FCM Send Error: ' . curl_error($ch));
                }
                
            }
            return response()->json([
                       'status' => 200,
                       'message' => "Changed"
                   ],201);
            die;

          }
          else
          {
            return response()->json([
                        'status' => 'fail',
                        'data' => "No data found"
                    ],201); 
          }
      }
      catch(\Exception $e)
        {
            return response()->json([
                    'status' => 'fail',
                    'message' => $e->getMessage()
                ],201);
        }
   }
}
