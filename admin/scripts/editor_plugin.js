(function() {
    tinymce.PluginManager.add('UCAPTCHAShortcodes', function( editor, url ) {
        editor.addButton( 'wptu_shortcodes_button', {
            title: 'Ultimate Captcha Shortcodes',
            type: 'menubutton',
            icon: 'icon mce_bup_shortcodes_button',
            menu: [
                
                {
                    text: 'Ticket Forms',
                    value: 'Text from menu item II',
                    onclick: function() {
                        editor.insertContent(this.value());
                    },
                    menu: [
                        {
                            text: 'Submit Ticket Form',
                            value: '[wptu_create_ticket product_id=""]',
                            onclick: function(e) {
                                e.stopPropagation();
                                editor.insertContent(this.value());
                            }       
                        },
						
						 {
                            text: 'Login Form',
                            value: '[wptu_user_login]',
                            onclick: function(e) {
                                e.stopPropagation();
                                editor.insertContent(this.value());
                            }       
                        },
						
						{
                            text: 'Registration Form',
                            value: '[wptu_user_signup]',
                            onclick: function(e) {
                                e.stopPropagation();
                                editor.insertContent(this.value());
                            }       
                        },
						
						{
                            text: 'Password Reset Form',
                            value: '[wptu_user_recover_password]',
                            onclick: function(e) {
                                e.stopPropagation();
                                editor.insertContent(this.value());
                            }       
                        },
						
						{
                            text: 'My Account',
                            value: '[wptu_account]',
                            onclick: function(e) {
                                e.stopPropagation();
                                editor.insertContent(this.value());
                            }       
                        },
                        
						
						
                    ]
                }
			
				
				
           ]
        });
    });
})();