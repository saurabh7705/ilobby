<?php
/**
 * Controller is the customized base controller class.
 * All controller classes for this application should extend from this base class.
 */
class Controller extends CController
{
	public $_user;
	/**
	 * @var string the default layout for the controller view. Defaults to '//layouts/column1',
	 * meaning using a single column layout. See 'protected/views/layouts/column1.php'.
	 */
	public $layout='//layouts/column1';
	/**
	 * @var array context menu items. This property will be assigned to {@link CMenu::items}.
	 */
	public $menu=array();
	/**
	 * @var array the breadcrumbs of the current page. The value of this property will
	 * be assigned to {@link CBreadcrumbs::links}. Please refer to {@link CBreadcrumbs::links}
	 * for more details on how to specify this property.
	 */
	public $breadcrumbs=array();

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

	protected function renderJSON($data) {
	    header('Content-type: application/json');
	    echo CJSON::encode($data);

	    foreach (Yii::app()->log->routes as $route) {
	        if($route instanceof CWebLogRoute) {
	            $route->enabled = false; // disable any weblogroutes
	        }
	    }
	    Yii::app()->end();
	    //$this->renderJSON($yourData);
	}
}