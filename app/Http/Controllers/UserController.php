<?php

namespace App\Http\Controllers;

use App\Models\FreePredicationResponse;
use App\Models\FreeQuizResponse;
use App\Models\Game;
use App\Models\PaidPredicationResponse;
use App\Models\PaidQuizResponse;
use App\Models\Prize;
use App\Models\Question;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Withdrawal;
use Illuminate\Http\Request;

class UserController extends Controller
{
    //Api List

    //1. Login user
    //2. Signup user
    //3. Update profile
    //4. Get user profile
    //5. Get current game (socket)
    //6. get prizes
    //7. get winner list
    //8. Get current question (socket)
    //9. mark paid response
    //10. mark free response
    //11. add money payu
    //12. payu success/failure webhook
    //13. submit withdrawal request
    //14. submit paid prediction 
    //15. submit free prediction
    //16. 


    //1. Login user
    public function login_user(Request $request){
        $email=$request->email;
        $password=$request->password;
        $token=$request->token;

        $user=User::where('email',$email)
                    ->first();

        if(!$user){
            //account doesn't exist
            return response()->json(['status' => 'FAILED', 'code' => 'FC_01', 'data' => null]);  
        }

        if($user->password!=$password){
            //incorrect passwrod
            return response()->json(['status' => 'FAILED', 'code' => 'FC_02', 'data' => null]);  
        }

        $user->token=$token;
        $user->save();

        return response()->json(['status' => 'SUCCESS', 'code' => 'SC_01', 'data' => $user]);  
    }


    //2. Signup user
    public function signup_user(Request $request){
        $name=$request->name;
        $email=$request->email;
        $password=$request->password;
        $mobile=$request->mobile;
        $token=$request->token;

        $profile=null;

        $user=User::where('email',$email)
                    ->first();

        if($user){
            //user account already exist
            return response()->json(['status' => 'FAILED', 'code' => 'FC_01', 'data' => null]);  
        }
    
        if ($request->hasFile('profile')) {
            $image = $request->file('profile');
            $name = time() . '.' . $image->getClientOriginalExtension();
            $destinationPath = public_path('/User');
            $image->move($destinationPath, $name);
    
            $profile=$name;
        }

        $userData=[];
        $userData['name']=$name;
        $userData['email']=$email;
        $userData['password']=$password;
        $userData['mobile']=$mobile;
        $userData['token']=$token;
        $userData['profile']=$profile;

        $user=User::create($userData);

        return response()->json(['status' => 'SUCCESS', 'code' => 'SC_01', 'data' => $user]);  
    }


    //3. Update profile
    public function update_profile(Request $request){
        $name=$request->name;
        $userId=$request->user_id;
    


        $user=User::where('id',$userId)
                    ->first();

        if(!$user){
            //user account doesn't exist
            return response()->json(['status' => 'FAILED', 'code' => 'FC_01', 'data' => null]);  
        }

        $user->name=$name;
    
        if ($request->hasFile('profile')) {
            $image = $request->file('profile');
            $name = time() . '.' . $image->getClientOriginalExtension();
            $destinationPath = public_path('/User');
            $image->move($destinationPath, $name);
    
            $user->profile=$name;
        }

        $user->save();

        return response()->json(['status' => 'SUCCESS', 'code' => 'SC_01', 'data' => $user]);
    }


    //4. Get user profile
    public function get_user_profile($userId){
        $user=User::where('id',$userId)
                ->first();

        return response()->json(['status' => 'SUCCESS', 'code' => 'SC_01', 'data' => $user]);
    }


    //5. Get current game (socket)
    public function get_current_game(){
        $lastGame=Game::orderBy('created_at','DESC')
                        ->first();

        if(!$lastGame){
            return response()->json(['status' => 'FAILED', 'code' => 'FC_01', 'data' => null]);   
        }

        $paidPredictionResponse=PaidPredicationResponse::where('game_id',$lastGame->id)
                                                    ->first();

        $freePredictionResponse=FreePredicationResponse::where('game_id',$lastGame->id)
                                                    ->first();

        $paidQuizResponse=PaidQuizResponse::where('game_id',$lastGame->id)
                                        ->count();

        $freeQuizResponse=FreeQuizResponse::where('game_id',$lastGame->id)
                                        ->count();

            
        if($paidPredictionResponse){
            $lastGame['paid_prediction']=$paidPredictionResponse->selected_team;
        }else{
            $lastGame['paid_prediction']=null;
        }

        if($freePredictionResponse){
            $lastGame['free_prediction']=$freePredictionResponse->selected_team;
        }else{
            $lastGame['free_prediction']=null;
        }

        $lastGame['paid_quiz']=$paidQuizResponse;
        $lastGame['free_quiz']=$freeQuizResponse;


        return response()->json(['status' => 'SUCCESS', 'code' => 'SC_01', 'data' => $lastGame]);
    }


    //6. get prizes
    public function get_prizes(){

        $prize=Prize::first();

        return response()->json(['status' => 'SUCCESS', 'code' => 'SC_01', 'data' => $prize]);      
    }

   //DONE IN ADMIN //7. get winner list

   
    //8. Get current question (socket)
    public function get_current_question(){
        $lastGame=Game::where('game_status',1)
                        ->orderBy('created_at','DESC')
                        ->first();

        if(!$lastGame){
            return response()->json(['status' => 'FAILED', 'code' => 'FC_01', 'data' => null]);   
        }

        $activeQuestion=Question::where('game_id',$lastGame->id)
                                ->orderBy('created_at','DESC')
                                ->first();

        // if(!$activeQuestion){
        //     //no question active to set answer. stop response first
        //     return response()->json(['status' => 'FAILED', 'code' => 'FC_02', 'data' => null]);   
        // }

        return response()->json(['status' => 'SUCCESS', 'code' => 'SC_01', 'data' => $activeQuestion]);      
    }


    //9. submit paid response
    public function mark_paid_prediction(Request $request){
        $userId=$request->user_id;
        $selectedTeam=$request->selected_team;

        $lastGame=Game::where('game_status',1)
                        ->orderBy('created_at','DESC')
                        ->first();

        if(!$lastGame){
            return response()->json(['status' => 'FAILED', 'code' => 'FC_01', 'data' => null]);   
        }

        $predictionFee=$lastGame->prediction_fee;

        $user=User::where('id',$userId)
                    ->first();

        $walletBalance=$user->coins;

        if($walletBalance<$predictionFee){
            //insufficient balance
            return response()->json(['status' => 'FAILED', 'code' => 'FC_02', 'data' => null]);   
        }

        $user->coins=$walletBalance-$predictionFee;
        $user->save();

        $predictionData=[];
        $predictionData['game_id']=$lastGame->id;
        $predictionData['user_id']=$userId;
        $predictionData['selected_team']=$selectedTeam;
        $predictionData['paid_amount']=$predictionFee;

        $prediction=PaidPredicationResponse::create($predictionData);

        return response()->json(['status' => 'SUCCESS', 'code' => 'SC_01', 'data' => $prediction]);   
    }


    //10. submit free response
    public function mark_free_prediction(Request $request){
        $userId=$request->user_id;
        $selectedTeam=$request->selected_team;

        $lastGame=Game::where('game_status',0)
                        ->orderBy('created_at','DESC')
                        ->first();

        if(!$lastGame){
            return response()->json(['status' => 'FAILED', 'code' => 'FC_01', 'data' => null]);   
        }

        $predictionData=[];
        $predictionData['game_id']=$lastGame->id;
        $predictionData['user_id']=$userId;
        $predictionData['selected_team']=$selectedTeam;
        $predictionData['paid_amount']=0;

        $prediction=FreePredicationResponse::create($predictionData);

        return response()->json(['status' => 'SUCCESS', 'code' => 'SC_01', 'data' => $prediction]);  
    }

    //11. add money payu
    public function add_payu_money(Request $request){
        $userId=$request->user_id;
        $txnAmount=$request->txn_amount;
        $txnId=$request->txn_id;
        $payuMoneyId=$request->payu_money_id;

        $transactionData=[];
        $transactionData['user_id']=$userId;
        $transactionData['txn_amount']=$txnAmount;
        $transactionData['txn_id']=$txnId;
        $transactionData['payu_money_id']=$payuMoneyId;
        $transactionData['txn_title']="Added by user";
        $transactionData['txn_message']="-";
        $transactionData['txn_mode']="PAYU";
        $transactionData['txn_status']="PENDING";
        
        $transaction=Transaction::create($transactionData);

        return response()->json(['status' => 'SUCCESS', 'code' => 'SC_01', 'data' => $transaction]);  
    }

    //12. payu success/failure webhook
    public function update_payment_status(Request $request){
        
        $status=$request->status;
        $txnId=$request->txn_id;
        $payuMoneyId=$request->payu_money_id;
        $transactionId=$request->transaction_id;

        $transaction=Transaction::where('id',$transactionId)
                            ->first();

        if($transaction->txn_status!="SUCCESS"){
            if($status=="SUCCESS"){
                $transaction->txn_id=$txnId;
                $transaction->payu_money_id=$payuMoneyId;
                $transaction->txn_status=$status;
                $transaction->save();

                $user=User::where('id',$transaction->user_id)
                            ->first();

                $walletBalance=$user->wallet_balance;
                $updatedWalletBalance=$walletBalance+$transaction->txn_amount;
                $user->wallet_balance=$updatedWalletBalance;
                $user->save();

            }else if($status=="FAILED"){
                $transaction->txn_id=$txnId;
                $transaction->payu_money_id=$payuMoneyId;
                $transaction->txn_status="FAILED";
                $transaction->save();
            }
        }

        return response()->json(['status' => 'SUCCESS', 'code' => 'SC_01', 'data' => $transaction]);  
    }

    //13. submit withdrawal request
    public function submit_withdrawal_request(Request $request){
        $userId=$request->user_id;
        $amount=$request->amount;
        $paytmNumber=$request->paytm_number;
        $upiId=$request->upi_id;

        $user=User::where('id',$userId)
                ->first();

        $walletBalance=$user->coins;

        if($walletBalance<$amount){
            return response()->json(['status' => 'FAILED', 'code' => 'FC_01', 'data' => null]);   
        }

        $name=$user->name;

        $user->coins=$walletBalance-$amount;
        $user->save();

        $title="Withdrawal request from ".$name;
        $message="-";

        $withdrawalData=[];
        $withdrawalData['user_id']=$userId;
        $withdrawalData['amount']=$amount;
        $withdrawalData['paytm_number']=$paytmNumber;
        $withdrawalData['upi_id']=$upiId;
        $withdrawalData['message']=$message;
        $withdrawalData['status']="PENDING";
        $withdrawalData['title']=$title;

        $withdrawal=Withdrawal::create($withdrawalData);



        return response()->json(['status' => 'SUCCESS', 'code' => 'SC_01', 'data' => $withdrawal]);   
    }

    //14. mark paid response
    public function mark_paid_response(Request $request){
        $userId=$request->user_id;
        $selectedAnswer=$request->selected_answer;

        $lastGame=Game::where('game_status',1)
                        ->orderBy('created_at','DESC')
                        ->first();

        if(!$lastGame){
            return response()->json(['status' => 'FAILED', 'code' => 'FC_01', 'data' => null]);   
        }

        $activeQuestion=Question::where('game_id',$lastGame->id)
                                ->where('question_status',0)
                                ->orderBy('created_at','DESC')
                                ->first();

        if(!$activeQuestion){
            //no question active to set answer response stopped
            return response()->json(['status' => 'FAILED', 'code' => 'FC_02', 'data' => null]);   
        }

        $paidResponse=PaidQuizResponse::where('game_id',$lastGame->id)
                                    ->where('user_id',$userId)
                                    ->where('question_id',$activeQuestion->id)
                                    ->first();

        $questionPoint=$activeQuestion->paid_question_point;

        $user=User::where('id',$userId)
                    ->first();

        $walletBalance=$user->coins;

        if($walletBalance<$questionPoint){
            return response()->json(['status' => 'FAILED', 'code' => 'FC_03', 'data' => null]);   
        }

        if(!$paidResponse){
            $paidResponseData=[];
            $paidResponseData['game_id']=$lastGame->id;
            $paidResponseData['user_id']=$userId;
            $paidResponseData['question_id']=$activeQuestion->id;
            $paidResponseData['selected_answer']=$selectedAnswer;
            $paidResponseData['earned_coin']=0;

            $paidResponse=PaidQuizResponse::create($paidResponseData);

            $user->coins=$walletBalance-$questionPoint;
            $user->save();

            return response()->json(['status' => 'SUCCESS', 'code' => 'SC_01', 'data' => $paidResponse]);   
        }

        $paidResponse->selected_answer=$selectedAnswer;
        $paidResponse->save();

        return response()->json(['status' => 'SUCCESS', 'code' => 'SC_01', 'data' => $paidResponse]);   
    }
    
    //15. mark free response
    public function mark_free_response(Request $request){
        $userId=$request->user_id;
        $selectedAnswer=$request->selected_answer;

        $lastGame=Game::where('game_status',1)
                        ->orderBy('created_at','DESC')
                        ->first();

        if(!$lastGame){
            return response()->json(['status' => 'FAILED', 'code' => 'FC_01', 'data' => null]);   
        }

        $activeQuestion=Question::where('game_id',$lastGame->id)
                                ->where('question_status',0)
                                ->orderBy('created_at','DESC')
                                ->first();

        if(!$activeQuestion){
            //no question active to set answer response stopped
            return response()->json(['status' => 'FAILED', 'code' => 'FC_02', 'data' => null]);   
        }

        $freeResponse=FreeQuizResponse::where('game_id',$lastGame->id)
                                    ->where('user_id',$userId)
                                    ->where('question_id',$activeQuestion->id)
                                    ->first();

        if(!$freeResponse){
            $freeResponseData=[];
            $freeResponseData['game_id']=$lastGame->id;
            $freeResponseData['user_id']=$userId;
            $freeResponseData['question_id']=$activeQuestion->id;
            $freeResponseData['selected_answer']=$selectedAnswer;
            $freeResponseData['earned_coin']=0;

            $freeResponse=FreeQuizResponse::create($freeResponseData);

            return response()->json(['status' => 'SUCCESS', 'code' => 'SC_01', 'data' => $freeResponse]);   
        }

        $freeResponse->selected_answer=$selectedAnswer;
        $freeResponse->save();

        return response()->json(['status' => 'SUCCESS', 'code' => 'SC_01', 'data' => $freeResponse]);
    }

    //16. Reset password
    public function reset_password(Request $request){
        $userId=$request->user_id;
        $currentPassword=$request->current_password;
        $newPassword=$request->new_password;

        $user=User::where('id',$userId)
                    ->where('password',$currentPassword)
                    ->first();

        if(!$user){
            return response()->json(['status' => 'FAILED', 'code' => 'FC_01', 'data' => null]);
        }

        $user->password=$newPassword;
        $user->save();

        return response()->json(['status' => 'SUCCESS', 'code' => 'SC_01', 'data' => $user]);
    }



    public function notification($tokenList, $title, $message, $auth)
	{
		$fcmUrl = 'https://fcm.googleapis.com/fcm/send';
		// $token=$token;

		$notification = [
			'title' => $title,
			'body' => $message,
			'sound' => true,
		];

		$extraNotificationData = ["message" => $notification];

		$fcmNotification = [
			'registration_ids' => $tokenList, //multple token array
			// 'to'        => $token, //single token
			'notification' => $notification,
			'data' => $extraNotificationData
		];

		$headers = [
			'Authorization: key=' . $auth,
			'Content-Type: application/json'
		];


		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $fcmUrl);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fcmNotification));
		$result = curl_exec($ch);
		curl_close($ch);

		return true;
	}

}
