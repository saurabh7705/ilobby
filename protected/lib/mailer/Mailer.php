<?php
//require_once 'EMailer.php';
class Mailer extends EMailer {

        public function __construct() {
            parent::__construct();
        }
	
	public function deliver() {
		try {
            $this->Send();            
		}
		catch(Exception $e) {
		}
	}
	
	public function mail($mail, $subject, $view_body_params, $link_params=array()) {
		$this->AddAddress($mail);
		$this->__set('Subject',$subject);
		$this->setViewPath($this);
		$this->getView($view_body_params[0],$view_body_params[1],$view_body_params[2], $link_params);
		$this->isHTML($view_body_params[3]);
		return $this;
	}
        
    public function layoutMail($mail, $subject, $body, $vars, $layout, $link_params=array()) {
        $this->AddAddress($mail);
		$this->__set('Subject',$subject);
        $this->getLayoutView($body, $vars, $layout, $link_params);
        $this->isHTML(1);
        return $this;
	}
	
	public function setViewPath($obj) {
		$view_path = "application.views.email";
		$class_name = get_class($obj);
                $this->setPathViews($view_path.'.'.$this->getFilePathFromClassName($class_name));
       }

	public function getFilePathFromClassName($class_name) {
		return $this->fromCamelCase($class_name);
	}
	
	private function fromCamelCase($str) {
		$str[0] = strtolower($str[0]);
	    $func = create_function('$c', 'return "_" . strtolower($c[1]);');
	    return preg_replace_callback('/([A-Z])/', $func, $str);
	}
}?>
