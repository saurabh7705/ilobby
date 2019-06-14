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
	 * Logs out the current user and redirect to homepage.
	 */
	public function actionLogout()
	{
		
	}

	public function actionMe() {
		if($this->_user) {
			$user = $this->_user;
			$response = array(
				'status'=>'SUCCESS', 
				'name'=>$user->name, 
				'id'=>(int)$user->id
			);
			$this->renderJSON($response);
		}
		else {
			$this->renderJSON(array('status'=>'AUTH_ERROR'));
		}
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

	
	public function actionCreateIssue() {	
		$model=new Issue;
		if(isset($_POST['Issue']) && $this->_user)
		{
			$model->attributes=$_POST['Issue'];
			$model->file_name = CUploadedFile::getInstance($model, 'file_name');
			$model->user_id = $this->_user->id;
			if($model->save()) {
				if($model->file_name) {
					$extension = $model->file_name->getExtensionName();            
					$model->extension = $extension;
					$path = Yii::app()->basePath."/../issue/$model->id.$extension";
					$model->file_name->saveAs($path);
					$model->save();
					$response = array('status'=>'SUCCESS');
					$this->renderJSON($response);
				}
			} else {
				$this->renderJSON(array('status'=>'ERROR', 'message'=>LoadDataHelper::lib()->getModelErrorsArray($model)));
			}
		}
		else {
			$this->renderJSON(array('status'=>'ERROR', 'message'=>"Insufficient Data!"));
		}
	}

	public function getIssuesData($issues) {
		$data = array();
		foreach ($issues as $issue) {
			$data[] = array(
				'id' => $issue->id,
				'notes' => $issue->notes,
				'created_at' => $issue->created_at,
				'location' => $issue->location,
				'image_url' => $issue->getFileUrl(),
			);
		}
	}

	public function actionList() {
		if($this->_user) {
			$issues = Issue::model()->findAll(array(
				"condition"=>"user_id = :user_id",
				"params"=>array('user_id'=>$this->_user)
			));
			$this->renderJSON(array('status'=>'SUCCESS', 'issues'=> $this->getIssuesData($issues)));
		}
		else {
			$this->renderJSON(array('status'=>'ERROR', 'message'=>"Insufficient Data!"));
		}
	}

	public function actionListFilter() {
		if($this->_user) {
			$conditions = array();
			$params = array();
			if(array_key_exists('type', $_GET)) {
				$conditions[] = "type = :type"
				$params['type'] = $_GET['type'];
			}
			if(array_key_exists('location', $_GET)) {
				$conditions[] = "user.location = :location"
				$params['location'] = $_GET['location'];
			}
			if(array_key_exists('zipcode', $_GET)) {
				$conditions[] = "user.zipcode = :zipcode"
				$params['zipcode'] = $_GET['zipcode'];
			}
			if(array_key_exists('gender', $_GET)) {
				$conditions[] = "user.gender = :gender"
				$params['zipcode'] = $_GET['zipcode'];
			}
			$issues = Issue::model()->with("user")->findAll(array(
				"condition"=>implode(" and ", $condition),
				"params"=>$params
			));

			$this->renderJSON(array('status'=>'SUCCESS', 'issues'=> $this->getIssuesData($issues)));
		}
		else {
			$this->renderJSON(array('status'=>'ERROR', 'message'=>"Insufficient Data!"));
		}
	}
}