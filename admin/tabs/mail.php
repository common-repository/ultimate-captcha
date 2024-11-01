<?php 
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
global $ultimatecaptcha;

?>
<h3><?php _e('Advanced Email Options','ultimate-captcha'); ?></h3>
<form method="post" action="" id="b_frm_settings" name="b_frm_settings">
<input type="hidden" name="ultimatecaptcha_update_settings" />
<input type="hidden" name="ultimatecaptcha_reset_email_template" id="ultimatecaptcha_reset_email_template" />
<input type="hidden" name="email_template" id="email_template" />


  <p><?php _e('Here you can control how WP Ticket Ultra will send the notification to your users.','ultimate-captcha'); ?></p>


<div class="ultimatecaptcha-sect  ultimatecaptcha-welcome-panel">  
   <table class="form-table">
<?php 
 

$this->create_plugin_setting(
        'input',
        'messaging_send_from_name',
        __('Send From Name','ultimate-captcha'),array(),
        __('Enter the your name or company name here.','ultimate-captcha'),
        __('Enter the your name or company name here.','ultimate-captcha')
);

$this->create_plugin_setting(
        'input',
        'messaging_send_from_email',
        __('Send From Email','ultimate-captcha'),array(),
        __('Enter the email address to be used when sending emails.','ultimate-captcha'),
        __('Enter the email address to be used when sending emails.','ultimate-captcha')
);

$this->create_plugin_setting(
	'select',
	'bup_smtp_mailing_mailer',
	__('Mailer:','ultimate-captcha'),
	array(
		'mail' => __('Use the PHP mail() function to send emails','ultimate-captcha'),
		'smtp' => __('Send all emails via SMTP','ultimate-captcha'), 
		'mandrill' => __('Send all emails via Mandrill','ultimate-captcha'),
		'third-party' => __('Send all emails via Third-party plugin','ultimate-captcha'), 
		
		),
		
	__('Specify which mailer method the pluigin should use when sending emails.','ultimate-captcha'),
  __('Specify which mailer method the pluigin should use when sending emails.','ultimate-captcha')
       );
	   
$this->create_plugin_setting(
                'checkbox',
                'bup_smtp_mailing_return_path',
                __('Return Path','ultimate-captcha'),
                '1',
                __('Set the return-path to match the From Email','ultimate-captcha'),
                __('Set the return-path to match the From Email','ultimate-captcha')
        ); 
?>
 </table>

 
 </div>
 
 
 
 <div class="ultimatecaptcha-sect  ultimatecaptcha-welcome-panel">
 
 <h3><?php _e('SMTP Settings','ultimate-captcha'); ?></h3>
  <p> <strong><?php _e('This options should be set only if you have chosen to send email via SMTP','ultimate-captcha'); ?></strong></p>
 
  <table class="form-table">
 <?php
$this->create_plugin_setting(
        'input',
        'bup_smtp_mailing_host',
        __('SMTP Host:','ultimate-captcha'),array(),
        __('Specify host name or ip address.','ultimate-captcha'),
        __('Specify host name or ip address.','ultimate-captcha')
); 

$this->create_plugin_setting(
        'input',
        'bup_smtp_mailing_port',
        __('SMTP Port:','ultimate-captcha'),array(),
        __('Specify Port.','ultimate-captcha'),
        __('Specify Port.','ultimate-captcha')
); 


$this->create_plugin_setting(
	'select',
	'bup_smtp_mailing_encrytion',
	__('Encryption:','ultimate-captcha'),
	array(
		'none' => __('No encryption','ultimate-captcha'),
		'ssl' => __('Use SSL encryption','ultimate-captcha'), 
		'tls' => __('Use TLS encryption','ultimate-captcha'), 
		
		),
		
	__('Specify the encryption method.','ultimate-captcha'),
  __('Specify the encryption method.','ultimate-captcha')
       );
	   
$this->create_plugin_setting(
	'select',
	'bup_smtp_mailing_authentication',
	__('Authentication:','ultimate-captcha'),
	array(
		'false' => __('No. Do not use SMTP authentication','ultimate-captcha'),
		'true' => __('Yes. Use SMTP Authentication','ultimate-captcha'), 
		
		),
		
	__('Specify the authentication method.','ultimate-captcha'),
  __('Specify the authentication method.','ultimate-captcha')
       );

$this->create_plugin_setting(
        'input',
        'bup_smtp_mailing_username',
        __('Username:','ultimate-captcha'),array(),
        __('Specify Username.','ultimate-captcha'),
        __('Specify Username.','ultimate-captcha')
); 

$this->create_plugin_setting(
        'input',
        'bup_smtp_mailing_password',
        __('Password:','ultimate-captcha'),array(),
        __('Input Password.','ultimate-captcha'),
        __('Input Password.','ultimate-captcha')
); 


 ?>
 
 </table>
 
 
 </div>
 






<div class="ultimatecaptcha-sect  ultimatecaptcha-welcome-panel">
  <h3><?php _e('User Registration Email','ultimate-captcha'); ?> <?php echo $label_pro?> <span class="ultimatecaptcha-main-close-open-tab"><a href="#" title="<?php _e('Close','ultimate-captcha'); ?>" class="ultimatecaptcha-widget-home-colapsable" widget-id="666"><i class="fa fa-sort-desc" id="ultimatecaptcha-close-open-icon-666"></i></a></span></h3>
  
  <p><?php _e('This message will be sent to the user and it includes the password.','ultimate-captcha'); ?></p>
<div class="ultimatecaptcha-messaging-hidden" id="ultimatecaptcha-main-cont-home-666">  
  
   <table class="form-table">

<?php 


$this->create_plugin_setting(
        'input',
        'email_registration_subject',
        __('Subject:','ultimate-captcha'),array(),
        __('Set Email Subject.','ultimate-captcha'),
        __('Set Email Subject.','ultimate-captcha')
); 

$this->create_plugin_setting(
        'textarearich',
        'email_registration_body',
        __('Message','ultimate-captcha'),array(),
        __('Set Email Message here.','ultimate-captcha'),
        __('Set Email Message here.','ultimate-captcha')
);
	
?>

<tr>

<th></th>
<td><input type="button" value="<?php _e('RESTORE DEFAULT TEMPLATE','ultimate-captcha'); ?>" class="ultimatecaptcha_restore_template button" b-template-id='email_registration_body'></td>

</tr>	
</table> 
</div>

</div>











<p class="submit">
	<input type="submit" name="mail_setting_submit" id="mail_setting_submit" class="button button-primary" value="<?php _e('Save Changes','ultimate-captcha'); ?>"  />

</p>

</form>