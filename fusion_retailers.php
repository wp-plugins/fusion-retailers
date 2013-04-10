<?php
/**
 * Plugin Name: Fusion Retailers
 * Author: Graphic Fusion Design
 * Author URI: http://graphicfusiondesign.com
 * Description: Plugin to manage and output retailers by region on a Google Map.
 * Version: 1.0
 */

if ( is_admin() ) {
	include('fusion_retailers_metaboxes.php');
	add_action('admin_menu','fusion_add_retailers_preference_page');
}

add_action('admin_enqueue_scripts','fusion_admin_enqueue_scripts');
add_action('wp_enqueue_scripts','fusion_enqueue_scripts_styles');
add_shortcode('fusion_retailers_map','fusion_output_state_map');
add_action( 'init', 'fusion_retailer_post_type' );
//add_action( 'init', 'fusion_import_retailers' );

if ( ! function_exists('dout') ) {
	function dout($data, $die = FALSE) {
		echo "<pre>";
		print_r($data);
		echo "</pre>";
		if ( $die ) {
			die();
		}
	}
}


function fusion_admin_enqueue_scripts() {
	wp_enqueue_style('wp-color-picker');
	wp_register_script('fusion-color-picker',plugin_dir_url(__FILE__) . 'js/fusion_color_picker.js',array('wp-color-picker'),FALSE,TRUE);
	wp_enqueue_script('fusion-color-picker');
}

function fusion_import_retailers() {
	if ( is_admin() ) {
		return;
	}
	$option = get_option('fusion_retailers_imported');
	if ( ! $option ) {
		global $wpdb;
		$prefix = $wpdb->prefix;
		$retailers_table = $prefix . 'fusion_retailers';
		$states_table = $prefix . 'fusion_states';
		$pivot = $prefix . 'fusion_retailer_state';
		$retailers = $wpdb->get_results("SELECT * FROM $retailers_table");
		foreach ( $retailers as $retailer ) {
			$data = array(
				'post_title' => $retailer->name,
				'post_type' => 'retailer',
				'post_status' => 'publish'
			);
			$post = wp_insert_post($data);
			$pivotqueries = $wpdb->get_results("SELECT * FROM $pivot WHERE retailer_id={$retailer->id}");
			$states = array();
			foreach ( $pivotqueries as $pivotquery ) {
				$state = $wpdb->get_results("SELECT * FROM $states_table WHERE id={$pivotquery->state_id}");
				$states[] = $state[0]->name;
			}
			update_post_meta($post,'fusion_states',$states);
		}
		add_option('fusion_retailers_imported','true');
	}
}

function fusion_retailer_post_type() {
	$labels = array(
		'name' => 'Retailers',
		'singular_name' => 'Retailer',
		'add_new' => 'Add New',
		'add_new_item' => 'Add New Retailer',
		'edit_item' => 'Edit Retailer',
		'new_item' => 'New Retailer',
		'all_items' => 'All Retailers',
		'view_item' => 'View Retailers',
		'search_items' => 'Search Retailers',
		'not_found' =>  'No retailers found',
		'not_found_in_trash' => 'No retailers found in Trash',
		'parent_item_colon' => '',
		'menu_name' => 'Retailers'
	);

	$args = array(
		'labels' => $labels,
		'public' => TRUE,
		'publicly_queryable' => TRUE,
		'show_ui' => TRUE,
		'show_in_menu' => TRUE,
		'query_var' => TRUE,
		'rewrite' => array( 'slug' => 'retailer' ),
		'capability_type' => 'post',
		'has_archive' => TRUE,
		'hierarchical' => FALSE,
		'menu_position' => NULL,
		'supports' => array( 'title' )
	);

	register_post_type( 'retailer', $args );
}

function fusion_add_retailers_preference_page() {
	add_submenu_page('edit.php?post_type=retailer','Retailer Settings','Retailer Settings','manage_options','retailer_settings_page','fusion_output_settings_page');
}

function fusion_setup_initial_state_options() {
	$raw_states = json_decode(file_get_contents(plugin_dir_path(__FILE__).'state_names.json'));
	$xml_states = simplexml_load_file(plugin_dir_path(__FILE__) . 'states.xml' );
	$states = array();
	foreach ( $raw_states as $state ) {
		$states[$state] = array(
			'name' => $state,
		);
	}
	foreach ( $xml_states as $xml_state ) {
		$xml_state = (array)$xml_state;
		$name = $xml_state['@attributes']['name'];
		$color = $xml_state['@attributes']['colour'];
		$states[$name]['color'] = $color;
	}
	add_option('fusion_states_settings',$states);
	return $states;
}

function fusion_get_settings() {
	$states = get_option('fusion_states_settings');
	if ( ! $states ) {
		$states = fusion_setup_initial_state_options();
	}
	return $states;
}

/**
 * Called after the settings page is submitted
 */
function fusion_update_settings() {
	//dout($_POST);
	$states = $_POST['states'];
	$settings = fusion_get_settings();
	//dout($states);
	foreach ( $states as $state => $data ) {
		$settings[$state]['color'] = $data['color'];
	}
	update_option('fusion_states_settings',$settings);
}


function fusion_output_settings_page() {
	if ( !empty($_POST) ) {
		if ( ! wp_verify_nonce($_POST['fusion_update_settings_nonce'],'fusion_update_settings') ) {
			die('error validating nonce');
		}
		fusion_update_settings();
	}
	$states = fusion_get_settings();
	echo '<div class="wrap">';
	echo '<form method="POST">';
	echo '<div class="icon32" id="icon-options-general"></div><h2>Retailer Settings</h2>';
	echo '<div class="state-settings"><h4>State Settings</h4>';
	$count = 0;
	echo '<div class="column" style="width:20%; display:inline-block;">';
	foreach ( $states as $state ) {
		$count++;
		if ( $count == 11 || $count == 21 || $count == 31 || $count == 41 ) {
			echo '</div><div class="column" style="width:20%; display:inline-block;">';
		}
		?>
		<div class="single-state-settings">
			<label for="<?php echo $state['name']; ?>-color-input"><?php echo $state['name']; ?></label><br />
			<input type="text" value="<?php echo $state['color']; ?>" class="fusion-color-picker" name="states[<?php echo $state['name']; ?>][color]" />
		</div>
		<?php
	}
	echo "</div>";//.column
	echo "</div>";//.state-settings
	wp_nonce_field('fusion_update_settings','fusion_update_settings_nonce');
	?>
	<input type="submit" value="Submit" class="button button-primary button-large">
	</form>
	<?php
	echo "</div>";//.wrap

}

function get_retailers() {
	$args = array(
		'post_type' => 'retailer',
		'post_status' => 'publish',
		'orderby' => 'title',
		'order' => 'asc',
		'nopaging' => TRUE
	);
	$query = new WP_Query($args);
	global $post;
	$retailers_array = array();
	if ( $query->have_posts() ) {
		while ( $query->have_posts() ) {
			$query->the_post();
			//dout($post);
			$meta = get_post_meta($post->ID,'fusion_states',TRUE);
			//dout($post->post_title);
			//dout($meta);
			$link = get_post_meta($post->ID,'fusion_retailer_website',TRUE);
			foreach ( $meta as $state ) {
				if ( $link ) {
					$data = '<a href="http://'.$link.'">'.$post->post_title.'</a>';
				} else {
					$data = $post->post_title;
				}
				$retailers_array[$state][] = $data;
			}
		}
	}

	wp_reset_query();
	return $retailers_array;

}


function fusion_enqueue_scripts_styles() {
	wp_register_script('google_maps', 'https://maps.googleapis.com/maps/api/js?sensor=false');
	wp_register_script('fusion_maps', plugin_dir_url(__FILE__) . '/js/fusion_maps.js', array('jquery','google_maps'),'1.0.0',TRUE);
	wp_enqueue_script('google_maps');
	wp_enqueue_script('fusion_maps');

	wp_register_style('fusion_retailers', plugin_dir_url(__FILE__) . '/css/fusion_retailers.css');
	wp_enqueue_style('fusion_retailers');

	$retailers = get_retailers();
	$returndata = array();
	foreach ( $retailers as $state => $retailer ) {
		$content = '<h4>'.$state.'</h4>';
		$content .= '<ul class="retailer-list">';
		foreach($retailer as $single) {
			$content .= '<li>'.$single.'</li>';
		}
		$content .= '</ul>';
		$returndata[$state] = $content;
	}
	$settings = fusion_get_settings();
	$colors = array();
	foreach ( $settings as $setting ) {
		$colors[$setting['name']] = $setting['color'];
	}
	$vars = array(
		'plugins_dir' => plugin_dir_url(__FILE__),
		'states_xml' => plugin_dir_url(__FILE__) . 'states.xml',
		'state_info' => $returndata,
		'colors' => $colors
	);
	wp_localize_script('fusion_maps','fusion_maps_vars',$vars);
}


function fusion_output_state_map() {
	return '<div id="fusion_retailers_map"></div>';
}
