<?php


class Jetcharter_Settings
{

	public static function add_settings_page()
	{
		add_submenu_page( 'edit.php?post_type=destinations', 'Jet Charters - Settings', 'Settings', 'manage_options', 'jetcharters', array('Jetcharter_Settings', 'settings_page') );
	}
	public static function settings_page()
		 { 
		?><div class="wrap">
		<form action='options.php' method='post'>
			
			<h2><?php esc_html(_e("Jet Charters", "jetcharters")); ?></h2>	
			<?php
			settings_fields( 'jc_settings' );
			do_settings_sections( 'jc_settings' );
			submit_button();
			?>			
		</form>
		
		<?php
	}
	
	public static function settings_init(  ) { 

		//setting, id, sanitize
		register_setting( 'jc_settings', 'mapbox_token', array('Jetcharter_Settings', 'sanitize_mapbox_token'));
		register_setting( 'jc_settings', 'mapbox_map_id', array('Jetcharter_Settings', 'sanitize_mapbox_map_id'));
		register_setting( 'jc_settings', 'mapbox_js_url', array('Jetcharter_Settings', 'sanitize_mapbox_js_url'));
		register_setting( 'jc_settings', 'mapbox_css_url', array('Jetcharter_Settings', 'sanitize_mapbox_css_url'));
		register_setting( 'jc_settings', 'mapbox_map_zoom', array('Jetcharter_Settings', 'sanitize_mapbox_map_zoom'));
		register_setting( 'jc_settings', 'mapbox_base_lat', array('Jetcharter_Settings', 'sanitize_mapbox_base_lat'));
		register_setting( 'jc_settings', 'mapbox_base_lon', array('Jetcharter_Settings', 'sanitize_mapbox_base_lon'));
		
		//algolia
		register_setting( 'jc_settings', 'algolia_token', array('Jetcharter_Settings', 'sanitize_algolia_token'));
		register_setting( 'jc_settings', 'algolia_index', array('Jetcharter_Settings', 'sanitize_algolia_index'));
		register_setting( 'jc_settings', 'algolia_id', array('Jetcharter_Settings', 'sanitize_algolia_id'));		
		register_setting( 'jc_settings', 'jet_webhook', array('Jetcharter_Settings', 'sanitize_jet_webhook'));		


		add_settings_section(
			'jc_settings-section', 
			esc_html(__( 'General Settings', 'jetcharters' )), 
			'', 
			'jc_settings'
		);

		add_settings_field( 
			'text_field_jetcharters_0', 
			esc_html(__( 'Mapbox Token', 'jetcharters' )), 
			array('Jetcharter_Settings', 'text_field_jetcharters_0_render'), 
			'jc_settings', 
			'jc_settings-section' 
		);
		add_settings_field( 
			'text_field_jetcharters_1', 
			esc_html(__( 'Map ID', 'jetcharters' )), 
			array('Jetcharter_Settings', 'text_field_jetcharters_1_render'), 
			'jc_settings', 
			'jc_settings-section' 
		);
		add_settings_field( 
			'text_field_jetcharters_2', 
			esc_html(__( 'Mapbox JS URL', 'jetcharters' )), 
			array('Jetcharter_Settings', 'text_field_jetcharters_2_render'), 
			'jc_settings', 
			'jc_settings-section' 
		);
		add_settings_field( 
			'text_field_jetcharters_3', 
			esc_html(__( 'Mapbox CSS URL', 'jetcharters' )), 
			array('Jetcharter_Settings', 'text_field_jetcharters_3_render'), 
			'jc_settings', 
			'jc_settings-section' 
		);
		add_settings_field( 
			'text_field_jetcharters_4', 
			esc_html(__( 'Mapbox Map Zoom', 'jetcharters' )), 
			array('Jetcharter_Settings', 'text_field_jetcharters_4_render'), 
			'jc_settings', 
			'jc_settings-section' 
		);
		add_settings_field( 
			'text_field_jetcharters_5', 
			esc_html(__( 'Base Latitud', 'jetcharters' )), 
			array('Jetcharter_Settings', 'text_field_jetcharters_5_render'), 
			'jc_settings', 
			'jc_settings-section' 
		);
		add_settings_field( 
			'text_field_jetcharters_6', 
			esc_html(__( 'Base Longitud', 'jetcharters' )), 
			array('Jetcharter_Settings', 'text_field_jetcharters_6_render'), 
			'jc_settings', 
			'jc_settings-section' 
		);	


		add_settings_field( 
			'text_field_jetcharters_8', 
			esc_html(__( 'Algolia Api Key', 'jetcharters' )), 
			array('Jetcharter_Settings', 'text_field_jetcharters_8_render'), 
			'jc_settings', 
			'jc_settings-section' 
		);

		add_settings_field( 
			'text_field_jetcharters_9', 
			esc_html(__( 'Algolia Index Name', 'jetcharters' )), 
			array('Jetcharter_Settings', 'text_field_jetcharters_9_render'), 
			'jc_settings', 
			'jc_settings-section' 
		);		
		add_settings_field( 
			'text_field_jetcharters_10', 
			esc_html(__( 'Algolia Api Id', 'jetcharters' )), 
			array('Jetcharter_Settings', 'text_field_jetcharters_10_render'), 
			'jc_settings', 
			'jc_settings-section' 
		);	

		add_settings_field( 
			'text_field_jetcharters_11', 
			esc_html(__( 'Webhook', 'jetcharters' )), 
			array('Jetcharter_Settings', 'text_field_jetcharters_11_render'), 
			'jc_settings', 
			'jc_settings-section' 
		);			

		
	}

	public static function text_field_jetcharters_0_render(  ) { 
		$options = get_option( 'mapbox_token' );
		?>
		<input type='text' name='mapbox_token[text_field_jetcharters_0]' value='<?php echo esc_html($options['text_field_jetcharters_0']); ?>'>
		<?php
	}
	public static function text_field_jetcharters_1_render(  ) { 
		$options = get_option( 'mapbox_map_id' );
		?>
		<select type='text' name='mapbox_map_id[text_field_jetcharters_1]'>
			<option value="mapbox.streets" <?php selected( $options['text_field_jetcharters_1'], 'mapbox.streets' ); ?>>Streets</option>
			<option value="mapbox.light" <?php selected( $options['text_field_jetcharters_1'], 'mapbox.light' ); ?>>Light</option>
			<option value="mapbox.dark" <?php selected( $options['text_field_jetcharters_1'], 'mapbox.dark' ); ?>>Dark</option>
			<option value="mapbox.outdoors" <?php selected( $options['text_field_jetcharters_1'], 'mapbox.outdoors' ); ?>>Outdoors</option>
		</select>
		<?php
	}
	public static function text_field_jetcharters_2_render(  ) { 
		$options = get_option( 'mapbox_js_url' );
		?>
		<input type='url' name='mapbox_js_url[text_field_jetcharters_2]' value='<?php echo esc_html($options['text_field_jetcharters_2']); ?>'>

		<?php
	}
	public static function text_field_jetcharters_3_render(  ) { 
		$options = get_option( 'mapbox_css_url' );
		?>
		<input type='url' name='mapbox_css_url[text_field_jetcharters_3]' value='<?php echo esc_html($options['text_field_jetcharters_3']); ?>'>
		<?php
	}
	public static function text_field_jetcharters_4_render(  ) { 
		$options = get_option( 'mapbox_map_zoom' );
		?>
		<input maxlength="3" size="3" type='number' name='mapbox_map_zoom[text_field_jetcharters_4]' value='<?php echo esc_html($options['text_field_jetcharters_4']); ?>'>
		<?php
	}
	public static function text_field_jetcharters_5_render(  ) { 
		$options = get_option( 'mapbox_base_lat' );
		?>
		<input type='text' name='mapbox_base_lat[text_field_jetcharters_5]' value='<?php echo esc_html($options['text_field_jetcharters_5']); ?>'>
		<?php
	}
	public static function text_field_jetcharters_6_render(  ) { 
		$options = get_option( 'mapbox_base_lon' );
		?>
		<input type='text' name='mapbox_base_lon[text_field_jetcharters_6]' value='<?php echo esc_html($options['text_field_jetcharters_6']); ?>'>
		<?php
	}

	public static function text_field_jetcharters_8_render(  ) { 
		$options = get_option( 'algolia_token' );
		?>
		<input type='text' name='algolia_token[text_field_jetcharters_8]' value='<?php echo esc_html($options['text_field_jetcharters_8']); ?>'>
		<?php
	}	
	public static function text_field_jetcharters_9_render(  ) { 
		$options = get_option( 'algolia_index' );
		?>
		<input type='text' name='algolia_index[text_field_jetcharters_9]' value='<?php echo esc_html($options['text_field_jetcharters_9']); ?>'>
		<?php
	}
	public static function text_field_jetcharters_10_render(  ) { 
		$options = get_option( 'algolia_id' );
		?>
		<input type='text' name='algolia_id[text_field_jetcharters_10]' value='<?php echo esc_html($options['text_field_jetcharters_10']); ?>'>
		<?php
	}
	public static function text_field_jetcharters_11_render(  ) { 
		$options = get_option( 'jet_webhook' );
		?>
		<input type='text' name='jet_webhook[text_field_jetcharters_11]' value='<?php echo esc_html($options['text_field_jetcharters_11']); ?>'>
		<?php
	}	
	public static function sanitize_mapbox_token( $input ) {
		$valid = array();
		$valid['text_field_jetcharters_0'] = sanitize_text_field( $input['text_field_jetcharters_0'] );
		return $valid;
	}	
	public static function sanitize_mapbox_map_id( $input ) {
		$valid = array();
		$valid['text_field_jetcharters_1'] = sanitize_text_field( $input['text_field_jetcharters_1']);
		return $valid;
	}	
	public static function sanitize_mapbox_js_url( $input ) {
		$valid = array();
		$valid['text_field_jetcharters_2'] = esc_url(sanitize_text_field( $input['text_field_jetcharters_2'] ));
		return $valid;
	}
	public static function sanitize_mapbox_css_url( $input ) {
		$valid = array();
		$valid['text_field_jetcharters_3'] = esc_url(sanitize_text_field( $input['text_field_jetcharters_3'] ));
		return $valid;
	}
	public static function sanitize_mapbox_map_zoom( $input ) {
		$valid = array();
		$valid['text_field_jetcharters_4'] = intval(sanitize_text_field( $input['text_field_jetcharters_4'] ));
		if($valid['text_field_jetcharters_4'] < 1 || $valid['text_field_jetcharters_4'] > 22)
		{
			$valid['text_field_jetcharters_4'] = 6;
		}
		return $valid;
	}	
	public static function sanitize_mapbox_base_lat( $input ) {
		$valid = array();
		$valid['text_field_jetcharters_5'] = sanitize_text_field( $input['text_field_jetcharters_5'] );
		return $valid;
	}
	public static function sanitize_mapbox_base_lon( $input ) {
		$valid = array();
		$valid['text_field_jetcharters_6'] = sanitize_text_field( $input['text_field_jetcharters_6'] );
		return $valid;
	}

	public static function sanitize_algolia_token( $input ) {
		$valid = array();
		$valid['text_field_jetcharters_8'] = sanitize_text_field( $input['text_field_jetcharters_8'] );
		return $valid;
	}		

	public static function sanitize_algolia_index( $input ) {
		$valid = array();
		$valid['text_field_jetcharters_9'] = sanitize_text_field( $input['text_field_jetcharters_9'] );
		return $valid;
	}

	public static function sanitize_algolia_id( $input ) {
		$valid = array();
		$valid['text_field_jetcharters_10'] = sanitize_text_field( $input['text_field_jetcharters_10'] );
		return $valid;
	}	
	public static function sanitize_jet_webhook($input)
	{
		$valid = array();
		$valid['text_field_jetcharters_11'] = esc_url(sanitize_text_field( $input['text_field_jetcharters_11']));
		return $valid;		
	}

}

?>