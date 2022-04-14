<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


//Admin App Api

Route::post('login_admin',[AdminController::class,'login_admin']);
Route::post('create_game',[AdminController::class,'create_game']);
Route::get('start_game',[AdminController::class,'start_game']);
Route::post('send_new_question',[AdminController::class,'send_new_question']);
Route::get('stop_question_response',[AdminController::class,'stop_question_response']);
Route::post('send_answer',[AdminController::class,'send_answer']);
Route::post('finish_game',[AdminController::class,'finish_game']);
Route::post('get_users',[AdminController::class,'get_users']);
Route::get('get_winners_list/{type}',[AdminController::class,'get_winners_list']);
Route::post('get_withdrawal_requests',[AdminController::class,'get_withdrawal_requests']);
Route::post('update_withdrawal_status',[AdminController::class,'update_withdrawal_status']);
Route::post('add_user_money',[AdminController::class,'add_user_money']);
Route::post('block_unblock_user',[AdminController::class,'block_unblock_user']);
Route::post('save_default_options',[AdminController::class,'save_default_options']);
Route::post('save_prizes',[AdminController::class,'save_prizes']);
Route::get('get_prizes',[AdminController::class,'get_prizes']);
Route::get('get_default_options',[AdminController::class,'get_default_options']);


//User App Api

Route::post('login_user',[UserController::class,'login_user']);
Route::post('signup_user',[UserController::class,'signup_user']);
Route::post('update_profile',[UserController::class,'update_profile']);
Route::get('get_user_profile/{userId}',[UserController::class,'get_user_profile']);
Route::get('get_current_game',[UserController::class,'get_current_game']);
Route::get('get_prizes',[UserController::class,'get_prizes']);
Route::get('get_current_question',[UserController::class,'get_current_question']);
Route::post('mark_paid_prediction',[UserController::class,'mark_paid_prediction']);
Route::post('mark_free_prediction',[UserController::class,'mark_free_prediction']);
Route::post('add_payu_money',[UserController::class,'add_payu_money']);
Route::post('update_payment_status',[UserController::class,'update_payment_status']);
Route::post('submit_withdrawal_request',[UserController::class,'submit_withdrawal_request']);
Route::post('mark_paid_response',[UserController::class,'mark_paid_response']);
Route::post('mark_free_response',[UserController::class,'mark_free_response']);
Route::post('reset_password',[UserController::class,'reset_password']);