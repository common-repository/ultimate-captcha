<?php
global $ultimatecaptcha;

define('ultimatecaptcha_profiles_url',plugin_dir_url(__FILE__ ));
define('ultimatecaptcha_profiles_path',plugin_dir_path(__FILE__ ));

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if(isset($ultimatecaptcha)){

	
	/* administration */
	if (is_admin()){
		foreach (glob(ultimatecaptcha_profiles_path . 'admin/*.php') as $filename) { include $filename; }
	}
	
}