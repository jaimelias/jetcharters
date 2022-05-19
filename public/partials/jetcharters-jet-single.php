<?php

$table = '<table class="text-center pure-table small pure-table-striped">';
$labels = array(__('Type', 'jetcharters'),
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
$keys = array('jet_type',
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

for($x = 0; $x < count($keys); $x++)
{
	$key = $keys[$x];
	$value = Charterflights_Meta_Box::jet_get_meta($key);
	
	if($value)
	{
		if($key == 'jet_type')
		{
			$value = Jetcharters_Public::jet_type($value);
		}
		else if($key == 'jet_range')
		{
			$value = $value.__('nm', 'jetcharters').' | '.round(intval($value)*1.15078).__('mi', 'jetcharters').' | '.round(intval($value)*1.852).__('km', 'jetcharters');
		}
		else if($key == 'jet_cruise_speed')
		{
			$value = $value.__('kn', 'jetcharters').' | '.round(intval($value)*1.15078).__('mph', 'jetcharters').' | '.round(intval($value)*1.852).__('kph', 'jetcharters');			
		}
		else if($key == 'jet_max_altitude')
		{
			$value = $value.__('ft', 'jetcharters').' | '.round(intval($value)*0.3048).__('m', 'jetcharters');
		}
		else if($key == 'jet_base_iata')
		{
			$value = Charterflights_Meta_Box::jet_get_meta('jet_base_name');
		}
		
		$table .= '<tr>';
		$table .= '<td><span class="semibold">'.esc_html($labels[$x]).'</span></td>';
		$table .= '<td>'.esc_html($value).'</td>';
		$table .= '</tr>';			
	}
}

$table .= '</table>';

global $post;

?>


<div class="pure-g gutters">
	<div class="pure-u-1 pure-u-md-2-3">
		<?php if(has_post_thumbnail() && empty($content)): ?>
			<p><?php the_post_thumbnail('medium', array('class' => 'img-responsive')); ?></p>
		<?php else: ?>
			<?php echo $content; ?>
		<?php endif;?>
		</div>
	<div class="pure-u-1 pure-u-md-1-3"><?php echo $table; ?></div>
</div>

<hr/>

<?php echo Jetcharters_Public::get_destination_table(Charterflights_Meta_Box::jet_get_meta('jet_base_iata')); ?>


<h2><?php esc_html_e(__('Instant Quotes', 'jetcharters')); ?></h2>
<div class="bottom-20">
	<?php echo Jetcharters_Public::price_calculator(); ?>
</div>




