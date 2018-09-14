<?php
$args19 = array('post_type' => 'jet','posts_per_page' => 50);	
$wp_query19 = new WP_Query( $args19 );

//pluggin settings at admin/class-travelpackages/settings.php
$jet_grid_cols = get_option('jet_grid_cols');
$jet_grid_cols = esc_html($jet_grid_cols['text_field_jetcharters_7']);

if(!get_option('jet_grid_cols'))
{
	$jet_grid_cols = '1';
}

?>
<div class="min_grid">
<div class="pure-g gutters">
<?php if ( $wp_query19->have_posts() ) :?>
	<?php $count=0;?>
	<?php while ( $wp_query19->have_posts() ) : $wp_query19->the_post(); ?>
		<?php
			global $post; 
			$jet_url = home_lang().esc_html($post->post_type).'/'.esc_html($post->post_name);
		?>
		
		
		<div class="pure-u-1 pure-u-sm-1-1 pure-u-md-1-<?php echo $jet_grid_cols; ?>">
		
		<div class="text-right"><?php echo esc_html(_e('Maximum', 'jetcharters')); ?> <?php echo esc_html(Charterflights_Meta_Box::jet_get_meta('jet_passengers')); ?> <i class="fa fa-male"></i></div>
		
		<?php if(has_post_thumbnail()): ?>
		<div class="min_thumbnail"><a href="<?php echo esc_url($jet_url); ?>"><?php the_post_thumbnail('thumbnail', array('class' => 'pure-img')); ?></a></div>
		<?php endif;?>	
		
		<div class="min_title pure-g">
			<div class="pure-u-4-5"><h3><a href="<?php echo esc_url($jet_url); ?>"><?php esc_html(the_title()); ?></a></h3></div>
			<div class="pure-u-1-5"><a class="min_more pull-right" href="<?php echo esc_url($jet_url); ?>"><i class="fa fa-chevron-right"></i></a></div>
		</div>
		 
		</div><!-- .col -->
		
		<?php $count++; ?>
		
		<?php if ($count == $jet_grid_cols || $wp_query19->found_posts==0  ) : ?>
			</div><div class="pure-g gutters">
			<?php $count=0; ?>
		<?php endif; ?>
		
	<?php endwhile; wp_reset_postdata(); ?>
		</div><!-- .pure-g -->	
	<?php else: ?>
	
	<div class="pure-u-1-1"><p><?php _e("No jets found", "packagebuilder"); ?>.</p></div></div>
	
<?php endif; ?>
</div><!-- .min_grid -->