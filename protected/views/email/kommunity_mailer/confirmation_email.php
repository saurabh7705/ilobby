<?php
/*Usage
 * use $user to access user model
 */
$url = "http://54.202.6.65/ilobby/index.php/session/confirm?token=".$user->id;
?>
<div id="content">
	<p style="background-color: #FFFFFF;padding:20px; font-size: 14px;">
		Please click on the button below to verify your account on Kommunity. <br /><br />
		<a href="<?php echo $url ?>" style="margin-left: 20px; background-color: #5BB75B; background-image: -moz-linear-gradient(center top , #62C462, #51A351); border: rgba(0, 0, 0, 0.1) rgba(0, 0, 0, 0.1) rgba(0, 0, 0, 0.25); border-radius: 6px 6px 6px 6px; height: 34px; font-size:16px; padding: 9px 15px; text-decoration: none; color:#FFF; font-weight:bold;">
			Confirm Email
		</a>
	</p>	
</div>