<?php
/**
* Initializing class called "OTS_Framework"
*/
if (!class_exists('OTS_Framework')) {
	class OTS_Framework
	{
	
		function __construct() {	}

		/**
		* ots_text_input() accepts parameters as array
		* 'name', 'value', 'help', 'size';
		*/
		function ots_text_input( $args ) {
		    $name = esc_attr( $args['name'] );
		    $value = esc_attr( $args['value'] );
		    $size = $args['size'];
			$help = $args['help'];
			if ($size) {
				$size = $size;
			} else {
				$size = 60;
			}
		    echo "<input size='".$size."' type='text' name='$name' value='$value' />";
			if (!empty($help)) {
				echo '<br />'.$args['help'];
			}
		}
		/**
		 * ots_checkbox() accepts parameters as array
		* 'name', 'title', 'value', check, 'help';	
		 */
		function ots_checkbox( $args ) {
		    $name = esc_attr( $args['name'] ); // name of field
		    $value = esc_attr( $args['value'] ); // value of field
			$check = esc_attr($args['check']); // value to check against for checkbox functionality
			if($check == $value) $checked = ' checked="checked"';
			echo '<label for="'.$name.'">';
			echo "<input id='$name' type='checkbox' name='$name' value='$value' $checked />";
			echo ' '.$args['help'].'</label>';
		}

		/**
		* ots_cat_dropdown() function accepts parameters as array
		* 'name', 'title', 'value', 'help';
		*/
		function ots_cat_dropdown( $args ) {
			$name = esc_attr( $args['name'] );
			$title = esc_attr( $args['title']);
			$value = esc_attr( $args['value'] );
			$ddargs = array(
				'show_option_none' => 'Select Category for '.$title,
				'hide_empty' => 0,
				'name' => $name,
				'orderby' => 'name'
			);
			if ($value != '0') {
				$ddargs['selected'] = $value;
			}
			wp_dropdown_categories($ddargs);
			echo $args['help'];
		}
	
		/**
		* textarea() creates a textarea, accepts parameters as array
		* 'name', 'title', 'value', 'help', 'row', 'cols;
		*/
		function ots_textarea( $args ) {		
			$name = esc_attr( $args['name'] );
			$title = esc_attr( $args['title']);
			$value = $args['value'];
			$help = $args['help'];
			if ($args['rows']) {
				$rows = $args['rows'];
			} else {
				$rows = 8;
			}
			if ($args['cols']) {
				$cols = $args['cols'];
			} else {
				$cols = 50;
			}
			echo '<textarea name="'.$name.'" rows="'.$rows.'" cols="'.$cols.'">'.$value.'</textarea>';
			echo '<br />'.$help;
		}

		/**
		* ots_page_dropdown() creates a page dropdown select field accepts parameters as array
		* 'name', 'title', 'value', 'help';
		*/
		function ots_page_dropdown( $args ) {
			$name = esc_attr( $args['name'] );
			$title = esc_attr( $args['title']);
			$value = esc_attr( $args['value'] );
			$help = $args['help'];
			$arg = array(
				'option_none_value' => 0,
				'show_option_none' => $title,
				'name' => $name,
			);
			if ($value) {
				$arg['selected'] = $value;
			}
			wp_dropdown_pages($arg);
			echo '<br />'.$help;
		}
		/**
		 * ots_number() function accepts arguments as array:
		* 'name', 'title', 'value', 'help', default;
		 */
		function ots_number($args) {
			$name = esc_attr( $args['name'] );
			$title = esc_attr( $args['title']);
			$value = esc_attr( $args['value'] );
			$default = esc_attr( $args['default']);
			if ($value != '') {
				$value = esc_attr( $args['value'] );
			} else {
				$value = esc_attr($args['default']);
			}
			echo '<input type="number" name="'.$name.'" step="1" min="0" id="'.$name.'" value="'.$value.'" class="small-text">';
			echo ' '.$args['help'];
		}
		/**
		* ots_post_type_dropdown() creates a dropdown for any given post type, showing the posts associated with it, accepts parameters as arrays
		* 'name', 'title', 'value', 'post_type', 'help'
		*/
		function ots_post_type_dropdown($args) {
			$name = esc_attr( $args['name'] );
			$title = esc_attr( $args['title']);
			$value = esc_attr( $args['value'] );
			$post_type = esc_attr($args['post_type']);
			$help = $args['help'];
			if ($post_type == '') {
				echo "Hey -- you gotta specify a post type in the function silly";
			} else {
				$selects = get_posts('posts_per_page=-1&orderby=menu_order&order=ASC&post_type='.$post_type.'&post_status=publish');
				if(empty($selects)) {
					echo '<p>You must first add '.$post_type.' in order to set this option.</p>';
				} else {
					// if the set value is NONE
					if ($value != '') {
						$help = $args['help'];
						// and if there are no books
					} // end check to see if the given is blank ?>
					<select name="<?php echo $name; ?>" id="<?php echo $name; ?>">
						<?php
						// creating drop down for post type
						// if no posts are present, spits out the "title""" ?>
						<option value="0"<? if($value == '' or $value == '0') { echo ' selected="selected"'; } ?>><?php echo $title; ?></option>
						<?php
						foreach ($selects as $select) { ?>
							<option class="level-0" <? if($value == $select->ID) { echo 'selected="selected" '; } ?> value="<? echo $select->ID; ?>"><? echo $select->post_title; ?></option>
						<? } // end loop through books
						?>
					</select>
					<?php
					echo '<br />'.$help;
				}
			} // endif post_type
		} // end post_type_dropdown function
	
		/**
		 * ots_select() accepts an second dimension array for options, all other parameters are given in the single dimension of the array
		* 'name', 'title', 'value', '$options = array( value => title)', 'help'
		 */ 
		function ots_select($args) {
			$options = (array)$args['options'];
			$name = esc_attr( $args['name'] );
			$title = esc_attr( $args['title']);
			$value = esc_attr( $args['value'] );
			$help = $args['help'];
			// this is text for the selected class
			$selected = ' selected="selected"'; ?>
				<select name="<?php echo $name; ?>" id="<?php echo $name; ?>">
					<option value="0"<?php if($value == '' or $value == '0') { echo $selected; } ?>><?php echo $title; ?></option>
			<?php
			foreach ($options as $optval => $opttitle) { ?>
				<option value="<?php echo $optval; ?>"<?php if($value == $optval) { echo $selected; } ?>>
					<?php echo $opttitle; ?></option>
			<?php } // endforeach ?>
			</select>
			<?php
			if (!empty($help)) {
				echo '<br />'.$help;
			}
		} // end post_type_dropdown function	
	}
	$OTS_Framework = new OTS_Framework();
}