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
		$retval = $api->listMemberInfo( $listId, array($email) );
//		$datetime1 = new DateTime($retval['data'][0]['timestamp']);
		//var_dump($retval);
		//echo 'signed up: ' . $retval['data'][0]['timestamp'] .'<br>';
		$date = strtotime($retval['data'][0]['timestamp']);
		$date = strtotime("+2 months", $date);
		$date = date('F j, Y', $date);
		if (!isset($_COOKIE["ot_wop_time"])) {
			setcookie("ot_wop_time", $date, strtotime( '+180 days' ), '/');
			echo 'act now -- only: <img src="http://outthinkgroup.com/countdown/gif_white.php?time='.$date.'">';
		} else {
			$date = $_COOKIE["ot_wop_time"];
			echo '<img src="http://outthinkgroup.com/countdown/gif_white.php?time='.$date.'">';
		}


		//$date = new DateTime('22-05-2011');
		//$date->modify('+1 week');
		//echo date('F j, Y', $date . '+ 1 week');
		//$date2 = new DateTime($retval['data'][0]['timestamp']);
		//$date2->modify('+1 week');
		die;
		// start building the date variable for the day the window closes by starting with the date they signed up.
		$windowCloses = new DateTime($retval['data'][0]['timestamp']);
		//var_dump($windowCloses);
//		print_r($windowCloses);
		// which means you need to take the variable from the previous line, and add the amount of days to it.
		$windowCloses->add(new DateInterval( "P".$ot_wop_settings['days_close']."D" ));
		// setting the timezone to Eastern
		date_timezone_set($windowCloses, timezone_open('America/New_York'));
		// setting the time to 10pm
		$windowCloses->setTime(22, 00);
		$datetime2 = new DateTime('NOW');
//		print_r($datetime2);

		$difference = $windowCloses->diff($datetime2);
		// 	Uncomment this stuff to debug!
//		print_r($difference);
		$invert = $difference->invert;
//		error_log($difference->d .' but days open should be: '.$ot_wop_settings['days_open']. 'and days closed should be '. $ot_wop_settings['days_close']);
		// if the days from signup is past the open time (or on it) and on, but not past the days closed time, don't do anything.
		if (is_user_logged_in() or ($invert == 1 && !empty($email) ) ) {
		// for all other cases -- redirect 'em.
		} else {
	        wp_redirect( get_permalink($ot_wop_settings['fallback_page']) );
		}
}
add_action( 'wp', 'ot_wop_protected_page_redirect' );
