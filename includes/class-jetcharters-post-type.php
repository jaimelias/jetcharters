<?php


class Charterflights_Post_Type
{
	// Register Custom Destination
	
	function fly_post_type() {

		$labels = array(
			'name'  => __( 'Destinations', 'Destination General Name', 'jetcharters' ),
			'singular_name' => __( 'Destination', 'Destination Singular Name', 'jetcharters' ),
			'menu_name' => __( 'Destinations', 'jetcharters' ),
			'name_admin_bar'  => __( 'Destination', 'jetcharters' ),
			'archives'  => __( 'Item Archives', 'jetcharters' ),
			'parent_item_colon' => __( 'Parent Item:', 'jetcharters' ),
			'all_items' => __( 'All Items', 'jetcharters' ),
			'add_new_item'  => __( 'Add New Item', 'jetcharters' ),
			'add_new' => __( 'Add New', 'jetcharters' ),
			'new_item'  => __( 'New Item', 'jetcharters' ),
			'edit_item' => __( 'Edit Item', 'jetcharters' ),
			'update_item' => __( 'Update Item', 'jetcharters' ),
			'view_item' => __( 'View Item', 'jetcharters' ),
			'search_items'  => __( 'Search Item', 'jetcharters' ),
			'not_found' => __( 'Not found', 'jetcharters' ),
			'not_found_in_trash'  => __( 'Not found in Trash', 'jetcharters' ),
			'featured_image'  => __( 'Featured Image', 'jetcharters' ),
			'set_featured_image'  => __( 'Set featured image', 'jetcharters' ),
			'remove_featured_image' => __( 'Remove featured image', 'jetcharters' ),
			'use_featured_image'  => __( 'Use as featured image', 'jetcharters' ),
			'insert_into_item'  => __( 'Insert into item', 'jetcharters' ),
			'uploaded_to_this_item' => __( 'Uploaded to this item', 'jetcharters' ),
			'items_list'  => __( 'Items list', 'jetcharters' ),
			'items_list_navigation' => __( 'Items list navigation', 'jetcharters' ),
			'filter_items_list' => __( 'Filter items list', 'jetcharters' ),
		);
		$args = array(
			'label' => __( 'Destination', 'jetcharters' ),
			'labels' => $labels,
			'supports' => array( ),
			'hierarchical' => false,
			'public' => true,
			'show_ui' => true,
			'show_in_menu' => true,
			'menu_position' => 5,
			'show_in_admin_bar' => true,
			'show_in_nav_menus' => true,
			'can_export' => true,
			'has_archive' => true,		
			'exclude_from_search' => true,
			'publicly_queryable' => false,
			'capability_type' => 'page',
			'show_in_rest' => true,
		);
		register_post_type( 'destinations', $args );

	}
		
	
	// Register Custom Destination
	function jet_post_type() {

		$labels = array(
			'name'  => __( 'Jets', 'Destination General Name', 'jetcharters' ),
			'singular_name' => __( 'Jet', 'Destination Singular Name', 'jetcharters' ),
			'menu_name' => __( 'Jets', 'jetcharters' ),
			'name_admin_bar'  => __( 'Jets', 'jetcharters' ),
			'parent_item_colon' => __( 'Parent Item:', 'jetcharters' ),
			'all_items' => __( 'All Items', 'jetcharters' ),
			'add_new_item'  => __( 'Add New Item', 'jetcharters' ),
			'add_new' => __( 'Add New', 'jetcharters' ),
			'new_item'  => __( 'New Item', 'jetcharters' ),
			'edit_item' => __( 'Edit Item', 'jetcharters' ),
			'update_item' => __( 'Update Item', 'jetcharters' ),
			'view_item' => __( 'View Item', 'jetcharters' ),
			'search_items'  => __( 'Search Item', 'jetcharters' ),
			'not_found' => __( 'Not found', 'jetcharters' ),
			'not_found_in_trash'  => __( 'Not found in Trash', 'jetcharters' ),
			'items_list'  => __( 'Items list', 'jetcharters' ),
			'items_list_navigation' => __( 'Items list navigation', 'jetcharters' ),
			'filter_items_list' => __( 'Filter items list', 'jetcharters' )
		);
		$args = array(
			'label' => __( 'Jet', 'jetcharters' ),
			'labels'  => $labels,
			'supports'  => array( 'title', 'editor', 'thumbnail', 'revisions', ),
			'hierarchical'  => false,
			'public'  => true,
			'show_ui' => true,
			'show_in_menu'  => true,
			'menu_position' => 5,
			'show_in_admin_bar' => true,
			'show_in_nav_menus' => true,
			'can_export'  => true,
			'has_archive' => true,		
			'exclude_from_search' => false,
			'publicly_queryable'  => true,
			'capability_type' => 'page',
			'show_in_rest' => true,
		);
		register_post_type( 'jet', $args );

	}
		

}

?>