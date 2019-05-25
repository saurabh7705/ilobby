<?php

class SiteController extends Controller
{
	/**
	 * Declares class-based actions.
	 */
	public function actions()
	{
		return array(
			// captcha action renders the CAPTCHA image displayed on the contact page
			'captcha'=>array(
				'class'=>'CCaptchaAction',
				'backColor'=>0xFFFFFF,
			),
			// page action renders "static" pages stored under 'protected/views/site/pages'
			// They can be accessed via: index.php?r=site/page&view=FileName
			'page'=>array(
				'class'=>'CViewAction',
			),
		);
	}

	/**
	 * This is the default 'index' action that is invoked
	 * when an action is not explicitly requested by users.
	 */
	public function actionIndex()
	{
		// renders the view file 'protected/views/site/index.php'
		// using the default layout 'protected/views/layouts/main.php'
		$this->render('index');
	}

	/**
	 * This is the action to handle external exceptions.
	 */
	public function actionError()
	{
		if($error=Yii::app()->errorHandler->error)
		{
			if(Yii::app()->request->isAjaxRequest)
				echo $error['message'];
			else
				$this->render('error', $error);
		}
	}

	/**
	 * Displays the contact page
	 */
	public function actionContact()
	{
		$model=new ContactForm;
		if(isset($_POST['ContactForm']))
		{
			$model->attributes=$_POST['ContactForm'];
			if($model->validate())
			{
				$name='=?UTF-8?B?'.base64_encode($model->name).'?=';
				$subject='=?UTF-8?B?'.base64_encode($model->subject).'?=';
				$headers="From: $name <{$model->email}>\r\n".
					"Reply-To: {$model->email}\r\n".
					"MIME-Version: 1.0\r\n".
					"Content-Type: text/plain; charset=UTF-8";

				mail(Yii::app()->params['adminEmail'],$subject,$model->body,$headers);
				Yii::app()->user->setFlash('contact','Thank you for contacting us. We will respond to you as soon as possible.');
				$this->refresh();
			}
		}
		$this->render('contact',array('model'=>$model));
	}

	/**
	 * Displays the login page
	 */
	public function actionLogin()
	{
		$model=new LoginForm;

		// if it is ajax validation request
		if(isset($_POST['ajax']) && $_POST['ajax']==='login-form')
		{
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}

		// collect user input data
		if(isset($_POST['LoginForm']))
		{
			$model->attributes=$_POST['LoginForm'];
			// validate user input and redirect to the previous page if valid
			if($model->validate() && $model->login())
				$this->redirect(Yii::app()->user->returnUrl);
		}
		// display the login form
		$this->render('login',array('model'=>$model));
	}

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
					$this->renderJSON(array('status'=>'ERROR', 'message'=>LoadDataHelper::lib()->getModelErrorsArray($user)));
				}
			}
			catch(Exception $e) {
				
			}
		}
		else {
			$this->renderJSON(array('status'=>'ERROR', 'message'=>"Insufficient Data!"));
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