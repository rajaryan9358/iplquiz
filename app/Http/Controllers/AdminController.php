<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\DefaultOption;
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
use Facade\Ignition\QueryRecorder\Query;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpParser\Builder\Trait_;

class AdminController extends Controller
{
    //Api List

    //1. login admin
    //2. create game (socket)
    //3. update the last active game (socket)
    //4. start game (socket)
    //5. send new question (socket)
    //6. stop question response (socket)
    //7. send answer (socket)
    //8. finish game (socket)
    //9. get users list (paging)
    //10. get winners list
    //11. get withdrawal requests
    //12. update withdrawal request status (notification)
    //13. add money to user wallet
    //14. block unblock user
    //15. Save default options
    //16. save prizes

    //Socket List
    //Send game
    //Send question
    //Wallet balance


    //1. login admin
    public function login_admin(Request $request){
        $username=$request->username;
        $password=$request->password;

        $admin=Admin::where('username',$username)
                    ->where('password',$password)
                    ->first();

        if($admin){
            return response()->json(['status' => 'SUCCESS', 'code' => 'SC_01', 'data' => $admin]);  
        }

        return response()->json(['status' => 'FAILED', 'code' => 'FC_01', 'data' => null]);  
    }

    //2. create game (socket)
    public function create_game(Request $request){
        $gameName=$request->game_name;
        $gameTeams=$request->game_teams;
        $teamOne=$request->team_one;
        $teamTwo=$request->team_two;
        $predictionFee=$request->prediction_fee;
        $type=$request->type; // NEW or CHANGE

        if($type=="CHANGE"){
            $game=Game::orderBy('created_at','DESC')
                    ->first();
            if($game->game_status==2||!$game){
                //game doesn't exist for change
                return response()->json(['status' => 'FAILED', 'code' => 'FC_01', 'data' => null]);  
            }
        }

        $gameImage=null;
        $teamOneImage=null;
        $teamTwoImage=null;

        if ($request->hasFile('game_image')) {
            $image = $request->file('game_image');
            $name = time() . '.' . $image->getClientOriginalExtension();
            $destinationPath = public_path('/Game');
            $image->move($destinationPath, $name);
    
            $gameImage=$name;
        }

        if ($request->hasFile('team_one_image')) {
            $image = $request->file('team_one_image');
            $name = time() . '.' . $image->getClientOriginalExtension();
            $destinationPath = public_path('/Team');
            $image->move($destinationPath, $name);
    
            $teamOneImage=$name;
        }

        if ($request->hasFile('team_two_image')) {
            $image = $request->file('team_two_image');
            $name = time() . '.' . $image->getClientOriginalExtension();
            $destinationPath = public_path('/Team');
            $image->move($destinationPath, $name);
    
            $teamTwoImage=$name;
        }


        if($type=="CHANGE"){
            $game->game_name=$gameName;
            $game->game_teams=$gameTeams;
            $game->team_one_name=$teamOne;
            $game->team_two_name=$teamTwo;
            $game->prediction_fee=$predictionFee;

            if($gameImage!=null)
            $game->game_image=$gameImage;
            if($teamOneImage!=null)
            $game->team_one_image=$teamOneImage;
            if($teamTwoImage!=null)
            $game->team_two_image=$teamTwoImage;

            $game->save();

            //send data to socket

            return response()->json(['status' => 'SUCCESS', 'code' => 'SC_01', 'data' => $game]);   
        }

        $gameData=[];
        $gameData['game_name']=$gameName;
        $gameData['game_teams']=$gameTeams;
        $gameData['team_one_name']=$teamOne;
        $gameData['team_two_name']=$teamTwo;
        $gameData['prediction_fee']=$predictionFee;
        $gameData['game_image']=$gameImage;
        $gameData['team_one_image']=$teamOneImage;
        $gameData['team_two_image']=$teamTwoImage;

        $game=Game::create($gameData);

        $game=Game::where('id',$game->id)
                ->first();

        $data_string = json_encode($game);

			$ch = curl_init('https://www.maxtambola.com:8080/sendGame');
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt(
				$ch,
				CURLOPT_HTTPHEADER,
				array(
					'Content-Type: application/json',
					'Content-Length: ' . strlen($data_string)
				)
			);

			curl_exec($ch) . "\n";
			curl_close($ch);

        return response()->json(['status' => 'SUCCESS', 'code' => 'SC_02', 'data' => $game]);   
    }

    //4. start game (socket)
    public function start_game(){
        $lastGame=Game::where('game_status',0)
                        ->orderBy('created_at','DESC')
                        ->first();

        if(!$lastGame){
            return response()->json(['status' => 'FAILED', 'code' => 'FC_01', 'data' => null]);   
        }

        $lastGame->game_status=1;
        $lastGame->save();

        $data_string = json_encode($lastGame);

			$ch = curl_init('https://www.maxtambola.com:8080/sendGame');
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt(
				$ch,
				CURLOPT_HTTPHEADER,
				array(
					'Content-Type: application/json',
					'Content-Length: ' . strlen($data_string)
				)
			);

			curl_exec($ch) . "\n";
			curl_close($ch);

        return response()->json(['status' => 'SUCCESS', 'code' => 'SC_01', 'data' => $lastGame]);   
    }

    //5. send new question (socket)
    public function send_new_question(Request $request){
        $question=$request->question;
        $paidQuestionPoint=$request->paid_question_point;
        $paidAnswerMultiple=$request->paid_answer_multiple;
        $freeQuestionPoint=$request->free_question_point;
        $freeAnswerPoint=$request->free_answer_point;

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

        if($activeQuestion){
            //stop response and send answer to last question
            return response()->json(['status' => 'FAILED', 'code' => 'FC_02', 'data' => null]);   
        }

        $defaultOptions=DefaultOption::first();

        if(!$defaultOptions){
            return response()->json(['status' => 'FAILED', 'code' => 'FC_03', 'data' => null]);   
        }

        $questionCount=Question::where('game_id',$lastGame->id)
                                ->count();

        $questionData=[];
        $questionData['question']=$question;
        $questionData['game_id']=$lastGame->id;
        $questionData['question_number']=$questionCount+1;
        $questionData['paid_question_point']=$paidQuestionPoint;
        $questionData['paid_answer_multiple']=$paidAnswerMultiple;
        $questionData['free_question_point']=$freeQuestionPoint;
        $questionData['free_answer_point']=$freeAnswerPoint;
        $questionData['option_one']=$defaultOptions->option_one;
        $questionData['option_two']=$defaultOptions->option_two;
        $questionData['option_three']=$defaultOptions->option_three;
        $questionData['option_four']=$defaultOptions->option_four;
        $questionData['option_five']=$defaultOptions->option_five;
        $questionData['option_six']=$defaultOptions->option_six;
        $questionData['option_seven']=$defaultOptions->option_seven;
        $questionData['option_eight']=$defaultOptions->option_eight;

        $newQuestion=Question::create($questionData);

        //send new question data to socket

        $question=Question::where('id',$newQuestion->id)
                            ->first();

        $data_string = json_encode($question);

			$ch = curl_init('https://www.maxtambola.com:8080/sendQuestion');
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt(
				$ch,
				CURLOPT_HTTPHEADER,
				array(
					'Content-Type: application/json',
					'Content-Length: ' . strlen($data_string)
				)
			);

			curl_exec($ch) . "\n";
			curl_close($ch);

        return response()->json(['status' => 'SUCCESS', 'code' => 'SC_01', 'data' => $newQuestion]);   
    }


    //6. stop question response (socket)
    public function stop_question_response(){
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
            //no question active to stop response
            return response()->json(['status' => 'FAILED', 'code' => 'FC_02', 'data' => null]);   
        }

        $activeQuestion->question_status=1;
        $activeQuestion->save();

        //send socket data to user

        $data_string = json_encode($activeQuestion);

        $ch = curl_init('https://www.maxtambola.com:8080/sendQuestion');
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt(
            $ch,
            CURLOPT_HTTPHEADER,
            array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($data_string)
            )
        );

        curl_exec($ch) . "\n";
        curl_close($ch);

        return response()->json(['status' => 'SUCCESS', 'code' => 'SC_01', 'data' => $activeQuestion]);   
    }


    //7. send answer (socket)
    public function send_answer(Request $request){
        $answer=$request->answer;

        $lastGame=Game::where('game_status',1)
                        ->orderBy('created_at','DESC')
                        ->first();

        if(!$lastGame){
            return response()->json(['status' => 'FAILED', 'code' => 'FC_01', 'data' => null]);   
        }

        $activeQuestion=Question::where('game_id',$lastGame->id)
                                ->where('question_status',1)
                                ->orderBy('created_at','DESC')
                                ->first();

        if(!$activeQuestion){
            //no question active to set answer. stop response first
            return response()->json(['status' => 'FAILED', 'code' => 'FC_02', 'data' => null]);   
        }

        $activeQuestion->correct_answer=$answer;
        $activeQuestion->question_status=2;
        $activeQuestion->save();

        $paidQuestionPoint=$activeQuestion->paid_question_point;
        $paidAnswerMultiple=$activeQuestion->paid_answer_multiple;
        $paidAnswerPoint=$paidQuestionPoint*$paidAnswerMultiple;

        $freeQuestionPoint=$activeQuestion->free_question_point;
        $freeAnswerPoint=$activeQuestion->free_answer_point;

        $paidQuizResponses=PaidQuizResponse::where('game_id',$lastGame->id)
                                                ->where('question_id',$activeQuestion->id)
                                                ->get();

        foreach($paidQuizResponses as $cpqr){
            $userId=$cpqr['user_id'];
            $selectedAnswer=$cpqr['selected_answer'];

            $user=User::where('id',$userId)
                        ->first();

            if($selectedAnswer==$answer){
                //answer of user is correct

                $pqr=PaidQuizResponse::where('game_id',$lastGame->id)
                                    ->where('question_id',$activeQuestion->id)
                                    ->where('user_id',$userId)
                                    ->update(['earned_coin'=>$paidAnswerPoint]);

                $walletBalance=$user->wallet_balance;
                $updatedWalletBalance=$walletBalance+$paidAnswerPoint;
                $user->wallet_balance=$updatedWalletBalance;
                $user->save();
            }else{
                $pqr=PaidQuizResponse::where('game_id',$lastGame->id)
                                    ->where('question_id',$activeQuestion->id)
                                    ->where('user_id',$userId)
                                    ->update(['earned_coin'=>-$paidQuestionPoint]);

                $walletBalance=$user->wallet_balance;
                $updatedWalletBalance=$walletBalance-$paidQuestionPoint;
                $user->wallet_balance=$updatedWalletBalance;
                $user->save();
            }
        }


        $freeQuizResponses=FreeQuizResponse::where('game_id',$lastGame->id)
                                                ->where('question_id',$activeQuestion->id)
                                                ->get();

        foreach($freeQuizResponses as $cfqr){
            $userId=$cfqr['user_id'];
            $selectedAnswer=$cfqr['selected_answer'];

            $user=User::where('id',$userId)
                        ->first();

            if($selectedAnswer==$answer){
                //answer of user is correct

                $pqr=FreeQuizResponse::where('game_id',$lastGame->id)
                                    ->where('question_id',$activeQuestion->id)
                                    ->where('user_id',$userId)
                                    ->update(['earned_coin'=>$freeAnswerPoint]);

                $walletBalance=$user->wallet_balance;
                $updatedWalletBalance=$walletBalance+$freeAnswerPoint;
                $user->wallet_balance=$updatedWalletBalance;
                $user->save();
            }else{
                $pqr=FreeQuizResponse::where('game_id',$lastGame->id)
                                    ->where('question_id',$activeQuestion->id)
                                    ->where('user_id',$userId)
                                    ->update(['earned_coin'=>-$freeQuestionPoint]);

                $walletBalance=$user->wallet_balance;
                $updatedWalletBalance=$walletBalance-$freeQuestionPoint;
                $user->wallet_balance=$updatedWalletBalance;
                $user->save();
            }
        }

        //send answer to user through socket

        $data_string = json_encode($activeQuestion);

			$ch = curl_init('https://www.maxtambola.com:8080/sendQuestion');
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt(
				$ch,
				CURLOPT_HTTPHEADER,
				array(
					'Content-Type: application/json',
					'Content-Length: ' . strlen($data_string)
				)
			);

			curl_exec($ch) . "\n";
			curl_close($ch);

        return response()->json(['status' => 'SUCCESS', 'code' => 'SC_01', 'data' => $activeQuestion]);   
    }

    //8. finish game (socket)
    public function finish_game(Request $request){
        $paidPredictionWinMultiple=$request->paid_prediction_win_multiple;
        $freePredictionWinAmount=$request->free_prediction_win_amount;
        $maxPaidQuizWinnerCount=$request->max_paid_quiz_winner_count;
        $maxFreeQuizWinnerCount=$request->max_free_quiz_winner_count;
        $winnerTeam=$request->winner_team;

        $lastGame=Game::where('game_status',1)
                        ->orderBy('created_at','DESC')
                        ->first();

        if(!$lastGame){
            return response()->json(['status' => 'FAILED', 'code' => 'FC_01', 'data' => null]);   
        }

        $predictionFee=$lastGame->prediction_fee;
        $paidPredictionWinAmount=$predictionFee*$paidPredictionWinMultiple;

        $lastGame->paid_prediction_multiple=$paidPredictionWinMultiple;
        $lastGame->free_prediction_amount=$freePredictionWinAmount;
        $lastGame->paid_winner_count=$maxPaidQuizWinnerCount;
        $lastGame->free_winner_count=$maxFreeQuizWinnerCount;
        $lastGame->winner_team=$winnerTeam;
        $lastGame->game_status=2;

        $lastGame->save();

        $paidPredictionResponses=PaidPredicationResponse::where('game_id',$lastGame->id)
                                                    ->get();


        foreach($paidPredictionResponses as $paidPred){
            $userId=$paidPred['user_id'];
            $selectedAnswer=$paidPred['selected_answer'];

            $user=User::where('id',$userId)
                    ->first();
        
            if($selectedAnswer==$winnerTeam){
                $walletBalance=$user->wallet_balance;
                $updatedWalletBalance=$walletBalance+$paidPredictionWinAmount;
                $user->wallet_balance=$updatedWalletBalance;
                $user->save();

            }else{
                $walletBalance=$user->wallet_balance;
                $updatedWalletBalance=$walletBalance-$predictionFee;
                $user->wallet_balance=$updatedWalletBalance;
                $user->save();
            }
        }

        $freePredictionResponses=FreePredicationResponse::where('game_id',$lastGame->id)
                                                    ->get();


        foreach($freePredictionResponses as $freePred){
            $userId=$freePred['user_id'];
            $selectedAnswer=$freePred['selected_answer'];

            $user=User::where('id',$userId)
                    ->first();
        
            if($selectedAnswer==$winnerTeam){
                $walletBalance=$user->wallet_balance;
                $updatedWalletBalance=$walletBalance+$freePredictionWinAmount;
                $user->wallet_balance=$updatedWalletBalance;
                $user->save();

            }
        }


        $data_string = json_encode($lastGame);

			$ch = curl_init('https://www.maxtambola.com:8080/sendGame');
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt(
				$ch,
				CURLOPT_HTTPHEADER,
				array(
					'Content-Type: application/json',
					'Content-Length: ' . strlen($data_string)
				)
			);

			curl_exec($ch) . "\n";
			curl_close($ch);


        return response()->json(['status' => 'SUCCESS', 'code' => 'SC_01', 'data' => $lastGame]);      
    }


    //9. get users list (paging)
    public function get_users(Request $request){
        $fromId=$request->from_id;
        $limit=$request->limit;

        if($fromId==0){
            $users=User::orderBy('created_at','DESC')
                        ->limit($limit)
                        ->get();
        }else{
            $users=User::orderBy('created_at','DESC')
                        ->where('id','<',$fromId)
                        ->limit($limit)
                        ->get();
        }

        return response()->json(['status' => 'SUCCESS', 'code' => 'SC_01', 'data' => $users]);      
    }


    //10. get winners list
    public function get_winners_list($type){
        $lastGame=Game::where('game_status',1)
                        ->orderBy('created_at','DESC')
                        ->first();

        if(!$lastGame){
            //no finished game yet
            return response()->json(['status' => 'FAILED', 'code' => 'FC_01', 'data' => null]);   
        }

        $totalQuestions=Question::where('game_id',$lastGame->id)
                                ->count();


        if($type=="PAID"){
            $paidQuizWinnerCount=$lastGame->paid_winner_count;

            $PredictionResponses=PaidPredicationResponse::leftJoin('users','users.id','paid_predication_responses.user_id')
                                        ->where('paid_predication_responses.game_id',$lastGame->id)
                                        ->select('paid_predication_responses.*','users.name','users.profile')
                                        ->get();

            $QuizResponses=PaidQuizResponse::leftJoin('users','users.id','paid_quiz_responses.user_id')
                                        ->where('paid_quiz_responses.game_id',$lastGame->id)
                                        // ->where(DB::raw('count(earned_coin)'),'=',$totalQuestions)
                                        ->groupBy('users.id','users.name','users.profile')
                                        ->select('users.id','users.name','users.profile')
                                        ->addSelect(DB::raw('sum(earned_coin) as total_coin'))
                                        ->orderBy('total_coin','DESC')
                                        ->limit($paidQuizWinnerCount)
                                        ->get();

        }else{
            $freeQuizWinnerCount=$lastGame->free_winner_count;

            $PredictionResponses=FreePredicationResponse::leftJoin('users','users.id','free_predication_responses.user_id')
                                        ->where('free_predication_responses.game_id',$lastGame->id)
                                        ->select('free_predication_responses.*','users.name','users.profile')
                                        ->get();

            $QuizResponses=FreeQuizResponse::leftJoin('users','users.id','free_quiz_responses.user_id')
                                        ->where('free_quiz_responses.game_id',$lastGame->id)
                                        ->where(DB::raw('count(earned_coin)'),'=',$totalQuestions)
                                        ->groupBy('users.id','users.name','users.profile')
                                        ->select('users.id','users.name','users.profile',DB::raw('sum(earned_coin) as total_coint'))
                                        ->orderBy('total_coin','DESC')
                                        ->limit($freeQuizWinnerCount)
                                        ->get();
        }

        $result=[];
        $result['prediction_winners']=$PredictionResponses;
        $result['quiz_winners']=$QuizResponses;

        return response()->json(['status' => 'SUCCESS', 'code' => 'SC_01', 'data' => $result]);      
    }


    //11. get withdrawal requests
    public function get_withdrawal_requests(Request $request){
        $fromId=$request->from_id;
        $limit=$request->limit;

        if($fromId==0){
            $withdrawals=Withdrawal::where('status','PENDING')
                                ->orderBy('created_at','DESC')
                                ->limit($limit)
                                ->get();
        }else{
            $withdrawals=Withdrawal::where('status','PENDING')
                                ->where('id','<',$fromId)
                                ->orderBy('created_at','DESC')
                                ->limit($limit)
                                ->get();
        }

        return response()->json(['status' => 'SUCCESS', 'code' => 'SC_01', 'data' => $withdrawals]);      
    }


    //12. update withdrawal request status (notification)
    public function update_withdrawal_status(Request $request){
        $id=$request->id;
        $status=$request->status;
        $message=$request->message;

        $withdrawal=Withdrawal::where('id',$id)
                            ->first();
            
        if($withdrawal&&$withdrawal->status=="PENDING"){
            $user=User::where('id',$withdrawal->user_id)
                        ->first();

            $notificationTitle="";
            $notificationMessage=$message;

            if($status=="SUCCESS"){
                $withdrawal->message=$message;
                $withdrawal->status=$status;
                $withdrawal->save();

                $notificationTitle="Withdrawal request completed";

            }else{
                $amount=$withdrawal->amount;
                $walletBalance=$user->wallet_balance;
                $updatedBalance=$walletBalance+$amount;
                $user->wallet_balance=$updatedBalance;
                $user->save();

                $withdrawal->message=$message;
                $withdrawal->status=$status;
                $withdrawal->save();

                $notificationTitle="Withdrawal request rejected";
            }

            //send notification to user
            $tokenList=[];
            array_push($tokenList,$user->token);
            $title=$notificationTitle;
            $message=$notificationMessage;
            $auth="";
    
            $this->notification($tokenList,$title,$message,$auth);

            //send balance to user
            $resultData=[];
            $resultData['user_id']=$user->id;
            $resultData['coins']=$user->coins;

            $data_string = json_encode($resultData);

			$ch = curl_init('https://www.maxtambola.com:8080/sendBalance');
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt(
				$ch,
				CURLOPT_HTTPHEADER,
				array(
					'Content-Type: application/json',
					'Content-Length: ' . strlen($data_string)
				)
			);

			curl_exec($ch) . "\n";
			curl_close($ch);
        }

        return response()->json(['status' => 'SUCCESS', 'code' => 'SC_01', 'data' => $withdrawal]);      
    }


    //13. add money to user wallet
    public function add_user_money(Request $request){
        $amount=$request->amount;
        $userId=$request->user_id;
        $message=$request->message;

        $transactionData=[];
        $transactionData['user_id']=$userId;
        $transactionData['txn_amount']=$amount;
        $transactionData['txn_title']="Coins added by admin";
        $transactionData['txn_id']="-";
        $transactionData['txn_mode']="ADMIN";
        $transactionData['txn_message']=$message;
        $transactionData['txn_status']="SUCCESS";
        $transactionData['payu_money_id']="-";

        $transaction=Transaction::create($transactionData);

        $user=User::where('id',$userId)
                ->first();

        $walletBalance=$user->coins;
        $updatedWalletBalance=$walletBalance+$amount;
        $user->coins=$updatedWalletBalance;
        $user->save();

        //send notification to user
        $tokenList=[];
        array_push($tokenList,$user->token);
        $title="Coins credited";
        $message=$amount." coins added to your wallet";
        $auth="";

        $this->notification($tokenList,$title,$message,$auth);

        //send balance to user
        $resultData=[];
        $resultData['user_id']=$user->id;
        $resultData['coins']=$user->coins;

        $data_string = json_encode($resultData);

			$ch = curl_init('https://www.maxtambola.com:8080/sendBalance');
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt(
				$ch,
				CURLOPT_HTTPHEADER,
				array(
					'Content-Type: application/json',
					'Content-Length: ' . strlen($data_string)
				)
			);

			curl_exec($ch) . "\n";
			curl_close($ch);

        return response()->json(['status' => 'SUCCESS', 'code' => 'SC_01', 'data' => $transaction]);      
    }


    //14. block unblock user
    public function block_unblock_user(Request $request){
        $userId=$request->user_id;

        $user=User::where('id',$userId)
                ->first();

        if($user){
            if($user->is_blocked==0){
                $user->is_blocked=1;
            }else{
                $user->is_blocked=0;
            }

            $user->save();
        }

        return response()->json(['status' => 'SUCCESS', 'code' => 'SC_01', 'data' => $user]);      
    }

    
    //15. Save default options
    public function save_default_options(Request $request){
        $optionOne=$request->option_one;
        $optionTwo=$request->option_two;
        $optionThree=$request->option_three;
        $optionFour=$request->option_four;
        $optionFive=$request->option_five;
        $optionSix=$request->option_six;
        $optionSeven=$request->option_seven;
        $optionEight=$request->option_eight;

        $defaultOptions=DefaultOption::first();

        if(!$defaultOptions){
            $optioData=[];
            $optioData['option_one']=$optionOne;
            $optioData['option_two']=$optionTwo;
            $optioData['option_three']=$optionThree;
            $optioData['option_four']=$optionFour;
            $optioData['option_five']=$optionFive;
            $optioData['option_six']=$optionSix;
            $optioData['option_seven']=$optionSeven;
            $optioData['option_eight']=$optionEight;

            $defaultOptions=DefaultOption::create($optioData);

            return response()->json(['status' => 'SUCCESS', 'code' => 'SC_01', 'data' => $defaultOptions]);      
        }

        $defaultOptions->option_one=$optionOne;
        $defaultOptions->option_two=$optionTwo;
        $defaultOptions->option_three=$optionThree;
        $defaultOptions->option_four=$optionFour;
        $defaultOptions->option_five=$optionFive;
        $defaultOptions->option_six=$optionSix;
        $defaultOptions->option_seven=$optionSeven;
        $defaultOptions->option_eight=$optionEight;

        $defaultOptions->save();

        return response()->json(['status' => 'SUCCESS', 'code' => 'SC_01', 'data' => $defaultOptions]);      
    }


    public function get_default_options(){
        $defaultOptions=DefaultOption::first();
        return response()->json(['status' => 'SUCCESS', 'code' => 'SC_01', 'data' => $defaultOptions]);      
    }


    //16. save prizes
    public function save_prizes(Request $request){
        $freePrize=$request->free_prize;
        $paidPrize=$request->paid_prize;

        $prize=Prize::first();

        if(!$prize){
            $prizeData=[];
            $prizeData['free_prize']=$freePrize;
            $prizeData['paid_prize']=$paidPrize;

            $prize=Prize::create($prizeData);

            return response()->json(['status' => 'SUCCESS', 'code' => 'SC_01', 'data' => $prize]);      
        }

        $prize->free_prize=$freePrize;
        $prize->paid_prize=$paidPrize;

        $prize->save();

        return response()->json(['status' => 'SUCCESS', 'code' => 'SC_01', 'data' => $prize]);      
    }


    public function get_prizes(){
        $prize=Prize::first();

        return response()->json(['status' => 'SUCCESS', 'code' => 'SC_01', 'data' => $prize]);      
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



