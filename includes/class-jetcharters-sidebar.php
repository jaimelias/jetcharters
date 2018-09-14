<?php

class JetCharters_SideBar
{
	public static function quote_sidebar() 
	{
		register_sidebar( array(
			'name' => __( 'Jet Quote Sidebar', 'jetcharters' ),
			'id' => 'quote-sidebar',
			'description' => __( 'Widgets in this area will be shown on all airports.', 'jetcharters' ),
			'before_widget' => '<li id="%1$s" class="widget %2$s">',
		'after_widget'  => '</li>',
		'before_title'  => '<h3>',
		'after_title'   => '</h3>',
		) );
	}
}

add_action( 'widgets_init', array('JetCharters_SideBar', 'quote_sidebar') );

?>