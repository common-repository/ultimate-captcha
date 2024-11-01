jQuery(document).ready(function($) {
	
		
	jQuery("body").on("click", "#bup-aweber-connect-save", function(e) {
		e.preventDefault();		
		 
		 var auth_key=  jQuery('#bup-aweber-authorization-code').val();		 	 
		 
		 jQuery("#bup-aw-auth-message").html(message_wait_availability);
				
		jQuery.ajax({
						type: 'POST',
						url: ajaxurl,
						data: {"action": "wptu_aweber_update_auth_code", "auth_key": auth_key },
						
						success: function(data){									

							
							
							
							var res =jQuery.parseJSON(data);						
							if(res.response=='OK')
							{
								window.location.reload();	
													
							}else{									
								
								jQuery("#bup-aw-auth-message").html(res.content);
								
							
							}								
													
							
							
							}
					});
		return false;
	});
	
	jQuery("body").on("click", "#bup-aweber-set-list", function(e) {
		e.preventDefault();		
		 
		 var list_id=  jQuery('#bup-selected-aweber-list').val();		 	 
		 
		 jQuery("#bup-aw-list-message").html(message_wait_availability);
				
		jQuery.ajax({
						type: 'POST',
						url: ajaxurl,
						data: {"action": "bup_aweber_set_list", "list_id": list_id },
						
						success: function(data){	
								
								jQuery("#bup-aw-list-message").html(data);
								window.location.reload();
								
											
							
							
							}
					});
		return false;
	});
	
	jQuery("body").on("click", "#bup-aweber-connect-reset", function(e) {
		e.preventDefault();		
		 
		
		jQuery.ajax({
						type: 'POST',
						url: ajaxurl,
						data: {"action": "bup_aweber_update_auth_code_reset"},
						
						success: function(data){									

							window.location.reload();								
													
							
							
							}
					});
		return false;
	});
	
	
	
});