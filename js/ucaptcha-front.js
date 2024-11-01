if(typeof $ == 'undefined'){
	var $ = jQuery;
}
(function($) {
    jQuery(document).ready(function () { 
	
	   "use strict";	   
	   
	   $("#ucaptcha-client-registration-form").validationEngine({promptPosition: 'inline'});	   
	   
	   jQuery(document).on("click", "#ucaptcha-btn-conf-signup", function(e) {
			
			var frm_validation  = $("#wptu-registration-form").validationEngine('validate');	
			
			if(frm_validation)
			{
				//alert('submit');
							
							
				$("#ucaptcha-client-registration-form").submit();							
				
				
			}else{
				
				
				
			}
			
			
									
    		e.preventDefault();		 
				
        });
	   
	   
	       
    }); //END READY
})(jQuery);







