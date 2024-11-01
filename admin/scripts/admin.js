var $ = jQuery;


jQuery(document).ready(function($) {
	
	
	
	jQuery( "#tabs-bupro" ).tabs({collapsible: false	});
	jQuery( "#tabs-bupro-settings" ).tabs({collapsible: false	});	
	
	
	/* 	Close Open Sections in Dasbhoard */
	jQuery(document).on("click", ".ultimatecaptcha-widget-home-colapsable", function(e) {
		
		e.preventDefault();
		var widget_id =  jQuery(this).attr("widget-id");		
		var iconheight = 20;
		
		
		if(jQuery("#ultimatecaptcha-main-cont-home-"+widget_id).is(":visible")) 
	  	{
					
			jQuery( "#ultimatecaptcha-close-open-icon-"+widget_id ).removeClass( "fa-sort-asc" ).addClass( "fa-sort-desc" );
			
		}else{
			
			jQuery( "#ultimatecaptcha-close-open-icon-"+widget_id ).removeClass( "fa-sort-desc" ).addClass( "fa-sort-asc" );			
	 	 }
		
		
		jQuery("#ultimatecaptcha-main-cont-home-"+widget_id).slideToggle();	
					
		return false;
	});
	
	
	jQuery(document).on("click", "#bupadmin-btn-validate-copy", function(e) {	
	
	
		 e.preventDefault();
		 
		 var p_ded =  $('#p_serial').val();
		 
		 jQuery("#loading-animation").slideDown();
		
		jQuery.ajax({
						type: 'POST',
						url: ajaxurl,
						data: {"action": "ultimatecaptcha_vv_c_de_a", 
						"p_s_le": p_ded },
						
						success: function(data){
							
							jQuery("#loading-animation").slideUp();							
						
								jQuery("#bup-validation-results").html(data);
								jQuery("#bup-validation-results").slideDown();								
								setTimeout("hidde_noti('bup-validation-results')", 6000)
								
								window.location.reload();
							
							}
					});
			
		 	
		
				
		return false;
	});
	
	
	//this adds the user and loads the user's details	
	jQuery(document).on("click", ".ultimatecaptcha_restore_template", function(e) {
			
			
			var template_id =  jQuery(this).attr("b-template-id");
			jQuery("#email_template").val(template_id);
			jQuery("#ultimatecaptcha_reset_email_template").val('yes');
			
			jQuery.ajax({
					type: 'POST',
					url: ajaxurl,
					data: {"action": "ultimatecaptcha_reset_email_template", 
					"email_template": template_id					
					
					 
					 },
					
					success: function(data){
						
						
						var res = data;								
						location.reload();	
						
						
						}
				});
			
			
			 
				
        });
		
		
		
		/* 	FIELDS CUSTOMIZER -  Edit Field Form */
	jQuery('#ultimatecaptcha__custom_registration_form').live('change',function(e)
	{		
		e.preventDefault();
		ultimatecaptcha_reload_custom_fields_set();
					
	});
	
	/* 	FIELDS CUSTOMIZER -  ClosedEdit Field Form */
	
	jQuery(document).on("click", ".ultimatecaptcha-btn-close-edition-field", function(e) {	
		e.preventDefault();
		var block_id =  jQuery(this).attr("data-edition");		
		jQuery("#ultimatecaptcha-edit-fields-bock-"+block_id).slideUp();				
	});
	
	/* 	FIELDS CUSTOMIZER -  Add New Field Form */
	jQuery('#ultimatecaptcha-add-field-btn').live('click',function(e)
	{
		
		e.preventDefault();
			
		jQuery("#ultimatecaptcha-add-new-custom-field-frm").slideDown();				
	});
	
	/* 	FIELDS CUSTOMIZER -  Add New Field Form */
	jQuery('#ultimatecaptcha-close-add-field-btn').live('click',function(e){
		
		e.preventDefault();
			
		jQuery("#ultimatecaptcha-add-new-custom-field-frm").slideUp();				
		return false;
	});
	
	
	/* 	FIELDS CUSTOMIZER - Add New Field Data */
	jQuery('#ultimatecaptcha-btn-add-field-submit').live('click',function(e){
		e.preventDefault();
		
		
		var _position = $("#uultra_position").val();		
		var _type =  $("#uultra_type").val();
		var _field = $("#uultra_field").val();		
		
		var _meta_custom = $("#uultra_meta_custom").val();		
		var _name = $("#uultra_name").val();
		var _tooltip =  $("#uultra_tooltip").val();	
		var _help_text =  $("#uultra_help_text").val();		
	
		
		var _can_edit =  $("#uultra_can_edit").val();		
		var _allow_html =  $("#uultra_allow_html").val();
				
		var _private = $("#uultra_private").val();
		var _required =  $("#uultra_required").val();		
		var _show_in_register = $("#uultra_show_in_register").val();
		
		var _choices =  $("#uultra_choices").val();	
		var _predefined_options =  $("#uultra_predefined_options").val();		
		var custom_form =  $('#ultimatecaptcha__custom_registration_form').val();	
				
		var _icon =  $('input:radio[name=uultra_icon]:checked').val();
		
				
		jQuery.ajax({
						type: 'POST',
						url: ajaxurl,
						data: {"action": "ultimatecaptcha_add_new_custom_profile_field", 
						"_position": _position , 
						"_type": _type ,
						"_field": _field ,
						"_meta_custom": _meta_custom ,
						"_name": _name  ,						
						"_tooltip": _tooltip ,
						
						"_help_text": _help_text ,	
						
						"_can_edit": _can_edit ,"_allow_html": _allow_html  ,
						"_private": _private, 
						"_required": _required  ,
						"_show_in_register": _show_in_register ,						
						"_choices": _choices,  
						"_predefined_options": _predefined_options , 
						"custom_form": custom_form,						
						"_icon": _icon },
						
						success: function(data){		
						
													
							jQuery("#ultimatecaptcha-sucess-add-field").slideDown();
							setTimeout("hidde_noti('ultimatecaptcha-sucess-add-field')", 3000)		
							//alert("done");
							window.location.reload();
							 							
							
							
							}
					});
			
		 
		
				
		return false;
	});
	
	/* 	FIELDS CUSTOMIZER - Update Field Data */
	jQuery(document).on("click", ".ultimatecaptcha-btn-submit-field", function(e) {
		
		e.preventDefault();
		
		var key_id =  jQuery(this).attr("data-edition");	
		
		jQuery('#p_name').val()		  
		
		var _position = $("#uultra_" + key_id + "_position").val();		
		var _type =  $("#uultra_" + key_id + "_type").val();
		var _field = $("#uultra_" + key_id + "_field").val();		
		var _meta =  $("#uultra_" + key_id + "_meta").val();
		var _meta_custom = $("#uultra_" + key_id + "_meta_custom").val();		
		var _name = $("#uultra_" + key_id + "_name").val();
				
		var _tooltip =  $("#uultra_" + key_id + "_tooltip").val();	
		var _help_text =  $("#uultra_" + key_id + "_help_text").val();		
				
		var _can_edit =  $("#uultra_" + key_id + "_can_edit").val();		
		
		var _required =  $("#uultra_" + key_id + "_required").val();		
		var _show_in_register = $("#uultra_" + key_id + "_show_in_register").val();
				
		var _choices =  $("#uultra_" + key_id + "_choices").val();	
		var _predefined_options =  $("#uultra_" + key_id + "_predefined_options").val();		
		var _icon =  $('input:radio[name=uultra_' + key_id +'_icon]:checked').val();
		
		var custom_form =  $('#ultimatecaptcha__custom_registration_form').val();
		
		jQuery.ajax({
						type: 'POST',
						url: ajaxurl,
						data: {"action": "ultimatecaptcha_save_fields_settings", 
						"_position": _position , "_type": _type ,
						"_field": _field ,
						"_meta": _meta ,
						"_meta_custom": _meta_custom  
						,"_name": _name  ,											
						
						"_tooltip": _tooltip ,
						"_help_text": _help_text ,												
						"_icon": _icon ,						
						"_required": _required  ,
						"_show_in_register": _show_in_register ,						
						"_choices": _choices, 
						"_predefined_options": _predefined_options,
						"pos": key_id  , 
						"custom_form": custom_form 
						
																	
						},
						
						success: function(data){		
						
												
						jQuery("#ultimatecaptcha-sucess-fields-"+key_id).slideDown();
						setTimeout("hidde_noti('ultimatecaptcha-sucess-fields-" + key_id +"')", 1000);
						
						ultimatecaptcha_reload_custom_fields_set();		
						
							
							}
					});
			
	});
	
	
	/* 	FIELDS CUSTOMIZER -  Edit Field Form */
		
	jQuery(document).on("click", ".ultimatecaptcha-btn-edit-field", function(e) {
		
		e.preventDefault();
		var block_id =  jQuery(this).attr("data-edition");			
		
		var custom_form = jQuery('#ultimatecaptcha__custom_registration_form').val();
		
		jQuery("#bup-spinner").show();
		
		jQuery.ajax({
						type: 'POST',
						url: ajaxurl,
						data: {"action": "ultimatecaptcha_reload_field_to_edit", 
						"pos": block_id, "custom_form": custom_form},
						
						success: function(data){
							
							
							jQuery("#ultimatecaptcha-edit-fields-bock-"+block_id).html(data);							
							jQuery("#ultimatecaptcha-edit-fields-bock-"+block_id).slideDown();							
							jQuery("#bup-spinner").hide();								
							
							
							}
					});
		
					
		return false;
	});
	
	
});

function ultimatecaptcha_reload_custom_fields_set ()	
{
	
	jQuery("#bup-spinner").show();
	
	 var custom_form =  jQuery('#ultimatecaptcha__custom_registration_form').val();
		
		jQuery.post(ajaxurl, {
							action: 'ultimatecaptcha_reload_custom_fields_set', 'custom_form': custom_form
									
							}, function (response){									
																
							jQuery("#uu-fields-sortable").html(response);							
							sortable_fields_list();
							
							jQuery("#bup-spinner").hide();
							
																
														
		 });
		
}
function sortable_fields_list ()
{
	var itemList = jQuery('#uu-fields-sortable');	 
	var ultimatecaptcha_custom_form =  jQuery('#ultimatecaptcha__custom_registration_form').val();
   
    itemList.sortable({
		cursor: 'move',
        update: function(event, ui) {
        jQuery("#ultimatecaptcha-spinner").show(); // Show the animate loading gif while waiting

            opts = {
                url: ajaxurl, // ajaxurl is defined by WordPress and points to /wp-admin/admin-ajax.php
                type: 'POST',
                async: true,
                cache: false,
                dataType: 'json',
                data:{
                    action: 'ultimatecaptcha_sort_fileds_list', // Tell WordPress how to handle this ajax request
					'ultimatecaptcha_custom_form': ultimatecaptcha_custom_form, // Tell WordPress how to handle this ajax request
                    order: itemList.sortable('toArray').toString() // Passes ID's of list items in  1,3,2 format
                },
                success: function(response) {
                   // $('#loading-animation').hide(); // Hide the loading animation
				   ultimatecaptcha_reload_custom_fields_set();
                    return; 
                },
                error: function(xhr,textStatus,e) {  // This can be expanded to provide more information
                    alert(e);
                    // alert('There was an error saving the updates');
                  //  $('#loading-animation').hide(); // Hide the loading animation
                    return; 
                }
            };
            jQuery.ajax(opts);
        }
    }); 
	
	
}