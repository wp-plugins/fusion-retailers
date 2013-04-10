<?php
/**
 * Created by christophermarslender
 */

add_action('save_post','fusion_save_retailer_post');
add_action('add_meta_boxes', 'fusion_add_admin_metaboxes');

function fusion_add_admin_metaboxes() {
	add_meta_box('retailer_details','Retailer Details','fusion_output_state_details_metaboxes','retailer','normal');
	add_meta_box('select_retailer_states','Select States','fusion_output_states_metaboxes','retailer','normal');
}

function fusion_save_retailer_post($post_id) {
	//dout($_POST,true);
	$slug = 'retailer';
	/* check whether anything should be done */
	$_POST += array("{$slug}_edit_nonce" => '');
	if ( $slug != $_POST['post_type'] ) {
		return;
	}
	if ( !current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	if ( !wp_verify_nonce( $_POST["{$slug}_edit_nonce"], plugin_basename( __FILE__ ) ) ) {
		//todo why doesnt this work???
		//dout('nonce doesnt verify!',true);
		//return;
	}

	$states = $_POST['fusion_states'];
	$metavalues = array();
	foreach ( $states as $state => $checkedvalue ) {
		$metavalues[] = $state;
	}
	$oldvalue = get_post_meta($post_id,'fusion_states',true);
	update_post_meta($post_id,'fusion_states',$metavalues,$oldvalue);
	$oldwebsite = get_post_meta($post_id,'fusion_retailer_website',true);
	$newurl = str_replace('http://','',$_POST['fusion-retailer-uri']);
	update_post_meta($post_id,'fusion_retailer_website',$newurl,$oldwebsite);
}

function fusion_output_state_details_metaboxes($post) {
	$url = get_post_meta($post->ID,'fusion_retailer_website',true);
	?>
	<div class="retailer-uri">
		<label for="retailer-uri-input">Retailer Website URL</label><br />
		http://<input value="<?php echo $url; ?>" type="text" name="fusion-retailer-uri" id="retailer-uri-input" />
	</div>
	<?php
}

function fusion_output_states_metaboxes($post) {
	$states = json_decode(file_get_contents(plugin_dir_path(__FILE__) . 'state_names.json'));
	$selected_states = get_post_meta($post->ID,'fusion_states',true);
	//dout($selected_states,true);
	echo "<div class='states column-1' style='width:50%; display:inline-block;'>";
	$count = 0;
	foreach ( $states as $state ) {
		$count++;
		if ( $count == 26 ) {
			echo '</div><div class="states column-2" style="width:50%; display:inline-block;">';
		}
		$selected = in_array($state,$selected_states) ? 'checked' : '';
		?>
		<div class="state-container">
			<input type="checkbox" name="fusion_states[<?php echo $state; ?>]" id="<?php echo $state; ?>_checkbox" <?php echo $selected; ?>>
			<label for="<?php echo $state; ?>_checkbox"><?php echo $state; ?></label>
		</div>
		<?php
	}
	echo "</div>";
}