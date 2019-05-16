<?php

class SessionController extends CController
{

	/**
	 * Logs out the current user and redirect to homepage.
	 */
	public function actionLogout()
	{
		
	}

	public function actionCreate() {
		if(isset($_POST['User'])) {
			try {
				$user = new User;
				$user->attributes = $_POST['User'];
				$user->password = md5($user->password);
				
				if($user->validate()) {
					$user->save();
					$token = ApiToken::createTokenForUser($user);
					$response = array('status'=>'SUCCESS', "auth_token"=>$token, 'name'=>$user->name, 'user_id'=>(int)$user->id);
					$this->renderJSON($response);
				} else {
					$this->renderJSON((array('status'=>'ERROR', 'message'=>LoadDataHelper::lib()->getModelErrorsArray($user)));
				}
			}
			catch(Exception $e) {
				
			}
		}
		else {
			$this->renderJSON((array('status'=>'ERROR', 'message'=>"Insufficient Data!"));
		}
	}

	public function actionLogin() {
		if(isset($_REQUEST['email']) && isset($_REQUEST['password'])) {
			$email = $_REQUEST['email'];
			$password = $_REQUEST['password'];
			$this->_identity = new UserIdentity($email,$password);
			if(!$this->_identity->authenticate()) {
				if($this->_identity->errorCode === UserIdentity::ERROR_ACCOUNT_NOT_CONFIRMED)
					$this->renderJSON(array('status'=>'ERROR', 'message'=>"Please verify your account to continue using the services"));
				else
					$this->renderJSON(array('status'=>'ERROR', 'message'=>'Incorrect email or password. Please contact support@ilobby.com in case of any queries.'));
			}
			else {
				$user = $this->_identity->user;
				$token = ApiToken::createTokenForUser($user);
				$response = array('status'=>'SUCCESS', "auth_token"=>$token, 'name'=>$user->name, 'user_id'=>(int)$user->id);
				$this->renderJSON($response);
			}
		}
		else {
			$this->renderJSON(array('status'=>'ERROR', 'message'=>'Incomplete data, please provide email and password.'));
		}
	}
}

?>