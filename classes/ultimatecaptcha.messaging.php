<?php
class UltimateCaptchaMessaging extends UltimateCaptchaCommon 
{
	var $mHeader;
	var $mEmailPlainHTML;
	var $mHeaderSentFromName;
	var $mHeaderSentFromEmail;
	var $mCompanyName;
	
	var $include_ticket_subject;
	var $include_ticket_number;
	

	function __construct() 
	{
		$this->setContentType();
		$this->setFromEmails();				
		$this->set_headers();	
		
	}
	
	function setFromEmails() 
	{
		global $ultimatecaptcha;
			
		$from_name =  $this->get_option('messaging_send_from_name'); 
		$from_email = $this->get_option('messaging_send_from_email'); 	
		if ($from_email=="")
		{
			$from_email =get_option('admin_email');
			
		}		
		$this->mHeaderSentFromName=$from_name;
		$this->mHeaderSentFromEmail=$from_email;
		
		
    }
	
	function setContentType() 
	{
		global $ultimatecaptcha;			
				
		$this->mEmailPlainHTML="text/html";
    }
	
	/* get setting */
	function get_option($option) 
	{
		$settings = get_option('ultimatecaptcha_options');
		if (isset($settings[$option])) 
		{
			return $settings[$option];
			
		}else{
			
		    return '';
		}
		    
	}
	
	public function set_headers() 
	{   			
		//Make Headers aminnistrators	
		$headers[] = "Content-type: ".$this->mEmailPlainHTML."; charset=UTF-8";
		$headers[] = "From: ".$this->mHeaderSentFromName." <".$this->mHeaderSentFromEmail.">";
		$headers[] = "Organization: ".$this->mCompanyName;	
		$this->mHeader = $headers;		
    }
	
	
	public function  send ($to, $subject, $message)
	{
		global $ultimatecaptcha , $phpmailer;
		
		$message = nl2br($message);
		//check mailing method	
		$bup_emailer = $ultimatecaptcha->get_option('bup_smtp_mailing_mailer');
		
		if($bup_emailer=='mail' || $bup_emailer=='' ) //use the defaul email function
		{
			$err = wp_mail( $to , $subject, $message, $this->mHeader);
			
			//echo $err. 'message: '.$message;
		
		}elseif($bup_emailer=='mandrill' && is_email($to)){ //send email via Mandrill
		
			$this->send_mandrill( $to , $recipient_name, $subject, $message);
		
		}elseif($bup_emailer=='third-party' && is_email($to)){ //send email via Third-Party
		
			if (function_exists('ucaptcha_third_party_email_sender')) 
			{
				
				ucaptcha_third_party_email_sender($to , $subject, $message);				
				
			}
			
		}elseif($bup_emailer=='smtp' &&  is_email($to)){ //send email via SMTP
		
			// Make sure the PHPMailer class has been instantiated 
			// (copied verbatim from wp-includes/pluggable.php)
			// (Re)create it, if it's gone missing
			if ( !is_object( $phpmailer ) || !is_a( $phpmailer, 'PHPMailer' ) ) {
				require_once ABSPATH . WPINC . '/class-phpmailer.php';
				require_once ABSPATH . WPINC . '/class-smtp.php';
				$phpmailer = new PHPMailer( true );
			}
			
			
			$phpmailer->IsSMTP(); // use SMTP
			
			
			// Empty out the values that may be set
			$phpmailer->ClearAddresses();
			$phpmailer->ClearAllRecipients();
			$phpmailer->ClearAttachments();
			$phpmailer->ClearBCCs();			
			
			// Set the mailer type as per config above, this overrides the already called isMail method
			$phpmailer->Mailer = $bup_emailer;
						
			$phpmailer->From     = $ultimatecaptcha->get_option('messaging_send_from_email');
			$phpmailer->FromName =  $ultimatecaptcha->get_option('messaging_send_from_name');
			
			//Set the subject line
			$phpmailer->Subject = $subject;			
			$phpmailer->CharSet     = 'UTF-8';
			
			//Set who the message is to be sent from
			//$phpmailer->SetFrom($phpmailer->FromName, $phpmailer->From);
			
			//Read an HTML message body from an external file, convert referenced images to embedded, convert HTML into a basic plain-text alternative body
			
			
			// Set the Sender (return-path) if required
			if ($ultimatecaptcha->get_option('bup_smtp_mailing_return_path')=='1')
				$phpmailer->Sender = $phpmailer->From; 
			
			// Set the SMTPSecure value, if set to none, leave this blank
			$uultra_encryption = $ultimatecaptcha->get_option('bup_smtp_mailing_encrytion');
			$phpmailer->SMTPSecure = $uultra_encryption == 'none' ? '' : $uultra_encryption;
			
			// If we're sending via SMTP, set the host
			if ($bup_emailer == "smtp")
			{				
				// Set the SMTPSecure value, if set to none, leave this blank
				$phpmailer->SMTPSecure = $uultra_encryption == 'none' ? '' : $uultra_encryption;
				
				// Set the other options
				$phpmailer->Host = $ultimatecaptcha->get_option('bup_smtp_mailing_host');
				$phpmailer->Port = $ultimatecaptcha->get_option('bup_smtp_mailing_port');
				
				// If we're using smtp auth, set the username & password
				if ($ultimatecaptcha->get_option('bup_smtp_mailing_authentication') == "true") 
				{
					$phpmailer->SMTPAuth = TRUE;
					$phpmailer->Username = $ultimatecaptcha->get_option('bup_smtp_mailing_username');
					$phpmailer->Password = $ultimatecaptcha->get_option('bup_smtp_mailing_password');
				}
				
			}
			
			//html plain text			
			$phpmailer->IsHTML(true);	
			$phpmailer->MsgHTML($message);	
			
			//Set who the message is to be sent to
			$phpmailer->AddAddress($to);
			
			//$phpmailer->SMTPDebug = 2;	
			
			//Send the message, check for errors
			if(!$phpmailer->Send()) {
			  echo "Mailer Error: " . $phpmailer->ErrorInfo;
			  exit();
			} else {
			//  echo "Message sent!";
			  
			 
			}
			
		
			//exit;

		
		}
		
		
		
	}
	
	public function  send_mandrill ($to, $recipient_name, $subject, $message_html)
	{
		global $ultimatecaptcha , $phpmailer;
		require_once(ucaptcha_path."libs/mandrill/Mandrill.php");
		
		$from_email     = $ultimatecaptcha->get_option('messaging_send_from_email');
		$from_name =  $ultimatecaptcha->get_option('messaging_send_from_name');
		$api_key =  $ultimatecaptcha->get_option('bup_mandrill_api_key');
		
					
		$text_html =  $message_html;
		$text_txt =  "";
			
		
		try {
				$mandrill = new Mandrill($api_key);
				$message = array(
					'html' => $text_html,
					'text' => $text_txt,
					'subject' => $subject,
					'from_email' => $from_email,
					'from_name' => $from_name,
					'to' => array(
						array(
							'email' => $to,
							'name' => $recipient_name,
							'type' => 'to'
						)
					),
					'headers' => array('Reply-To' => $from_email, 'Content-type' => $this->mEmailPlainHTML),
					'important' => false,
					'track_opens' => null,
					'track_clicks' => null,
					'auto_text' => null,
					'auto_html' => null,
					'inline_css' => null,
					'url_strip_qs' => null,
					'preserve_recipients' => null,
					'view_content_link' => null,
					/*'bcc_address' => 'message.bcc_address@example.com',*/
					'tracking_domain' => null,
					'signing_domain' => null,
					'return_path_domain' => null
					/*'merge' => true,
					'global_merge_vars' => array(
						array(
							'name' => 'merge1',
							'content' => 'merge1 content'
						)
					),
					
					
					/*'google_analytics_domains' => array('example.com'),
					'google_analytics_campaign' => 'message.from_email@example.com',
					'metadata' => array('website' => 'www.example.com'),*/
					
				);
				$async = false;
				$ip_pool = 'Main Pool';
				$send_at = date("Y-m-d H:i:s");
				//$result = $mandrill->messages->send($message, $async, $ip_pool, $send_at);
				$result = $mandrill->messages->send($message, $async);
				//print_r($result);
				
			} catch(Mandrill_Error $e) {
				// Mandrill errors are thrown as exceptions
				echo 'A mandrill error occurred: ' . get_class($e) . ' - ' . $e->getMessage();
				// A mandrill error occurred: Mandrill_Unknown_Subaccount - No subaccount exists with the id 'customer-123'
				throw $e;
			}
	}
	
	//--- Parse Custom Fields
	public function  parse_custom_fields($content, $ticket )
	{
		global $ultimatecaptcha, $wptucomplement;
		
		if(isset($wptucomplement))
		{
			
			preg_match_all("/\[([^\]]*)\]/", $content, $matches);
			$results = $matches[1];			
			$custom_fields_col = array();
			
			foreach ($results as $field){
				
				//clean field
				$clean_field = str_replace("UCAPTCHA_CUSTOM_", "", $field);
				$custom_fields_col[] = $clean_field;
			
			}
			
			foreach ($custom_fields_col as $field)
			{
				//get field data from booking table				
				$field_data = $ultimatecaptcha->ticket->get_ticket_meta($ticket->ticket_id, $field);
				//replace data in template				
				$content = str_replace("[UCAPTCHA_CUSTOM_".$field."]", $field_data, $content);				
							
			}
			
			

			
		}
		
		return $content;
		
	}
	
	
	
	
	//--- Reset Link	
	public function  send_reset_link($receiver, $link)
	{
		global $ultimatecaptcha;
		
		$admin_email =get_option('admin_email'); 
		$company_name = $this->get_option('company_name');
		$company_phone = $this->get_option('company_phone');
		
		$u_email = $receiver->user_email;
		
		$template_client =stripslashes($this->get_option('email_reset_link_message_body'));
		$subject = $this->get_option('email_reset_link_message_subject');
		
		$template_client = str_replace("{{ucaptcha_staff_name}}", $receiver->display_name,  $template_client);				
		$template_client = str_replace("{{ucaptcha_reset_link}}", $link,  $template_client);
		
		$template_client = str_replace("{{ucaptcha_company_name}}", $company_name,  $template_client);
		$template_client = str_replace("{{ucaptcha_company_phone}}", $company_phone,  $template_client);
		$template_client = str_replace("{{ucaptcha_company_url}}", $site_url,  $template_client);	
		
		$this->send($u_email, $subject, $template_client);				
		
	}

	
		//--- Registration Link
	public function  send_client_registration_link($receiver, $link, $password)
	{
		global $bookingultrapro;
		
		$admin_email =get_option('admin_email'); 
		$company_name = $this->get_option('company_name');
		$company_phone = $this->get_option('company_phone');
		
		$u_email = $receiver->user_email;
		
		$template_client =stripslashes($this->get_option('email_registration_body'));
		$subject = $this->get_option('email_registration_subject');
		
		$template_client = str_replace("{{ucaptcha_client_name}}", $receiver->display_name,  $template_client);
		$template_client = str_replace("{{ucaptcha_user_name}}", $receiver->user_login,  $template_client);	
		$template_client = str_replace("{{ucaptcha_user_password}}", $password,  $template_client);			
		$template_client = str_replace("{{ucaptcha_login_link}}", $link,  $template_client);
		
		$template_client = str_replace("{{ucaptcha_company_name}}", $company_name,  $template_client);
		$template_client = str_replace("{{ucaptcha_company_phone}}", $company_phone,  $template_client);
		$template_client = str_replace("{{ucaptcha_company_url}}", $site_url,  $template_client);	
		
		$this->send($u_email, $subject, $template_client);				
		
	}
	
	
	//--- New Password Backend
	public function  send_new_password_to_user($staff, $password1)
	{
		global $bookingultrapro;
				
		$admin_email =get_option('admin_email'); 
		$company_name = $this->get_option('company_name');
		$company_phone = $this->get_option('company_phone');
		
		//get templates	
		$template_client =stripslashes($this->get_option('email_password_change_staff'));
		
		$site_url =site_url("/");
	
		$subject_client = $this->get_option('email_password_change_staff_subject');				
		//client		
		$template_client = str_replace("{{ucaptcha_staff_name}}", $staff->display_name,  $template_client);	
		$template_client = str_replace("{{ucaptcha_company_name}}", $company_name,  $template_client);
		$template_client = str_replace("{{ucaptcha_company_phone}}", $company_phone,  $template_client);
		$template_client = str_replace("{{ucaptcha_company_url}}", $site_url,  $template_client);										
		//send to client
		$this->send($staff->user_email, $subject_client, $template_client);		
		
	}
	
	
	
	
	
	
	public function  paypal_ipn_debug( $message)
	{
		global $bookingultrapro;
		$admin_email =get_option('admin_email'); 	
		
		
		$this->send($admin_email, "IPN notification", $message);
					
		
	}
	
	
	

}
