<?php

/**
 * Post Type: Documents
 */

function simply_register_documents() {

	
	$labels = array(
		"name" => __( "Documents", "twentysixteen" ),
		"singular_name" => __( "Document", "twentysixteen" ),
	);

	$args = array(
		"label" => __( "Documents", "twentysixteen" ),
		"labels" => $labels,
		"description" => "",
		"public" => true,
		"publicly_queryable" => true,
		"show_ui" => true,
		"show_in_rest" => false,
		"rest_base" => "",
		"has_archive" => false,
		"show_in_menu" => true,
		"show_in_nav_menus" => true,
		"exclude_from_search" => false,
		"capability_type" => "post",
		"map_meta_cap" => true,
		"hierarchical" => false,
		"rewrite" => array( "slug" => "simply_documents", "with_front" => false ),
		"query_var" => true,
		"menu_icon" => "dashicons-portfolio",
		"supports" => array( "title" ),
	);

	register_post_type( "simply_documents", $args );
}

add_action( 'init', 'simply_register_documents' );

/* !ACF - Document Fields */
	
if(function_exists("register_field_group"))
{
	register_field_group(array (
		'id' => 'acf_document-information',
		'title' => 'Document Information',
		'fields' => array (
			array (
				'key' => 'field_5b8c62da212b4',
				'label' => 'Document Settings',
				'name' => '',
				'type' => 'tab',
				'conditional_logic' => array (
					'status' => 1,
					'rules' => array (
						array (
							'field' => 'field_5b8c64b1a8045',
							'operator' => '!=',
							'value' => '',
						),
					),
					'allorany' => 'all',
				),
			),
			
			array (
							'key' => 'field_5b8c64f94f2c0',
							'label' => 'Document Type',
							'name' => 'simply_document_type',
							'type' => 'post_object',
							'instructions' => 'You can modify the Document Types in Simply Settings - Document Types',
							'post_type' => array (
								0 => 'simply_document_type',
							),
							'taxonomy' => array (
								0 => 'all',
							),
							'allow_null' => 1,
							'multiple' => 0,
						),
			
			array (
				'key' => 'field_5b8c5a9af4a8b',
				'label' => 'Privacy Level',
				'name' => 'simply_document_privacy',
				'type' => 'select',
				'instructions' => 'Select who can view this document.',
				'required' => 1,
				'choices' => array (
					'' => '-- Select --',
					'public' => 'Public',
					'private' => 'Private',
				),
				'default_value' => '',
				'allow_null' => 0,
				'multiple' => 0,
			),
			array (
				'key' => 'field_5b8c6105eb3e2',
				'label' => 'Document Files',
				'name' => '',
				'type' => 'tab',
				'conditional_logic' => array (
					'status' => 1,
					'rules' => array (
						array (
							'field' => 'field_5b8c64b1a8045',
							'operator' => '!=',
							'value' => '',
						),
					),
					'allorany' => 'all',
				),
			),
			array (
				'key' => 'field_5b8c612deb3e3',
				'label' => 'PDF Document',
				'name' => 'simply_document_pdf',
				'type' => 'file',
				'required' => 1,
				'save_format' => 'url',
				'library' => 'all',
				'mime_types' => 'pdf',
			),
			array (
				'key' => 'field_5b8c615deb3e4',
				'label' => 'Word Document',
				'name' => 'simply_document_doc',
				'type' => 'file',
				'instructions' => 'Optional: Upload a word document',
				'save_format' => 'url',
				'library' => 'all',
			),
			array (
				'key' => 'field_5b8c6181eb3e5',
				'label' => 'Excel Document',
				'name' => 'simply_document_xls',
				'type' => 'file',
				'instructions' => 'Optional: Upload an xls spreadsheet document',
				'save_format' => 'url',
				'library' => 'all',
			),
			array (
				'key' => 'field_5b8c61a3eb3e6',
				'label' => 'Document Translation',
				'name' => 'simply_document_translation',
				'type' => 'file',
				'instructions' => 'Optional: Upload a pdf translation of the document',
				'save_format' => 'url',
				'library' => 'all',
			),
		),
		'location' => array (
			array (
				array (
					'param' => 'post_type',
					'operator' => '==',
					'value' => 'simply_documents',
					'order_no' => 0,
					'group_no' => 0,
				),
			),
		),
		'options' => array (
			'position' => 'normal',
			'layout' => 'no_box',
			'hide_on_screen' => array (
				0 => 'permalink',
				1 => 'the_content',
				2 => 'excerpt',
				3 => 'custom_fields',
				4 => 'discussion',
				5 => 'comments',
				6 => 'revisions',
				7 => 'slug',
				8 => 'author',
				9 => 'format',
				10 => 'featured_image',
				11 => 'categories',
				12 => 'tags',
				13 => 'send-trackbacks',
			),
		),
		'menu_order' => 0,
	));
}

?>