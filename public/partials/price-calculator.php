<form class="jet_calculator" method="get" action="<?php echo esc_url(home_lang().'/instant_quote/'); ?>">


		<div class="bottom-20"><label><i class="linkcolor fas fa-map-marker"></i> <?php esc_html(_e('Origin', 'jetcharters')); ?></label>
		<input type="text" id="jet_origin" name="jet_origin" class="jet_list" spellcheck="false" placeholder="<?php esc_html(_e('country / city / airport', 'jetcharters')); ?>" /></div>

		
		<div class="bottom-20"><label><i class="linkcolor fas fa-map-marker"></i> <?php esc_html(_e('Destination', 'jetcharters')); ?></label>	
		<input type="text" id="jet_destination" name="jet_destination" class="jet_list" spellcheck="false" placeholder="<?php esc_html(_e('country / city / airport', 'jetcharters')); ?>" /></div>
		
		
		<div class="pure-g gutters">
			<div class="pure-u-1 pure-u-sm-1-2 pure-u-md-1-2">
				<div class="bottom-20">
					<label><i class="linkcolor fas fa-male"></i> <?php esc_html(_e('Passengers', 'jetcharters')); ?></label>
				<input type="number" min="1" name="jet_pax" id="jet_pax"/>
				</div>
			</div>
			<div class="pure-u-1 pure-u-sm-1-2 pure-u-md-1-2">
				<div class="bottom-20">
					<label><i class="linkcolor fas fa-plane"></i> <?php esc_html(_e('Flight', 'jetcharters')); ?></label>
					<select name="jet_flight" id="jet_flight">
						<option value="0"><?php esc_html(_e('One way', 'jetcharters')); ?></option>
						<option value="1"><?php esc_html(_e('Round trip', 'jetcharters')); ?></option>
					</select>
				</div>
			</div>
		</div>	
		
		<div class="pure-g gutters">
			<div class="pure-u-1 pure-u-sm-1-2 pure-u-md-1-2">
				<div class="bottom-20">
					<label><i class="linkcolor fas fa-calendar-alt"></i> <?php esc_html(_e('Date of Departure', 'jetcharters')); ?></label><input type="text" class="datepicker" name="jet_departure_date" id="jet_departure_date"/>
				</div>
			</div>
			<div class="pure-u-1 pure-u-sm-1-2 pure-u-md-1-2">
				<div class="bottom-20">
					<label><i class="linkcolor fas fa-clock"></i> <?php esc_html(_e('Hour of Departure', 'jetcharters')); ?></label><input placeholder="<?php esc_html(_e('Local Time', 'jetcharters')); ?>" type="text" class="timepicker" name="jet_departure_hour" id="jet_departure_hour"/>
				</div>
			</div>
		</div>
		
		<div class="jet_return">
			<div class="pure-g gutters">
				<div class="pure-u-1 pure-u-sm-1-2 pure-u-md-1-2">
					<div class="bottom-20">
						<label><i class="linkcolor fas fa-calendar-alt"></i> <?php esc_html(_e('Date of Return', 'jetcharters')); ?></label><input type="text" class="datepicker" name="jet_return_date" id="jet_return_date"/>
					</div>
				</div>
				<div class="pure-u-1 pure-u-sm-1-2 pure-u-md-1-2">
					<div class="bottom-20">
						<label><i class="linkcolor fas fa-clock"></i> <?php esc_html(_e('Hour of Return', 'jetcharters')); ?></label><input placeholder="<?php esc_html(_e('Local Time', 'jetcharters')); ?>" type="text" class="timepicker" name="jet_return_hour" id="jet_return_hour"/>
					</div>
				</div>
			</div>	
		</div>

<div class="text-center bottom-20"><button data-callback="validate_jet_form" data-badge="bottomleft" data-sitekey="<?php echo esc_html(get_option('captcha_site_key')); ?>" id="jet_submit" class="g-recaptcha strong uppercase pure-button pure-button-primary" type="button"><i class="fa fa-search" aria-hidden="true"></i> <?php esc_html(_e('Find Aircrafts', 'jetcharters')); ?></button></div>

<div class="text-center"><small class="text-muted">Powered by</small> <img style="vertical-align: middle;" width="57" height="18" alt="algolia" src="<?php echo esc_url(plugin_dir_url(__DIR__) . 'img/algolia.svg'); ?>"/></div>
		
</form> 






