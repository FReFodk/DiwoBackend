<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
Route::post('register', 'UserController@register');
Route::post('login', 'UserController@authenticate');
Route::post('logout', 'AuthController@logout');
//Route::post('forgetPassword', 'UserController@forgetPassword');
Route::post('forgetPassword', 'UserController@forgetPassword');

Route::group(['middleware' => ['jwt.verify']], function() {
    Route::get('getUserInfoCp', 'UserController@getUserInfoCp');
    Route::post('team', 'TeamController@getTeam');
    Route::post('role', 'RoleController@getRoles');
    Route::post('user', 'UserController@getAuthenticatedUser');
    Route::post('getUserInfo', 'UserController@getUserInfo');
    Route::post('getAllUserInfo', 'UserController@getAllUserInfo');
    Route::post('userExperience', 'ExperienceController@getUserExperience');
    Route::post('authenticatedUserExperience', 'ExperienceController@authenticatedUserExperience');
    Route::get('addExpLikes/{id}', 'ExperienceController@addLikes');
    Route::get('expDislike/{id}', 'ExperienceController@disLikes');
    Route::post('addExperience', 'ExperienceController@addExperience');
    Route::post('addWorkJoy', 'WorkJoyController@addWorkJoy');
    Route::post('lastAddedWorkJoy', 'WorkJoyController@getlastAddedWorkJoy');
    Route::post('latestWorkJoy', 'WorkJoyController@getLatestWorkJoy');
    Route::post('lastRecordsWorkJoy', 'WorkJoyController@getLastRecordsWorkJoy');
    Route::post('updateCommentsWorkJoy', 'WorkJoyController@updateCommentsWorkJoy');
    Route::post('addSocialKapital', 'SocialKapitalController@addSocialKapital');
    Route::post('lastAddedSocialkapital', 'SocialKapitalController@getlastAddedSocialkapital');
    Route::post('latestSocialkapital', 'SocialKapitalController@getLatestSocialkapital');
    Route::post('userMessages','MassageController@getUserMessages');
    Route::post('sendMessage','MassageController@sendMessage');
    Route::post('deleteSingleMessage','MassageController@deleteSingleUserMessage');
    Route::post('getSingleMessage','MassageController@getSingleUserMessage');
    Route::post('getSingleExperienceDetails','ExperienceController@getSingleExperience');
    Route::post('readSingleMessage','MassageController@readSingleMessage');
    Route::post('checkForNewMessages','MassageController@checkForNewMessages');
    Route::post('getLastMessageReadStatus','MassageController@getLastMessageReadStatus');
    Route::post('edit_notification','MassageController@edit_notification_data');
});
    Route::post('add_notification','MassageController@add_notification_data');