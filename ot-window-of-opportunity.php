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
include 'inc/shortcodes.php';
define('OTWINDOWCOOKIE', '_owt');
// hook for protected page to be redirected IF cookie doesn't exist
function ot_wop_protected_page_redirect() {
	$ot_wop_settings = get_option('ot_wop_settings');
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
		if ($output['d']) {
			$days = '+ ' . $output['d']. ' days';
		} else {
			$days = '+ 7 days';
		}
		$retval = $api->listMemberInfo( $listId, array($email) );
//		$datetime1 = new DateTime($retval['data'][0]['timestamp']);
		//var_dump($retval);
		//echo 'signed up: ' . $retval['data'][0]['timestamp'] .'<br>';
		$date = strtotime($retval['data'][0]['timestamp']);
		$date = strtotime($days, $date);
		$date = date('F j, Y', $date);
		if (!isset($_COOKIE[OTWINDOWCOOKIE])) {
			setcookie(OTWINDOWCOOKIE, $date, strtotime( '+180 days' ), '/');
			$merge_vars = array(
				'OTW' => $date
			);
			//print_r($merge_vars);
			$addMergeVar = $api->listUpdateMember($listId, $email, $merge_vars, 'html', true);
			var_dump($addMergeVar);
			echo 'There is no cookie loaded.<br>
			<img src="http://outthinkgroup.com/countdown/gif_white.php?time='.$date.'">';
		} else {
			$date = $_COOKIE[OTWINDOWCOOKIE];
			echo 'There is a cookie loaded.<br>';
			echo '<img src="http://outthinkgroup.com/countdown/gif_white.php?time='.$date.'">';
		}
		die;
//		error_log($difference->d .' but days open should be: '.$ot_wop_settings['days_open']. 'and days closed should be '. $ot_wop_settings['days_close']);
		// if the days from signup is past the open time (or on it) and on, but not past the days closed time, don't do anything.
		if (is_user_logged_in() or ($invert == 1 && !empty($email) ) ) {
		// for all other cases -- redirect 'em.
		} else {
	        wp_redirect( get_permalink($ot_wop_settings['fallback_page']) );
		}
}
add_action( 'wp', 'ot_wop_protected_page_redirect' );
