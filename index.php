<?php
/*
Plugin Name: Ultimate Captcha
Plugin URI: https://ultimatecaptcha.com
Description: Ultimate Captcha.
Version: 1.0.5
Author: Ultimate Captcha
Text Domain: ultimate-captcha
Domain Path: /languages
Author URI: https://ultimatecaptcha.com/
*/


if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

define('ultimatecaptcha_url',plugin_dir_url(__FILE__ ));
define('ultimatecaptcha_path',plugin_dir_path(__FILE__ ));
define('UCAPTCHA_PLUGIN_SETTINGS_URL',"?page=ultimatecaptcha&tab=pro");
define('UCAPTCHA_PLUGIN_WELCOME_URL',"?page=ultimatecaptcha&tab=welcome");

$plugin = plugin_basename(__FILE__);


define('ultimatecaptcha_licence_url','https://ultimatecaptcha.com/');

/* Master Class  */
require_once ('loader.php');
register_activation_hook( __FILE__, 'ultimatecaptcha');


function ultimatecaptcha_load_textdomain() 
{     	   
	   $locale = apply_filters( 'plugin_locale', get_locale(), 'ultimate-captcha' );	   
       $mofile = ultimatecaptcha_path . "languages/ultimate-captcha-$locale.mo";
			
		// Global + Frontend Locale
		load_textdomain( 'ultimate-captcha', $mofile );
		load_plugin_textdomain( 'ultimate-captcha', false, dirname(plugin_basename(__FILE__)).'/languages/' );
}

/* Load plugin text domain (localization) */
add_action('init', 'ultimatecaptcha_load_textdomain');	

function  ultimatecaptcha( $network_wide ) 
{
	$plugin = "ultimate-captcha/index.php";
	$plugin_path = '';	
	
	if ( is_multisite() && $network_wide ) // See if being activated on the entire network or one blog
	{ 
		activate_plugin($plugin_path,NULL,true);			
		
	} else { // Running on a single blog		   	
			
		activate_plugin($plugin_path,NULL,false);		
		
	}
}
global $ultimatecaptcha;
$ultimatecaptcha = new UltimateCaptchaPlugin();

register_activation_hook(__FILE__, 'ucaptcha_my_plugin_activate');
add_action('admin_init', 'ucaptcha_my_plugin_redirect');

function ucaptcha_my_plugin_activate() 
{
    add_option('ucaptcha_plugin_do_activation_redirect', true);
}

function ucaptcha_my_plugin_deactivate() 
{

}

function ucaptcha_my_plugin_redirect() 
{
    if (get_option('ucaptcha_plugin_do_activation_redirect', false)) {
        delete_option('ucaptcha_plugin_do_activation_redirect');
		
		if (! get_option('ucaptcha_ini_setup')) 
		{
			wp_redirect(UCAPTCHA_PLUGIN_WELCOME_URL);
		
		}else{
				
			wp_redirect(UCAPTCHA_PLUGIN_SETTINGS_URL);
			
		}
    }
}