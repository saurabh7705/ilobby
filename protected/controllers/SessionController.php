<?php

class SessionController extends Controller
{

	public $_identity;

	/**
	 * Logs out the current user and redirect to homepage.
	 */
	public function actionLogout()
	{
		
	}

	public function actionCreate() {
		$json = file_get_contents('php://input');
		$data = json_decode($json, true);
		if($data != NULL && isset($data['User'])) {
			try {
				$user = new User;
				$user->attributes = $data['User'];
				$user->password = md5($user->password);
				
				if($user->validate()) {
					$user->save();
					$token = ApiToken::createTokenForUser($user);
					$response = array('status'=>'SUCCESS', "auth_token"=>$token, 'name'=>$user->name, 'user_id'=>(int)$user->id);
					$this->renderJSON($response);
				} else {
					$this->renderJSON(array('status'=>'ERROR', 'message'=>LoadDataHelper::lib()->getModelErrorsArray($user)));
				}
			}
			catch(Exception $e) {
				var_dump($e->getMessage());
			}
		}
		else {
			$this->renderJSON(array('status'=>'ERROR', 'message'=>"Insufficient Data!"));
		}
	}

	public function actionLogin() {
		$json = file_get_contents('php://input');
		$data = json_decode($json, true);
		if($data != NULL && isset($data['email']) && isset($data['password'])) {
			$email = $data['email'];
			$password = $data['password'];
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