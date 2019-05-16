<?php
class LoadDataHelper {

	public $_user;
	public $_display_density_multiplier;

	public static function lib() {
		$helper = new LoadDataHelper;
		return $helper;
	}

	public function getModelErrorsArray($model) {
		$error_arr = array();
		foreach($model->getErrors() as $errors) {
			foreach($errors as $error) {
				$error_arr[] = $error;
			}
		}
		return $error_arr;
	}
}
?>