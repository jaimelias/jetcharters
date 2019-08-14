<?php
global $jet_attr;
$args = array('post_type' => 'jet','posts_per_page' => 50);
$args['meta_query'] = array();

$only_charters = array();
$only_charters['key'] = 'jet_commercial'; 
$only_charters['value'] = 0;
$only_charters['compare'] = '=';
array_push($args['meta_query'], $only_charters);

if(is_array($jet_attr))
{
	if(array_key_exists('type', $jet_attr))
	{
		if($jet_attr['type'] != null)
		{
			$aircraft = array();
			
			
			
			if($jet_attr['type'] == 'helicopter' || $jet_attr['type'] == 'helicopters')
			{
				//helicopters
				$aircraft['key'] = 'jet_type';
				$aircraft['compare'] = '=';
				$aircraft['value'] = 5;	
			}

			else if($jet_attr['type'] == 'turboprop' || $jet_attr['type'] == 'turbo' || $jet_attr['type'] == 'prop')
			{
				$aircraft['key'] = 'jet_type';
				$aircraft['compare'] = '=';
				$aircraft['value'] = 0;	
			}
			else if($jet_attr['type'] == 'jet' || $jet_attr['type'] == 'jets')
			{
				//helicopters
				$jet_relation = array('relation' => 'OR');
				$jets = array(1,2,3,4);
				
				for($x = 0; $x < count($jets); $x++)
				{
					array_push($jet_relation, array('value' => $jets[$x], 'compare' => '=', 'key' => 'jet_type'));
				}
				
				$aircraft = $jet_relation;	
			}			

			array_push($args['meta_query'], $aircraft);
		}

	}
	
}

$jet_query = new WP_Query( $args );

?>
<div class="min_grid">
<div class="pure-g gutters">
<?php if ( $jet_query->have_posts() ) :?>
	<?php $count=0;?>
	<?php while ( $jet_query->have_posts() ) : $jet_query->the_post(); ?>
		<?php
			global $post; 
			$jet_url = home_lang().esc_html($post->post_type).'/'.esc_html($post->post_name);
		?>
		
		
		<div class="pure-u-1 pure-u-sm-1-1 pure-u-md-1-3 bottom-40">
			<div class="dy_jet padding-10">
				<div class="text-right small text-muted bottom-10"><?php echo esc_html(Charterflights_Meta_Box::jet_get_meta('jet_passengers')); ?> <i class="fa fa-male"></i></div>
				
				<?php if(has_post_thumbnail()): ?>
				<div class="min_thumbnail"><a href="<?php echo esc_url($jet_url); ?>"><?php the_post_thumbnail('thumbnail', array('class' => 'img-responsive')); ?></a></div>
				<?php endif;?>	
				
				<div class="min_title pure-g">
					<div class="pure-u-4-5"><h3><a href="<?php echo esc_url($jet_url); ?>"><?php esc_html(the_title()); ?></a></h3></div>
					<div class="pure-u-1-5"><a class="min_more pull-right" href="<?php echo esc_url($jet_url); ?>"><i class="fa fa-chevron-right"></i></a></div>
				</div>
			</div>
		</div><!-- .col -->
		
		<?php $count++; ?>
		
		<?php if ($count == 3 || $jet_query->found_posts==0  ) : ?>
			</div><div class="pure-g gutters">
			<?php $count=0; ?>
		<?php endif; ?>
		
	<?php endwhile; wp_reset_postdata(); ?>
		</div><!-- .pure-g -->	
	<?php else: ?>
	
	<div class="pure-u-1-1"><p><?php _e("No jets found", "packagebuilder"); ?>.</p></div></div>
	
<?php endif; ?>
</div><!-- .min_grid -->