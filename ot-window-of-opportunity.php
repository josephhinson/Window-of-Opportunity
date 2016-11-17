<?php
/*
Plugin Name: Window of Opportunity
Plugin URI: http://outthinkgroup.com/wop
Description: Window of Opportunity - A mailchimp plugin to allow only a limited time to open/shut a page.
Author: Joseph Hinson
Version: 1.0
Author URI: http://outthinkgroup.com/wop
*/

define('OTW_PLUGINPATH', trailingslashit( plugin_dir_path( __FILE__ ) ) );
require_once( OTW_PLUGINPATH . 'inc/options.php' );
include('mcapi/MailChimp.php');
include 'inc/shortcodes.php';
define('OTWINDOWCOOKIE', '_owt');

// this function gets both an email and any days that were passed into the string.
function otw_parse_string() {
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
		$days = '+ '. $ot_wop_settings['days_close'];
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
	$tid = get_the_ID();
	$ot_wop_settings = get_option('ot_wop_settings');
		define( 'DONOTCACHEPAGE', 1 );
		//use \DrewM\MailChimp\MailChimp;
		$apiKey = $ot_wop_settings['api_key'];
		$MailChimp = new MailChimp($apiKey);
		//$api = new MCAPI($apiKey);
		$listId = $ot_wop_settings['mc_list_id'];
		$return = otw_parse_string();
		$days = $return['days'];
		$email = $return['email'];
		// new mailchimp 3.0 API wrapper
		$subscriber_hash = $MailChimp->subscriberHash( $email );
		$subscriberURL = "/lists/$listId/members/$subscriber_hash";
		$retval = $MailChimp->get($subscriberURL);
		// print_r($retval);
		// echo $retval['timestamp_opt'];
		//$retval = $api->listMemberInfo( $listId, array($email) );
		$date = strtotime($retval['timestamp_opt']);
		$date = strtotime($days, $date);
		$date = date('F j, Y', $date);
		if (isset($_COOKIE[OTWINDOWCOOKIE]) or $retval) {
			if (!isset($_COOKIE[OTWINDOWCOOKIE])) {
				setcookie(OTWINDOWCOOKIE, $date, strtotime( '+180 days' ), '/');
				$merge_vars = array(
					'OTW' => $date
				);
				//print_r($merge_vars);
				//$addMergeVar = $api->listUpdateMember($listId, $email, $merge_vars, 'html', true);
			//	var_dump($addMergeVar);
			//	echo 'There is no cookie loaded.<br>
			//	<img src="http://outthinkgroup.com/countdown/gif_white.php?time='.$date.'">';
			} else {
				$date = $_COOKIE[OTWINDOWCOOKIE];
			//	echo 'There is a cookie loaded.<br>';
			//	echo '<img src="http://outthinkgroup.com/countdown/gif_white.php?time='.$date.'">';
			}
		}
//		$datetime1 = new DateTime($retval['data'][0]['timestamp']);
		//var_dump($retval);
		//echo 'signed up: ' . $retval['data'][0]['timestamp'] .'<br>';

//		die;
//		error_log($difference->d .' but days open should be: '.$ot_wop_settings['days_open']. 'and days closed should be '. $ot_wop_settings['days_close']);
		// if the days from signup is past the open time (or on it) and on, but not past the days closed time, don't do anything.
//		if (is_user_logged_in() or ($invert == 1 && !empty($email) ) ) {
		// for all other cases -- redirect 'em.
//		} else {
//	        wp_redirect( get_permalink($ot_wop_settings['fallback_page']) );
//		}
	wp_redirect( get_permalink($tid) );
	//exit;
}
add_action( 'init', 'ot_wop_protected_page_redirect' );

function otw_window_open() {
	if (otw_parse_string() or ($_COOKIE[OTWINDOWCOOKIE] && strtotime($_COOKIE[OTWINDOWCOOKIE]) > strtotime(today)) ) {
		return true;
	} else {
		return false;
	}
}
