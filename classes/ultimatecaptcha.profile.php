<?php
class UltimateCaptchaProfile
{
	var $table_prfix = 'ucaptcha';
	var $ajax_p = 'ucaptcha';
	
	function __construct() 
	{
		$this->current_page = $_SERVER['REQUEST_URI'];
		
		add_action( 'init',   array(&$this,'profile_shortcodes'));	
		add_action( 'init', array($this, 'handle_init' ) );
		add_action('wp_enqueue_scripts', array(&$this, 'add_front_end_styles'), 11);
		
		add_action( 'wp_ajax_'.$this->ajax_p.'_confirm_reset_password', array( $this, 'confirm_reset_password' ));		
		add_action( 'wp_ajax_nopriv_'.$this->ajax_p.'_confirm_reset_password', array( $this, 'confirm_reset_password' ));
		
		add_action( 'wp_ajax_'.$this->ajax_p.'_confirm_reset_password_user', array( $this, 'confirm_reset_password_user' ));
		add_action( 'wp_ajax_'.$this->ajax_p.'_confirm_update_email_user', array( $this, 'confirm_update_email_user' ));		
		add_action( 'wp_ajax_'.$this->ajax_p.'_update_personal_data_profile', array( $this, 'update_personal_data_profile' ));	
		
		add_action( 'wp_ajax_'.$this->ajax_p.'_ajax_upload_avatar_staff', array( &$this, 'bup_ajax_upload_avatar' ));
		add_action( 'wp_ajax_'.$this->ajax_p.'_crop_avatar_user_profile_image_staff', array( &$this, 'bup_crop_avatar_user_profile_image' ));
		
		add_action( 'wp_ajax_'.$this->ajax_p.'_delete_user_avatar_staff', array( &$this, 'delete_user_avatar' ));
		
		add_action( 'wp_ajax_'.$this->ajax_p.'_send_welcome_email_to_staff', array( &$this, 'send_welcome_email_to_staff' ));
		
	
		/* Remove bar except for admins */
		add_action('init', array(&$this, 'remove_admin_bar'), 9);
		
	
	}
	
	
	/* front styles */
	public function add_front_end_styles()
	{
		global $wp_locale, $ultimatecaptcha;;
		
		$theme_path = get_template_directory();	
	
		/* Custom style */		
		wp_register_style('ultimatecaptcha_profile_style', ultimatecaptcha_url.'templates/user-account-styles.css',null,null);
		wp_enqueue_style('ultimatecaptcha_profile_style');	
			
		
		$date_picker_format = $ultimatecaptcha->get_date_picker_format();
		
		wp_register_script( 'ultimatecaptcha_profile_js', ultimatecaptcha_url.'js/ucaptcha-profile.js', array( 
			'jquery','jquery-ui-core','jquery-ui-autocomplete'),null );
		wp_enqueue_script( 'ultimatecaptcha_profile_js' );
		
		 wp_localize_script( 'ultimatecaptcha_profile_js', 'ultimatecaptcha_profile_v98', array(
            'msg_wait_cropping'  => __( 'Please wait ...', 'ultimate-captcha' ) ,
			'msg_wait'  => __( '<img src="'.ultimatecaptcha_url.'/templates/images/loaderB16.gif" width="16" height="16" /> &nbsp; Please wait ... ', 'ultimate-captcha' ) ,
			
						
			'msg_ticket_submiting_reply'  => '<div class="ucaptcha-ultra-wait"><span><i class="fa fa-ok"></i>'.__(' <img src="'.ultimatecaptcha_url.'/templates/images/loaderB16.gif" width="16" height="16" /> &nbsp; Please wait ... ',"wpticku").'</span></div>' ,
			
			'msg_make_selection'  => __( 'You must make a selection first', 'ultimate-captcha' ) ,
			'msg_wait_reschedule'  => __( 'Please wait ...', 'ultimate-captcha' ) ,
			
			'err_message_private_credential_title'  => __( 'Please input a name', 'ultimate-captcha' ) ,
			
			'err_message_private_notes_title'  => __( 'Please input a name', 'ultimate-captcha' ) ,
			
			
			'are_you_sure'     => __( 'Are you sure?',     'ultimate-captcha' ),			
			'err_message_note_title'  => __( 'Please input a title', 'ultimate-captcha' ) ,
			'err_message_note_text'  => __( 'Please write a message', 'ultimate-captcha' ),
			
						
			'bb_date_picker_format'     => $date_picker_format                
            
        ) );
		
		
		//localize our js
		$date_picker_array = array(
					'closeText'         => __( 'Done', 'ultimate-captcha' ),
					'currentText'       => __( 'Today', 'ultimate-captcha' ),
					'prevText' =>  __('Prev','ultimate-captcha'),
		            'nextText' => __('Next','ultimate-captcha'),				
					'monthNames'        => array_values( $wp_locale->month ),
					'monthNamesShort'   => array_values( $wp_locale->month_abbrev ),
					'monthStatus'       => __( 'Show a different month', 'ultimate-captcha' ),
					'dayNames'          => array_values( $wp_locale->weekday ),
					'dayNamesShort'     => array_values( $wp_locale->weekday_abbrev ),
					'dayNamesMin'       => array_values( $wp_locale->weekday_initial ),					
					// get the start of week from WP general setting
					'firstDay'          => get_option( 'start_of_week' ),
					// is Right to left language? default is false
					'isRTL'             => $wp_locale->is_rtl(),
				);
				
		wp_localize_script('ultimatecaptcha_profile_js', 'UCAPTCHADatePicker', $date_picker_array);
		
	}
	
	function handle_init() 
	{
		
		/*Form is when login*/
		if (isset($_POST['ucaptcha-client-form-confirm'])) {
						
			/* Prepare array of fields */
			$this->prepare( $_POST );
			
			// Setting default to false;
			$this->errors = false;
			
			/* Validate, get errors, etc before we login a user */
			$this->handle();

		}
		
		/*Form reset password*/
		if (isset($_POST['ucaptcha-client-recover-pass-form-confirm'])) {						
					
			// Setting default to false;
			$this->errors = false;
			
			/* Validate, get errors, etc before we login a user */
			$this->handle_password_reset();

		}
		
		/*Registration Form is fired*/
		if (isset($_POST['ucaptcha-client-form-registration-confirm'])) {
						
			$this->prepare( $_POST );			
			$this->errors = false;			
			$this->handle_registration();

		}
		
				
	}
	
	function remove_admin_bar() 
	{
		
		global  $ultimatecaptcha;
		
		if (!current_user_can('manage_options') && !is_admin())
		{
			
			if ($ultimatecaptcha->get_option('hide_admin_bar')==1) 
			{				
				show_admin_bar(false);
			}
		}
	}
	
	function is_user_admin($user_id) 
	{
		
		global  $ultimatecaptcha;
		
		if(user_can( $user_id, 'manage_options' ))
		{
			return true;
			
		
		}else{
			
			return false;
			
		
		}
		
		
	}
	
		/* Get picture by ID */
	function get_user_pic( $id, $size, $pic_type=NULL, $pic_boder_type= NULL, $size_type=NULL, $with_url=true ) 
	{
		
		 global  $ultimatecaptcha;
		 
		$site_url = site_url()."/";
		
		//rand_val_cache		
		$cache_rand = time();
			 
		$avatar = "";
		$pic_size = "";
		
				
		$upload_dir = wp_upload_dir(); 
		$path =   $upload_dir['baseurl']."/".$id."/";
				
		$author_pic = get_the_author_meta('user_pic', $id);
		
		//get user url
		//$user_url=$this->get_user_profile_permalink($id);
		
		if($pic_boder_type=='none'){$pic_boder_type='uultra-none';}
		
		
		if($size_type=="fixed" || $size_type=="")
		{
			$dimension = "width:";
			$dimension_2 = "height:";
		}
		
		if($size_type=="dynamic" )
		{
			$dimension = "max-width:";
		
		}
		
		if($size!="")
		{
			$pic_size = $dimension.$size."px".";".$dimension_2.$size."px";
		
		}
		
		if($ultimatecaptcha->get_option('bup_force_cache_issue')=='yes')
		{
			$cache_by_pass = '?rand_cache='.$cache_rand;
		
		}
		
		$user = get_user_by( 'id', $id );
		
			
		
		if ($author_pic  != '') 
			{
				$avatar_pic = $path.$author_pic;
				
				
				if($with_url)
				{
		 
					$avatar= '<a href="'.$user_url.'">'. '<img src="'.$avatar_pic.'" class="avatar '.$pic_boder_type.'" style="'.$pic_size.' "   id="ucaptcha-avatar-img-'.$id.'" title="'.$user->display_name.'" /></a>';
				
				}else{
					
					$avatar=  '<img src="'.$avatar_pic.'" class="avatar '.$pic_boder_type.'" style="'.$pic_size.' "   id="ucaptcha-avatar-img-'.$id.'" title="'.$user->display_name.'" />';
				
				}
				
				
				
			} else {
				
				$user = get_user_by( 'id', $id );		
				$avatar = get_avatar( $user->user_email, $size );
		
	    	}
		
		return $avatar;
	}
	
	public function avatar_uploader($staff_id=NULL) 
	{
		
	   // Uploading functionality trigger:
	  // (Most of the code comes from media.php and handlers.js)
	      $template_dir = get_template_directory_uri();
		  
		  
		  $plupload_init = array(
				'runtimes'            => 'html5,silverlight,flash,html4',
				'browse_button'       => 'plupload-browse-button-avatar',
				'container'           => 'plupload-upload-ui-avatar',
				'drop_element'        => 'ucaptcha-drag-avatar-section',
				'file_data_name'      => 'async-upload',
				'multiple_queues'     => true,
				'multi_selection'	  => false,
				'max_file_size'       => wp_max_upload_size().'b',
				//'max_file_size'       => get_option('drag-drop-filesize').'b',
				'url'                 => admin_url('admin-ajax.php'),
				'flash_swf_url'       => includes_url('js/plupload/plupload.flash.swf'),
				'silverlight_xap_url' => includes_url('js/plupload/plupload.silverlight.xap'),
				//'filters'             => array(array('title' => __('Allowed Files', $this->text_domain), 'extensions' => "jpg,png,gif,bmp,mp4,avi")),
				'filters'             => array(array('title' => __('Allowed Files', 'ultimate-captcha'), 'extensions' => "jpg,png,gif,jpeg")),
				'multipart'           => true,
				'urlstream_upload'    => true,

				// Additional parameters:
				'multipart_params'    => array(
					'_ajax_nonce' => wp_create_nonce('photo-upload'),
					'staff_id' => $staff_id,
					'action'      => 'ultimatecaptcha_ajax_upload_avatar_staff' // The AJAX action name
					
				),
			);
			
			//print_r($plupload_init);

			// Apply filters to initiate plupload:
			$plupload_init = apply_filters('plupload_init', $plupload_init); 
?>
		
		<div id="uploadContainer" style="margin-top: 10px;">
			
			
			<!-- Uploader section -->
			<div id="uploaderSection" style="position: relative;">
				<div id="plupload-upload-ui-avatar" class="hide-if-no-js">
                
					<div id="drag-drop-area-avatar">
						<div class="drag-drop-inside">
							<p class="drag-drop-info"><?php	_e('Drop '.$avatar_is_called.' here', 'ultimate-captcha') ; ?></p>
							<p><?php _ex('or', 'Uploader: Drop files here - or - Select Files'); ?></p>
							                            
                            
							<p>
                                                      
                            <button name="plupload-browse-button-avatar" id="plupload-browse-button-avatar" class="ucaptcha-button-upload-avatar" type="button"><span><i class="fa fa-camera"></i></span> <?php	_e('Select Image', 'ultimate-captcha') ; ?>	</button>
                            </p>
                            
                            <p>
                                                      
                            <button name="plupload-browse-button-avatar" id="btn-delete-user-avatar" class="ucaptcha-button-delete-avatar" user-id="<?php echo $staff_id?>" redirect-avatar="yes" type="button"><span><i class="fa fa-times"></i></span> <?php	_e('Remove', 'ultimate-captcha') ; ?>	</button>
                            </p>
                            
                            <p>
                            <a href="?module=home" class="uultra-remove-cancel-avatar-btn"><?php	_e('Cancel', 'ultimate-captcha') ; ?></a>
                            </p>
                                                        
                           
														
						</div>
                        
                        <div id="progressbar-avatar"></div>                 
                         <div id="bup_filelist_avatar" class="cb"></div>
					</div>
				</div>
                
                 
			
			</div>
            
           
		</div>
        
         <form id="bup_frm_img_cropper" name="bup_frm_img_cropper" method="post">                
                
                	<input type="hidden" name="image_to_crop" value="" id="image_to_crop" />
                    <input type="hidden" name="crop_image" value="crop_image" id="crop_image" />
                    
                    <input type="hidden" name="site_redir" value="<?php echo $my_account_url."?module=upload_avatar"?>" id="site_redir" />                   
                
                </form>

		<?php
			
			?>

			<script type="text/javascript">
			
				jQuery(document).ready(function($){
					
					// Create uploader and pass configuration:
					var uploader_avatar = new plupload.Uploader(<?php echo json_encode($plupload_init); ?>);

					// Check for drag'n'drop functionality:
					uploader_avatar.bind('Init', function(up){
						
						var uploaddiv_avatar = $('#plupload-upload-ui-avatar');
						
						// Add classes and bind actions:
						if(up.features.dragdrop){
							uploaddiv_avatar.addClass('drag-drop');
							
							$('#drag-drop-area-avatar')
								.bind('dragover.wp-uploader', function(){ uploaddiv_avatar.addClass('drag-over'); })
								.bind('dragleave.wp-uploader, drop.wp-uploader', function(){ uploaddiv_avatar.removeClass('drag-over'); });

						} else{
							uploaddiv_avatar.removeClass('drag-drop');
							$('#drag-drop-area').unbind('.wp-uploader');
						}

					});

					
					// Init ////////////////////////////////////////////////////
					uploader_avatar.init(); 
					
					// Selected Files //////////////////////////////////////////
					uploader_avatar.bind('FilesAdded', function(up, files) {
						
						
						var hundredmb = 100 * 1024 * 1024, max = parseInt(up.settings.max_file_size, 10);
						
						// Limit to one limit:
						if (files.length > 1){
							alert("<?php _e('You may only upload one image at a time!', 'ultimate-captcha'); ?>");
							return false;
						}
						
						// Remove extra files:
						if (up.files.length > 1){
							up.removeFile(uploader_avatar.files[0]);
							up.refresh();
						}
						
						// Loop through files:
						plupload.each(files, function(file){
							
							// Handle maximum size limit:
							if (max > hundredmb && file.size > hundredmb && up.runtime != 'html5'){
								alert("<?php _e('The file you selected exceeds the maximum filesize limit.', 'ultimate-captcha'); ?>");
								return false;
							}
						
						});
						
						jQuery.each(files, function(i, file) {
							jQuery('#bup_filelist_avatar').append('<div class="addedFile" id="' + file.id + '">' + file.name + '</div>');
						});
						
						up.refresh(); 
						uploader_avatar.start();
						
					});
					
					// A new file was uploaded:
					uploader_avatar.bind('FileUploaded', function(up, file, response){					
						
						
						
						var obj = jQuery.parseJSON(response.response);												
						var img_name = obj.image;							
						
						$("#image_to_crop").val(img_name);
						$("#bup_frm_img_cropper").submit();

						
						
						
						jQuery.ajax({
							type: 'POST',
							url: ajaxurl,
							data: {"action": "refresh_avatar"},
							
							success: function(data){
								
								//$( "#uu-upload-avatar-box" ).slideUp("slow");								
								$("#uu-backend-avatar-section").html(data);
								
								//jQuery("#uu-message-noti-id").slideDown();
								//setTimeout("hidde_noti('uu-message-noti-id')", 3000)	;
								
								
								}
						});
						
						
					
					});
					
					// Error Alert /////////////////////////////////////////////
					uploader_avatar.bind('Error', function(up, err) {
						alert("Error: " + err.code + ", Message: " + err.message + (err.file ? ", File: " + err.file.name : "") + "");
						up.refresh(); 
					});
					
					// Progress bar ////////////////////////////////////////////
					uploader_avatar.bind('UploadProgress', function(up, file) {
						
						var progressBarValue = up.total.percent;
						
						jQuery('#progressbar-avatar').fadeIn().progressbar({
							value: progressBarValue
						});
						
						jQuery('#progressbar-avatar').html('<span class="progressTooltip">' + up.total.percent + '%</span>');
					});
					
					// Close window after upload ///////////////////////////////
					uploader_avatar.bind('UploadComplete', function() {
						
						//jQuery('.uploader').fadeOut('slow');						
						jQuery('#progressbar-avatar').fadeIn().progressbar({
							value: 0
						});
						
						
					});
					
					
					
				});
				
					
			</script>
			
		<?php
	
	
	}
	
	function get_me_wphtml_editor($meta, $content)
	{
		// Turn on the output buffer
		ob_start();
		
		$editor_id = $meta;				
		$editor_settings = array('media_buttons' => false , 'textarea_rows' => 15 , 'teeny' =>true); 
							
					
		wp_editor( $content, $editor_id , $editor_settings);
		
		// Store the contents of the buffer in a variable
		$editor_contents = ob_get_clean();
		
		// Return the content you want to the calling function
		return $editor_contents;

	
	
	}
	
	//crop avatar image
	function bup_crop_avatar_user_profile_image()
	{
		global $ultimatecaptcha;
		global $wpdb;
		
		$site_url = site_url()."/";		
	
		/// Upload file using Wordpress functions:
		$x1 = $_POST['x1'];
		$y1 = $_POST['y1'];
		
		$x2 = $_POST['x2'];
		$y2= $_POST['y2'];
		$w = $_POST['w'];
		$h = $_POST['h'];	
		
		$image_id =   $_POST['image_id'];
	
		$current_user = $ultimatecaptcha->userpanel->get_user_info();
		$user_id = $current_user->ID;	
		
		if($user_id==''){echo 'error';exit();}
				
		
		$ultimatecaptcha->imagecrop->setDimensions($x1, $y1, $w, $h)	;
		
		$upload_dir = wp_upload_dir(); 
		$path_pics =   $upload_dir['basedir'];		
		$src = $path_pics.'/'.$user_id.'/'.$image_id;
		
		//new random image and crop procedure				
		$ultimatecaptcha->imagecrop->setImage($src);
		$ultimatecaptcha->imagecrop->createThumb();		
		$info = pathinfo($src);
        $ext = $info['extension'];
		$ext=strtolower($ext);		
		$new_i = time().".". $ext;		
		$new_name =  $path_pics.'/'.$user_id.'/'.$new_i;				
		$ultimatecaptcha->imagecrop->renderImage($new_name);
		//end cropping
		
		//check if there is another avatar						
		$user_pic = get_user_meta($user_id, 'user_pic', true);	
		
		//resize
		//check max width		
		$original_max_width = $ultimatecaptcha->get_option('media_avatar_width'); 
        $original_max_height =$ultimatecaptcha->get_option('media_avatar_height'); 
		
		if($original_max_width=="" || $original_max_height=="")
		{			
			$original_max_width = 80;			
			$original_max_height = 80;			
		}
														
		list( $source_width, $source_height, $source_type ) = getimagesize($new_name);
		
		if($source_width > $original_max_width) 
		{
			if ($this->image_resize($new_name, $new_name, $original_max_width, $original_max_height,0)) 
			{
				$old = umask(0);
				chmod($new_name, 0755);
				umask($old);										
			}		
		}					
						
		if ( $user_pic!="" )
		{
				
			 //there is a pending avatar - delete avatar																					
			 	
			 $path_avatar = $path_pics['baseurl']."/".$user_id."/".$image_id;					
										  
			 //delete								
			 //update meta
			  update_user_meta($user_id, 'user_pic', $new_i);		  
			  
		  }else{
			  
			  //update meta
			  update_user_meta($user_id, 'user_pic', $new_i);
								  
		  
		  }
		  
		  
		  if(file_exists($src))
		  {
			  unlink($src);
		  }
			 
	
		// Create response array:
		$uploadResponse = array('image' => $new_name);
		
		// Return response and exit:
		echo json_encode($uploadResponse);
		
		die();
		
	}
	
	function image_resize($src, $dst, $width, $height, $crop=0)
	{
		
		  if(!list($w, $h) = getimagesize($src)) return "Unsupported picture type!";
		
		  $type = strtolower(substr(strrchr($src,"."),1));
		  if($type == 'jpeg') $type = 'jpg';
		  switch($type){
			case 'bmp': $img = imagecreatefromwbmp($src); break;
			case 'gif': $img = imagecreatefromgif($src); break;
			case 'jpg': $img = imagecreatefromjpeg($src); break;
			case 'png': $img = imagecreatefrompng($src); break;
			default : return "Unsupported picture type!";
		  }
		
		  // resize
		  if($crop){
			if($w < $width or $h < $height) return "Picture is too small!";
			$ratio = max($width/$w, $height/$h);
			$h = $height / $ratio;
			$x = ($w - $width / $ratio) / 2;
			$w = $width / $ratio;
		  }
		  else{
			if($w < $width and $h < $height) return "Picture is too small!";
			$ratio = min($width/$w, $height/$h);
			$width = $w * $ratio;
			$height = $h * $ratio;
			$x = 0;
		  }
		
		  $new = imagecreatetruecolor($width, $height);
		
		  // preserve transparency
		  if($type == "gif" or $type == "png"){
			imagecolortransparent($new, imagecolorallocatealpha($new, 0, 0, 0, 127));
			imagealphablending($new, false);
			imagesavealpha($new, true);
		  }
		
		  imagecopyresampled($new, $img, 0, 0, $x, 0, $width, $height, $w, $h);
		
		  switch($type){
			case 'bmp': imagewbmp($new, $dst); break;
			case 'gif': imagegif($new, $dst); break;
			case 'jpg': imagejpeg($new, $dst,100); break;
			case 'jpeg': imagejpeg($new, $dst,100); break;
			case 'png': imagepng($new, $dst,9); break;
		  }
		  return true;
	}
	
	function display_avatar_image_to_crop($image, $user_id=NULL)	
	{
		 global $ultimatecaptcha;
		
		/* Custom style */		
		wp_register_style( 'ultimatecaptcha_image_cropper_style',ultimatecaptcha_url.'js/cropper/cropper.min.css');
		wp_enqueue_style('ultimatecaptcha_image_cropper_style');	
					
		wp_enqueue_script('simple_cropper',  ultimatecaptcha_url.'js/cropper/cropper.min.js' , array('jquery'), false, false);
		
	  
	    $template_dir = get_template_directory_uri();		  
				
		$site_url = site_url()."/";
		
		$html = "";
		
		$upload_dir = wp_upload_dir(); 
		$upload_folder =   $upload_dir['basedir'];		
				
		$user_pic = get_user_meta($user_id, 'user_profile_bg', true);		
		
		if($image!="")
		{
			$url_image_to_crop = $upload_dir['baseurl'].'/'.$user_id.'/'.$image;			
			$html_image = '<img src="'.$url_image_to_crop.'" id="uultra-profile-cover-horizontal" />';					
			
		}
		
		$my_account_url = $ultimatecaptcha->userpanel->get_my_account_direct_link 
		
		
		
		?>
        
        
      	<div id="uultra-dialog-user-bg-cropper-div" class="ucaptcha-dialog-user-bg-cropper"  >	
				<?php echo $html_image ?>                   
		</div>          
             
             
             <p>
                                                      
                            <button name="plupload-browse-button-avatar" id="ucaptcha-confirm-avatar-cropping" class="ucaptcha-button-upload-avatar" type="link"><span><i class="fa fa-crop"></i></span> <?php	_e('Crop & Save', 'ultimate-captcha') ; ?>	</button>
                            <div class="ucaptcha-please-wait-croppingmessage" id="ucaptcha-cropping-avatar-wait-message">&nbsp;</div>
                            </p>                           
                            
                            <div class="uultra-uploader-buttons-delete-cancel" id="btn-cancel-avatar-cropping" >
                            <a href="<?php echo $my_account_url?>" class="uultra-remove-cancel-avatar-btn"><?php	_e('Cancel', 'ultimate-captcha') ; ?></a>
                            </div>
            
     			<input type="hidden" name="x1" value="0" id="x1" />
				<input type="hidden" name="y1" value="0" id="y1" />				
				<input type="hidden" name="w" value="<?php echo $w?>" id="w" />
				<input type="hidden" name="h" value="<?php echo $h?>" id="h" />
                <input type="hidden" name="image_id" value="<?php echo $image?>" id="image_id" />
                <input type="hidden" name="user_id" value="<?php echo $user_id?>" id="user_id" />
                <input type="hidden" name="site_redir" value="<?php echo $my_account_url."?module=upload_avatar&"?>" id="site_redir" />
                
		
		<script type="text/javascript">
		
		
				jQuery(document).ready(function($){
					
				
					<?php
					
					
					
					$source_img = $upload_folder.'/'.$user_id.'/'.$image;	
									 
					 $r_width = $this->getWidth($source_img);
					 $r_height= $this->getHeight($source_img);
					 
					$original_max_width = $ultimatecaptcha->get_option('media_avatar_width'); 
					$original_max_height =$ultimatecaptcha->get_option('media_avatar_height'); 
					
					if($original_max_width=="" || $original_max_height=="")
					{			
						$original_max_width = 80;			
						$original_max_height = 80;
						
					}
					
					$aspectRatio = $original_max_width/$original_max_height;
					
					
					 
						 ?>
						var $image = jQuery(".ucaptcha-dialog-user-bg-cropper img"),
						$x1 = jQuery("#x1"),
						$y1 = jQuery("#y1"),
						$h = jQuery("#h"),
						$w = jQuery("#w");
					
					$image.cropper({
								  aspectRatio: <?php echo $aspectRatio?>,
								  autoCropArea: 0.6, // Center 60%
								  zoomable: false,
								  preview: ".img-preview",
								  done: function(data) {
									$x1.val(Math.round(data.x));
									$y1.val(Math.round(data.y));
									$h.val(Math.round(data.height));
									$w.val(Math.round(data.width));
								  }
								});
			
			})	
				
									
			</script>
		
		
	<?php	
		
	}
	
	//You do not need to alter these functions
	function getHeight($image) {
		$size = getimagesize($image);
		$height = $size[1];
		return $height;
	}

	//You do not need to alter these functions
	function getWidth($image) {
		$size = getimagesize($image);
		$width = $size[0];
		return $width;
	}
	
	
	// File upload handler:
	function bup_ajax_upload_avatar()
	{
		global $ultimatecaptcha;
		global $wpdb;
		
		$site_url = site_url()."/";
		
		// Check referer, die if no ajax:
		check_ajax_referer('photo-upload');
		
		/// Upload file using Wordpress functions:
		$file = $_FILES['async-upload'];
		
		
		$original_max_width = $ultimatecaptcha->get_option('media_avatar_width'); 
        $original_max_height =$ultimatecaptcha->get_option('media_avatar_height'); 
		
		if($original_max_width=="" || $original_max_height=="")
		{			
			$original_max_width = 80;			
			$original_max_height = 80;
			
		}
		
			
	
		$current_user = $ultimatecaptcha->userpanel->get_user_info();
		$o_id = $current_user->ID;

		
				
		$info = pathinfo($file['name']);
		$real_name = $file['name'];
        $ext = $info['extension'];
		$ext=strtolower($ext);
		
		$rand = $this->genRandomString();
		
		$rand_name = "avatar_".$rand."_".session_id()."_".time(); 
		
	
		$upload_dir = wp_upload_dir(); 
		$path_pics =   $upload_dir['basedir'];
			
			
		if($ext == 'png' || $ext == 'jpg' || $ext == 'jpeg' || $ext == 'gif') 
		{
			if($o_id != '')
			{
				
				   if(!is_dir($path_pics."/".$o_id."")) 
				   {
						//$this->CreateDir($path_pics."/".$o_id);	
						 wp_mkdir_p( $path_pics."/".$o_id );							   
					}					
										
					$pathBig = $path_pics."/".$o_id."/".$rand_name.".".$ext;						
					
					
					if (copy($file['tmp_name'], $pathBig)) 
					{
						//check auto-rotation						
						if($ultimatecaptcha->get_option('avatar_rotation_fixer')=='yes')
						{
							$this->orient_image($pathBig);
						
						}
						
						$upload_folder = $ultimatecaptcha->get_option('media_uploading_folder');				
						$path = $site_url.$upload_folder."/".$o_id."/";
						
						//check max width												
						list( $source_width, $source_height, $source_type ) = getimagesize($pathBig);
						
						if($source_width > $original_max_width) 
						{
							//resize
						//	if ($this->createthumb($pathBig, $pathBig, $original_max_width, $original_max_height,$ext)) 
							//{
								//$old = umask(0);
								//chmod($pathBig, 0755);
								//umask($old);
														
							//}
						
						
						}
						
						
						
						$new_avatar = $rand_name.".".$ext;						
						$new_avatar_url = $path.$rand_name.".".$ext;				
						
						
						//check if there is another avatar						
						$user_pic = get_user_meta($o_id, 'user_pic', true);						
						
						if ( $user_pic!="" )
			            {
							//there is a pending avatar - delete avatar																					
							$path_avatar = $path_pics."/".$o_id."/".$user_pic;					
														
							//delete								
							if(file_exists($path_avatar))
							{
								//unlink($path_avatar);
							}
							
												
							
						}else{
							
																	
						
						}
						
						//update user meta
						
					}
									
					
			     }  		
			
        } // image type
		
		// Create response array:
		$uploadResponse = array('image' => $new_avatar);
		
		// Return response and exit:
		echo json_encode($uploadResponse);
		
		//echo $new_avatar_url;
		die();
		
	}
	
	
	
	public function orient_image($file_path) 
	{
        if (!function_exists('exif_read_data')) {
            return false;
        }
        $exif = @exif_read_data($file_path);
        if ($exif === false) {
            return false;
        }
        $orientation = intval(@$exif['Orientation']);
        if (!in_array($orientation, array(3, 6, 8))) {
            return false;
        }
        $image = @imagecreatefromjpeg($file_path);
        switch ($orientation) {
            case 3:
                $image = @imagerotate($image, 180, 0);
                break;
            case 6:
                $image = @imagerotate($image, 270, 0);
                break;
            case 8:
                $image = @imagerotate($image, 90, 0);
                break;
            default:
                return false;
        }
        $success = imagejpeg($image, $file_path);
        // Free up memory (imagedestroy does not delete files):
        @imagedestroy($image);
        return $success;
    }
	
	public function confirm_reset_password_user()
	{
		global $wpdb,  $ultimatecaptcha, $wp_rewrite;
		
	
		$wp_rewrite = new WP_Rewrite();
		
		$user_id = get_current_user_id();		
				
		//check redir		
		$account_page_id = $ultimatecaptcha->get_option('login_page_id');		
		$my_account_url = get_permalink($account_page_id);		
		
		
		$PASSWORD_LENGHT =7;
		
		$password1 = $_POST['p1'];
		$password2 = $_POST['p2'];
		
		$html = '';
		$validation = '';
		
	
		if($password1!=$password2)
		{
			$validation .= "<div class='ucaptcha-ultra-error'>".__(" ERROR! Password must be identical ", 'ultimate-captcha')."</div>";
			$html = $validation;			
		}
		
		if(strlen($password1)<$PASSWORD_LENGHT)
		{
			$validation .= "<div class='ucaptcha-ultra-error'>".__(" ERROR! Password should contain at least 7 alphanumeric characters ", 'ultimate-captcha')."</div>";
			$html = $validation;		
		}
		
		
		if($validation=="" )
		{
		
			if($user_id >0 )
			{
					$user = get_userdata($user_id);
					//print_r($user);
					$user_id = $user->ID;
					$user_email = $user->user_email;
					$user_login = $user->user_login;			
					
					wp_set_password( $password1, $user_id ) ;
					
					//notify user					
					$ultimatecaptcha->messaging->send_new_password_to_user($user, $password1);
					
					$html = "<div class='ucaptcha-ultra-success'>".__(" Success!! The new password has been changed. Please click on the login link to get in your account.  ", 'ultimate-captcha')."</div>";
					
					// Here is the magic:
					wp_cache_delete($user_id, 'users');
					wp_cache_delete($username, 'userlogins'); // This might be an issue for how you are doing it. Presumably you'd need to run this for the ORIGINAL user login name, not the new one.
					wp_logout();
					wp_signon(array('user_login' => $user_login, 'user_password' => $password1));
					
				}else{
									
				}
					
			}
		 echo $html;
		 die();
		
	
	}
	
	public function update_personal_data_profile()
	{
		global $wpdb,  $ultimatecaptcha, $wp_rewrite;
		
		$user_id = get_current_user_id();
	
	
		$display_name = $_POST['bup_display_name'];
		
		$country = $_POST['bup_country'];
		$city = $_POST['bup_city'];
		$address = $_POST['bup_address'];
		
		$description = $_POST['desc_text'];
		$summary = $_POST['summary_text'];
		$html = '';
		$validation = '';
		
		wp_update_user( array( 'ID' => $user_id, 'display_name' => $display_name ) );

		//update meta
		update_user_meta ($user_id, 'bup_description', $description);
		update_user_meta ($user_id, 'bup_summary', $summary);	
		
		update_user_meta ($user_id, 'country', $country);
		update_user_meta ($user_id, 'city', $city);
		update_user_meta ($user_id, 'address', $address);
		
	    $html = "<div class='ucaptcha-ultra-success'>".__(" Success!! Your Personal Details Were Updated  ", 'ultimate-captcha')."</div>";
		
		 echo $html;
		 die();
		
	
	}
	
	
	public function confirm_update_email_user()
	{
		global $wpdb,  $ultimatecaptcha, $wp_rewrite;

		$wp_rewrite = new WP_Rewrite();
		$user_id = get_current_user_id();
	
		$email = $_POST['email'];
		$html = '';
		$validation = '';
		
	
		//validate if it's a valid email address	
		$ret_validate_email = $this->validate_valid_email($email);
		
		if($email=="")
		{
			$validation .= "<div class='ucaptcha-ultra-error'>".__(" ERROR! Please type your new email ", 'ultimate-captcha')."</div>";
			$html = $validation;			
		}
		
		if(!$ret_validate_email)
		{
			$validation .= "<div class='ucaptcha-ultra-error'>".__(" ERROR! Please type a valid email address ", 'ultimate-captcha')."</div>";
			$html = $validation;			
		}
		
		$current_user = get_userdata($user_id);
		//print_r($user);
		$current_user_email = $current_user->user_email;
		
		//check if already used
		
		$check_user = get_user_by('email',$email);
		$user_check_id = $check_user->ID;
		$user_check_email = $check_user->ID;
		
		if($validation=="" )
		{
		
			if($user_check_id==$user_id) //this is the same user then change email
			{
				$validation .= "<div class='ucaptcha-ultra-error'>".__(" ERROR! You haven't changed your email. ", 'ultimate-captcha')."</div>";
				$html = $validation;
				
			
			}else{ //email already used by another user
			
				if($user_check_email!="")
				{
			
					$validation .= "<div class='ucaptcha-ultra-error'>".__(" ERROR! The email is in use already ", 'ultimate-captcha')."</div>";
					$html = $validation;
				
				}else{
					
					//email available
					
				}
				
			
			}
		
		}
		
		
		
		if($validation=="" )
		{
		
			if($user_id >0 )
			{
					$user = get_userdata($user_id);
					$user_id = $user->ID;
					$user_email = $user->user_email;
					$user_login = $user->user_login;	
					
					$user_id = wp_update_user( array( 'ID' => $user_id, 'user_email' => $email ) );
					
					//update mailchimp?
					$mail_chimp = get_user_meta( $user_id, 'bup_mailchimp', true);
					
					if($mail_chimp==1) //the user has a mailchip account, then we have to sync
					{
						if($ultimatecaptcha->get_option('mailchimp_api'))
						{
							$list_id =  $ultimatecaptcha->get_option('mailchimp_list_id');					 
							//$ultimatecaptcha->newsletter->mailchimp_subscribe($user_id, $list_id);
						}
					}
					
					
																
										
					$html = "<div class='ucaptcha-ultra-success'>".__(" Success!! Your email account has been changed to : ".$email."  ", 'ultimate-captcha')."</div>";
					
																			
				}else{
					
									
				}
					
			}
		 echo $html;
		 die();
		
	
	}
	
	function validate_valid_email ($myString)
	{
		$ret = true;
		if (!filter_var($myString, FILTER_VALIDATE_EMAIL)) {
    		// invalid e-mail address
			$ret = false;
		}
					
		return $ret;	
	
	}
	
	/**
	Get Menu Links
	******************************************/
	public function get_user_backend_menu_new($slug, $title , $icon = null)
	{
		global $ultimatecaptcha;
		
		$url = "";
		
		$uri = $this->build_user_menu_uri($slug);	
		
		$url = '<a class="ucaptcha-btn-u-menu" href="'.$uri.'" title="'.$title.'"><span><i class="fa '.$icon.' fa-2x"></i></span><span class="ucaptcha-user-menu-text">'.$title.'</span></a>';
		
		
		if($slug=='profile')
		{
			//check if unread replies or messages			
			$user_id = get_current_user_id();
			
			//$uri = $this->get_user_profile_permalink($user_id);
			
			$url = '<a class="ucaptcha-btn-u-menu" href="'.$uri.'" title="'.$title.'"><span><i class="fa '.$icon.' fa-2x"></i></span><span class="ucaptcha-user-menu-text">'.$title.'</span></a>';	
						
		
		}	
				
		//messsages
		if($module["slug"]=='messages')
		{
			//check if unread replies or messages			
			$user_id = get_current_user_id();
			///$total = $xoouserultra->mymessage->get_unread_messages_amount($user_id);
			
			if($total>0)
			{
				$url .= '<div class="uultra-noti-bubble" title="'.__('Unread Messages', 'ultimate-captcha').'">'.$total.'</div>';			
			}			
		
		}
		
		
		return $url;	
		
	
	}
	
	function build_user_menu_uri($slug)
	{
		global $ultimatecaptcha;
		$uri = "";
		
				
		if(!isset($_GET["page_id"]))
		{
			$uri = '?module='.$slug;
			
		}else{
						
			$uri = '?page_id='.$_GET["page_id"].'&module='.$slug;
			
		}
		
				
		if($slug=='logout')
		{
			$uri = $this->get_logout_url();
		
		}
		
		return $uri;
	
	}
	
	public function get_logout_url ()
	{
		
		/*$defaults = array(
		            'redirect_to' => $this->current_page
		    );
		$args = wp_parse_args( $args, $defaults );
		
		extract( $args, EXTR_SKIP );*/
		
		$redirect_to = $this->current_page;
			
		return wp_logout_url($redirect_to);
	}
	
	
	/*Prepare user meta*/
	function prepare ($array ) 
	{
		foreach($array as $k => $v) {
			if ($k == 'ucaptcha-client-form') continue;
			$this->usermeta[$k] = $v;
		}
		return $this->usermeta;
	}
	
	/*Handle Registration*/
	function handle_registration() 
	{
	    global $ultimatecaptcha, $blog_id, $ultimatecaptcha_aweber, $ucaptcha_passwordstrength;
	    	
		if ( empty( $GLOBALS['wp_rewrite'] ) )
		{
			 $GLOBALS['wp_rewrite'] = new WP_Rewrite();
	    }		
			
		/* Create account, update user meta */				
		$visitor_ip = $_SERVER['REMOTE_ADDR'];
		
		$g_recaptcha_response = '';		
		if(isset($_POST['g-recaptcha-response']) && $_POST['g-recaptcha-response']!=''){
			
			$g_recaptcha_response = sanitize_text_field($_POST['g-recaptcha-response']);		
		}
		
		//check reCaptcha
		$is_valid_recaptcha = true;	
		if($ultimatecaptcha->get_option('recaptcha_site_key')!='' && $ultimatecaptcha->get_option('recaptcha_secret_key')!='' && $ultimatecaptcha->get_option('recaptcha_display_registration')=='1' ){
			
			$is_valid_recaptcha = $ultimatecaptcha->validate_recaptcha_field($g_recaptcha_response);	
		
		}
		
		
		$ask_password=false;		
		$password_strength = true;			
		//check Password Strenght		
		if(isset( $ucaptcha_passwordstrength)){
			
			if($ultimatecaptcha->get_option('registration_password_ask')==1){
				
				$ask_password=true;				
				$password_strength = $ucaptcha_passwordstrength->ucaptcha_check_pass_strenght($_POST['user_password']);			
			
			}		
		}
		
		
		if($_POST['first_name']=='')
		{
			$this->errors[] = __('<strong>ERROR:</strong> Please input your First Name.','ultimate-captcha');
		
		}elseif($_POST['last_name']==''){
			
			$this->errors[] = __('<strong>ERROR:</strong> Please input your Last Name.','ultimate-captcha');
			
			
		}elseif($ask_password && $_POST['user_password']==''){
			
			$this->errors[] = __('<strong>ERROR:</strong> Please input a password.','ultimate-captcha');			
			
		}elseif($_POST['email']==''){
			
			$this->errors[] = __('<strong>ERROR:</strong> Please input an email address.','ultimate-captcha');	
			
		}elseif(!$is_valid_recaptcha){
			
			$this->errors[] = __('<strong>ERROR:</strong> reCaptcha validation failed.','ultimate-captcha');
			
		}elseif(!$password_strength && $ask_password){
			
			$this->errors[] =$ucaptcha_passwordstrength->errors;	
				
		}else{
		
								
			if(email_exists($_POST['email']))
			{			
				$this->errors[] = __('<strong>ERROR:</strong> The email address already exists.','ultimate-captcha');
				
			}elseif(username_exists($_POST['user_name'])){
				
				$this->errors[] = __('<strong>ERROR:</strong> The username already exists.','ultimate-captcha');
			
			}else{ // new user we have to create it.			
							
				$sanitized_user_login = sanitize_user($_POST['user_name']);
			
				/* We create the New user */
				if($_POST['user_password']!=''){
					
					$user_pass = sanitize_text_field($_POST['user_password']);
										
				}else{
					
					$user_pass = wp_generate_password( 8, false);						
				}
				
				$user_id = wp_create_user( $sanitized_user_login, $user_pass, $_POST['email'] );	
				wp_update_user( array('ID' => $user_id, 'first_name' => esc_attr($_POST['first_name'])) );
				
				if($user_id)
				{
					
					update_user_meta($user_id, 'ultimatecaptcha_user_registered_ip', $visitor_ip);					
					update_user_meta($user_id, 'last_name', $_POST['last_name']);							
					update_user_meta($user_id, 'first_name',$_POST['first_name']);
										
					//set account status						
					$verify_key = $this->get_unique_verify_account_id();					
					update_user_meta ($user_id, 'ultimatecaptcha_ultra_very_key', $verify_key);
					
										
					$login_link_id = $ultimatecaptcha->get_option('bup_user_login_page');				
					$login_link = get_page_link($login_link_id);
					
					$user = get_user_by( 'id', $user_id );					
					$ultimatecaptcha->messaging->send_client_registration_link($user, $login_link, $user_pass);
					
					if(isset($ultimatecaptcha_aweber)){
					
					 //aweber	
					 $list_id = get_option( "ultimatecaptcharoaw_aweber_list");				 
					 if(isset($_POST["ucaptcha-aweber-confirmation"]) && $_POST["ucaptcha-aweber-confirmation"]==1 && $list_id !='')				 {						 
						// $user_l = get_user_by( 'id', $user_id ); 				 
						 $ultimatecaptcha_aweber->ultimatecaptcharoaw_subscribe($user, $list_id);
						 update_user_meta ($user_id, 'ultimatecaptcha_aweber', 1);				 						
						
					 }
				
				}				
					
					$this->handle_redir_success();
					
				} //end if email exists
				
			} //end if required fields
		
		}		
			
	}
	
	//this is the custom redirecton after ticket submission sucess
	public function handle_redir_success($key, $ticket_id)
	{
		global $ultimatecaptcha, $ultimatecaptchacomplement, $wp_rewrite ;
		
		$wp_rewrite = new WP_Rewrite();		
		
		$url = '';
		$my_success_url = '';	
		
		$url_info = array_merge($_GET, $_POST, $_COOKIE);
		
		if(isset($url_info['redirect_to']) && $url_info['redirect_to']!='')
		{
			
			$my_success_url = $url_info['redirect_to'];	
			
		}
		
		if($my_success_url=="")
		{
			$url = $_SERVER['REQUEST_URI'].'?ultimatecaptcha_registration=ok';
					
		}else{
									
			$url = $my_success_url.'?ultimatecaptcha_status=ok&ultimatecaptcha_ticket_key='.$key;				
					
		}
		
		
		 		  
		wp_redirect( $url );
		exit;
		  
		 
	}
	
	public function get_unique_verify_account_id()
	{
		  session_start();
		  $rand = $this->genRandomStringActivation(8);
		  $key = session_id()."_".time()."_".$rand;
		  
		  return $key;
		  
		 
	}
	  
	public function genRandomStringActivation($length) 
	{
			
			$characters = "0123456789ABCDEFGHIJKLMNOPQRSTUVWZYZ";
			
			$real_string_legnth = strlen($characters) ;
			//$real_string_legnth = $real_string_legnth– 1;
			$string="ID";
			
			for ($p = 0; $p < $length; $p++)
			{
				$string .= $characters[mt_rand(0, $real_string_legnth-1)];
			}
			
			return strtolower($string);
	}
	
	
	/*Handle commons login*/
	function handle() 
	{
	    global $ultimatecaptcha, $blog_id;
	    	
		if ( empty( $GLOBALS['wp_rewrite'] ) )
		{
			 $GLOBALS['wp_rewrite'] = new WP_Rewrite();
	    }		
		
		$noactive = false;
		foreach($this->usermeta as $key => $value) 
		{
		
			if ($key == 'user_login') 
			{
				if (sanitize_user($value) == '')
				{
					$this->errors[] = __('<strong>ERROR:</strong> The username field is empty.','ultimate-captcha');
				}
			}
			
			if ($key == 'user_pass')
			{
				if (esc_attr($value) == '') 
				{
					$this->errors[] = __('<strong>ERROR:</strong> The password field is empty.','ultimate-captcha');
				}
			}
		}
		
		
		$g_recaptcha_response = '';		
		if(isset($_POST['g-recaptcha-response']) && $_POST['g-recaptcha-response']!=''){
			
			$g_recaptcha_response = sanitize_text_field($_POST['g-recaptcha-response']);		
		}
		
		
		//check reCaptcha		
		$is_valid_recaptcha = true;
		if($ultimatecaptcha->get_option('recaptcha_site_key')!='' && $ultimatecaptcha->get_option('recaptcha_secret_key')!='' && $ultimatecaptcha->get_option('recaptcha_display_loginform')=='1' ){
			
			$is_valid_recaptcha = $ultimatecaptcha->validate_recaptcha_field($g_recaptcha_response);	
		
		}
		
		if(!$is_valid_recaptcha){
			
			$this->errors[] = __('<strong>ERROR:</strong> The captcha validation is wrong.','ultimate-captcha');
		
		}	
	
			/* attempt to signon */
			if (!is_array($this->errors)) 
			{				
				$creds = array();
				
				// Adding support for login by email
				if(is_email($_POST['user_login']))
				{
				    $user = get_user_by( 'email', $_POST['user_login'] );
				    
				    if(isset($user->data->user_login))
					{
				        $creds['user_login'] = $user->data->user_login;
						
				    }else{
						
				        $creds['user_login'] = '';
						$this->errors[] = __('<strong>ERROR:</strong> Invalid Email was entered.','ultimate-captcha');				
					}
					
					// check if active					
					$user_id =$user->ID;				
					if(!$this->is_active($user_id))
					{
						$noactive = true;
						
					}else{
						
						
					}		
				
				}else{
					
					// User is trying to login using username					
					$user = get_user_by('login',$_POST['user_login']);
					
					// check if active and it's not an admin		
					if(isset($user))	
					{
						$user_id =$user->ID;	
						
					
					}else{
						
						$user_id ="";
						
					}
							
					if(!$this->is_active($user_id) && !is_super_admin($user_id))
					{
						$noactive = true;						
					}				
					
					$creds['user_login'] = sanitize_user($_POST['user_login']);			
				
				}
				
				$creds['user_password'] = $_POST['login_user_pass'];
				$creds['remember'] = $_POST['rememberme'];
				
							
				if(!$noactive )
				{					
					if(!is_array($this->errors))	
					{
						
					  $user = wp_signon( $creds, false );			
	  
					  if ( is_wp_error($user) ) 
					  {						
						  if ($user->get_error_code() == 'invalid_username') {
							  $this->errors[] = __('<strong>ERROR:</strong> Invalid Username was entered.','ultimate-captcha');
						  }
						  if ($user->get_error_code() == 'incorrect_password') {
							  $this->errors[] = __('<strong>ERROR:</strong> Incorrect password was entered.','ultimate-captcha');
						  }
						  
						  if ($user->get_error_code() == 'empty_password') {
							  $this->errors[] = __('<strong>ERROR:</strong> Please provide Password.','ultimate-captcha');
						  }
						  
											  
					  }else{						
						  
						  $this->bup_auto_login($user->user_login);						
						  $this->login_registration_afterlogin();					
					  }
					
					}
					
				
				}else{
					
					//not active
					$this->errors[] = __('<strong>ERROR:</strong> Your account is not active.','ultimate-captcha');
				 
				}
			}
			
	}
	
			
	
	/*Handle password reest*/
	function handle_password_reset() 
	{
	    global $ultimatecaptcha, $blog_id, $ultimatecaptcha_recaptcha;
	    		
		if ( empty( $GLOBALS['wp_rewrite'] ) )
		{
			 $GLOBALS['wp_rewrite'] = new WP_Rewrite();
	    }
		
		$noactive = false;	
					 
		if( isset($_POST['user_login_reset']))
	    {
			$user_login = $_POST['user_login_reset'];
			 
		}else{
			
			$user_login ='';			 
			 
		}
		
		
		$g_recaptcha_response = '';		
		if(isset($_POST['g-recaptcha-response']) && $_POST['g-recaptcha-response']!=''){
			
			$g_recaptcha_response = sanitize_text_field($_POST['g-recaptcha-response']);		
		}
		
		//check reCaptcha
		$is_valid_recaptcha = true;	
		if($ultimatecaptcha->get_option('recaptcha_site_key')!='' && $ultimatecaptcha->get_option('recaptcha_secret_key')!='' && $ultimatecaptcha->get_option('recaptcha_display_forgot_password')=='1' ){
			
			$is_valid_recaptcha = $ultimatecaptcha->validate_recaptcha_field($g_recaptcha_response);		
		}
		
		 if( !$is_valid_recaptcha)
		 {			 
			 $this->errors[] = __('<strong>ERROR:</strong> The captcha validation is wrong..','ultimate-captcha');	 
		 }
		 
		$user_login =sanitize_user($user_login);
		 
		 if( $user_login=='')
		 {			 
			 $this->errors[] = __('<strong>ERROR:</strong> The username field is empty.','ultimate-captcha');	 
		 }
		  
   		 /* attempt to get recover */
		 if (!is_array($this->errors)) 
		 {			 
			 
			 // Adding support for login by email
			 if(is_email($_POST['user_login_reset']))
			 {				 
				 $user = get_user_by( 'email', $_POST['user_login_reset'] );
				 
		 
				 // check if we have a valid username		
				 if(isset($user) && $user != false)	
				 {
					 
					$user_id =$user->ID;		
					
				 }else{
											
					$user_id ="";	
					$this->errors[] = __('<strong>ERROR:</strong> Invalid Email or Username.','ultimate-captcha');					
					
				 }
									
				 if(!$this->is_active($user_id))
				 {
					 $noactive = true;						
				 }
				
			  }else{
				  
				   					
					// User is trying to login using username					
					$user = get_user_by('login',$_POST['user_login_reset']);
					
					// check if we have a valid username		
					if(isset($user) && $user != false)	
					{
						$user_id =$user->ID;		
					
					}else{
												
						$user_id ="";	
						$this->errors[] = __('<strong>ERROR:</strong> Invalid Email or Username.','ultimate-captcha');					
					}
							
					if(!$this->is_active($user_id) && !is_super_admin($user_id))
					{
						$noactive = true;
						
					}				
					
					$user_login = sanitize_user($_POST['user_login_reset']);	
				
				 }
				
			
				
				if(!$noactive)
				{								
					
								
				}else{
					
					//not active
					$this->errors[] = __('<strong>ERROR:</strong> Your account is not active.','ultimate-captcha');
				 
				}				
				
			}else{				
				
				
			}
			
			
			//we send notification emails			
			if($user_id!="" && isset($user) && $user != false)
		  	{				
				//generate reset link
				$unique_key =  $this->get_unique_verify_account_id();
				
				//web url
				$web_url = $this->get_password_reset_page_direct_link();
				
				$pos = strpos("page_id", $web_url);
  
				
				if ($pos === false) //not page_id found
				{
					  //
					  $reset_link = $web_url."?resskey=".$unique_key;
					  
				} else {
					   
					   // found then we're using seo links					 
					   $reset_link = $web_url."&resskey=".$unique_key;					  
				}
				
				//update meta
				update_user_meta ($user_id, 'ultimatecaptcha_ultra_very_key', $unique_key);	
				
				//notify users			  
				$ultimatecaptcha->messaging->send_reset_link($user, $reset_link);			  
				
				//send reset link to user		  			  
				 $html = "<div class='ucaptcha-ultra-success'>".__(" A reset link has been sent to your email. ", 'ultimate-captcha')."</div>";
				 
				 $this->get_sucess_message_reset= $html; 
			 
		  	} ///end send emails
		
    }
  
  /*---->> Check if user is active before login  ****/
	function is_active($user_id) 
	{
		global $ultimatecaptcha ;
		
		//$is_client  = $this->is_client($user_id);
		
		$checkuser = get_user_meta($user_id, 'ultimatecaptcha_account_status', true);
		$res = '';
		if ($checkuser == 'active' || $checkuser == '') //this is a tweak for already members
		{
			$res = true; //the account is active
		
		}else{
			
			$res = false;
		
		}
		
				
		
		return $res;		
		
   }
  
  public function get_login_page_direct_link()
  {
		global $ultimatecaptcha, $wp_rewrite ;
		
		$wp_rewrite = new WP_Rewrite();		
		
		$account_page_id = $ultimatecaptcha->get_option('bup_user_login_page');		
		$my_account_url = get_permalink($account_page_id);
		
		return $my_account_url;
	
  }
	
  public function get_password_reset_page_direct_link()
  {
		global $ultimatecaptcha, $wp_rewrite ;
		
		$wp_rewrite = new WP_Rewrite();		
		
		$account_page_id = $ultimatecaptcha->get_option('bup_password_reset_page');		
		$my_account_url = get_permalink($account_page_id);
		
		return $my_account_url;
	
	}
	

  
  public function genRandomString($length) 
  {
		
		$characters = "0123456789ABCDEFGHIJKLMNOPQRSTUVWZYZ";
		
		$real_string_legnth = strlen($characters) ;
		//$real_string_legnth = $real_string_legnth– 1;
		$string="ID";
		
		for ($p = 0; $p < $length; $p++)
		{
			$string .= $characters[mt_rand(0, $real_string_legnth-1)];
		}
		
		return strtolower($string);
	}
	
	public function get_password_recover_page()
	{
		global $ultimatecaptcha, $wp_rewrite, $blog_id ; 
		
		$wp_rewrite = new WP_Rewrite();
		
		$account_page_id = $ultimatecaptcha->get_option('bup_password_reset_page');				
		$my_account_url = get_page_link($account_page_id);				
						
		if($account_page_id=="")
		{
			$url = "NO";						
		}else{
			
			$url = $my_account_url;		
						
		}
		
		return $url;					
				
		
	}
	
	public function get_login_page()
	{
		global $ultimatecaptcha, $wp_rewrite, $blog_id ; 
		
		$wp_rewrite = new WP_Rewrite();
		
		$account_page_id = $ultimatecaptcha->get_option('bup_user_login_page');				
		$my_account_url = get_page_link($account_page_id);				
						
		if($account_page_id=="")
		{
			$url = "NO";						
		}else{
			
			$url = $my_account_url;		
						
		}
		
		return $url;					
				
		
	}
	
	public function get_registration_page()
	{
		global $ultimatecaptcha, $wp_rewrite, $blog_id ; 
		
		$wp_rewrite = new WP_Rewrite();
		$account_page_id = $ultimatecaptcha->get_option('ultimatecaptcha_registration_page');				
		$my_account_url = get_page_link($account_page_id);				
						
		if($account_page_id=="")
		{
			$url = "NO";						
		}else{
			
			$url = $my_account_url;		
						
		}
		
		return $url;					
				
		
	}
	
	
	
	
	public function login_registration_afterlogin()
	{
		global $ultimatecaptcha, $wp_rewrite, $blog_id ; 
		
		$wp_rewrite = new WP_Rewrite();
		
		if (isset($_REQUEST['redirect_to']))
		{
			$url = $_REQUEST['redirect_to'];
				
		} elseif (isset($_POST['redirect_to'])) {
		
			$url = $_POST['redirect_to'];
				
		} else {
								
					//$redirect_custom_page = $ultimatecaptcha->get_option('redirect_after_registration_login');				
					//$url = get_page_link($redirect_custom_page);
					
					//if($url=='' || $redirect_custom_page=='')
					//{						
						//check redir		
						$account_page_id = $ultimatecaptcha->get_option('bup_my_account_page');				
						$my_account_url = get_page_link($account_page_id);				
						
						if($my_account_url=="")
						{
							$url = $_SERVER['REQUEST_URI'];
						
						}else{
							
							$url = $my_account_url;				
						
						}
						
						//echo "acc page". $url;	
					
				
		}	
		
		//echo $url;	
		
		wp_redirect( $url );
		exit();
	
	}
	
	/* Auto login user */
	function bup_auto_login( $username, $remember=true ) 
	{
		ob_start();
		if ( !is_user_logged_in() ) {
			$user = get_user_by('login', $username );
			$user_id = $user->ID;
			wp_set_current_user( $user_id, $username );
			wp_set_auth_cookie( $user_id, $remember );
			do_action( 'wp_login', $user->user_login, $user );
		} else {
			wp_logout();
			$user = get_user_by('login', $username );
			$user_id = $user->ID;
			wp_set_current_user( $user_id, $username );
			wp_set_auth_cookie( $user_id, $remember );
			do_action( 'wp_login', $user->user_login, $user );
		}
		ob_end_clean();
	}
	
		
	
	/**
	* Add the shortcodes
	*/
	function profile_shortcodes() 
	{	
		add_shortcode( 'ultimatecaptcha_user_login', array(&$this,'user_login') );
		add_shortcode( 'ultimatecaptcha_user_recover_password', array(&$this,'user_recover_password') );
		add_shortcode( 'ultimatecaptcha_account', array(&$this,'user_account') );	
		add_shortcode( 'ultimatecaptcha_user_signup', array(&$this,'user_signup') );	
	}
	
		
	public function  user_login ($atts)
	{
		global $ultimatecaptcha;				
		return $this->get_client_login_form($atts);		
		
	}
	
	public function  user_signup ($atts)
	{
		global $ultimatecaptcha;				
		return $this->get_client_signup_form($atts);		
		
	}
	
	public function  user_recover_password ($atts)
	{
		global $ultimatecaptcha;				
		return $this->get_client_recover_password_form($atts);		
		
	}
	
	public function  user_account ($atts)
	{
		global $ultimatecaptcha;
		
		
			if (!is_user_logged_in()) 
			{				
				
				return $this->get_client_login_form( $atts );
				
			}else{				
				
				return $this->get_my_account_page( $atts );
			}	
							
			
	}
	
	function get_user_avatar_top($staff_id)
	{
		global $wpdb,  $ultimatecaptcha, $wp_rewrite;
		
		$html = '';
		
		$html .='<div class="ucaptcha-staff-profile-top" >
		'.$this->get_user_pic( $staff_id, 100, 'avatar', null, null, false).' <div class="ucaptcha-div-for-avatar-upload"> <a href="?module=upload_avatar"><div name="ucaptcha-button-change-avatar" id="ucaptcha-button-change-avatar" class="ucaptcha-button-change-avatar" ><i class="fa fa-camera"></i></div></a></div>
		
		</div>';
		
		return $html;
		
	}
	
	
	
	/**
	 * Users Dashboard
	 */
	public function get_my_account_page($atts )
	{
		global $wpdb, $current_user;		
		$user_id = get_current_user_id();
		
		extract( shortcode_atts( array(	
			
			'disable' => ''						
			
		), $atts ) );
		
		$modules = array();
		$modules  = explode(',', $disable);
		
      
        $content = $this->get_staff_account();
		return  $content;
		  
	}
	
	function get_staff_account(){
		
		global $ultimatecaptcha;		
		
		//turn on output buffering to capture script output
        ob_start();
		
        //include the specified file			
		$theme_path = get_template_directory();		
		
		if(file_exists($theme_path."/ultimatecaptcha/dashboard.php"))
		{
			
			include($theme_path."/ultimatecaptcha/dashboard.php");
		
		}else{
			
			include(ultimatecaptcha_path.'/templates/dashboard.php');
		
		}		
		//assign the file output to $content variable and clean buffer
        $content = ob_get_clean();
		return  $content;		
	
	}
	
	
	/*Get errors display*/
	function get_errors()
	 {
		global $ultimatecaptcha;
		
		$display = null;
		
		if (isset($this->errors) && is_array($this->errors))  
		{
		    $display .= '<div class="ucaptcha-ultra-error">';
		
			foreach($this->errors as $newError) 
			{
				
				$display .= '<span class="ucaptcha-error ucaptcha-error-block"><i class="ucaptcha-icon-remove"></i>'.$newError.'</span>';
			
			}
		$display .= '</div>';
		
		
		} else {
			
			if (isset($_REQUEST['redirect_to']))
			{
				$url = $_REQUEST['redirect_to'];
				
			} elseif (isset($_POST['redirect_to'])) {
				
				$url = $_POST['redirect_to'];
				
			} else {
				
				$url = $_SERVER['REQUEST_URI'];
			}
			wp_redirect( $url );
		}
		return $display;
	}
	
	
	/*Get errors display*/
	function get_errors_reset()
	 {
		global $ultimatecaptcha;
		
		$display = null;
		
		if (isset($this->errors) && is_array($this->errors))  
		{
		    $display .= '<div class="ucaptcha-ultra-error">';
		
			foreach($this->errors as $newError) 
			{
				
				$display .= '<span class="ucaptcha-error ucaptcha-error-block"><i class="ucaptcha-icon-remove"></i>'.$newError.'</span>';
			
			}
		$display .= '</div>';
		
		
		
		}
		return $display;
	}
	
	
	public function get_client_signup_form($args=array()) 
	{
		
		global $ultimatecaptcha, $ultimatecaptcha_aweber, $ucaptcha_passwordstrength;
		
		/* Arguments */
		$defaults = array(       
			'redirect_to' => null,
			'form_header_text' => __('Sign Up','ultimate-captcha')
			
        		    
		);
		$args = wp_parse_args( $args, $defaults );
		$args_2 = $args;
		extract( $args, EXTR_SKIP );
		
		$display = null;	
		
		$display .= '<div class="ucaptcha-front-cont">';
		
	    $display .= '<div class="ucaptcha-user-data-registration-form">';
		
		/*Display errors*/
		if (isset($_POST['ucaptcha-client-form-registration-confirm']))
		{
			$display .= $this->get_errors();			
		}
		
		/*Display errors*/
		if (isset($_GET['ultimatecaptcha_registration']))
		{
			$display .= '<div class="ucaptcha-ultra-success"><span><i class="fa fa-check"></i>'.__('Your request has been sent successfully. Please check your email.','ultimate-captcha').'</span></div>';						
		}		
		
		$display .= '<form action="" method="post" id="ucaptcha-client-registration-form" name="ucaptcha-client-registration-form" enctype="multipart/form-data">';
		
		$display .= '<input type="hidden" name="ucaptcha-client-form-registration-confirm" id="ucaptcha-client-form-confirm-registration-confirm" >';
		
		$display .= '<input type="hidden" name="ucaptcha-custom-forms" id="ucaptcha-custom-forms" >';	
		$display .= '<div class="ucaptcha-profile-separator">'.__('Account Data','ultimate-captcha').'</div>';
		
		
		//name
		$display .= '<div class="ucaptcha-profile-field">';									
		$display .= '<label class="ucaptcha-field-type" for="first_name">';
		//$display .= '<i class="fa fa-user"></i>';	
		$display .= '<span>'.__('First Name', 'ultimate-captcha').' '.$required_text.'</span></label>';					
		$display .= '<div class="ucaptcha-field-value">';
		
					$display .= '<input type="text" class="'.$required_class.' ucaptcha-input " name="first_name" id="first_name" value="'.$ultimatecaptcha->get_post_value('first_name').'" title="'.__('Type your First Name','ultimate-captcha').'"  placeholder="'.__('Type your First Name','ultimate-captcha').'" data-errormessage-value-missing="'.__(' * This input is required!','ultimate-captcha').'"/>';					
					$display .= '</div>'; //end field value
					
		$display .= '</div>'; //end field
		
		//Last name
		$display .= '<div class="ucaptcha-profile-field">';									
		$display .= '<label class="ucaptcha-field-type" for="last_name">';
		//$display .= '<i class="fa fa-user"></i>';	
		$display .= '<span>'.__('Last Name', 'ultimate-captcha').' '.$required_text.'</span></label>';					
		$display .= '<div class="ucaptcha-field-value">';
		
					$display .= '<input type="text" class="'.$required_class.' ucaptcha-input " name="last_name" id="last_name" value="'.$ultimatecaptcha->get_post_value('last_name').'" title="'.__('Type your Last Name','ultimate-captcha').'"  placeholder="'.__('Type your Last Name','ultimate-captcha').'" data-errormessage-value-missing="'.__(' * This input is required!','ultimate-captcha').'"/>';					
					$display .= '</div>'; //end field value
					
		$display .= '</div>'; //end field
		
		//User
		$display .= '<div class="ucaptcha-profile-field">';									
		$display .= '<label class="ucaptcha-field-type" for="user_name">';
		//$display .= '<i class="fa fa-user"></i>';	
		$display .= '<span>'.__('Username', 'ultimate-captcha').' '.$required_text.'</span></label>';					
		$display .= '<div class="ucaptcha-field-value">';
		
					$display .= '<input type="text" class="'.$required_class.' ucaptcha-input " name="user_name" id="user_name" value="'.$ultimatecaptcha->get_post_value('user_name').'" title="'.__('Type your Username','ultimate-captcha').'"  placeholder="'.__('Type your Username','ultimate-captcha').'" data-errormessage-value-missing="'.__(' * This input is required!','ultimate-captcha').'"/>';					
					$display .= '</div>'; //end field value
					
		$display .= '</div>'; //end field	
		
		
		//check Password Strenght		
		if(isset( $ucaptcha_passwordstrength)){
			
			if($ultimatecaptcha->get_option('registration_password_ask')==1){
				
				//Password
				$display .= '<div class="ucaptcha-profile-field">';									
				$display .= '<label class="ucaptcha-field-type" for="user_password">';
				//$display .= '<i class="fa fa-user"></i>';	
				$display .= '<span>'.__('Password', 'ultimate-captcha').' '.$required_text.'</span></label>';					
				$display .= '<div class="ucaptcha-field-value">';
				
							$display .= '<input type="password" class="'.$required_class.' ucaptcha-input " name="user_password" id="user_password" value="'.$ultimatecaptcha->get_post_value('user_password').'" title="'.__('Type your Password','ultimate-captcha').'"  placeholder="'.__('Type your Password','ultimate-captcha').'" data-errormessage-value-missing="'.__(' * This input is required!','ultimate-captcha').'"/>';					
				$display .= '</div>'; //end field value
							
				$display .= '</div>'; //end field	
			
			
			}
		
		}	
		
		
		$display .= '<div class="ucaptcha-profile-field">';									
		$display .= '<label class="ucaptcha-field-type" for="email">';
		//$display .= '<i class="fa fa-user"></i>';	
		$display .= '<span>'.__('Email', 'ultimate-captcha').' '.$required_text.'</span></label>';	
		
		
		$help = 	__('The login information will be sent to this email address. A random password will be generated and you can change it later from your account.','ultimate-captcha');	
					
		$display .= '<div class="ucaptcha-field-value">';
		
					$display .= '<input type="text" class="'.$required_class.' ucaptcha-input " name="email" id="email" value="'.$ultimatecaptcha->get_post_value('email').'" title="'.__('Type your Email','ultimate-captcha').'"  placeholder="'.__('Type your Email','ultimate-captcha').'" data-errormessage-value-missing="'.__(' * This input is required!','ultimate-captcha').'"/>';
					$display .= '<div class="ucaptcha-help">'.$help.'</div>';
									
					$display .= '</div>'; //end field value
					
		$display .= '</div>'; //end field
		
		
		/*If aweber*/		
		if($ultimatecaptcha->get_option('newsletter_active')=='aweber' && $ultimatecaptcha->get_option('aweber_consumer_key')!="" && isset($ultimatecaptcha_aweber) && !is_user_logged_in())
		{
			
			//new aweber field			
			$aweber_text = stripslashes($ultimatecaptcha->get_option('aweber_text'));
			$aweber_header_text = stripslashes($ultimatecaptcha->get_option('aweber_header_text'));
			
			if($aweber_header_text==''){
				
				$aweber_header_text = __('Receive Daily Updates ', 'ultimate-captcha');				
			}	
			
			if($aweber_text==''){
				
				$aweber_text = __('Yes, I want to receive daily updates. ', 'ultimate-captcha');				
			}			
			
			
			//
			
			$aweber_autchecked = $ultimatecaptcha->get_option('aweber_auto_checked');
			
			$aweber_auto = '';
			if($aweber_autchecked==1){
				
				$aweber_auto = 'checked="checked"';				
			}
			
			 $display .= '<div class="ucaptcha-profile-separator">'.$aweber_header_text.'</div>';			 
			 $display .= '<div class="ucaptcha-profile-field " style="text-align:left">';			
						
			 $display .= '<input type="checkbox"  title="'.$aweber_header_text.'" name="ucaptcha-aweber-confirmation"  id="ucaptcha-aweber-confirmation" value="1"  '.$aweber_auto.' > <label for="ucaptcha-aweber-confirmation"><span></span>'.$aweber_text.'</label>' ;								
			 $display .= '</div>';
			
		
		}
		
		//recaptcha			
		if($ultimatecaptcha->get_option('recaptcha_site_key')!='' && $ultimatecaptcha->get_option('recaptcha_secret_key')!='' && $ultimatecaptcha->get_option('recaptcha_display_registration')=='1'){	
		
			$display .= '<div class="ucaptcha-profile-field">';			
			$display .= $ultimatecaptcha->recaptcha_field(); 				
			$display .= '</div>'; 		
		}
		
		$display .= '<div class="ucaptcha-profile-field">';
		
					$display .= '<button type="button" id="ucaptcha-btn-conf-signup" class="ucaptcha-button-submit-changes">'.__('Submit','ultimate-captcha').'	</button>';	
					
					$display .= '<br><br>';	
					
					$login_link = $this->get_login_page();
					
					if($login_link=='NO'){
						
						$login_page = __('Please set a login page.','ultimate-captcha');
						
					}else{
						
						$login_page = '<a href="'.$login_link.'">'.__('Already have an account?','ultimate-captcha').'</a>';					
					}
					
					$display .= '<p class="ucaptcha-pass-reset-link">'.$login_page.'</p>';				
								
				
		$display .= '</div>'; //end submit button	
		
		
		$display .= '</form>'; //end registration form
		$display .= '</div>'; //end registration form
		$display .= '</div>'; //end bup main cont
		
		
		return $display;
	}
	
	public function get_client_login_form($args=array()) 
	{
		
		global $ultimatecaptcha, $ultimatecaptcha_recaptcha;
		
		/* Arguments */
		$defaults = array(       
			'redirect_to' => null,
			'form_header_text' => __('Login','ultimate-captcha')
			
        		    
		);
		$args = wp_parse_args( $args, $defaults );
		$args_2 = $args;
		extract( $args, EXTR_SKIP );
		
		$display = null;	
		
		$display .= '<div class="ucaptcha-front-cont">';
		
	    $display .= '<div class="ucaptcha-user-data-registration-form">';
		
		/*Display errors*/
		if (isset($_POST['ucaptcha-client-form-confirm']))
		{
			$display .= $this->get_errors();
		}		
		
		$display .= '<form action="" method="post" id="ucaptcha-client-form" name="ucaptcha-client-form" enctype="multipart/form-data">';
		
		$display .= '<input type="hidden" name="ucaptcha-client-form-confirm" id="ucaptcha-client-form-confirm" >';
		
		$display .= '<input type="hidden" name="ucaptcha-custom-forms" id="ucaptcha-custom-forms" >';

		
		$display .= '<div class="ucaptcha-profile-separator">'.__('Login data','ultimate-captcha').'</div>';
		
		
		$display .= '<div class="ucaptcha-profile-field">';									
		$display .= '<label class="ucaptcha-field-type" for="user_email_2">';
		$display .= '<i class="fa fa-user"></i>';	
		$display .= '<span>'.__('Username or Email', 'ultimate-captcha').' '.$required_text.'</span></label>';
						
					
					
		$display .= '<div class="ucaptcha-field-value">';
		
					$display .= '<input type="text" class="'.$required_class.' ucaptcha-input " name="user_login" id="user_login" value="'.$ultimatecaptcha->get_post_value('user_login').'" title="'.__('Type your Username or Email','ultimate-captcha').'"  placeholder="'.__('Type your Username or Email','ultimate-captcha').'" data-errormessage-value-missing="'.__(' * This input is required!','ultimate-captcha').'"/>';					
					$display .= '</div>'; //end field value
					
		$display .= '</div>'; //end field
		
		
		$display .= '<div class="ucaptcha-profile-field">';									
		$display .= '<label class="ucaptcha-field-type" for="login_user_pass">';
		$display .= '<i class="fa fa-lock"></i>';	
		$display .= '<span>'.__('Password', 'ultimate-captcha').' '.$required_text.'</span></label>';
						
					
					
		$display .= '<div class="ucaptcha-field-value">';
		
					$display .= '<input type="password" class="'.$required_class.' ucaptcha-input " name="login_user_pass" id="login_user_pass" value="'.$ultimatecaptcha->get_post_value('login_user_pass').'" title="'.__('Type your Password','ultimate-captcha').'"  placeholder="'.__('Type your Password','ultimate-captcha').'" data-errormessage-value-missing="'.__(' * This input is required!','ultimate-captcha').'"/>';					
					$display .= '</div>'; //end field value
					
		$display .= '</div>'; //end field
		
		
		//recaptcha			
		if( $ultimatecaptcha->get_option('recaptcha_site_key')!='' && $ultimatecaptcha->get_option('recaptcha_secret_key')!='' && $ultimatecaptcha->get_option('recaptcha_display_loginform')=='1'){	
		
			$display .= '<div class="ucaptcha-profile-field">';			
			$display .= $ultimatecaptcha->recaptcha_field(); 				
			$display .= '</div>'; 		
		}
		
		$display .= '<div class="ucaptcha-profile-field">';
		
					$display .= '<button name="ucaptcha-btn-book-app-confirm-login" type="submit"  class="ucaptcha-button-submit-changes">'.__('Submit','ultimate-captcha').'	</button>';	
					
					$display .= '<br><br>';	
					
					$reset_link = $this->get_password_recover_page();
					
					if($reset_link=='NO'){
						
						$reset_password = __('Please set a password reset page.','ultimate-captcha');
						
					}else{
						
						$reset_password = '<a href="'.$reset_link.'">'.__('Forgot Password?','ultimate-captcha').'</a>';				
					
					}
					
					$display .= '<p class="ucaptcha-pass-reset-link">'.$reset_password.'</p>';
					
					$signup_link = $this->get_registration_page();
					
					if($signup_link=='NO'){
						
						$registrationpage = __('Please set a registration page.','ultimate-captcha');
						
					}else{
						
						$registrationpage = '<a href="'.$signup_link.'">'.__("Don't you have an account?",'ultimate-captcha').'</a>';				
					
					}
					
					$display .= '<p class="ucaptcha-pass-reset-link">'.$registrationpage.'</p>';
					
					
					
					
									
								
				
		$display .= '</div>'; //end submit button	
		
		
		
		
		$display .= '</form>'; //end registration form
		$display .= '</div>'; //end registration form
		$display .= '</div>'; //end bup main cont
		
		
		return $display;
	}
	
	public function get_client_recover_password_form($args=array()) 
	{
		
		global $ultimatecaptcha, $ultimatecaptcha_recaptcha;
		
		/* Arguments */
		$defaults = array(       
			'redirect_to' => null,
			'form_header_text' => __('Recover Password','ultimate-captcha')
			
        		    
		);
		$args = wp_parse_args( $args, $defaults );
		$args_2 = $args;
		extract( $args, EXTR_SKIP );
		
		$display = null;	
		
		$display .= '<div class="ucaptcha-front-cont">';		
	    $display .= '<div class="ucaptcha-user-data-registration-form">';
		
		/*Display errors*/
		if (isset($_POST['ucaptcha-client-recover-pass-form-confirm']))
		{
			$display .= $this->get_errors_reset();
			$display .= $this->get_sucess_message_reset;
		}		
		
		$display .= '<form action="" method="post" id="ucaptcha-client-recover-pass-form" name="ucaptcha-client-recover-pass-form" enctype="multipart/form-data">';
		
		if(isset($_GET['resskey']) && $_GET['resskey']!='') //this is a reset confirmation form
		{   
		    $icon = 'fa fa-lock';
			$type = 'password';
			$type_password=true;
			$reset_password_button='ucaptcha-reset-password-button-conf';	
			
			$display .= '<input type="hidden" name="ucaptcha-client-recover-pass-form-confirm-reset" id="ucaptcha-client-recover-pass-form-confirm-reset" >';
			
			$display .= '<input type="hidden" name="ucaptcha-custom-forms" id="ucaptcha-custom-forms" >';	
			
			$display .= '<input type="hidden" name="bup_reset_key" id="bup_reset_key" value="'.$_GET['resskey'].'" >';	
			
			$legend = __('Type your new password', 'ultimate-captcha');
			$legend2 = __('Re-Type your password', 'ultimate-captcha');
				
		
		}else{ //the user is requestin a new password
		
			$icon = 'fa fa-user';
			$type = 'text';
			$reset_password_button='';
			$type_password=false;	
			$legend = __('Username or Email', 'ultimate-captcha');		
			$display .= '<input type="hidden" name="ucaptcha-client-recover-pass-form-confirm" id="ucaptcha-client-recover-pass-form-confirm" >';						
		
		}
		
		$display .= '<div class="ucaptcha-profile-separator">'.__('Recover your password','ultimate-captcha').'</div>';					
		
		$display .= '<div class="ucaptcha-profile-field">';									
		$display .= '<label class="ucaptcha-field-type" for="user_email_2">';
		$display .= '<i class="'.$icon.'"></i>';
		
		$display .= '<span>'.$legend .' '.$required_text.'</span></label>';					
					
		$display .= '<div class="ucaptcha-field-value">';
		
					$display .= '<input type="'.$type .'" class="'.$required_class.' ucaptcha-input " name="user_login_reset" id="user_login_reset" value="'.$ultimatecaptcha->get_post_value('user_login_reset').'" title="'.__('Type your Password','ultimate-captcha').'"  placeholder="'.__('Type your Password','ultimate-captcha').'" data-errormessage-value-missing="'.__(' * This input is required!','ultimate-captcha').'"/>';					
		
		
		$display .= '</div>'; //end field value
					
		$display .= '</div>'; //end field
		
		if($type_password){
			
			$display .= '<div class="ucaptcha-profile-field">';									
			$display .= '<label class="ucaptcha-field-type" for="user_email_2">';
			$display .= '<i class="'.$icon.'"></i>';
			
			$display .= '<span>'.$legend2 .' '.$required_text.'</span></label>';					
						
			$display .= '<div class="ucaptcha-field-value">';
			
						$display .= '<input type="password" class="'.$required_class.' ucaptcha-input " name="user_password_reset_2" id="user_password_reset_2" value="'.$ultimatecaptcha->get_post_value('user_login_reset').'" title="'.__('Type your new password again','ultimate-captcha').'"  placeholder="'.__('Type your new password again','ultimate-captcha').'" data-errormessage-value-missing="'.__(' * This input is required!','ultimate-captcha').'"/>';					
			
			$display .= '</div>'; //end field value						
			$display .= '</div>'; //end field
		}
		
		//recaptcha			
		if($ultimatecaptcha->get_option('recaptcha_site_key')!='' && $ultimatecaptcha->get_option('recaptcha_secret_key')!='' && $ultimatecaptcha->get_option('recaptcha_display_forgot_password')=='1'){	
		
			$display .= '<div class="ucaptcha-profile-field">';			
			$display .= $ultimatecaptcha->recaptcha_field(); 				
			$display .= '</div>'; 		
		}
		
		
		$display .= '<div class="ucaptcha-profile-field">';
		 
					$display .= '<button name="ucaptcha-btn-book-app-confirm-resetlink" id="ucaptcha-btn-book-app-confirm-resetlink" type="submit"  class="ucaptcha-button-submit-changes '.$reset_password_button.'">'.__('Submit','ultimate-captcha').'	</button>';
					$display .= '<span id="ucaptcha-pass-reset-message">&nbsp;</span>';
					
					$display .= '<br><br>';	
					
					$reset_link = $this->get_login_page();
					
					if($reset_link=='NO'){
						
						$reset_password = __('Please set a login page.','ultimate-captcha');
						
					}else{
						
						$reset_password = '<a href="'.$reset_link.'">'.__('Login to your account?','ultimate-captcha').'</a>';					
					}
					
					$display .= '<p class="ucaptcha-pass-reset-link">'.$reset_password.'</p>';							
				
		$display .= '</div>'; //end submit button	
		
		
		$display .= '</form>'; //end registration form
		$display .= '</div>'; //end registration form
		$display .= '</div>'; //end bup main cont
		
		return $display;
	}
	
	
	public function confirm_reset_password()
	{
		global $wpdb,  $ultimatecaptcha, $wp_rewrite;
		
	
		$wp_rewrite = new WP_Rewrite();
		
		//check redir		
		$account_page_id = $ultimatecaptcha->get_option('login_page_id');
		$my_account_url = get_permalink($account_page_id);
		
		
		$PASSWORD_LENGHT =7;
		
		$password1 = $_POST['p1'];
		$password2 = $_POST['p2'];
		$key = $_POST['key'];
		
		$html = '';
		$validation = '';
		
		//check password		
		if($password1!=$password2)
		{
			$validation .= "<div class='ucaptcha-ultra-error'>".__(" ERROR! Password must be identical ", 'ultimate-captcha')."</div>";
			$html = $validation;			
		}
		
		if(strlen($password1)<$PASSWORD_LENGHT)
		{
			$validation .= "<div class='ucaptcha-ultra-error'>".__(" ERROR! Password should contain at least 7 alphanumeric characters ", 'ultimate-captcha')."</div>";
			$html = $validation;		
		}		
		
		$user = $this->get_one_user_with_key($key);		
		
		if($validation=="" )
		{			
			if($user->ID >0 )
			{
				//print_r($user);
				$user_id = $user->ID;
				$user_email = $user->user_email;
				$user_login = $user->user_login;
				
				wp_set_password( $password1, $user_id ) ;
				
				//notify user				
				$ultimatecaptcha->messaging->send_new_password_to_user($user, $password1);				
				$html = "<div class='ucaptcha-ultra-success'>".__(" Success!! The new password has been changed. Please click on the login link to get in your account.", 'ultimate-captcha')."</div>";
				
												
			}else{
				
				// we couldn't find the user			
				$html = "<div class='ucaptcha-ultra-error'>".__(" ERROR! Invalid reset link ", 'ultimate-captcha')."</div>";
			
			}					
		}
		 echo $html;
		 die();
		
	
	}
	
	
	function get_one_user_with_key($key)
	{
		global $wpdb,  $ultimatecaptcha;
		
		$args = array( 	
						
			'meta_key' => 'ultimatecaptcha_ultra_very_key',                    
			'meta_value' => $key,                  
			'meta_compare' => '=',  
			'count_total' => true,   


			);
		
		 // Create the WP_User_Query object
		$user_query = new WP_User_Query( $args );
		 
		// Get the results//
		$users = $user_query->get_results();	
		
		if(count($users)>0)
		{
			foreach ($users as $user)
			{
				return $user;
			
			}
			
		
		}else{			
			
			
		}		
	
	}	
	
	
}
?>