<?php
class UltimateCaptchaStaffProfile {

	var $options;

	function __construct() {
		
		
		$this->ini_module();
	
		/* Plugin slug and version */
		$this->slug = 'ultimatecaptcha';
		$this->subslug = 'ucaptcha-profile';
		require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		$this->plugin_data = get_plugin_data(ultimatecaptcha_profiles_path . 'index.php', false, false);
		$this->version = $this->plugin_data['Version'];
		
		/* Priority actions */
		add_action('admin_menu', array(&$this, 'add_menu'), 11);
		add_action('admin_enqueue_scripts', array(&$this, 'add_styles'), 9);
		add_action('admin_head', array(&$this, 'admin_head'), 9 );
		add_action('admin_init', array(&$this, 'admin_init'), 9);
		
		
	}
	
	
	/*Create profile page */
	public function create_profile_page($parent) 
	{
		global $ultimatecaptcha;
		
		if (!$ultimatecaptcha->get_option('profile_page_id')) 
		{
			$slug = $ultimatecaptcha->get_option("bup_slug");
			
			$new = array(
			  'post_title'    => __('Staff Profile','xoousers'),
			  'post_type'     => 'page',
			  'post_name'     => $slug,			 
			  'post_content'  => '[bup_profile]',
			  'post_status'   => 'publish',
			  'comment_status' => 'closed',
			  'ping_status' => 'closed',
			  'post_author' => 1
			);
			$new_page = wp_insert_post( $new, FALSE );
			
			
			if (isset($new_page))
			{
				
			  $current_option = get_option('bup_options');
			  $page_data = get_post($new_page);

			
				if(isset($page_data->guid))
				{
					//update settings
					$this->bup_set_option('profile_page_id',$new_page);
					
				}
				
			}
		}
	}
	
	
	
	public function bup_set_option($option, $newvalue)
	{
		$settings = get_option('ultimatecaptcha_options');
		$settings[$option] = $newvalue;
		update_option('ultimatecaptcha_options', $settings);
	}
	
	public function ini_module()
	{
		global $wpdb;		   		  		   
		
	}
	
	function admin_init() 
	{
	
		$this->tabs = array(
			'manage' => __('Users Pages','wp-ticket-ultra')
			
		);
		$this->default_tab = 'manage';		
		
	}		
	
	function admin_head(){

	}

	function add_styles(){
	
		wp_register_script( 'ultimatecaptcha_profiles_js', ultimatecaptcha_profiles_url . 'admin/scripts/admin.js', array( 
			'jquery'
		) );
		wp_enqueue_script( 'ultimatecaptcha_profiles_js' );
	
		wp_register_style('ultimatecaptcha_profiles_css', wptu_profiles_url . 'admin/css/admin.css');
		wp_enqueue_style('ultimatecaptcha_profiles_css');
		
	}
	
	function add_menu()
	{
		add_submenu_page( 'ultimatecaptcha', __('User Pages','ultimate-captcha'), __('User Pages','ultimate-captcha'), 'manage_options', 'ucaptcha-profiles', array(&$this, 'admin_page') );
		
	
		
	}

	function admin_tabs( $current = null ) {
			$tabs = $this->tabs;
			$links = array();
			if ( isset ( $_GET['tab'] ) ) {
				$current = $_GET['tab'];
			} else {
				$current = $this->default_tab;
			}
			foreach( $tabs as $tab => $name ) :
				if ( $tab == $current ) :
					$links[] = "<a class='nav-tab nav-tab-active' href='?page=".$this->subslug."&tab=$tab'>$name</a>";
				else :
					$links[] = "<a class='nav-tab' href='?page=".$this->subslug."&tab=$tab'>$name</a>";
				endif;
			endforeach;
			foreach ( $links as $link )
				echo $link;
	}

	function get_tab_content() {
		$screen = get_current_screen();
		if( strstr($screen->id, $this->subslug ) ) {
			if ( isset ( $_GET['tab'] ) ) {
				$tab = $_GET['tab'];
			} else {
				$tab = $this->default_tab;
			}
			require_once ultimatecaptcha_profiles_path.'admin/panels/'.$tab.'.php';
		}
	}
	
	
	
	function admin_page() {
		
		
		global $ultimatecaptcha, $bupcomplement;
		
		
		if (isset($_POST['update_settings']) &&  $_POST['reset_email_template']=='') {
            $ultimatecaptcha->update_settings();
        }
		
		if (isset($_POST['update_ucaptcha_slugs']) && $_POST['update_ucaptcha_slugs']=='bup_slugs')
		{
		   $ultimatecaptcha->update_settings();
          // $ultimatecaptcha->create_rewrite_rules();
			echo '<div class="updated"><p><strong>'.__('Rewrite Rules were Saved.','wp-ticket-ultra').'</strong></p></div>';
        }
	
		
				
	?>
	
		<div class="wrap <?php echo $this->slug; ?>-admin">
        
           <h2>ULTIMATE CAPTCHA - <?php _e('CUSTOM PAGES SETTINGS','wp-ticket-ultra'); ?></h2>
           
           <div id="icon-users" class="icon32"></div>
			
						
			<h2 class="nav-tab-wrapper"><?php $this->admin_tabs(); ?></h2>

			<div class="<?php echo $this->slug; ?>-admin-contain">
				
				<?php $this->get_tab_content(); ?>
				
				<div class="clear"></div>
				
			</div>
			
		</div>

	<?php }

}
global $ucaptcha_staff_profile;
$ucaptcha_staff_profile = new UltimateCaptchaStaffProfile();