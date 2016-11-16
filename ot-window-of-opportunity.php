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
define('OTWINDOWCOOKIE', '_owt');

// this function gets both an email and any days that were passed into the string.
function otw_parse_string() {
	$ot_wop_settings = get_option('ot_wop_settings');
	$str = $_SERVER['QUERY_STRING'];
	parse_str($str, $output);
	//var_dump($output);
	if ($output['email']) {
		$email = $output['email'];
	} elseif ($output['EMAIL']) {
		$email = $output['EMAIL'];
	} elseif ($output['e']) {
		$email = $output['e'];
	}
	$return = array();
	if ($output['d']) {
		// if the variable d= has been passed
		$days = '+ ' . $output['d']. ' days';
	} else {
		// otherwise, default to whatever's set in the options
		$days = '+ '. $ot_wop_settings['days_close']. ' days';
	}
	$return['days'] = $days;
	$return['email'] = $email;

	if ( $email ) {
		return $return;
	} else {
		return false;
	}
}
// hook for protected page to be redirected IF cookie doesn't exist
function ot_wop_protected_page_redirect() {
	global $post;
	$ot_wop_settings = get_option('ot_wop_settings');
		define( 'DONOTCACHEPAGE', 1 );
		require_once('inc/mcapi/MCAPI.class.php');
		$apiKey = $ot_wop_settings['api_key'];
		$api = new MCAPI($apiKey);
		$listId = $ot_wop_settings['mc_list_id'];
		$return = otw_parse_string();
		if (otw_parse_string()) {
			// this is gonna be a string + x, where X is the amount of days, either passed by string, or a default setting.
			$days = $return['days'];
			$email = $return['email'];
			$retval = $api->listMemberInfo( $listId, array($email) );
			$date = strtotime($retval['data'][0]['timestamp']);
			$date = strtotime($days, $date);
			$date = date('F j, Y', $date);
			// if there's a cookie, or we checked the user and they checked out:
			if (isset($_COOKIE[OTWINDOWCOOKIE]) or $retval) {
				// if there's NO cookie, set one now.
				if (!isset($_COOKIE[OTWINDOWCOOKIE])) {
					setcookie(OTWINDOWCOOKIE, $date, strtotime( '+180 days' ), '/');
					// buildling merge vars to update OTW with the date
					$merge_vars = array(
						'OTW' => $date
					);
					// Update member with OTW merge field as Date (counting $days from the subscribe date)
					$addMergeVar = $api->listUpdateMember($listId, $email, $merge_vars, 'html', true);
				//	var_dump($addMergeVar);
				//	echo 'There is no cookie loaded.<br>
				//	<img src="http://outthinkgroup.com/countdown/gif_white.php?time='.$date.'">';
				} else {
					// if a cookie IS set, then just get it, 'cause we'll probably need it soon enough
					$date = $_COOKIE[OTWINDOWCOOKIE];
				//	echo 'There is a cookie loaded.<br>';
				//	echo '<img src="http://outthinkgroup.com/countdown/gif_white.php?time='.$date.'">';
				}
			}
		$campaign_url = $ot_wop_settings['campaign_url'];
		wp_redirect( get_permalink($post->ID) . $campaign_url);
		exit;
	}


}
add_action( 'template_redirect', 'ot_wop_protected_page_redirect' );

function otw_window_open() {
	if ( $_COOKIE[OTWINDOWCOOKIE] && strtotime($_COOKIE[OTWINDOWCOOKIE]) > strtotime(today) ) {
		return true;
	} else {
		return false;
	}
}
