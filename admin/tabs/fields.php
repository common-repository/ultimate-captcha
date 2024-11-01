<?php 
global $ultimatecaptcha, $ultimatecaptchacomplement, $ultimatecaptcha_custom_fields;
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

$fields = array();
$fields = get_option('ultimatecaptcha_profile_fields');
ksort($fields);


$last_ele = end($fields);
$new_position = $last_ele['position']+1;

$meta_custom_value = "";
$qtip_classes = 'qtip-light ';
?>

<div class="ultimatecaptcha-sect  ultimatecaptcha-welcome-panel" >
<h3>
	<?php _e('Custom Fields Customizer','ultimate-captcha'); ?>
</h3>
<p>
	<?php _e("This section allow you to set different fields to the registration form.",'ultimate-captcha'); ?>
</p>


<a href="#ultimatecaptcha-add-field-btn" class="button button-secondary"  id="ultimatecaptcha-add-field-btn"><i
	class="ultimatecaptcha-icon-plus"></i>&nbsp;&nbsp;<?php _e('Click here to add new field','ultimate-captcha'); ?>
</a>


</div>

<div class="ultimatecaptcha-sect  ultimatecaptcha-welcome-panel" >
<label for="bup__custom_form"><?php _e('Form:','ultimate-captcha'); ?> </label>
<?php //echo $this->get_sites_drop_down_admin();?>
               

</div>

<div class="ultimatecaptcha-sect  ultimatecaptcha-welcome-panel ultimatecaptcha-ultra-rounded" id="ultimatecaptcha-add-new-custom-field-frm" >

<table class="form-table uultra-add-form">

	

	<tr valign="top">
		<th scope="row"><label for="uultra_type"><?php _e('Type','ultimate-captcha'); ?> </label>
		</th>
		<td><select name="uultra_type" id="uultra_type">
				<option value="usermeta">
					<?php _e('Field','ultimate-captcha'); ?>
				</option>
				<option value="separator">
					<?php _e('Separator','ultimate-captcha'); ?>
				</option>
		</select> <i class="uultra-icon-question-sign uultra-tooltip2"
			title="<?php _e('You can create a separator or a field','ultimate-captcha'); ?>"></i>
		</td>
	</tr>

	<tr valign="top">
		<th scope="row"><label for="uultra_field"><?php _e('Editor / Input Type','ultimate-captcha'); ?>
		</label></th>
		<td><select name="uultra_field" id="uultra_field">
				<?php  foreach($ultimatecaptcha->allowed_inputs as $input=>$label) { ?>
				<option value="<?php echo $input; ?>">
					<?php echo $label; ?>
				</option>
				<?php } ?>
		</select> <i class="uultra-icon-question-sign uultra-tooltip2"
			title="<?php _e('When user edit profile, this field can be an input (text, textarea, image upload, etc.)','ultimate-captcha'); ?>"></i>
		</td>
	</tr>

	<tr valign="top" >
		<th scope="row"><label for="uultra_meta_custom"><?php _e('New Custom Meta Key','ultimate-captcha'); ?>
		</label></th>
		<td><input name="uultra_meta_custom" type="text" id="uultra_meta_custom"
			value="<?php echo $meta_custom_value; ?>" class="regular-text" /> <i
			class="uultra-icon-question-sign uultra-tooltip2"
			title="<?php _e('Enter a custom meta key for this profile field if do not want to use a predefined meta field above. It is recommended to only use alphanumeric characters and underscores, for example my_custom_meta is a proper meta key.','ultimate-captcha'); ?>"></i>
		</td>
	</tr>
    
   
	<tr valign="top">
		<th scope="row"><label for="uultra_name"><?php _e('Label','ultimate-captcha'); ?> </label>
		</th>
		<td><input name="uultra_name" type="text" id="uultra_name"
			value=""
			class="regular-text" /> <i
			class="uultra-icon-question-sign uultra-tooltip2"
			title="<?php _e('Enter the label / name of this field as you want it to appear in front-end (Profile edit/view)','ultimate-captcha'); ?>"></i>
		</td>
	</tr>

	<tr valign="top">
		<th scope="row"><label for="uultra_tooltip"><?php _e('Tooltip Text','ultimate-captcha'); ?>
		</label></th>
		<td><input name="uultra_tooltip" type="text" id="uultra_tooltip"
			value=""
			class="regular-text" /> <i
			class="uultra-icon-question-sign uultra-tooltip2"
			title="<?php _e('A tooltip text can be useful for social buttons on profile header.','ultimate-captcha'); ?>"></i>
		</td>
	</tr>
    
    
     <tr valign="top">
                <th scope="row"><label for="uultra_help_text"><?php _e('Help Text','ultimate-captcha'); ?>
                </label></th>
                <td>
                    <textarea class="uultra-help-text" id="uultra_help_text" name="uultra_help_text" title="<?php _e('A help text can be useful for provide information about the field.','ultimate-captcha'); ?>" ></textarea>
                    <i class="uultra-icon-question-sign uultra-tooltip2"
                                title="<?php _e('Show this help text under the profile field.','ultimate-captcha'); ?>"></i>
                </td>
            </tr>

	
  

	<tr valign="top">
		<th scope="row"><label for="uultra_can_edit"><?php _e('User can edit','ultimate-captcha'); ?>
		</label></th>
		<td><select name="uultra_can_edit" id="uultra_can_edit">
				<option value="1">
					<?php _e('Yes','ultimate-captcha'); ?>
				</option>
				<option value="0">
					<?php _e('No','ultimate-captcha'); ?>
				</option>
		</select> <i class="uultra-icon-question-sign uultra-tooltip2"
			title="<?php _e('Users can edit this profile field or not.','ultimate-captcha'); ?>"></i>
		</td>
	</tr>

	
	


	<tr valign="top">
		<th scope="row"><label for="uultra_private"><?php _e('This field is required','ultimate-captcha'); ?>
		</label></th>
		<td><select name="uultra_required" id="uultra_required">
				<option value="0">
					<?php _e('No','ultimate-captcha'); ?>
				</option>
				<option value="1">
					<?php _e('Yes','ultimate-captcha'); ?>
				</option>
		</select> <i class="uultra-icon-question-sign uultra-tooltip2"
			title="<?php _e('Selecting yes will force user to provide a value for this field at registration and edit profile. Registration or profile edits will not be accepted if this field is left empty.','ultimate-captcha'); ?>"></i>
		</td>
	</tr>

	<tr valign="top">
		<th scope="row"><label for="uultra_show_in_register"><?php _e('Show on Registration form','ultimate-captcha'); ?>
		</label></th>
		<td><select name="uultra_show_in_register" id="uultra_show_in_register">
				<option value="0">
					<?php _e('No','ultimate-captcha'); ?>
				</option>
				<option value="1">
					<?php _e('Yes','ultimate-captcha'); ?>
				</option>
		</select> <i class="uultra-icon-question-sign uultra-tooltip2"
			title="<?php _e('Show this field on the registration form? If you choose no, this field will be shown on edit profile only and not on the registration form. Most users prefer fewer fields when registering, so use this option with care.','ultimate-captcha'); ?>"></i>
		</td>
        
        
	</tr>
    
    
     
    
            
   

	
	<tr valign="top">
		<th scope="row"></th>
		<td>
          <div class="ultimatecaptcha-ultra-success ultimatecaptcha-notification" id="ultimatecaptcha-sucess-add-field"><?php _e('Success ','ultimate-captcha'); ?></div>
        <input type="submit" name="bup-add" 	value="<?php _e('Submit New Field','ultimate-captcha'); ?>"
			class="button button-primary" id="ultimatecaptcha-btn-add-field-submit" /> 
            <input type="button"class="button button-secondary " id="ultimatecaptcha-close-add-field-btn"	value="<?php _e('Cancel','ultimate-captcha'); ?>" />
		</td>
	</tr>

</table>


</div>


<!-- show customizer -->
<ul class="ultimatecaptcha-sect ultimatecaptcha-ultra-rounded" id="uu-fields-sortable" >
		
  </ul>
  
           <script type="text/javascript">  
		
		      var custom_fields_del_confirmation ="<?php _e('Are you totally sure that you want to delete this field?','ultimate-captcha'); ?>";
			  
			  var custom_fields_reset_confirmation ="<?php _e('Are you totally sure that you want to restore the default fields?','ultimate-captcha'); ?>";
			   
			  var custom_fields_duplicate_form_confirmation ="<?php _e('Please input a name','ultimate-captcha'); ?>";
		 
		      ultimatecaptcha_reload_custom_fields_set();
		 </script>
         
         <div id="bup-spinner" class="ultimatecaptcha-spinner" style="display:">
            <span> <img src="<?php echo ultimatecaptcha_url?>admin/images/loaderB16.gif" width="16" height="16" /></span>&nbsp; <?php echo __('Please wait ...','ultimate-captcha')?>
	</div>
         
        