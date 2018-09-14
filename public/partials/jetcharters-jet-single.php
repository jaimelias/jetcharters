<?php

$table_html = '<table class="text-center pure-table pure-table-striped">';
$table_label = array(__('Type', 'jetcharters'),
	 __('Manufacturer', 'jetcharters'),
	 __('Model', 'jetcharters'),
	 __('Year of Construction', 'jetcharters'),
	 __('Passengers', 'jetcharters'),
	 __('Range', 'jetcharters'),
	 __('Cruise Speed', 'jetcharters'),
	 __('Max Altitude', 'jetcharters'),
	 __('Takeoff Field', 'jetcharters'),
	 __('Base Airport', 'jetcharters'),
	 __('Base Location', 'jetcharters')
	 );
$table_id = array('jet_type',
	 'jet_manufacturer',
	 'jet_model',
	 'jet_year_of_construction',
	 'jet_passengers',
	 'jet_range',
	 'jet_cruise_speed',
	 'jet_max_altitude',
	 'jet_takeoff_field',
	 'jet_base_iata',
	 'jet_base_city'
	 );

for($x = 0; $x < count($table_id); $x++)
{
	if(Charterflights_Meta_Box::jet_get_meta($table_id[$x]))
	{
		$value = Charterflights_Meta_Box::jet_get_meta($table_id[$x]);
		
		if($table_id[$x] == 'jet_type')
		{
			$value = Jetcharters_Public::jet_type($value);
		}
		else if($table_id[$x] == 'jet_range')
		{
			$value = $value.__('nm', 'jetcharters').' | '.round(intval($value)*1.15078).__('mi', 'jetcharters').' | '.round(intval($value)*1.852).__('km', 'jetcharters');
		}
		else if($table_id[$x] == 'jet_cruise_speed')
		{
			$value = $value.__('kn', 'jetcharters').' | '.round(intval($value)*1.15078).__('mph', 'jetcharters').' | '.round(intval($value)*1.852).__('kph', 'jetcharters');			
		}
		else if($table_id[$x] == 'jet_max_altitude')
		{
			$value = $value.__('ft', 'jetcharters').' | '.round(intval($value)*0.3048).__('m', 'jetcharters');
		}
		else if($table_id[$x] == 'jet_base_iata')
		{
			$airport_name = Charterflights_Meta_Box::jet_get_meta('jet_base_name');
			$value = '<a href="'.esc_url(home_lang()).'fly/'.Jetcharters_Public::cleanURL($airport_name).'/" >'.$value.' | '.$airport_name.'</a>';
		}
		
		$table_html .= '<tr>';
		$table_html .= '<td><strong>'.$table_label[$x].'</strong></td>';
		$table_html .= '<td>'.$value.'</td>';
		$table_html .= '</tr>';			
	}
}

$table_html .= '</table>';

global $post;

?>


<div class="pure-g gutters">
	<div class="pure-u-1 pure-u-md-1-2">
		<?php if(has_post_thumbnail()): ?>
			<?php the_post_thumbnail('large', array('class' => 'pure-img')); ?>
		<?php endif;?>
		<?php echo $content; ?>
		</div>
	<div class="pure-u-1 pure-u-md-1-2"><?php echo $table_html; ?></div>
</div>

<h2><?php echo esc_html(__('Instant Quotes', 'jetcharters')); ?></h2>
<?php echo Jetcharters_Public::get_destination_table(Charterflights_Meta_Box::jet_get_meta('jet_base_iata')); ?>



<?php if ( is_active_sidebar( 'quote-sidebar' ) ) { ?>
	<ul id="quote-sidebar">
		<?php dynamic_sidebar( 'quote-sidebar' ); ?>
	</ul>
<?php } ?>