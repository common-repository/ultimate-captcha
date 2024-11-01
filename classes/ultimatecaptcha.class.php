<?php
class UltimateCaptchaPlugin extends UltimateCaptchaCommon 
{
	var $RECAPTCHA_SITE_KEY;
	var $RECAPTCHA_SECRET_KEY;
	var $wp_all_pages = false;
	public $classes_array = array();
	
	var $notifications_email = array();
	var $ultimatecaptcha_default_options;	
	var $ajax_prefix = 'ultimatecaptcha';	
	var $allowed_inputs = array();
	
		
	public function __construct()
	{		
		/* Plugin slug and version */
		$this->slug = 'ultimatecaptcha';	
		
		/* Allowed input types */
		$this->allowed_inputs = array(
			'text' => __('Text','ultimate-captcha'),			
			'textarea' => __('Textarea','ultimate-captcha'),
			'select' => __('Select Dropdown','ultimate-captcha'),
			'radio' => __('Radio','ultimate-captcha'),
			'checkbox' => __('Checkbox','ultimate-captcha'),			
		    'datetime' => __('Date Picker','ultimate-captcha')
		);
		
		
		$this->set_default_email_messages();				
		$this->update_default_option_ini();		
		
		require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		$this->plugin_data = get_plugin_data( ultimatecaptcha_path . 'index.php', false, false);
		$this->version = $this->plugin_data['Version'];			
		
		add_action('admin_menu', array(&$this, 'add_menu'), 11);
		add_action('admin_head', array(&$this, 'admin_head'), 9 );
		add_action('admin_init', array(&$this, 'admin_init'), 9);		
		add_action('admin_init', array(&$this, 'do_valid_checks'), 9);
		
		add_action('admin_enqueue_scripts', array(&$this, 'add_styles'), 9);					
		add_action('wp_enqueue_scripts', array(&$this, 'add_front_end_scripts'), 12);
		add_action('wp_enqueue_scripts', array(&$this, 'add_front_end_styles'), 12);
		add_action('ini', array(&$this, 'create_actions'), 11);	
		
		add_action('plugins_loaded', array(&$this, 'add_front_recaptcha'), 12);		
		
		add_action( 'wp_ajax_'.$this->ajax_prefix.'_reset_email_template', array( &$this, 'reset_email_template' ));
		
		
		add_action( 'wp_ajax_'.$this->ajax_prefix.'_save_fields_settings', array( &$this, 'save_fields_settings' ));
				
		add_action( 'wp_ajax_'.$this->ajax_prefix.'_add_new_custom_profile_field', array( &$this, 'add_new_custom_profile_field' ));
		add_action( 'wp_ajax_'.$this->ajax_prefix.'_delete_profile_field', array( &$this, 'delete_profile_field' ));
		add_action( 'wp_ajax_'.$this->ajax_prefix.'_sort_fileds_list', array( &$this, 'sort_fileds_list' ));
		
		//user to get all fields
		add_action( 'wp_ajax_'.$this->ajax_prefix.'_reload_custom_fields_set', array( &$this, 'reload_custom_fields_set' ));
		
		//used to edit a field
		add_action( 'wp_ajax_'.$this->ajax_prefix.'_reload_field_to_edit', array( &$this, 'reload_field_to_edit' ));			
		
		add_action( 'wp_ajax_custom_fields_reset', array( &$this, 'custom_fields_reset' ));	
		
		$this->startactions_native_display() ;		
		$this->load_classes();		
		
    }
	
		/*This Function Change the Profile Fields Order when drag/drop */	
	public function sort_fileds_list() 
	{
		global $wpdb;
	
		$order = explode(',', $_POST['order']);
		$counter = 0;
		$new_pos = 10;
		
		//multi fields		
		
		if(isset($_POST["custom_form"]) && $_POST["custom_form"]!=''){
			
			$custom_form = $_POST["ultimatecaptcha_custom_form"];		
			$custom_form = 'ultimatecaptcha_profile_fields_'.$custom_form;
			
		}else{
			
			$custom_form = 'ultimatecaptcha_profile_fields';
			
		}		
			
					
		$fields = get_option($custom_form);			
		$fields_set_to_update =$custom_form;
		
		
		$new_fields = array();
		
		$fields_temp = $fields;
		ksort($fields);
		
		foreach ($fields as $field) 
		{
			
			$fields_temp[$order[$counter]]["position"] = $new_pos;			
			$new_fields[$new_pos] = $fields_temp[$order[$counter]];				
			$counter++;
			$new_pos=$new_pos+10;
		}
		
		ksort($new_fields);		
		
		
		update_option($fields_set_to_update, $new_fields);		
		die(1);
		
    }
	/*  delete profile field */
    public function delete_profile_field() 
	{						
		
		if($_POST['_item']!= "")
		{
			
			//multi fields		
			$custom_form = sanitize_text_field($_POST["custom_form"]);
			
			if($custom_form!="")
			{
				$custom_form = 'ultimatecaptcha_profile_fields_'.$custom_form;		
				$fields = get_option($custom_form);			
				$fields_set_to_update =$custom_form;
				
			}else{
				
				$fields = get_option('ultimatecaptcha_profile_fields');
				$fields_set_to_update ='ultimatecaptcha_profile_fields';
			
			}
			
			$pos = $_POST['_item'];
			
			unset($fields[$pos]);
			
			ksort($fields);
			print_r($fields);
			update_option($fields_set_to_update, $fields);
			
		
		}
	
	}
	
	
	 /* create new custom profile field */
    public function add_new_custom_profile_field() 
	{				
		
		
		if($_POST['_meta']!= "")
		{
			$meta = sanitize_text_field($_POST['_meta']);
		
		}else{
			
			$meta = sanitize_text_field($_POST['_meta_custom']);
		}
		
		//if custom fields
		
		
		//multi fields		
		$custom_form = sanitize_text_field( $_POST["custom_form"]);
		
		if($custom_form!="")
		{
			$custom_form = 'ultimatecaptcha_profile_fields_'.$custom_form;		
			$fields = get_option($custom_form);			
			$fields_set_to_update =$custom_form;
			
		}else{
			
			$fields = get_option('ultimatecaptcha_profile_fields');
			$fields_set_to_update ='ultimatecaptcha_profile_fields';
		
		}
		
		$min = min(array_keys($fields)); 
		
		$pos = $min-1;
		
		$fields[$pos] =array(
			  'position' => $pos,
				'icon' => sanitize_text_field($_POST['_icon']),
				'type' => sanitize_text_field($_POST['_type']),
				'field' => sanitize_text_field($_POST['_field']),
				'meta' => sanitize_text_field($meta),
				'name' => sanitize_text_field($_POST['_name']),				
				'tooltip' => sanitize_text_field($_POST['_tooltip']),
				'help_text' => sanitize_text_field($_POST['_help_text']),							
				'can_edit' => sanitize_text_field($_POST['_can_edit']),
				'allow_html' => sanitize_text_field($_POST['_allow_html']),
				'can_hide' => sanitize_text_field($_POST['_can_hide']),				
				'private' => sanitize_text_field($_POST['_private']),
				'required' => sanitize_text_field($_POST['_required']),
				'show_in_register' => sanitize_text_field($_POST['_show_in_register']),
				'predefined_options' => sanitize_text_field($_POST['_predefined_options']),				
				'choices' => sanitize_text_field($_POST['_choices']),												
				'deleted' => 0
				

			);			
					
			ksort($fields);
			print_r($fields);			
		   update_option($fields_set_to_update, $fields);         


    }
	
	
	 // save form
    public function save_fields_settings() 
	{		
		
		$pos = sanitize_text_field($_POST['pos']); 
		
		if($_POST['_meta']!= "")
		{
			$meta = sanitize_text_field($_POST['_meta']);
		
		}else{
			
			$meta = sanitize_text_field($_POST['_meta_custom']);
		}
		
		//if custom fields
		
		//multi fields		
		$custom_form = sanitize_text_field($_POST["custom_form"]);
		
		if($custom_form!="")
		{
			$custom_form = 'ultimatecaptcha_profile_fields_'.$custom_form;		
			$fields = get_option($custom_form);			
			$fields_set_to_update =$custom_form;
			
		}else{
			
			$fields = get_option('ultimatecaptcha_profile_fields');
			$fields_set_to_update ='ultimatecaptcha_profile_fields';
		
		}
		
		$fields[$pos] =array(
			  'position' => $pos,
				'icon' => sanitize_text_field($_POST['_icon']),
				'type' => sanitize_text_field($_POST['_type']),
				'field' => sanitize_text_field($_POST['_field']),
				'meta' => sanitize_text_field($meta),
				'name' => sanitize_text_field($_POST['_name']),
				'ccap' => sanitize_text_field($_POST['_ccap']),
				'tooltip' => sanitize_text_field($_POST['_tooltip']),
				'help_text' => sanitize_text_field($_POST['_help_text']),
				'social' =>  sanitize_text_field($_POST['_social']),
				'is_a_link' =>  sanitize_text_field($_POST['_is_a_link']),
				'can_edit' => sanitize_text_field($_POST['_can_edit']),
				'allow_html' => sanitize_text_field($_POST['_allow_html']),				
				'required' => sanitize_text_field($_POST['_required']),
				'show_in_register' => sanitize_text_field($_POST['_show_in_register']),
				
				'predefined_options' => sanitize_text_field($_POST['_predefined_options']),				
				'choices' => sanitize_text_field($_POST['_choices']),												
				'deleted' => 0,
				'show_to_user_role' => sanitize_text_field($_POST['_show_to_user_role']),
                'edit_by_user_role' => sanitize_text_field($_POST['_edit_by_user_role'])
			);
			
			
						
			print_r($fields);
			
		    update_option($fields_set_to_update , $fields);
		
         


    }
	
		
	/*This load a custom field to be edited Implemented on 08-08-2014*/
	function reload_field_to_edit()	
	{
		global $wpticketultra;
		
		//get field
		$pos = sanitize_text_field($_POST["pos"]);
		
		
		//multi fields		
		$custom_form = sanitize_text_field($_POST["custom_form"]);
		
		if($custom_form!="")
		{
			$custom_form = 'ultimatecaptcha_profile_fields_'.$custom_form;		
			$fields = get_option($custom_form);			
			$fields_set_to_update =$custom_form;
			
		}else{
			
			$fields = get_option('ultimatecaptcha_profile_fields');
			$fields_set_to_update ='ultimatecaptcha_profile_fields';
		
		}
		
		$array = $fields[$pos];
		
		
		extract($array); $i++;

		if(!isset($required))
		       $required = 0;

		    if(!isset($fonticon))
		        $fonticon = '';				
				
			if ($type == 'seperator' || $type == 'separator') {
			   
				$class = "separator";
				$class_title = "";
			} else {
			  
				$class = "profile-field";
				$class_title = "profile-field";
			}
		
		
		?>
		
		

				<p>
					<label for="uultra_<?php echo $pos; ?>_position"><?php _e('Position','ultimate-captcha'); ?>
					</label> <input name="uultra_<?php echo $pos; ?>_position"
						type="text" id="uultra_<?php echo $pos; ?>_position"
						value="<?php echo $pos; ?>" class="small-text" /> <i
						class="uultra_icon-question-sign uultra-tooltip2"
						title="<?php _e('Please use a unique position. Position lets you place the new field in the place you want exactly in Profile view.','ultimate-captcha'); ?>"></i>
				</p>

				<p>
					<label for="uultra_<?php echo $pos; ?>_type"><?php _e('Field Type','ultimate-captcha'); ?>
					</label> <select name="uultra_<?php echo $pos; ?>_type"
						id="uultra_<?php echo $pos; ?>_type">
						<option value="usermeta" <?php selected('usermeta', $type); ?>>
							<?php _e('Profile Field','ultimate-captcha'); ?>
						</option>
						<option value="separator" <?php selected('separator', $type); ?>>
							<?php _e('Separator','ultimate-captcha'); ?>
						</option>
					</select> <i class="uultra-icon-question-sign uultra-tooltip2"
						title="<?php _e('You can create a separator or a usermeta (profile field)','ultimate-captcha'); ?>"></i>
				</p> 
				
				<?php if ($type != 'separator') { ?>

				<p class="uultra-inputtype">
					<label for="uultra_<?php echo $pos; ?>_field"><?php _e('Field Input','ultimate-captcha'); ?>
					</label> <select name="uultra_<?php echo $pos; ?>_field"
						id="uultra_<?php echo $pos; ?>_field">
						<?php
						
						 foreach($this->allowed_inputs as $input=>$label) { ?>
						<option value="<?php echo $input; ?>"
						<?php selected($input, $field); ?>>
							<?php echo $label; ?>
						</option>
						<?php } ?>
					</select> <i class="uultra-icon-question-sign uultra-tooltip2"
						title="<?php _e('When user edit profile, this field can be an input (text, textarea, image upload, etc.)','ultimate-captcha'); ?>"></i>
				</p>

				
				<p>
					<label for="uultra_<?php echo $pos; ?>_meta_custom"><?php _e('Custom Meta Field','ultimate-captcha'); ?>
					</label> <input name="uultra_<?php echo $pos; ?>C"
						type="text" id="uultra_<?php echo $pos; ?>_meta_custom"
						value="<?php if (!isset($all_meta_for_user[$meta])) echo $meta; ?>" />
					<i class="uultra-icon-question-sign uultra-tooltip2"
						title="<?php _e('Enter a custom meta key for this profile field if do not want to use a predefined meta field above. It is recommended to only use alphanumeric characters and underscores, for example my_custom_meta is a proper meta key.','ultimate-captcha'); ?>"></i>
				</p> <?php } ?>

				
                
                
                <p>
					<label for="uultra_<?php echo $pos; ?>_name"><?php _e('Label / Name','ultimate-captcha'); ?>
					</label> <input name="uultra_<?php echo $pos; ?>_name" type="text"
						id="uultra_<?php echo $pos; ?>_name" value="<?php echo $name; ?>" />
					<i class="uultra-icon-question-sign uultra-tooltip2"
						title="<?php _e('Enter the label / name of this field as you want it to appear in front-end (Profile edit/view)','ultimate-captcha'); ?>"></i>
				</p>
                
                

			<?php if ($type != 'separator' ) { ?>

				
				<p>
					<label for="uultra_<?php echo $pos; ?>_tooltip"><?php _e('Tooltip Text','ultimate-captcha'); ?>
					</label> <input name="uultra_<?php echo $pos; ?>_tooltip" type="text"
						id="uultra_<?php echo $pos; ?>_tooltip"
						value="<?php echo $tooltip; ?>" /> <i
						class="uultra-icon-question-sign uultra-tooltip2"
						title="<?php _e('A tooltip text can be useful for social buttons on profile header.','ultimate-captcha'); ?>"></i>
				</p> 
                
               <p>
               
               <label for="uultra_<?php echo $pos; ?>_help_text"><?php _e('Help Text','ultimate-captcha'); ?>
                </label><br />
                    <textarea class="uultra-help-text" id="uultra_<?php echo $pos; ?>_help_text" name="uultra_<?php echo $pos; ?>_help_text" title="<?php _e('A help text can be useful for provide information about the field.','ultimate-captcha'); ?>" ><?php echo $help_text; ?></textarea>
                    <i class="uultra-icon-question-sign uultra-tooltip2"
                                title="<?php _e('Show this help text under the profile field.','ultimate-captcha'); ?>"></i>
                              
               </p> 
				
				
				
                
               				
				<?php 
				if(!isset($can_edit))
				    $can_edit = '1';
				?>
				<p>
					<label for="uultra_<?php echo $pos; ?>_can_edit"><?php _e('User can edit','ultimate-captcha'); ?>
					</label> <select name="uultra_<?php echo $pos; ?>_can_edit"
						id="uultra_<?php echo $pos; ?>_can_edit">
						<option value="1" <?php selected(1, $can_edit); ?>>
							<?php _e('Yes','ultimate-captcha'); ?>
						</option>
						<option value="0" <?php selected(0, $can_edit); ?>>
							<?php _e('No','ultimate-captcha'); ?>
						</option>
					</select> <i class="uultra-icon-question-sign uultra-tooltip2"
						title="<?php _e('Users can edit this profile field or not.','ultimate-captcha'); ?>"></i>
				</p> 
				
				<?php if (!isset($array['allow_html'])) { 
				    $allow_html = 0;
				} ?>
								
				
				
				<?php 
				if(!isset($required))
				    $required = '0';
				?>
				<p>
					<label for="uultra_<?php echo $pos; ?>_required"><?php _e('This field is Required','ultimate-captcha'); ?>
					</label> <select name="uultra_<?php echo $pos; ?>_required"
						id="uultra_<?php echo $pos; ?>_required">
						<option value="0" <?php selected(0, $required); ?>>
							<?php _e('No','ultimate-captcha'); ?>
						</option>
						<option value="1" <?php selected(1, $required); ?>>
							<?php _e('Yes','ultimate-captcha'); ?>
						</option>
					</select> <i class="uultra-icon-question-sign uultra-tooltip2"
						title="<?php _e('Selecting yes will force user to provide a value for this field at registration and edit profile. Registration or profile edits will not be accepted if this field is left empty.','ultimate-captcha'); ?>"></i>
				</p> <?php } ?> <?php

				/* Show Registration field only when below condition fullfill
				1) Field is not private
				2) meta is not for email field
				3) field is not fileupload */
				if(!isset($private))
				    $private = 0;

				if(!isset($meta))
				    $meta = '';

				if(!isset($field))
				    $field = '';


				//if($type == 'separator' ||  ($private != 1 && $meta != 'user_email' ))
				if($type == 'separator' ||  ($private != 1 && $meta != 'user_email' ))
				{
				    if(!isset($show_in_register))
				        $show_in_register= 0;
						
					 if(!isset($show_in_widget))
				        $show_in_widget= 0;
				    ?>
				<p>
					<label for="uultra_<?php echo $pos; ?>_show_in_register"><?php _e('Show on Registration Form','ultimate-captcha'); ?>
					</label> <select name="uultra_<?php echo $pos; ?>_show_in_register"
						id="uultra_<?php echo $pos; ?>_show_in_register">
						<option value="0" <?php selected(0, $show_in_register); ?>>
							<?php _e('No','ultimate-captcha'); ?>
						</option>
						<option value="1" <?php selected(1, $show_in_register); ?>>
							<?php _e('Yes','ultimate-captcha'); ?>
						</option>
					</select> <i class="uultra-icon-question-sign uultra-tooltip2"
						title="<?php _e('Show this profile field on the registration form','ultimate-captcha'); ?>"></i>
				</p>    
               
                
                 <?php } ?>
                 
			<?php if ($type != 'seperator' || $type != 'separator') { ?>

		  <?php if (in_array($field, array('select','radio','checkbox')))
				 {
				    $show_choices = null;
				} else { $show_choices = 'uultra-hide';
				
				
				} ?>

				<p class="uultra-choices <?php echo $show_choices; ?>">
					<label for="uultra_<?php echo $pos; ?>_choices"
						style="display: block"><?php _e('Available Choices','ultimate-captcha'); ?> </label>
					<textarea name="uultra_<?php echo $pos; ?>_choices" type="text" id="uultra_<?php echo $pos; ?>_choices" class="large-text"><?php if (isset($array['choices'])) echo trim($choices); ?></textarea>
                    
                    <?php
                    
					if($this->uultra_if_windows_server())
					{
						echo ' <p>'.__('<strong>PLEASE NOTE: </strong>Enter values separated by commas, example: 1,2,3. The choices will be available for front end user to choose from.').' </p>';					
					}else{
						
						echo ' <p>'.__('<strong>PLEASE NOTE:</strong> Enter one choice per line please. The choices will be available for front end user to choose from.').' </p>';
					
					
					}
					
					?>
                    <p>
                    
                    
                    </p>
					<i class="uultra-icon-question-sign uultra-tooltip2"
						title="<?php _e('Enter one choice per line please. The choices will be available for front end user to choose from.','ultimate-captcha'); ?>"></i>
				</p> <?php //if (!isset($array['predefined_loop'])) $predefined_loop = 0;
				
				if (!isset($predefined_options)) $predefined_options = 0;
				
				 ?>

				<p class="uultra_choices <?php echo $show_choices; ?>">
					<label for="uultra_<?php echo $pos; ?>_predefined_options" style="display: block"><?php _e('Enable Predefined Choices','ultimate-captcha'); ?>
					</label> 
                    <select name="uultra_<?php echo $pos; ?>_predefined_options"id="uultra_<?php echo $pos; ?>_predefined_options">
						<option value="0" <?php selected(0, $predefined_options); ?>>
							<?php _e('None','ultimate-captcha'); ?>
						</option>
						<option value="countries" <?php selected('countries', $predefined_options); ?>>
							<?php _e('List of Countries','ultimate-captcha'); ?>
						</option>
                        
                        <option value="age" <?php selected('age', $predefined_options); ?>>
							<?php _e('Age','ultimate-captcha'); ?>
						</option>
					</select> <i class="uultra-icon-question-sign uultra-tooltip2"
						title="<?php _e('You can enable a predefined filter for choices. e.g. List of countries It enables country selection in profiles and saves you time to do it on your own.','ultimate-captcha'); ?>"></i>
				</p>

				
				<div class="clear"></div> 
				
				<?php } ?>


  <div class="ultimatecaptcha-ultra-success ultimatecaptcha-notification" id="bup-sucess-fields-<?php echo $pos; ?>"><?php _e('Success ','ultimate-captcha'); ?></div>
				<p>
                
               
                 
				<input type="button" name="submit"	value="<?php _e('Update','ultimate-captcha'); ?>"						class="button button-primary ultimatecaptcha-btn-submit-field"  data-edition="<?php echo $pos; ?>" /> 
                   <input type="button" value="<?php _e('Cancel','ultimate-captcha'); ?>"
						class="button button-secondary ultimatecaptcha-btn-close-edition-field" data-edition="<?php echo $pos; ?>" />
				</p>
                
      <?php
	  
	  die();
		
	}
	
	public function uultra_if_windows_server()
	{
		$os = PHP_OS;
		$os = strtolower($os);			
		$pos = strpos($os, "win");	
		
		if ($pos === false) {
			
			//echo "NO, It's not windows";
			return false;
		} else {
			//echo "YES, It's windows";
			return true;
		}			
	
	}
	
	
	/**
	 * This has been added to avoid the window server issues
	 */
	public function uultra_one_line_checkbox_on_window_fix($choices)
	{		
		
		if($this->uultra_if_windows_server()) //is window
		{
			$loop = array();		
			$loop = explode(",", $choices);
		
		}else{ //not window
		
			$loop = array();		
			$loop = explode(PHP_EOL, $choices);	
			
		}	
		
		
		return $loop;
	
	}
	
	/*Loads all field list */	
	function reload_custom_fields_set ()	
	{
		
		global $ultimatecaptcha;
		
		
		if(isset($_POST["custom_form"]) && $_POST["custom_form"]!=''){
		
			$custom_form = $_POST["custom_form"];
			$custom_form = 'ultimatecaptcha_profile_fields_'.$custom_form;
			
		}else{
			
			$custom_form = 'ultimatecaptcha_profile_fields';		
		}
		
		$fields = get_option($custom_form);
		
		
		if(!is_array($fields)){$fields = array();}
		ksort($fields);		
		
		$i = 0;
		foreach($fields as $pos => $array) 
		{
		    extract($array); $i++;

		    if(!isset($required))
		        $required = 0;

		    if(!isset($fonticon))
		        $fonticon = '';
				
				
			if ($type == 'seperator' || $type == 'separator') {
			   
				$class = "separator";
				$class_title = "";
			} else {
			  
				$class = "profile-field";
				$class_title = "profile-field";
			}
		    ?>
            
          <li class="ultimatecaptcha-profile-fields-row <?php echo $class_title?>" id="<?php echo $pos; ?>">
            
            
            <div class="heading_title  <?php echo $class?>">
            
            <h3>
            <?php
			
			if (isset($array['name']) && $array['name'])
			{
			    echo  stripslashes($array['name']);
			}
			?>
            
            <?php
			if ($type == 'separator') {
				
			    echo __(' - Separator','ultimate-captcha');
				
			} else {
				
			    echo __(' - Profile Field','ultimate-captcha');
				
			}
			?>
            
            </h3>
            
            
              <div class="options-bar">
             
                 <p>                
                    <input type="submit" name="submit" value="<?php _e('Edit','ultimate-captcha'); ?>"						class="button ultimatecaptcha-btn-edit-field button-primary" data-edition="<?php echo $pos; ?>" /> <input type="button" value="<?php _e('Delete','ultimate-captcha'); ?>"	data-field="<?php echo $pos; ?>" class="button button-secondary ultimatecaptcha-delete-profile-field-btn" />
                    </p>
            
             </div>
            
            
          

            </div>
            
             
             <div class="ultimatecaptcha-ultra-success ultimatecaptcha-notification" id="ultimatecaptcha-sucess-delete-fields-<?php echo $pos; ?>"><?php _e('Success! This field has been deleted ','ultimate-captcha'); ?></div>
            
           
        
          <!-- edit field -->
          
          <div class="user-ultra-sect-second uultra-fields-edition user-ultra-rounded"  id="ultimatecaptcha-edit-fields-bock-<?php echo $pos; ?>">
        
          </div>
          
          
          <!-- edit field end -->

       </li>







	<?php
	
	}
		
		die();
		
	
	}
	
	
	
	function admin_head(){
		$screen = get_current_screen();
		$slug = $this->slug;
		
	}
	
	function reset_email_template() 	
	{
		global  $ultimatecaptcha;
		
		$template = $_POST['email_template'];
		$new_template = $this->get_email_template($template);
		$this->ultimatecaptcha_set_option($template, $new_template);
		echo "reset";
		
		die();
		
		
	}
	
	function startactions_native_display() 	
	{
		global  $ultimatecaptcha;		
		
		if($this->get_option('recaptcha_display_comments_native')==1){		
			add_action( 'comment_form_after_fields',array( &$this, 'get_recaptcha_on_natives' ) );		
		}
		
		if($this->get_option('recaptcha_display_loginform_native')==1){		
			add_action( 'login_form',array( &$this, 'get_recaptcha_on_natives' ) );		
		}
		
		if($this->get_option('recaptcha_display_registration_native')==1){		
			add_action( 'register_form',array( &$this, 'get_recaptcha_on_natives' ) );		
		}
		
		if($this->get_option('recaptcha_display_forgot_password_native')==1){		
			add_action( 'lostpassword_form',array( &$this, 'get_recaptcha_on_natives' ) );
		}
		
		//woocommerce		
		add_action( 'woocommerce_login_form',array( &$this, 'get_recaptcha_on_natives' ) );
		add_action( 'woocommerce_register_form',array( &$this, 'get_recaptcha_on_natives' ) );
		add_action( 'woocommerce_lostpassword_form',array( &$this, 'get_recaptcha_on_natives' ) );		
		
		add_action( 'init',array( &$this, 'ultimatecaptcha_check' ) );

		
	}
	
	function ultimatecaptcha_verify($input) {
		
		global $ucaptcha_custompage;
		
		if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["g-recaptcha-response"]) ) {
			
			$recaptcha_response = sanitize_text_field($_POST["g-recaptcha-response"]);						
			$recaptcha_secret= $this->get_option('recaptcha_secret_key');
			
			$response = wp_remote_get("https://www.google.com/recaptcha/api/siteverify?secret=".$recaptcha_secret."&response=".$recaptcha_response);
			$response = json_decode($response["body"], true);
			
			if ($response["success"] == true) {
				return $input;
			} else {
				wp_die("<p><strong>".__("ERROR:", "ultimate-captcha")."</strong> ".__("Google reCAPTCHA verification failed.", "ultimate-captcha")."</p>\n\n<p><a href=".wp_get_referer().">&laquo; ".__("Back", "ultimate-captcha")."</a>");
				return null;
			}
			
		} else {
			wp_die("<p><strong>".__("ERROR:", "ultimate-captcha")."</strong> ".__("Google reCAPTCHA verification failed.", "ultimate-captcha")." ".__("Do you have JavaScript enabled?", "ultimate-captcha")."</p>\n\n<p><a href=".wp_get_referer().">&laquo; ".__("Back", "ultimate-captcha")."</a>");
			return null;
		}
	}
	
	function ultimatecaptcha_check() {
		
		global $ucaptcha_custompage,  $woocommerce;
		
		
		$RECAPTCHA_SITE_KEY = $this->get_option('recaptcha_site_key');
		$RECAPTCHA_SECRET_KEY= $this->get_option('recaptcha_secret_key');   
		
		if ($RECAPTCHA_SITE_KEY != "" && $RECAPTCHA_SECRET_KEY != "" && !isset($_POST["ucaptcha-custom-forms"]) ) {
			
			
			if (!is_user_logged_in()) {		
		
				add_action( 'preprocess_comment',array( &$this, 'ultimatecaptcha_verify' ) );
			}
			
			
			add_action( 'wp_authenticate_user',array( &$this, 'ultimatecaptcha_verify' ) );
			add_action( 'registration_errors',array( &$this, 'ultimatecaptcha_verify' ) );
			add_action( 'lostpassword_post',array( &$this, 'ultimatecaptcha_verify' ) );
			add_action( 'resetpass_post',array( &$this, 'ultimatecaptcha_verify' ) );
			
			add_action( 'woocommerce_register_post',array( &$this, 'ultimatecaptcha_verify' ) );
			
		}
	}
	
	
	
	
	function get_recaptcha_on_natives() 	
	{
		global  $ultimatecaptcha;
		
		$display = '';
		$display .= '<div class="ucaptcha-profile-field">';			
		$display .= $ultimatecaptcha->recaptcha_field(); 				
		$display .= '</div>'; 	
		
		echo $display;	
		
	}
	
	
	
	
	/*Post value*/
	function get_post_value($meta) 
	{			
				
		if (isset($_POST[$meta]) ) {
				return sanitize_text_field($_POST[$meta]);
			}
			
			
	}
	
	function load_classes(){		
		
		$this->common = new UltimateCaptchaCommon();
		$this->profile = new UltimateCaptchaProfile();	
		$this->messaging = new UltimateCaptchaMessaging();		
	}
	
	
	public  function get_date_picker_format( )
    {
		global  $ultimatecaptcha;
		
		$date_format = $ultimatecaptcha->get_option('ultimatecaptcha_date_picker_format');
		
		if($date_format=='d/m/Y'){			
			
			$date_format = 'dd/mm/yy';
			
		}elseif($date_format=='m/d/Y'){
			
			$date_format = 'mm/dd/yy';			
			
		}else{
			
			$date_format = 'mm/dd/yy';
			
		}
        return $date_format;
		
	
	}
	
	public  function get_date_picker_date( )
    {
		global  $ultimatecaptcha;
		
		$date_format = $ultimatecaptcha->get_option('ultimatecaptcha_date_picker_format');
		
		if($date_format==''){			
			
			$date_format = 'm/d/Y';					
		}
        return $date_format;
		
	
	}
	

	
	function ini_plugin(){
		
		$is_admin = is_admin() && ! defined( 'DOING_AJAX' );
		
		/* Add hooks */
		if ( ! $is_admin  ) {
			
			$this->create_actions();
		}
		
	}
		
	
	function create_actions(){		
		
		$is_user_logged_in = is_user_logged_in();
		
		if ( $this->get_option('recaptcha_display_loginform') == '1' ) {			
						
				add_action('login_form', array(&$this, 'display_captcha_wp_login_form_display'), 11);	
				
				if ( ! $gglcptch_ip_in_whitelist ) {					
					
					add_action('authenticate', array(&$this, 'login_check'), 22,1);	
				}
		}
		
	}
	
	/* Check google captcha in login form */
	function login_check( $user ) {

		if ( is_wp_error( $user ) )
			return $user;

		$ultimatecaptcha_check = validate_recaptcha_field();

		/* reCAPTCHA is not configured */
		if ( ! $ultimatecaptcha_check['response'] && $ultimatecaptcha_check['reason'] == 'ERROR_NO_KEYS' ) {
			return $user;
		}

		//$la_result = gglcptch_handle_by_limit_attempts( $gglcptch_check['response'], 'login_form' );
		
		$la_result = true;

		if ( true !== $la_result ) {
			$user = new WP_Error();

			if ( is_wp_error( $la_result ) ) {
				$user = $la_result;
			} elseif ( is_string( $la_result ) ) {
				$user->add( 'gglcptch_la_error', $la_result );
			}

			if ( $ultimatecaptcha_check['reason'] == 'VERIFICATION_FAILED' ) {
				wp_clear_auth_cookie();
			}

			if ( ! $ultimatecaptcha_check['response'] ) {
				$error_message = sprintf( '<strong>%s</strong>:&nbsp;%s', __( 'Error', 'google-captcha' ), gglcptch_get_message() );
				$user->add( 'gglcptch_error', $error_message );
			}
		}

		return $user;
	}

	
	function display_captcha_wp_login_form_display(){
		
		$is_admin = is_admin() && ! defined( 'DOING_AJAX' );
		
		/* Add hooks */
		if ( ! $is_admin  ) {
			create_actions();
		}
		
	}
	
	
	
	
	
	function add_styles()
	{
		
		 global $wp_locale, $ultimatecaptcha , $pagenow;
		 
		 if('customize.php' != $pagenow )
        {
		 
			wp_register_style('ultimatecaptcha_admin', ultimatecaptcha_url.'admin/css/admin.css');
			wp_enqueue_style('ultimatecaptcha_admin');
			
			wp_register_style('ultimatecaptcha_datepicker', ultimatecaptcha_url.'admin/css/datepicker.css');
			wp_enqueue_style('ultimatecaptcha_datepicker');
			
							
				
			//color picker		
			 wp_enqueue_style( 'ultimatecaptcha-color-picker' );	
				 
			 wp_register_script( 'ultimatecaptcha_color_picker', ultimatecaptcha_url.'admin/scripts/color-picker-js.js', array( 
				'ultimatecaptcha-color-picker'
			) );
			wp_enqueue_script( 'ultimatecaptcha_color_picker' );
			
			
			wp_register_script( 'ultimatecaptcha_admin',ultimatecaptcha_url.'admin/scripts/admin.js', array( 
				'jquery','jquery-ui-core','jquery-ui-draggable','jquery-ui-droppable',	'jquery-ui-sortable', 'jquery-ui-tabs', 'jquery-ui-autocomplete', 'jquery-ui-widget', 'jquery-ui-position'	), null );
			wp_enqueue_script( 'ultimatecaptcha_admin' );
			
			
			/* Font Awesome */
			wp_register_style( 'ultimatecaptcha_font_awesome', ultimatecaptcha_url.'css/css/font-awesome.min.css');
			wp_enqueue_style('ultimatecaptcha_font_awesome');
			
			

		}
		 
		
		
				
		
	}
	
	public function update_default_option_ini () 
	{
		$this->options = get_option('ultimatecaptcha_options');		
		$this->bup_set_default_options();
		
		if (!get_option('ultimatecaptcha_options')) 
		{
			
			update_option('ultimatecaptcha_options', $this->ultimatecaptcha_default_options );
		}
		
		if (!get_option('ultimatecaptcha_pro_active')) 
		{
			
			update_option('ultimatecaptcha_pro_active', true);
		}	
		
		
	}
	
	
	/* default options */
	function bup_set_default_options()
	{
	
		$this->ultimatecaptcha_default_options = array(									
						
						'messaging_send_from_name' => get_option('blogname'),
						
						'bup_noti_admin' => 'yes',
						'bup_noti_staff' => 'yes',
						'bup_noti_client' => 'yes',
						'messaging_send_from_email' => get_option( 'admin_email' ),
						'company_name' => get_option('blogname'),	
						
						'allowed_extensions' => 'jpg,png,gif,jpeg,pdf,doc,docx,xls',
						
															
						'email_password_change_staff' => $this->get_email_template('email_password_change_staff'),
						'email_password_change_staff_subject' => __('Password Changed','ultimate-captcha'),
						
						'email_reset_link_message_body' => $this->get_email_template('email_reset_link_message_body'),
						'email_reset_link_message_subject' => __('Password Reset','ultimate-captcha'),
						
												
						'email_registration_body' => $this->get_email_template('email_registration_body'),
						'email_registration_subject' => __('Your Account Details','ultimate-captcha'),
								
						
				);
		
	}
	
	public function set_default_email_messages()
	{
		$line_break = "\r\n";	
						
		//Staff Password Reset	
		$email_body =  '{{ucaptcha_staff_name}},'.$line_break.$line_break;
		$email_body .= __("Please use the following link to reset your password.","ultimate-captcha") . $line_break.$line_break;			
		$email_body .= "{{ucaptcha_reset_link}}".$line_break.$line_break;
		$email_body .= __('If you did not request a new password delete this email.','ultimate-captcha'). $line_break.$line_break;	
			
		$email_body .= __('Best Regards!','ultimate-captcha'). $line_break;
		$email_body .= '{{ucaptcha_company_name}}'. $line_break;
		$email_body .= '{{ucaptcha_company_phone}}'. $line_break;
		$email_body .= '{{ucaptcha_company_url}}'. $line_break. $line_break;		
	    $this->notifications_email['email_reset_link_message_body'] = $email_body;
		
				
		//User Registration Email
		$email_body =  __('Hello ','ultimate-captcha') .'{{ucaptcha_client_name}},'.$line_break.$line_break;
		$email_body .= __("Thank you for your registration. Your login details for your account are as follows:","ultimate-captcha") . $line_break.$line_break;
		$email_body .= __('Username: {{ucaptcha_user_name}}','ultimate-captcha') . $line_break;
		$email_body .= __('Password: {{ucaptcha_user_password}}','ultimate-captcha') . $line_break;
		$email_body .= __("Please use the following link to login to your account.","ultimate-captcha") . $line_break.$line_break;			
		$email_body .= "{{ucaptcha_login_link}}".$line_break.$line_break;
			
		$email_body .= __('Best Regards!','ultimate-captcha'). $line_break;
		$email_body .= '{{ucaptcha_company_name}}'. $line_break;
		$email_body .= '{{ucaptcha_company_phone}}'. $line_break;
		$email_body .= '{{ucaptcha_company_url}}'. $line_break. $line_break;
		
	    $this->notifications_email['email_registration_body'] = $email_body;	
		
	
	}
	
	public function get_email_template($key)
	{
		return $this->notifications_email[$key];
	
	}
	
	
	
	function admin_init() 
	{
		
		$this->tabs = array(
		    'main' => __('Dashboard','ultimate-captcha'),			
			'settings' => __('Settings','ultimate-captcha'),
			'fields' => __('Custom Fields','ultimate-captcha'),
			'mail' => __('Email Templates','ultimate-captcha'),				
			'help' => __('Doc','ultimate-captcha'),
			'pro' => __('GO PRO!','ultimate-captcha'),
		);
		
		
		$this->default_tab = 'main';			
		
		$this->default_tab_membership = 'main';
		
		
	}
	
	function add_menu() 
	{
		global $ultimatecaptcha, $ultimatecaptcha_activation ;
		
			
		$menu_label = __('Ultimate Captcha','ultimate-captcha');
		
		add_menu_page( __('Ultimate Captcha','ultimate-captcha'), $menu_label, 'manage_options', $this->slug, array(&$this, 'admin_page'), ultimatecaptcha_url .'admin/images/small_logo_16x16.png', '159.140');
		
		//
		
		
		if(!isset($ultimatecaptcha_activation))
		{
		
			add_submenu_page( $this->slug, __('More Functionality!','ultimate-captcha'), __('More Functionality!','ultimate-captcha'), 'manage_options', 'ultimatecaptcha&tab=pro', array(&$this, 'admin_page') );
		
		}
		
		if(isset($ultimatecaptcha_activation))
		{
			add_submenu_page( $this->slug, __('Licensing','ultimate-captcha'), __('Licensing','ultimate-captcha'), 'manage_options', 'ultimatecaptcha&tab=licence', array(&$this, 'admin_page') );
		
		
		}
		
		do_action('ultimatecaptcha_admin_menu_hook');
		
			
	}
	
	

	function admin_tabs( $current = null ) {
		
		global $ultimatecaptchacomplement, $ultimatecaptcha_custom_fields;
		
			$tabs = $this->tabs;
			$links = array();
			if ( isset ( $_GET['tab'] ) ) {
				$current = $_GET['tab'];
			} else {
				$current = $this->default_tab;
			}
			foreach( $tabs as $tab => $name ) :
			
			
			    if($tab=="pro"){
					
					$custom_badge = 'ultimatecaptcha-pro-tab-bubble ';
					
				}
				
				if($tab=="fields" && !isset($ultimatecaptcha_custom_fields)){continue;}
				if($tab=="mail" && !isset($ultimatecaptcha_custom_fields)){continue;}
				
				if(isset($ultimatecaptchacomplement) && $tab=="pro"){continue;}
				
				
				if ( $tab == $current ) :
					$links[] = "<a class='nav-tab nav-tab-active ".$custom_badge."' href='?page=".$this->slug."&tab=$tab'><span class='ultimatecaptcha-adm-tab-legend'>".$name."</span></a>";
				else :
					$links[] = "<a class='nav-tab ".$custom_badge."' href='?page=".$this->slug."&tab=$tab'><span class='ultimatecaptcha-adm-tab-legend'>".$name."</span></a>";
				endif;
			endforeach;
			foreach ( $links as $link )
				echo $link;
	}

	
	
	function do_action(){
		global $userultra;
				
		
	}
		
	
	/* set a global option */
	function ultimatecaptcha_set_option($option, $newvalue)
	{
		$settings = get_option('ultimatecaptcha_options');		
		$settings[$option] = $newvalue;
		update_option('ultimatecaptcha_options', $settings);
	}
	
	
	public function add_front_end_styles()
	{
		global $wp_locale;
		
		wp_enqueue_script('jquery-ui-dialog');
		wp_enqueue_style("wp-jquery-ui-dialog");
		wp_enqueue_script('jquery-ui-datepicker');	
		
		
		/*uploader*/					
		wp_enqueue_script('jquery-ui');			
		wp_enqueue_script('plupload-all');	
		wp_enqueue_script('jquery-ui-progressbar');				

		/* Font Awesome */
		wp_register_style('ultimatecaptcha_font_awesome', ultimatecaptcha_url.'css/css/font-awesome.min.css');
		wp_enqueue_style('ultimatecaptcha_font_awesome');
		
		//----MAIN STYLES		
				
		/* Custom style */		
		wp_register_style('ultimatecaptcha_style', ultimatecaptcha_url.'templates/css/styles.css');
		wp_enqueue_style('ultimatecaptcha_style');			
				
		
		/*Users JS*/		
		wp_register_script( 'ultimatecaptcha-front_js', ultimatecaptcha_url.'js/ucaptcha-front.js',array('jquery'),  null);
		wp_enqueue_script('ultimatecaptcha-front_js');
		
		wp_register_script('ultimatecaptcha-form-validate-lang', ultimatecaptcha_url.'js/languages/jquery.validationEngine-en.js',array('jquery'));
		wp_enqueue_script('ultimatecaptcha-form-validate-lang');
					
		wp_register_script( 'ultimatecaptcha-form-validate', ultimatecaptcha_url.'js/jquery.validationEngine.js',array('jquery'));
		wp_enqueue_script('ultimatecaptcha-form-validate');
		
		
		$message_wait_submit ='<img src="'.ultimatecaptcha_url.'admin/images/loaderB16.gif" width="16" height="16" /></span>&nbsp; '.__("Please wait ...","ultimate-captcha").'';		
		
				
		
//localize our js
		$date_picker_array = array(
					'closeText'         => __( 'Done', "ultimate-captcha" ),
					'currentText'       => __( 'Today', "ultimate-captcha" ),
					'prevText' =>  __('Prev',"ultimate-captcha"),
		            'nextText' => __('Next',"ultimate-captcha"),				
					'monthNames'        => array_values( $wp_locale->month ),
					'monthNamesShort'   => array_values( $wp_locale->month_abbrev ),
					'monthStatus'       => __( 'Show a different month', "ultimate-captcha" ),
					'dayNames'          => array_values( $wp_locale->weekday ),
					'dayNamesShort'     => array_values( $wp_locale->weekday_abbrev ),
					'dayNamesMin'       => array_values( $wp_locale->weekday_initial ),					
					// get the start of week from WP general setting
					'firstDay'          => get_option( 'start_of_week' ),
					// is Right to left language? default is false
					'isRTL'             => $wp_locale->is_rtl(),
				);
				
		
		
	}
	
	
	function add_front_end_scripts() {
		
		
		wp_register_script("ultimatecaptcha_recaptcha_js", "https://www.google.com/recaptcha/api.js?hl=".get_locale()."");
		wp_enqueue_script("ultimatecaptcha_recaptcha_js");
		
	}
	
	function add_front_recaptcha() {	
		
		
		wp_register_style('ultimate_captchastyle', ultimatecaptcha_url.'templates/css/captchastyles.css');
		wp_enqueue_style('ultimate_captchastyle');	
	
		wp_register_script("ultimatecaptcha_recaptcha_js", "https://www.google.com/recaptcha/api.js?hl=".get_locale()."");
		wp_enqueue_script("ultimatecaptcha_recaptcha_js");
		
		
	}
	
	function get_login_captcha_login_form() {
		
		
		
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
	
	function recaptcha_field() {
		
		global  $ultimatecaptcha;
		
		$RECAPTCHA_SITE_KEY = $this->get_option('recaptcha_site_key');
		$RECAPTCHA_SECRET_KEY= $this->get_option('recaptcha_secret_key');
    
		$html = '
		<fieldset>
			<label>'.__( "Are you human?", "ultimate-captcha" ).'</label>
			
				<div class="g-recaptcha" data-sitekey="'.$RECAPTCHA_SITE_KEY.'"></div>
			
		</fieldset>';
		
		return $html;
	
	}
	
	function get_option($option) 
	{
		$settings = get_option('ultimatecaptcha_options');
		if (isset($settings[$option])) 
		{
			if(is_array($settings[$option]))
			{
				return $settings[$option];
			
			}else{
				
				return stripslashes($settings[$option]);
			}
			
		}else{
			
		    return '';
		}
		    
	}
	
	function validate_recaptcha_field($grecaptcharesponse) {		
		
		global  $ultimatecaptcha;
		
		$RECAPTCHA_SITE_KEY = $this->get_option('recaptcha_site_key');
		$RECAPTCHA_SECRET_KEY= $this->get_option('recaptcha_secret_key');    
		
		$response = wp_remote_get( add_query_arg( array(
			'secret'   => $RECAPTCHA_SECRET_KEY,
			'response' => $grecaptcharesponse,
			'remoteip' => isset( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR']
		), 'https://www.google.com/recaptcha/api/siteverify' ) );
		
		if ( is_wp_error( $response ) || empty( $response['body'] ) || ! ( $json = json_decode( $response['body'] ) ) || ! $json->success ) {
						
			$result = false;
			
		}else{
			
			$result = true;
			
		}
		
		return $result;
	}
	
	function initial_setup() {
		
		global $ultimatecaptcha, $wpdb, $ultimatecaptchacomplement ;
		
		$inisetup   = get_option('ultimatecaptcha_ini_setup');
		
		if (!$inisetup) 
		{				
					
			update_option('ultimatecaptcha_ini_setup', true);
		}
		
		
	}
	
	public function do_valid_checks()
	{
		
		global $ultimatecaptcha_activation ;
		
		$va = get_option('ultimatecaptcha_c_key');
		
		if(isset($ultimatecaptcha_activation))		
		{		
			if($va=="")
			{
				//
				$this->valid_c = "no";
			
			}
		
		}	
	
	}
	
	function include_tab_content() {
		
		global $ultimatecaptcha, $wpdb, $ultimatecaptchacomplement ;
		
		$screen = get_current_screen();
		
		if( strstr($screen->id, $this->slug ) ) 
		{
			if ( isset ( $_GET['tab'] ) ) 
			{
				$tab = $_GET['tab'];
				
			} else {
				
				$tab = $this->default_tab;
			}
			
			//
			
			
			if (! get_option('ultimatecaptcha_ini_setup')) 
			{
				//this is the first time
				$this->initial_setup();
				
				$tab = "welcome";				
				require_once (ultimatecaptcha_path.'admin/tabs/'.$tab.'.php');				
				
				
			}else{
			
				if($this->valid_c=="" )
				{
					require_once (ultimatecaptcha_path.'admin/tabs/'.$tab.'.php');			
				
				}else{ //no validated
					
					$tab = "licence";				
					require_once (ultimatecaptcha_path.'admin/tabs/'.$tab.'.php');
					
				}
			
			}
			
			
		}
	}
	
		// update settings
    function update_settings() 
	{
		foreach($_POST as $key => $value) 
		{
            if ($key != 'submit')
			{
				if (strpos($key, 'html_') !== false)
                {
                      //$this->userultra_default_options[$key] = stripslashes($value);
                }else{
					
					 // $this->userultra_default_options[$key] = esc_attr($value);
                 }
					
								
					$this->ultimatecaptcha_set_option($key, $value) ;
					//special setting for page
					if($key=="ultimatecaptcha_my_account_page")
					{						
						
						 update_option('ultimatecaptcha_my_account_page',$value);				 
						 
						 
					}  

            }
        }
		
		//get checks for each tab
		
		
		 if ( isset ( $_GET['tab'] ) )
		 {
			 
			    $current = $_GET['tab'];
				
          } else {
                $current = $_GET['page'];
          }	 
            
		$special_with_check = $this->get_special_checks($current);
         
        foreach($special_with_check as $key)
        {
           
            
                if(!isset($_POST[$key]))
				{			
                    $value= '0';
					
				 } else {
					 
					  $value= $_POST[$key];
				}	 	
         
			
			$this->ultimatecaptcha_set_option($key, $value) ;  
			
			
            
        }
         
      $this->options = get_option('ultimatecaptcha_options');

        echo '<div class="updated"><p><strong>'.__('Settings saved.','ultimate-captcha').'</strong></p></div>';
    }
	
	public function get_special_checks($tab) 
	{
		$special_with_check = array();
		
		if($tab=="settings")
		{				
		
		 $special_with_check = array('social_media_fb_active',  'social_media_google', 'twitter_connect',  'mailchimp_active', 'mailchimp_auto_checked',  'aweber_active', 'aweber_auto_checked',  'password_1_letter_1_number' , 'password_one_uppercase' , 'password_one_lowercase', 'recaptcha_display_registration', 'recaptcha_display_loginform' ,'recaptcha_display_comments','recaptcha_display_forgot_password','recaptcha_display_registration_native', 'recaptcha_display_loginform_native' ,'recaptcha_display_comments_native','recaptcha_display_forgot_password_native');
		 
		
		 
		}
		
		if($tab=="ucaptcha-custompages")
		{				
		
		 $special_with_check = array('redirect_backend_profile','redirect_backend_registration', 'redirect_registration_when_social','redirect_backend_login');		
		 
		}
		
		if($tab=="ucaptcha-passwordstrength")
		{				
		
		 $special_with_check = array('registration_password_ask','registration_password_ask_confirmation', 'registration_password_lenght','registration_password_1_letter_1_number' ,'registration_password_one_uppercase','registration_password_one_lowercase');		
		 
		}
		
		
		
	
	return  $special_with_check ;
	
	}	
	
	
	function admin_page() 
	{
		global $ultimatecaptchaembers;

		
		
		if (isset($_POST['ultimatecaptcha_update_settings']) ) {
            $this->update_settings();
        }
		
				
		
		
			
	?>
	
		<div class="wrap <?php echo $this->slug; ?>-admin"> 
        
           
           
           <?php if (get_option('ultimatecaptcha_ini_setup')) 
				{?>
            
                <h2 class="nav-tab-wrapper"><?php $this->admin_tabs(); ?>               
                
                 
                
                </h2>  
                
            <?php } ?>       
            

			<div class="<?php echo $this->slug; ?>-admin-contain">    
            
               
			
				<?php 		
				
				
					$this->include_tab_content(); 
				
				
				?>
				
				<div class="clear"></div>
				
			</div>
			
		</div>

	<?php }
	
	
}
?>