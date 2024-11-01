<?php
global $ultimatecaptcha, $ultimatecaptcha_staff_profile;
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

?>

<form method="post" action="">
<input type="hidden" name="update_settings" />

<div class="ultimatecaptcha-sect  ultimatecaptcha-welcome-panel ">

 <h3><?php _e('Ticket Ultra Pro Staff Profile Pages','ultimate-captcha'); ?></h3>
        
              <p><?php _e('Here you can set your custom pages for the staff profiles.','ultimate-captcha'); ?></p>
        
  <table class="form-table">
<?php 



	$ultimatecaptcha->create_plugin_setting(
            'select',
            'ultimatecaptcha_registration_page',
            __('Registration Page','ultimate-captcha'),
            $ultimatecaptcha->get_all_sytem_pages(),
            __('Make sure you have the <code>[ultimatecaptcha_user_signup]</code> shortcode on this page.','ultimate-captcha'),
            __('This page is where users will be able to sign up to your website.','ultimate-captcha')
    );

	
	$ultimatecaptcha->create_plugin_setting(
            'select',
            'bup_my_account_page',
            __('My Account Page','ultimate-captcha'),
            $ultimatecaptcha->get_all_sytem_pages(),
            __('Make sure you have the <code>[ultimatecaptcha_account]</code> shortcode on this page.','ultimate-captcha'),
            __('This page is where users and staff members will be able to manage their appointments.','ultimate-captcha')
    );
	
	$ultimatecaptcha->create_plugin_setting(
            'select',
            'bup_user_login_page',
            __('Users Login Page','ultimate-captcha'),
            $ultimatecaptcha->get_all_sytem_pages(),
            __('Make sure you have the <code>[ultimatecaptcha_user_login]</code> shortcode on this page.','ultimate-captcha'),
            __('This page is where users and staff members & clients will be able to recover to login to their accounts.','ultimate-captcha')
    );
	
	
		$ultimatecaptcha->create_plugin_setting(
            'select',
            'bup_password_reset_page',
            __('Password Recover Page','ultimate-captcha'),
            $ultimatecaptcha->get_all_sytem_pages(),
            __('Make sure you have the <code>[ultimatecaptcha_user_recover_password]</code> shortcode on this page.','ultimate-captcha'),
            __('This page is where users and staff members will be able to recover their passwords.','ultimate-captcha')
    );
	
	
			
	$ultimatecaptcha->create_plugin_setting(
	'select',
	'hide_admin_bar',
	__('Hide WP Admin Tool Bar?','ultimate-captcha'),
	array(
		0 => __('NO','ultimate-captcha'), 		
		1 => __('YES','ultimate-captcha')),
		
	__('If checked, User will not see the WP Admin Tool Bar','ultimate-captcha'),
  __('If checked, User will not see the WP Admin Tool Bar','ultimate-captcha')
       );
	   
	     
	
	   
		
?>
</table>      
   

             

</div>

<p class="submit">
	<input type="submit" name="submit" id="submit" class="button button-primary" value="<?php _e('Save Changes','ultimate-captcha'); ?>"  />
	
</p>

</form>

