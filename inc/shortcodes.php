<?php
// [countdown_timer]
function ot_countdown_timer($atts) {
		extract(shortcode_atts(array(), $atts));
		ob_start(); ?>
		<?php
		define( 'DONOTCACHEPAGE', 1 );
		$ot_wop_settings = get_option('ot_wop_settings');
		require_once('mcapi/MCAPI.class.php');
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
		// let's build a date variable for right now
		$now = new DateTime('NOW');

		// start building the date variable for the day the window closes
		$windowCloses = new DateTime($retval['data'][0]['timestamp']);
		// which means you need to take the variable from the previous line, and add the amount of days to it.
		$windowCloses->add(new DateInterval( "P".$ot_wop_settings['days_close']."D" ));
		date_timezone_set($windowCloses, timezone_open('America/New_York'));
		$windowCloses->setTime(22, 00);
		// now that we have the window closes -- let's get the exact day...
//		print_r($windowCloses);

		$timeRemaining = $now->diff($windowCloses);

//		print_r($timeRemaining);
		$days = '+'.$timeRemaining->format("%d").'d';
		$hours = '+'.$timeRemaining->format("%H").'h';
		$minutes = '+'.$timeRemaining->format("%i").'m';
		$seconds = '+'.$timeRemaining->format("%s");
// 	Uncomment this stuff to debug!
//		print_r($difference);
//		print_r($date);
// if the email address is not provided...don't show anything
	if (!empty($email)) {
		?>
		<style type="text/css" media="screen">
			/* line 141, /Users/joseph/Sites/unitedwayla/wp-content/themes/UnitedWay/css/less/bmia.less */
			.countdown-wrapper {
				overflow: hidden;
				padding: 10px;
			}
			.countdown-wrapper .countdown-section {
			font-family: 'Lucida Grande', Lucida, Verdana, sans-serif;
			  color: #000;
			  display: inline-block;
			  float: left;
			  text-align: center;
			  margin: 0 4px;
			}
			/* line 146, /Users/joseph/Sites/unitedwayla/wp-content/themes/UnitedWay/css/less/bmia.less */
			.countdown-wrapper .countdown-section .countdown-amount {
  			  border-radius: 4px;
  			  background: #000;
  			  background-image: -webkit-linear-gradient(top, #000 0%, #686868 50%, #000 50%, #535050 100%);
   			  background-image: -moz-linear-gradient(top, #000 0%, #686868 50%, #000 50%, #535050 100%);
   			  background-image: linear-gradient(top, #000 0%, #686868 50%, #000 50%, #535050 100%);
  			  color: #fff;
			  font-size: 44px;
			  text-align: center;
			  display: block;
			  line-height: 46px;
			  padding: 10px 20px;
			}
			/* line 151, /Users/joseph/Sites/unitedwayla/wp-content/themes/UnitedWay/css/less/bmia.less */
			.countdown-wrapper .countdown-section .countdown-period {
			  display: block;
			  font-size: 16px;
			  font-weight: bold;
			  text-transform: uppercase;
			  padding-top: 8px;
			}
			.countdown-wrapper {
				width: 420px;
				margin: 0 auto;
			}
			</style>
			<div style="" class="countdown-wrapper">
				<script>
				jQuery(document).ready(function() {
					jQuery('#defaultCountdown').countdown( {
						until: '<?php echo $days. ', '.$hours.', '.$minutes. ', '.$seconds; ?>',
						padZeroes: true
					});

				});
				</script>
				<div id="defaultCountdown"></div>
			</div><!--end countdown-wrapper -->
		<?php
		}
		return ob_get_clean();
}
add_shortcode("countdown_timer", "ot_countdown_timer");
// end shortcode
?>
