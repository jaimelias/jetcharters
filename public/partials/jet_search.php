<?php 

$capacity_query = array();
$capacity_args = array();
$capacity_args['key'] = 'jet_passengers';
$capacity_args['value'] = intval(sanitize_text_field($_GET['jet_pax']));
$capacity_args['type'] = 'numeric';
$capacity_args['compare'] = '>=';
$aircraft_count = 0;
$not_available = '<p class="large">'.esc_html(__('The requested quote is not available in our website yet. Please contact our sales team for an immediate answer.', 'jetcharters'));

if(get_theme_mod('min_tel') != null)
{
	$not_available .= ' '.esc_html(__('Call us at our 24/7 phone number', 'jetcharters')).' <strong><a href="tel:'.esc_html(get_theme_mod('min_tel')).'">'.esc_html(get_theme_mod('min_tel')).'</a></strong>';
	$not_available .= '.';
}

$not_available .= '</p>';
 
array_push($capacity_query, $capacity_args);

$args_jet_search = array(
	'post_type' => 'jet',
	'posts_per_page' => 200,
	'meta_query' => $capacity_query,
	'meta_key' => 'jet_commercial',
	'orderby' => 'meta_value',
	'order' => 'ASC'
	);
	
$wp_jet_search = new WP_Query( $args_jet_search );

if ($wp_jet_search->have_posts())
{
	$output = null;
	$legs = __('One Way', 'jetcharters');
	
	if(intval($_GET['jet_flight']))
	{
		$legs = __('Round Trip', 'jetcharters');
	}

	$jet_departure_date = strtotime(sanitize_text_field($_GET['jet_departure_date']));
	$depdate_format = date_i18n(get_option( 'date_format' ), $jet_departure_date);	
	$departure_flight = esc_html(sanitize_text_field($_GET['jet_origin']));
	$departure_flight .= '-';
	$departure_flight .= esc_html(sanitize_text_field($_GET['jet_destination']));
	$departure_flight .= ' '.esc_html(__('on', 'jetcharters')).' ';
	$departure_flight .= esc_html($depdate_format);
	$departure_flight .= ' '.esc_html(__('at', 'jetcharters')).' ';
	$departure_flight .= esc_html(sanitize_text_field($_GET['jet_departure_hour']));
	$return_flight = null;
	
	if(intval($_GET['jet_flight']) == 1)
	{
		$retdate_format = date_i18n(get_option( 'date_format' ), strtotime(sanitize_text_field($_GET['jet_return_date'])));
		$return_flight = esc_html(sanitize_text_field($_GET['jet_destination']));
		$return_flight .= '-';
		$return_flight .= esc_html(sanitize_text_field($_GET['jet_origin']));
		$return_flight .= ' '.esc_html(__('on', 'jetcharters')).' ';
		$return_flight .= esc_html($retdate_format);
		$return_flight .= ' '.esc_html(__('at', 'jetcharters')).' ';
		$return_flight .= esc_html(sanitize_text_field($_GET['jet_return_hour']));		
	}
	
	$request = '<h3>'.esc_html(__('Departure', 'jetcharters')).': <span class="linkcolor">';
	$request .= $departure_flight;
	
	if(intval($_GET['jet_flight']) == 1) 
	{
		$request .= '</span></h3><h3>'.esc_html(__('Return', 'jetcharters')).': <span class="linkcolor"> '.$return_flight;
	}
	
	$request .= '</span>';	
	$request .= '</h3>';
	$request .= '<h3>'.esc_html(__('Passengers', 'jetcharters')).': <span class="linkcolor">'.esc_html(sanitize_text_field($_GET['jet_pax'])).'</span></h3>';
	$table = null;
	
	while ($wp_jet_search->have_posts())
	{
		$wp_jet_search->the_post();
		global $post;
		$aircraft_url = home_lang().esc_html($post->post_type).'/'.esc_html($post->post_name);
		$table_price = Charterflights_Meta_Box::jet_get_meta( 'jet_rates' );
		$table_price = json_decode(html_entity_decode($table_price), true);
		
		for($x = 0; $x < count($table_price); $x++)
		{
			if(($_GET['jet_origin'] == $table_price[$x][0] && $_GET['jet_destination'] == $table_price[$x][1]) || ($_GET['jet_origin'] == $table_price[$x][1] && $_GET['jet_destination'] == $table_price[$x][0]))
			{
				$duration = $table_price[$x][2];
				$price = $table_price[$x][3];
				
				if(Jetcharters_Public::is_commercial() || Jetcharters_Public::ferry() || Jetcharters_Public::ground())
				{
					$price = floatval($price)*floatval(sanitize_text_field($_GET['jet_pax']));
				}

				$flight_array = array();
				$aircraft_count++;
				$fees = floatval($table_price[$x][4]);


				if(intval($_GET['jet_flight']) == 1)
				{
					$price = floatval($price) * 2;
					$fees = $fees * 2;
				}
				
				
				$seats = $table_price[$x][6];
				$weight_pounds = $table_price[$x][7];
				$weight_kg = intval(intval($weight_pounds)*0.453592);
				$weight_allowed = esc_html($weight_pounds.' '.__('pounds', 'jetcharters').' | '.$weight_kg.__('kg', 'jetcharters'));
				$flight_array['aircraft_price'] = floatval($price) + (floatval($fees) * floatval($_GET['jet_pax']));
				$flight_array['aircraft_name'] = esc_html($post->post_title);
				$flight_array['aircraft_id'] = intval(esc_html($post->ID));
				$flight_array['aircraft_seats'] = intval(esc_html($seats));
				$flight_array['aircraft_weight'] = esc_html($weight_allowed);
				
				$flight_desc = '';
				
				if(Charterflights_Meta_Box::jet_get_meta( 'jet_commercial' ) != 0)
				{
					if(Jetcharters_Public::is_commercial())
					{
						$flight_desc .= '<strong>'.esc_html(__('Commercial Flight', 'jetcharters')).'</strong>';
					}
					else if(Jetcharters_Public::ferry())
					{
						$flight_desc .= '<strong>'.esc_html(__('Ferry', 'jetcharters')).'</strong>';
					}
					else if(Jetcharters_Public::ground())
					{
						$flight_desc .= '<strong>'.esc_html(__('Ground Transport', 'jetcharters')).'</strong>';
					}
					
					$price_row = '<td><small class="text-muted">USD</small><br/><strong class="large">'.esc_html('$'.number_format($price, 0, '.', ',')).'</strong><br /><span class="small text-muted">'.esc_html('$'.number_format(($price / floatval(sanitize_text_field($_GET['jet_pax']))), 0, '.', ',')).' '.esc_html(__('Per Person', 'jetcharters')).'</span>';
					
					if(floatval($fees) > 0)
					{
						
						
						
						$price_row .= '<br/><span class="text-muted">'.__('Fees per pers.', 'jetcharters').' $'.number_format($fees, 0, '.', ',').'</span>';
					}					
					
					$price_row .= '</td>';
				}
				else
				{
					$flight_desc .= '<a class="strong" href="'.esc_url($aircraft_url).'">'.esc_html($post->post_title).'</a>';
					$flight_desc .= '<br/>';
					$flight_desc .= '<small>'.esc_html(Jetcharters_Public::jet_type(Charterflights_Meta_Box::jet_get_meta( 'jet_type' ))).'</small>';
					$flight_desc .= ' <strong><i class="fas fa-male" aria-hidden="true"></i> '.esc_html($seats).'</strong>';					
					$flight_desc .= '<br/>';
					$flight_desc .= '<small>'.esc_html('Max').' ('.$weight_allowed.')</small>';
					$price_row = '<td><small class="text-muted">USD</small><br/><strong class="large">'.esc_html('$'.number_format($price, 0, '.', ',')).'</strong>';
					
					if(floatval($fees) > 0)
					{
						$price_row .= '<br/><span class="text-muted">'.__('Fees per pers.', 'jetcharters').' $'.number_format($fees, 0, '.', ',').'</span>';
					}					
					
					$price_row .= '</td>';
				}			
				
				$row = '<tr>';
				$row .= '<td>'.$flight_desc.'</td>';
				
				if(!wp_is_mobile())
				{
					$row .= '<td><i class="fas fa-clock" aria-hidden="true"></i> '.esc_html(Jetcharters_Public::convertTime($duration)).'</td>';
				}
				
				$row .= $price_row;
				$row .= '<td>';
				
				$select_label = __('Quote', 'jetcharters');	
				$row .= '<button class="strong button-success pure-button" data-aircraft="'.esc_html(htmlentities(json_encode($flight_array))).'"><i class="fas fa-envelope" aria-hidden="true"></i> '.esc_html($select_label).'</button>';			
				$row .= '</td>';
				$row .= "</tr>";				
				$table .= $row;
			}
		}
		
		
	}
	wp_reset_postdata();
	
	if($aircraft_count == 0)
	{
		$table .= '<tr><td colspan="5">'.$not_available.'</td></tr>';
	}
	?>
	
	<hr/>
	<?php echo $request; ?>	
	<hr/>
		
	<table class="margin-bottom pure-table pure-table-bordered text-center instant_quote_table small">
		<thead>
			<tr>
				<th><?php echo esc_html(__('Flights', 'jetcharters')); ?></th>

				<?php if(!wp_is_mobile()): ?>
					<th><?php echo esc_html(__('Duration', 'jetcharters')); ?></th>
				<?php endif; ?>
				
				<th colspan="2"><?php echo esc_html($legs);?></th>
			</tr>
		</thead>
		<tbody>
			<?php echo $table; ?>
		</tbody>
	</table>
	
		<div id="jet_booking_container" class="hidden animate-fade">

			<form method="post" id="jet_booking_request" action="<?php echo esc_url(home_lang().'request_submitted');?>/">
			
			<div class="modal-header clearfix">
				<h3 class="pull-left inline-block text-center uppercase linkcolor"><?php echo esc_html(__('Request a Quote', 'jetcharters')); ?></h3>
				<span class="close pointer pull-right large"><i class="fas fa-times"></i></span>
			</div>				
		
					<div class="pure-g gutters">
						<div class="pure-u-1 pure-u-md-1-2">
							<div class="bottom-20">
								<label for="lead_name"><?php echo esc_html(__('Name', 'jetcharters')); ?></label>
								<input type="text" name="lead_name" />								
							</div>
						</div>
						<div class="pure-u-1 pure-u-md-1-2">
							<div class="bottom-20">
								<label for="lead_lastname"><?php echo esc_html(__('Last Name', 'jetcharters')); ?></label>
								<input type="text" name="lead_lastname" />			
							</div>
						</div>
					</div>
					<div class="pure-g gutters">
						<div class="pure-u-1 pure-u-md-1-2">
							<div class="bottom-20">
								<label for="lead_email"><?php echo esc_html(__('Email', 'jetcharters')); ?></label>
								<input type="email" name="lead_email" />								
							</div>
						</div>
						<div class="pure-u-1 pure-u-md-1-2">
							<div class="bottom-20">
								<label for="lead_phone"><?php echo esc_html(__('Phone', 'jetcharters')); ?></label>
								<input type="text" name="lead_phone" />								
							</div>
						</div>
					</div>
					<div class="pure-g gutters">
						<div class="pure-u-1 pure-u-md-1-2">
							<div class="bottom-20">
								<label for="country"><?php echo esc_html(__('Country', 'jetcharters')); ?></label>
								<select name="lead_country" class="countrylist"><option>--</option></select>								
							</div>
						</div>
						<div class="pure-u-1 pure-u-md-1-2">
							<div class="bottom-20">
								<label for="lead_children"><?php echo esc_html(__('Traveling With Children', 'jetcharters')); ?>?</label>
								<select name="lead_children">
									<option value="no"><?php echo esc_html(__('No', 'jetcharters')); ?></option>
									<option value="yes"><?php echo esc_html(__('Yes', 'jetcharters')); ?></option>
								</select>								
							</div>
						</div>
					</div>				
								
				<div class="hidden">
					<div id="aircraft_fields"></div>
					<input type="text"  name="channel" class="channel"/>
					<input type="text"  name="device" class="device"/>
					<input type="text"  name="landing_path" class="landing_path"/>
					<input type="text"  name="landing_domain" class="landing_domain"/>
					
					
					
					<input type="text" value="<?php echo esc_html(sanitize_text_field($_GET['jet_origin']));?>" name="jet_origin"/>
					<input type="text" value="<?php echo esc_html(sanitize_text_field($_GET['jet_destination']));?>" name="jet_destination"/>
					<input type="text" value="<?php echo esc_html(sanitize_text_field($_GET['jet_pax']));?>" name="jet_pax"/>
					<input type="text" value="<?php echo esc_html(sanitize_text_field($_GET['jet_departure_date']));?>" name="jet_departure_date"/>
					<input type="text" value="<?php echo esc_html(sanitize_text_field($_GET['jet_departure_hour']));?>" name="jet_departure_hour"/>
					<input type="text" value="<?php echo esc_html($departure_flight);?>" name="departure_itinerary"/>
					
					<input type="text" value="<?php echo esc_html(sanitize_text_field($_GET['jet_return_date']));?>" name="jet_return_date"/>	
					<input type="text" value="<?php echo esc_html(sanitize_text_field($_GET['jet_return_hour']));?>" name="jet_return_hour"/>	
					<input type="text" value="<?php echo esc_html($return_flight);?>" name="return_itinerary"/>
					
					<input type="text" value="<?php echo esc_html(sanitize_text_field($_GET['jet_origin_l']));?>" name="jet_origin_l"/>
					<input type="text" value="<?php echo esc_html(sanitize_text_field($_GET['jet_destination_l']));?>" name="jet_destination_l"/>
					
				</div>
				
			
			
				<?php if(get_option('captcha_site_key')): ?>
					<?php if(get_option('captcha_site_key') != null): ?>
						<button data-badge="bottomleft" data-callback="validate_request_quote" class="g-recaptcha pure-button pure-button-primary" id="jet_request" data-sitekey="<?php echo esc_html(get_option('captcha_site_key')); ?>"><i class="fas fa-plane"></i> <?php echo esc_html(__('Send Request', 'jetcharters'));?></button>	
					<?php endif; ?>
				<?php endif; ?>				
			
				
			</form>
		</div>

		
	<?php
	
}
else
{
	echo $not_available;	
}

?>