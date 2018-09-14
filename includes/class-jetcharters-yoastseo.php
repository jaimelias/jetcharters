<?php


class Jetcharters_YoastSEO_Fix
{
	
	public static function wpseo_exclude( $tag )
	{
		global $post;
		
		if(get_query_var( 'fly' ) || get_query_var( 'cacheimg' ) || is_singular('destinations') || is_singular('jet'))
		{
			$tag = false;
		}
		return $tag;
	}
	
	public static function yoast_fixes()
	{
		add_filter('wpseo_canonical', array('Jetcharters_YoastSEO_Fix', 'wpseo_exclude'));		
		add_filter('wpseo_title', array('Jetcharters_YoastSEO_Fix', 'wpseo_exclude'));		
		add_filter('wpseo_metadesc', array('Jetcharters_YoastSEO_Fix', 'wpseo_exclude'));		
		add_filter('wpseo_author_link', array('Jetcharters_YoastSEO_Fix', 'wpseo_exclude'));			
		add_filter('wpseo_author_link', array('Jetcharters_YoastSEO_Fix', 'wpseo_exclude'));		
		add_filter('wpseo_locale', array('Jetcharters_YoastSEO_Fix', 'wpseo_exclude'));

		
		//open graph
		add_filter('wpseo_opengraph_title', array('Jetcharters_YoastSEO_Fix', 'wpseo_exclude'));		
		add_filter('wpseo_opengraph_url', array('Jetcharters_YoastSEO_Fix', 'wpseo_exclude'));		
		add_filter('wpseo_opengraph_site_name', array('Jetcharters_YoastSEO_Fix', 'wpseo_exclude'));		
		add_filter('wpseo_opengraph_type', array('Jetcharters_YoastSEO_Fix', 'wpseo_exclude'));		
		add_filter('wpseo_opengraph_image', array('Jetcharters_YoastSEO_Fix', 'wpseo_exclude'));		
		add_filter('wpseo_opengraph_image_size', array('Jetcharters_YoastSEO_Fix', 'wpseo_exclude'));
		add_filter('wpseo_opengraph_desc', array('Jetcharters_YoastSEO_Fix', 'wpseo_exclude'));
		
		add_filter('wpseo_prev_rel_link', array('Jetcharters_YoastSEO_Fix', 'wpseo_exclude'));		
		add_filter('wpseo_next_rel_link', array('Jetcharters_YoastSEO_Fix', 'wpseo_exclude'));

		//image
		add_filter('wpseo_xml_sitemap_img_src', array('Jetcharters_YoastSEO_Fix', 'wpseo_exclude'));
		
		//twitter
		add_filter('wpseo_twitter_card_type', array('Jetcharters_YoastSEO_Fix', 'wpseo_exclude'));
		add_filter('wpseo_twitter_image', array('Jetcharters_YoastSEO_Fix', 'wpseo_exclude'));
		add_filter('wpseo_twitter_description', array('Jetcharters_YoastSEO_Fix', 'wpseo_exclude'));		
	}	
}

