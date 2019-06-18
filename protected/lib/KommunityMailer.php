<?php
class KommunityMailer extends Mailer {

        public static function mailer() {
            return new KommunityMailer;
        }			
        
        public $subjects=array(
			'confirmation_email' => 'Confirm your Email',
        );

        public function confirmationEmail($user) {
			$link_params = array();
            return $this->mail(
            	$user->email,
            	$this->subjects["confirmation_email"],  
            	array("confirmation_email", array('user'=>$user, "email_type"=> "confirmation_email","confirm",1)), 
            	$link_params
            );
        }
}
?>
