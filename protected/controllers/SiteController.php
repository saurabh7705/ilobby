<?php

class SiteController extends Controller
{
	public $_user;

	public function filters() {
		return array(
			'authenticate'
		);
    }

    public function filterAuthenticate($filterChain) {
		if(isset($_GET['auth_token'])) {
			$token = ApiToken::model()->active()->find("token=:token", array(":token"=>$_GET['auth_token']));
			if($token)
				$this->setUser($token->user_id);
			else
				$this->renderJSON(array('status'=>'AUTH_ERROR', 'errors'=>array("Authentication Failed.")), false);
		}
		else {
			$this->renderJSON(array('status'=>'AUTH_ERROR', 'errors'=>array("Authentication Failed.")), false);
		}

		$filterChain->run();
	}

	public function setUser($user_id) {
		$this->_user = User::model()->findByPk($user_id);
	}

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
		if($this->_user) {
			ApiToken::expireTokensForUserId($this->_user->id);
			$response = array(
				'status'=>'SUCCESS'
			);
			$this->renderJSON($response);
		}
		else {
			$this->renderJSON(array('status'=>'AUTH_ERROR'));
		}
	}

	public function actionMe() {
		if($this->_user) {
			$user = $this->_user;
			$response = array(
				'status'=>'SUCCESS', 
				'name'=>$user->name, 
				'id'=>(int)$user->id,
				'age'=>$user->age,
				'address'=>$user->address,
				'zipcode'=>$user->zipcode,
				'gender'=>$user->sex,
				'education_level'=>$user->education_level,
				'ethnicity'=>$user->ethnicity
			);
			$this->renderJSON($response);
		}
		else {
			$this->renderJSON(array('status'=>'AUTH_ERROR'));
		}
	}

	public function actionUpdate() {
		$json = file_get_contents('php://input');
		$data = json_decode($json, true);
		if($this->_user && $data != NULL && isset($data['User'])) {
			try {
				$this->_user->attributes = $data['User'];
				
				if($this->_user->validate()) {
					$this->_user->save();
					$response = array('status'=>'SUCCESS', "auth_token"=>$token, 'name'=>$this->_user->name, 'user_id'=>(int)$this->_user->id);
					$this->renderJSON($response);
				} else {
					$this->renderJSON(array('status'=>'ERROR', 'message'=>LoadDataHelper::lib()->getModelErrorsArray($this->_user)));
				}
			}
			catch(Exception $e) {

			}
		}
		else {
			$this->renderJSON(array('status'=>'ERROR', 'message'=>"Insufficient Data!"));
		}
	}

	
	public function actionCreateIssue() {
		if($_POST != NULL && isset($_POST['Issue']) && $this->_user)
		{
			$model = new Issue;
			$model->attributes=json_decode($_POST['Issue'], true);
			$model->user_id = $this->_user->id;
			if($model->save()) {
				if(array_key_exists('image', $_FILES)) {
					$path_parts = pathinfo($_FILES["image"]["name"]);
					$extension = $path_parts['extension'];
					$path = Yii::app()->basePath."/../issue/$model->id.$extension";
					move_uploaded_file($_FILES['image']['tmp_name'], $path);

					$model->extension = $extension;
					$model->file_name = $model->getFileName();
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
				'type' => $issue->type,
				'notes' => $issue->notes,
				'created_at' => $issue->created_at,
				'location' => $issue->location,
				'image_url' => $issue->getFileUrl(),
			);
		}

		return $data;
	}

	public function actionList() {
		if($this->_user) {
			$issues = Issue::model()->findAll(array(
				"condition"=>"user_id = :user_id",
				"params"=>array('user_id'=>$this->_user->id)
			));

			$zipcode_issues = Issue::model()->with("user")->findAll(array(
				"condition"=>"zipcode = :zipcode",
				"params"=>array('zipcode'=>$this->_user->zipcode)
			));

			$male_issues = Issue::model()->with("user")->findAll(array(
				"condition"=>"sex = 0"
			));

			$female_issues = Issue::model()->with("user")->findAll(array(
				"condition"=>"sex = 1"
			));

			/*$location_issues = Issue::model()->with("user")->findAll(array(
				"condition"=>"location = :location",
				"params"=>array('location'=>$this->_user->location)
			));*/


			$this->renderJSON(array('status'=>'SUCCESS', 'issues'=> $this->getIssuesData($issues), 'zipcode_issues'=> $this->getIssuesData($zipcode_issues), 'male_issues'=> $this->getIssuesData($male_issues), 'female_issues'=> $this->getIssuesData($female_issues)));
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
				$conditions[] = "type = :type";
				$params['type'] = $_GET['type'];
			}
			if(array_key_exists('location', $_GET)) {
				$conditions[] = "user.location = :location";
				$params['location'] = $_GET['location'];
			}
			if(array_key_exists('zipcode', $_GET)) {
				$conditions[] = "user.zipcode = :zipcode";
				$params['zipcode'] = $this->_user->zipcode;
			}
			if(array_key_exists('gender', $_GET)) {
				$conditions[] = "user.sex = :gender";
				$params['gender'] = $_GET['gender'];
			}
			$issues = Issue::model()->with("user")->findAll(array(
				"condition"=>implode(" and ", $conditions),
				"params"=>$params
			));

			$this->renderJSON(array('status'=>'SUCCESS', 'issues'=> $this->getIssuesData($issues)));
		}
		else {
			$this->renderJSON(array('status'=>'ERROR', 'message'=>"Insufficient Data!"));
		}
	}
}