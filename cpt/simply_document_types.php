<?php
	
/**
 * Post Type: Document Types
 */

function simply_register_document_type() {

	$labels = array(
		"name" => __( "Document Types", "twentysixteen" ),
		"singular_name" => __( "Document Type", "twentysixteen" ),
	);

	$args = array(
		"label" => __( "Document Types", "twentysixteen" ),
		"labels" => $labels,
		"description" => "",
		"public" => false,
		"publicly_queryable" => true,
		"show_ui" => true,
		"show_in_rest" => false,
		"rest_base" => "",
		"has_archive" => false,
		"show_in_menu" => false,
		"show_in_nav_menus" => false,
		"exclude_from_search" => true,
		"capability_type" => "post",
		"map_meta_cap" => true,
		"hierarchical" => false,
		"rewrite" => array( "slug" => "simply_document_type", "with_front" => true ),
		"query_var" => true,
		"supports" => array( "title" ),
	);

	register_post_type( "simply_document_type", $args );
}

add_action( 'init', 'simply_register_document_type' );

?>