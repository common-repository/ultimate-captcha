<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
global $ultimatecaptcha, $ultimatecaptchacomplement, $ultimatecaptcha_aweber, $ultimatecaptcha_mailchimp, $ultimatecaptcha_recaptcha, $ultimatecaptcha_activation;
?>
<h3><?php _e('Plugin Settings','ultimate-captcha'); ?></h3>
<form method="post" action="">
<input type="hidden" name="ultimatecaptcha_update_settings" />


<div id="tabs-bupro-settings" class="ultimatecaptcha-multi-tab-options">

<ul class="nav-tab-wrapper bup-nav-pro-features">



<li class="nav-tab bup-pro-li"><a href="#tabs-ultimatecaptcha-recaptcha" title="<?php _e('reCaptcha','ultimate-captcha'); ?>"><?php _e('reCaptcha','ultimate-captcha'); ?> </a></li>



<li class="nav-tab bup-pro-li"><a href="#tabs-bup-newsletter" title="<?php _e('Newsletter','ultimate-captcha'); ?>"><?php _e('Newsletter','ultimate-captcha'); ?> </a></li>



</ul>

<div id="tabs-bup-newsletter">
  
  <?php if(isset($ultimatecaptcha_aweber) || isset($ultimatecaptcha_mailchimp))
{?>


<div class="ultimatecaptcha-sect ultimatecaptcha-welcome-panel ">
<h3><?php _e('Newsletter Preferences','ultimate-captcha'); ?></h3>
  
  <p><?php _e('Here you can activate your preferred newsletter tool.','ultimate-captcha'); ?></p>

<table class="form-table">
<?php 
   
$this->create_plugin_setting(
	'select',
	'newsletter_active',
	__('Activate Newsletter','ultimate-captcha'),
	array(
		'no' => __('No','ultimate-captcha'), 
		'aweber' => __('AWeber','ultimate-captcha'),
		'mailchimp' => __('MailChimp','ultimate-captcha'),
		),
		
	__('Just set "NO" to deactivate the newsletter tool.','ultimate-captcha'),
  __('Just set "NO" to deactivate the newsletter tool.','ultimate-captcha')
       );

	
?>
</table>

<p class="submit">
	<input type="submit" name="submit" id="submit" class="button button-primary" value="<?php _e('Save Changes','ultimate-captcha'); ?>"  />
</p>


</div>


<?php }else{?>


<div class="ultimatecaptcha-sect  ultimatecaptcha-welcome-panel">

<p><?php _e('This function is available only on certain versions.','ultimate-captcha'); ?>. Click <a href="https://ultimatecaptcha.com/compare-packages.php">here</a> to compare packages </p>


</div>

<?php }?> 
  <?php if(isset($ultimatecaptcha_aweber))
{?>


<div class="ultimatecaptcha-sect ultimatecaptcha-welcome-panel ">
<h3><?php _e('Aweber Settings','ultimate-captcha'); ?></h3>
  
  <p><?php _e('This module gives you the capability to subscribe your clients automatically to any of your Aweber List when they submit a ticket.','ultimate-captcha'); ?></p>
  
  
<table class="form-table">
<?php 
   
		
$this->create_plugin_setting(
        'input',
        'aweber_app_id',
        __('APP ID','ultimate-captcha'),array(),
        __('Fill out this field with your AWeber APP ID.','ultimate-captcha'),
        __('Fill out this field with your AWeber APP ID.','ultimate-captcha')
);

$this->create_plugin_setting(
        'input',
        'aweber_consumer_key',
        __('Consumer Key','ultimate-captcha'),array(),
        __('Fill out this field your AWeber Consumer Key.','ultimate-captcha'),
        __('Fill out this field your AWeber Consumer Key.','ultimate-captcha')
);

$this->create_plugin_setting(
        'input',
        'aweber_consumer_secret',
        __('Consumer Secret','ultimate-captcha'),array(),
        __('Fill out this field your AWeber Consumer Secret.','ultimate-captcha'),
        __('Fill out this field your AWeber Consumer Secret.','ultimate-captcha')
);




$this->create_plugin_setting(
                'checkbox',
                'aweber_auto_text',
                __('Auto Checked Aweber','ultimate-captcha'),
                '1',
                __('If checked, the user will not need to click on the AWeber checkbox. It will appear checked already.','ultimate-captcha'),
                __('If checked, the user will not need to click on the AWeber checkbox. It will appear checked already.','ultimate-captcha')
        );
$this->create_plugin_setting(
        'input',
        'aweber_text',
        __('Aweber Text','ultimate-captcha'),array(),
        __('Please input the text that will appear when asking users to get periodical updates.','ultimate-captcha'),
        __('Please input the text that will appear when asking users to get periodical updates.','ultimate-captcha')
);

	$this->create_plugin_setting(
        'input',
        'aweber_header_text',
        __('Aweber Header Text','ultimate-captcha'),array(),
        __('Please input the text that will appear as header when AWeber is active.','ultimate-captcha'),
        __('Please input the text that will appear as header when AWeber is active.','ultimate-captcha')
);
	
?>
</table>

<p class="submit">
	<input type="submit" name="submit" id="submit" class="button button-primary" value="<?php _e('Save Changes','ultimate-captcha'); ?>"  />
</p>


</div>

<?php }?> 


  <?php if(isset($ultimatecaptcha_mailchimp))
{?>


<div class="ultimatecaptcha-sect ultimatecaptcha-welcome-panel ">
<h3><?php _e('MailChimp Settings','ultimate-captcha'); ?></h3>
  
  <p><?php _e('.','ultimate-captcha'); ?></p>
  
  
<table class="form-table">
<?php 
   
		
$this->create_plugin_setting(
        'input',
        'mailchimp_api',
        __('MailChimp API Key','ultimate-captcha'),array(),
        __('Fill out this field with your MailChimp API key here to allow integration with MailChimp subscription.','ultimate-captcha'),
        __('Fill out this field with your MailChimp API key here to allow integration with MailChimp subscription.','ultimate-captcha')
);

$this->create_plugin_setting(
        'input',
        'mailchimp_list_id',
        __('MailChimp List ID','ultimate-captcha'),array(),
        __('Fill out this field your list ID.','ultimate-captcha'),
        __('Fill out this field your list ID.','ultimate-captcha')
);



$this->create_plugin_setting(
                'checkbox',
                'mailchimp_auto_checked',
                __('Auto Checked MailChimp','ultimate-captcha'),
                '1',
                __('If checked, the user will not need to click on the mailchip checkbox. It will appear checked already.','ultimate-captcha'),
                __('If checked, the user will not need to click on the mailchip checkbox. It will appear checked already.','ultimate-captcha')
        );
$this->create_plugin_setting(
        'input',
        'mailchimp_text',
        __('MailChimp Text','ultimate-captcha'),array(),
        __('Please input the text that will appear when asking users to get periodical updates.','ultimate-captcha'),
        __('Please input the text that will appear when asking users to get periodical updates.','ultimate-captcha')
);

	$this->create_plugin_setting(
        'input',
        'mailchimp_header_text',
        __('MailChimp Header Text','ultimate-captcha'),array(),
        __('Please input the text that will appear as header when mailchip is active.','ultimate-captcha'),
        __('Please input the text that will appear as header when mailchip is active.','ultimate-captcha')
);
	
?>
</table>

<p class="submit">
	<input type="submit" name="submit" id="submit" class="button button-primary" value="<?php _e('Save Changes','ultimate-captcha'); ?>"  />
</p>


</div>



<?php }?>  
  
  


</div>




<div id="tabs-ultimatecaptcha-recaptcha">


<div class="ultimatecaptcha-sect  ultimatecaptcha-welcome-panel">
  <h3><?php _e('reCaptcha','ultimate-captcha'); ?></h3>
  
    
    <p><?php _e("You can get the Site Key and Secret Key on Google reCaptcha Dashboard",'ultimate-captcha'); ?>. <a href="https://www.google.com/recaptcha/admin" target="_blank"> <?php _e("Click here",'ultimate-captcha'); ?> </a> </p>
    
    <p><?php _e("You may check the reCaptcha setup tutorial as well. ",'ultimate-captcha'); ?> <a href="http://docs.ultimatecaptcha.com/installing-recaptcha/" target="_blank"> <?php _e("Click here",'ultimate-captcha'); ?> </a> </p>
    
     
  
  <table class="form-table">
<?php


	$this->create_plugin_setting(
			'input',
			'recaptcha_site_key',
			__('Site Key:','ultimate-captcha'),array(),
			__('Enter your site key here.','ultimate-captcha'),
			__('Enter your site key here.','ultimate-captcha')
	);
	
	$this->create_plugin_setting(
			'input',
			'recaptcha_secret_key',
			__('Secret Key:','ultimate-captcha'),array(),
			__('Enter your site secret here.','ultimate-captcha'),
			__('Enter your site secret here.','ultimate-captcha')
	);

	
?>
</table>
</div>


<div class="ultimatecaptcha-sect  ultimatecaptcha-welcome-panel">
  <h3><?php _e('Protect WordPress Default Pages','ultimate-captcha'); ?></h3>
  
    
  <p><?php _e('Select what pages will be protected by reCaptcha','ultimate-captcha'); ?></p>
  
  <table class="form-table">
<?php


	$this->create_plugin_setting(
                'checkbox',
                'recaptcha_display_registration_native',
                __('Registration Form','ultimate-captcha'),
                '1',
                __('If checked, the reCaptcha will be displayed in the registration form.','ultimate-captcha'),
                __('If checked, the reCaptcha will be displayed in the registration form.','ultimate-captcha')
        );
		
	$this->create_plugin_setting(
                'checkbox',
                'recaptcha_display_loginform_native',
                __('Login Form','ultimate-captcha'),
                '1',
                __('If checked, the reCaptcha will be displayed in the login form.','ultimate-captcha'),
                __('If checked, the reCaptcha will be displayed in the login form.','ultimate-captcha')
        );
		
	
	$this->create_plugin_setting(
                'checkbox',
                'recaptcha_display_forgot_password_native',
                __('Forgot Password Form','ultimate-captcha'),
                '1',
                __('If checked, the reCaptcha will be displayed in the forgot password form.','ultimate-captcha'),
                __('If checked, the reCaptcha will be displayed in the forgot password form.','ultimate-captcha')
        ); 
		
		
		$this->create_plugin_setting(
                'checkbox',
                'recaptcha_display_comments_native',
                __('Comments','ultimate-captcha'),
                '1',
                __('If checked, the reCaptcha will be displayed in the forgot password form.','ultimate-captcha'),
                __('If checked, the reCaptcha will be displayed in the forgot password form.','ultimate-captcha')
        ); 
		
	
		
?>
</table>
</div>

<?php if (isset($ultimatecaptcha_activation)){?>

<div class="ultimatecaptcha-sect  ultimatecaptcha-welcome-panel">
  <h3><?php _e('Custom Pages','ultimate-captcha'); ?></h3>
  
    
  <p><?php _e('Select what pages will be protected by reCaptcha','ultimate-captcha'); ?></p>
  
  <table class="form-table">
<?php


	$this->create_plugin_setting(
                'checkbox',
                'recaptcha_display_registration',
                __('Registration Form','ultimate-captcha'),
                '1',
                __('If checked, the reCaptcha will be displayed in the registration form.','ultimate-captcha'),
                __('If checked, the reCaptcha will be displayed in the registration form.','ultimate-captcha')
        );
		
	$this->create_plugin_setting(
                'checkbox',
                'recaptcha_display_loginform',
                __('Login Form','ultimate-captcha'),
                '1',
                __('If checked, the reCaptcha will be displayed in the login form.','ultimate-captcha'),
                __('If checked, the reCaptcha will be displayed in the login form.','ultimate-captcha')
        );
		
	
	$this->create_plugin_setting(
                'checkbox',
                'recaptcha_display_forgot_password',
                __('Forgot Password Form','ultimate-captcha'),
                '1',
                __('If checked, the reCaptcha will be displayed in the forgot password form.','ultimate-captcha'),
                __('If checked, the reCaptcha will be displayed in the forgot password form.','ultimate-captcha')
        ); 
		
	
		
?>
</table>
</div>

<?php }?>

<p class="submit">
	<input type="submit" name="submit" id="submit" class="button button-primary" value="<?php _e('Save Changes','ultimate-captcha'); ?>"  />
</p>

  
</div>



</div>




</form>