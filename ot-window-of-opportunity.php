<?php 
/*
Plugin Name: Window of Opportunity
Plugin URI: http://outthinkgroup.com/wop
Description: Window of Opportunity - A mailchimp plugin to allow only a limited time to open/shut a page.
Author: Joseph Hinson
Version: 1.0
Author URI: http://outthinkgroup.com/wop
*/

require_once( trailingslashit( plugin_dir_path( __FILE__ ) ) . 'inc/options.php' );
include 'inc/ot-nlsignup.php';
include 'inc/shortcodes.php';

// hook for protected page to be redirected IF cookie doesn't exist
function ot_wop_protected_page_redirect() {
	$ot_wop_settings = get_option('ot_wop_settings');	
    if( is_page($ot_wop_settings['offered_page'])) {
		define( 'DONOTCACHEPAGE', 1 );
		require_once('inc/mcapi/MCAPI.class.php');
		$apiKey = $ot_wop_settings['api_key'];
		$api = new MCAPI($apiKey);
		$listId = $ot_wop_settings['mc_list_id'];
		$str = $_SERVER['QUERY_STRING'];
		parse_str($str, $output);
		if ($output['email']) {
			$email = $output['email'];
		} elseif ($output['EMAIL']) {
			$email = $output['EMAIL'];
		} elseif ($output['e']) {
			$email = $output['e'];			
		}
		$retval = $api->listMemberInfo( $listId, array($email) );
		$datetime1 = new DateTime($retval['data'][0]['timestamp']);
//		$datetime1 = new DateTime('2014-05-12 18:23:37');
		$datetime2 = new DateTime('NOW');
//		print_r($datetime2);

		$difference = $datetime1->diff($datetime2);
// 	Uncomment this stuff to debug!		
//		print_r($difference);
//		error_log($difference->d .' but days open should be: '.$ot_wop_settings['days_open']. 'and days closed should be '. $ot_wop_settings['days_close']);
		$days_between = $difference->days;
		// if the days from signup is past the open time (or on it) and on, but not past the days closed time, don't do anything.
		if ($days_between >= $ot_wop_settings['days_open'] and $days_between <= $ot_wop_settings['days_close'] && !empty($email) ) {
		// for all other cases -- redirect 'em.
		} else {
	        wp_redirect( get_permalink($ot_wop_settings['fallback_page']) );
		}
	} // end check for page
}
add_action( 'template_redirect', 'ot_wop_protected_page_redirect' );


/**
 * Load and Activate Plugin Updater Class.
 */
function ot_membergate_plugin_updater_init() {
	/* Load Plugin Updater */
	require_once( trailingslashit( plugin_dir_path( __FILE__ ) ) . 'inc/plugin-updater.php' );
	$userinfo = get_option('ot-plugin-validation');
	
	/* Updater Config */
	$config = array(
		'base'      => plugin_basename( __FILE__ ), //required
		'username'    => $userinfo['user'], // user login name in your site.
		'key' => $userinfo['email'],
		'repo_uri'  => 'http://outthinkgroup.com/training/aps/',
		'repo_slug' => 'outthink-wop',
	);

	/* Load Updater Class */
	new OT_Plugin_Updater( $config );
}
//add_action( 'init', 'ot_membergate_plugin_updater_init' );