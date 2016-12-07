<?php
require_once( OTW_PLUGINPATH . '/inc/settings_callbacks.php' );
require_once( OTW_PLUGINPATH . '/inc/mcapi/MCAPI.class.php' );
//require_once(OTW_PLUGINPATH . '/mcapi/MailChimp.php');

class OT_WOP_Settings {

	public function __construct() {
		add_action( 'admin_menu', array($this, 'admin_menu') );
		add_action( 'admin_init', array($this, 'admin_init') );
		add_action( 'init', array($this, 'script_setup') );
	}
	function script_setup() {
		wp_enqueue_script( 'countdown_plugin', plugins_url( 'js/jquery.plugin.min.js' , __FILE__ ), 'jquery', '1.0', true );
		wp_enqueue_script( 'countdown_timer', plugins_url( 'js/jquery.countdown.min.js' , __FILE__ ), 'countdown_plugin', '1.0', true );
	}
	// initialize admin menu:
	function admin_menu() {
	    add_options_page( 'Out:think Window of Opportunity', 'Out:think Window of Opportunity', 'manage_options', 'ot_wop', array($this, 'options_page') );
	}

	function admin_init() {
		$userinfo = (array)get_option('ot-plugin-validation');
		$ot_wop_settings = (array)get_option('ot_wop_settings');
		register_setting( 'ot_wop', 'ot-plugin-validation' );
		register_setting( 'ot_wop', 'ot_wop_settings');
		// Begin section two here
		add_settings_section( 'section-two', 'Plugin Settings', array($this, 'section_two_callback'), 'ot_wop' );

		/**
		* ots_page_dropdown() creates a page dropdown select field accepts parameters as array
		* 'name', 'title', 'value', 'help';
		*/
		add_settings_field( 'campaign_url', 'Campaign URL', array('OTS_Framework', 'ots_text_input'), 'ot_wop', 'section-two', array(
			'title' => 'Enter your Analytics Campaign URL',
			'name' => 'ot_wop_settings[campaign_url]',
			'value' => $ot_wop_settings['campaign_url'],
			'help' => '<small>Campaign URL for tracking purposes</small>'

		) );
		/*
		 * ots_number() function accepts arguments as array:
		* 'name', 'title', 'value', 'help';
		*/
		add_settings_field( 'days_close', 'Window Closes', array('OTS_Framework','ots_number'), 'ot_wop', 'section-two', array(
		    'name' => 'ot_wop_settings[days_close]',
		    'value' => $ot_wop_settings['days_close'],
			'help' => '<small>Days after signup</small>',
			'default' => '',
		) );

			add_settings_section( 'section-three', 'Mailchimp Connection Details', array($this, 'section_three_callback'), 'ot_wop' );
			add_settings_field( 'api_key', 'Enter your API Key', array('OTS_Framework','ots_text_input'), 'ot_wop', 'section-three', array(
				'name' => 'ot_wop_settings[api_key]',
			    'value' => $ot_wop_settings['api_key']
			));
			if (!empty($ot_wop_settings['api_key'])) :
				add_settings_field( 'mc_list_id', 'Mailchimp List', array($this, 'ots_mc_listselect'), 'ot_wop', 'section-three', array(
					'apikey' => $ot_wop_settings['api_key'],
					'value' => $ot_wop_settings['mc_list_id'],
				));
			endif;
			add_settings_section( 'section-four', 'How to use this plugin:', array($this, 'section_four_callback'), 'ot_wop' );
	}
	function section_one_callback() { ?>
		<p>Enter your Out:think Group username and email to enable automatic updates of this plugin.</p>
		<?php
	}
	function section_two_callback() { ?>
		<p>Please configure your plugin below.</p>
		<?php
	}
	function section_three_callback() { ?>
		<p>Connect your Mailchimp account:</p>
		<?php
	}
	function section_four_callback() { ?>
		<?php $ot_wop_settings = get_option('ot_wop_settings'); ?>
		<style type="text/css" media="screen">
		textarea {
			font-family: courier;
			font-size: 13px;
			width: 90%;
		}
		</style>
		<p>You can include your link in mailchimp campaigns that, once clicked, will check the subscribe date of the user and see if they are inside of the "window" of opportunity. If so, they get access to the restricted page, otherwise, they get redirected to the default page.</p>
		<p>Copy and paste the link from the fields below:</p>
		<p>
			<label><strong>HTML link to be inserted into campaigns:</strong></label><br />
			<textarea rows="2" onclick="this.select();"><a href="<?php echo get_permalink($ot_wop_settings['offered_page']); ?>?e=*|EMAIL|*">Click here for your limited time offer.</a></textarea>
		</p>
		<p>
			<label><strong>Direct URL</strong></label><br />
			<textarea rows="1" onclick="this.select();"><?php echo get_permalink($ot_wop_settings['offered_page']); ?>?e=*|EMAIL|*</textarea>
		<br />
		<small>You can also use ?email, or ?EMAIL if you prefer, but ?e= keeps the url shorter.</small></p>
		<?php
	}

	function ots_ISapp_name() {
		$ot_wop_settings = (array)get_option('ot_wop_settings'); ?>
			<input type="text" name="ot_wop_settings[is_app_name]" value="<?php echo $ot_wop_settings['is_app_name']; ?>" id="is_app_name"><br>
			<small>This appears before infusionsoft.com on your account</small>
	<?php }
	function ots_mc_listselect($args) {
		$apiKey = $args['apikey'];
		$listID = $args['value'];
		$MailChimp = new MailChimp($apiKey);
		$listData = $MailChimp->get("/lists");
		//print_r($listData['lists']);
		//$api = new MCAPI($apiKey);
		//$retval = $api->lists();
	//	print_r($retval);
		if ($MailChimp->errorCode){
			echo "Unable to load lists! ";
			echo $MailChimp->errorMessage;
		} else { ?>
			<select name="ot_wop_settings[mc_list_id]" id="listID">
			<?php
				foreach ($listData['lists'] as $list) { ?>
				<option value="<?php echo $list['id']; ?>"<?php if ($listID == $list['id']): ?> selected="selected"<?php endif; ?>><?php echo $list['name']. ' (' . $list['stats']['member_count'].' subs)'; ?></option>
			<?php } // end for ?>
			</select><br>
			<?php
		}

	}
	function options_page() {
	?>
	    <div class="wrap">
	        <h2>Out:think Window of Opportunity Options</h2>
	        <form action="options.php" method="POST">
	            <?php settings_fields( 'ot_wop' ); ?>
	            <?php do_settings_sections( 'ot_wop' ); ?>
	            <?php submit_button(); ?>
	        </form>
	    </div>
	    <?php
	}
	public function active() {
		/* get wp version */
		global $wp_version;
		$otpu =  new OT_Plugin_Updater();
		$updater_data =$otpu->updater->updater_data();

		/* get current domain */
		$domain = $updater_data['domain'];
		$userinfo = get_option('ot-plugin-validation');
		$key = $userinfo['email'];
		$username = $userinfo['user'];

		$valid = "invalid";

		if( empty($key) || empty($username) ) return $valid;

		/* Get data from server */
		$remote_url = add_query_arg( array( 'plugin_repo' => $updater_data['repo_slug'], 'ahr_check_key' => 'validate_key' ), $updater_data['repo_uri'] );
		$remote_request = array( 'timeout' => 20, 'body' => array( 'key' => md5( $key ), 'login' => $username, 'autohosted' => $updater_data['autohosted'] ), 'user-agent' => 'WordPress/' . $wp_version . '; ' . $updater_data['domain'] );
		$raw_response = wp_remote_post( $remote_url, $remote_request );

		/* get response */
		$response = '';
		if ( !is_wp_error( $raw_response ) && ( $raw_response['response']['code'] == 200 ) )
			$response = trim( wp_remote_retrieve_body( $raw_response ) );

		/* if call to server sucess */
		if ( !empty( $response ) ){

			/* if key is valid */
			if ( $response == 'valid' ) $valid = 'valid';

			/* if key is not valid */
			elseif ( $response == 'invalid' ) $valid = 'invalid';

			/* if response is value is not recognized */
			else $valid = 'unrecognized';
		}

		return $valid;
	}
}
$OT_WOP_Settings = new OT_WOP_Settings();
