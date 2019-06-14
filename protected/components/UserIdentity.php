<?php

/**
* UserIdentity represents the data needed to identity a user.
* It contains the authentication method that checks if the provided
* data can identity the user.
*/
class UserIdentity extends CUserIdentity
{
	private $_id;
	public $user;
	const ERROR_ACCOUNT_NOT_CONFIRMED = 3;

	/**
	* Authenticates a user.
	* The example implementation makes sure if the username and password
	* are both 'demo'.
	* In practical applications, this should be changed to authenticate
	* against some persistent user identity storage (e.g. database).
	* @return boolean whether authentication succeeds.
	*/

	public function getId()
	{
		return $this->_id;
	}

	public function authenticate() {
		$user = User::model()->find(array('condition'=>"email=:email OR phone=:email", 'params'=>array('email'=>$this->username)));
		if($user) {
			if(md5($this->password) == $user->password) {
				if(false) {
				//if(!$user->is_verified) {
					$this->errorCode = self::ERROR_ACCOUNT_NOT_CONFIRMED;
				}
				else {
					$this->_id = $user->id;
					$this->user = $user;
					$this->errorCode = self::ERROR_NONE;
				}
			}
			else 
				$this->errorCode=self::ERROR_PASSWORD_INVALID;  
		}
		return !$this->errorCode;
	}
}