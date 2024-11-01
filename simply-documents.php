<?php
	
/*
Plugin Name: Simply Documents
Plugin URI: https://developer.javahat.com/
Description: A lightweight plugin for WordPress that uses shortcode to customize where and when documents are listed. Easily upload documents and post them to the appropriate document type. Display documents in a table format on any page.
Version: 1.0
Author: Tammy Craik @ Template Design
Author URI: https://javahat.com
License: GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: simply-documents
*/

/* !0. TABLE OF CONTENTS */

/*
	
	1. HOOKS
		1.1 - register custom admin column headers
		1.2 - register custom admin column data
		1.3 - Advanced Custom Fields Settings
		1.4 - register our custom menus
		1.5 - registers all our custom shortcodes on init
		1.6 - load external files to public website
		1.7 - load external files to private website
	
	2. SHORTCODES
		2.1 - simply_register_shortcodes()
		2.2 - simply_documents_shortcode()
		
	3. FILTERS
		3.1 - simply_documents_column_headers()
		3.2 - simply_documents_column_data()
		3.3 - simply_document_type_column_headers()
		3.4 - simply_document_type_column_data()
		3.5 - simply_admin_menus()
		
	4. EXTERNAL SCRIPTS
		4.1 - include ACF
		4.2 - simply_public_scripts()
		4.3 - simply_admin_scripts()
		
	5. HELPERS
		
	6. CUSTOM POST TYPES
		6.1 - documents
		6.2 - document_types
	
	7. ADMIN PAGES
		1.1 - simply_dashboard_admin_page()

*/




/* !1. HOOKS */

// 1.1
// hint: register custom admin column headers
add_filter('manage_edit-simply_documents_columns','simply_documents_column_headers');
add_filter('manage_edit-simply_document_type_columns','simply_document_type_column_headers');

// 1.2
// hint: register custom admin column data
add_filter('manage_simply_documents_posts_custom_column','simply_documents_column_data',1,2);
add_filter('manage_simply_document_type_posts_custom_column','simply_document_type_column_data',1,2);
//add_action('admin_head-edit.php', 'simply_register_custom_admin_titles');

// 1.3
// hint: Advanced Custom Fields Settings
add_filter('acf/settings/path', 'slb_acf_settings_path');
add_filter('acf/settings/dir', 'slb_acf_settings_dir');
add_filter('acf/settings/show_admin', 'slb_acf_show_admin');
//if( !defined('ACF_LITE') ) define('ACF_LITE',true); // turn off ACF plugin menu

// 1.4
// hint: register our custom menus
add_action('admin_menu', 'simply_admin_menus');

// 1.5
// hint: registers all our custom shortcodes on init
add_action('init', 'simply_register_shortcodes');

// 1.6
// load external files to public website
add_action('wp_enqueue_scripts', 'simply_public_scripts');

// 1.7
// load external files to private website
add_action('admin_enqueue_scripts', 'simply_admin_scripts');


/* !2. SHORTCODES */

// 2.1
// hint: registers all our custom shortcodes
function simply_register_shortcodes() 
	{
	add_shortcode('simply_documents', 'simply_documents_shortcode');
	}
	
// 2.2
// hint: displays Documents in tables
// example: [simply_documents]
function simply_documents_shortcode( $args, $content="" ) 
	{	
	global $post;
	
	$output = '';
	
	// get the list id
	$simply_document_type = '';
	if( isset($args['type']) ) $simply_document_type = (string)$args['type'];
	
	// get title
	$simply_document_table_title = '';
	
	if( isset($args['title']) ) : 
		$simply_document_table_title = (string)$args['title'];
		if ( $simply_document_table_title == 'none') : $simply_document_table_title =''; endif;
	endif;
	
	// if no title is set, get the Document Type
	if( !isset($args['title']) && isset($args['type']) ) :
		$post_object = (string)$args['type'];
		if( $post_object ):
			// override $post
			global $post;
			$post = $post_object;
			setup_postdata( $post );
			$simply_document_table_title .= get_the_title();
			//$output .= $post_object->ID; 
			wp_reset_postdata();
		endif;
	endif;
	
	// Determining New Documents
	// Documents posted within the time specified below are considered 'new'
	$days_ago = '-1 week +1 day'; // example -1 week +1 day 
	$date = get_the_date();
	$new_date = date('Y-m-d', strtotime($days_ago) );
	$new_date_formatted = date('F d, Y', strtotime($days_ago) );

	// Get base url for internal links
	$url = get_site_url();

	// if user can read private posts, query all posts other than 'minutes'
	if ( current_user_can('read_private_posts') )
		{
		$args = array(
			'post_type' => 'simply_documents',
			'post_status' => 'publish',
			'meta_key' => 'simply_document_type',
			'meta_value' => $simply_document_type,
			'orderby' => 'simply_document_type date title',
			'order' => 'DESC',
			'posts_per_page' => -1
			);
		}
	
	// if user can only read public posts, query all public posts
	else 
		{
		
		$args = array(
			'numberposts' => -1,
			'post_type' => 'simply_documents',
			'meta_query'	=> array(
					'relation'		=> 'AND',
					array(
						'key'		=> 'simply_document_type',
						'value'		=> $simply_document_type,
						'compare'	=> 'LIKE'
					),
					array(
						'key'		=> 'simply_document_privacy',
						'value'		=> 'public',
						'compare'	=> 'LIKE'
					)
				),
			'orderby' => 'document_type date title',
			'order' => 'DESC',
			'posts_per_page' => -1
		);
		}

		$simply_document_posts = get_posts($args); 
		
		// Begin writing table content
		
		// open the div and table
		$output .= '
			<div class="document-section">
		';
		
		// add a title if attribute passed through shortcode
		if ( $simply_document_table_title != ''):
			$output .= '<h2 class="table-title">' . $simply_document_table_title . '</h2>';
		endif;
		
		// include table headers
		$output .= '
			<table class="document-list" title="' . $simply_document_table_title . '">
				<thead>
					<tr style="background-color: #000; color: #fff">
						<th class="doc-title">Document Title</th>
						<th scope="col">Printable Files</th>
						<th scope="col">Posted On</th>
					</tr>
				</thead>
				<tbody>';
			
		// If no documents to display
		if (! $simply_document_posts ) :
			$output .= '
				<tr><td colspan="3">Sorry, there are no documents posted at this time.</td></tr>';
		endif; 
		
		
		// If documents are found
		if ( $simply_document_posts ) :
		
			// for each document posting found
			foreach($simply_document_posts as $post)
				{
			
				setup_postdata( $post ); 
						
				$title = get_the_title();				
				$add_pdf = get_field('simply_document_pdf');
				$add_doc = get_field('simply_document_doc');
				$add_xls = get_field('simply_document_xls');
				$add_translation = get_field('simply_document_translation');
				$posted_on = get_the_date( 'M d, Y' );
				$last_revised = get_the_modified_date( 'M d, Y' );
				$author = get_the_author();
			
				// Format the posted date to compare withe the 'new date' specified above
				$posted = get_the_date();
				$posted_new = new DateTime($posted);
				$posted_date = $posted_new->format('Y-m-d');
				
				// Create Document File List
				$document_files = '';
				
				if ( $add_pdf ):
					$document_files .= ' <a href="' . $add_pdf . '"><img src="' . plugins_url( 'images/icon-pdf-en.png', __FILE__ ) . '" width="25" height="25" alt="icon for English PDF - ' . $title . '"></a> ';
				endif;
				
				if ( $add_translation ):
					$document_files .= ' <a href="' . $add_translation . '"><img src="' . plugins_url( 'images/icon-pdf-ch.png', __FILE__ ) . '" width="25" height="25" alt="icon for Chinese translation PDF - ' . $title . '"></a> ';
				endif;
				
				if ( $add_doc ):
					$document_files .= '<a href="' . $add_doc . '"><img src="' . plugins_url( 'images/icon-doc.png', __FILE__ ) . '" width="25" height="25" alt="icon for DOC - ' . $title . '"></a> ';
				endif;
				
				if ( $add_xls ):
					$document_files .= '<a href="' . $add_xls . '"><img src="' . plugins_url( 'images/icon-xls.png', __FILE__ ) . '" width="25" height="25" alt="icon for XLS - ' . $title . '"></a>';
				endif;
			
				// Begin first clumn of row with document Title
				$output .= '<tr><td scope="row">' . $title;
				
				// Add NEW after title if document has been recenty posted
				if ( $new_date <= $posted_date ) :
					$output .= ' <span class="new">«NEW»</span>';
				endif;
				
				//End column and begin next one.
				$output .= ' </td>
					<td>' . $document_files . '</td>
					<td>' . $posted_on . '</td>
					</tr>';	
				} // end foreach
			
			wp_reset_postdata(); 
			
			endif;
		$output .= '</tbody></table></div>';
	
	// return our results/html
	return $output; 
			
	}
		

/* !3. FILTERS */


// 3.1
function simply_documents_column_headers( $columns ) {
	
	// creating custom column header data
	$columns = array(
		'cb'=>'<input type="checkbox" />',
		'title'=>__('Title'),
		'document_type'=>__('Document Type'),
		'privacy'=>__('Privacy Level'),
		'files'=>__('Documents'),
		'author'=>__('Author'),
		'date'=>__('Date Posted'),	
	);
	
	// returning new columns
	return $columns;
	
}

// 3.2
function simply_documents_column_data( $column, $post_id ) {
	
	// setup our return text
	$output = '';
	
	switch( $column ) {
		
		case 'title':
			// get the title
			$title = get_field('title', $post_id );
			$output .= $title;
			break;
		case 'document_type':
			// get the custom document_type
			$post_object = get_field('simply_document_type');
				if( $post_object ):
					// override $post
					global $post;
					$post = $post_object;
					setup_postdata( $post );
					$output .= get_the_title();
					//$output .= $post_object->ID; 
					wp_reset_postdata();
				endif;
			
			//$document_type =  esc_attr__(get_field('simply_document_type', $post_id)); // secure data from ssl injection
			//$document_type = ucwords(str_replace("_", " ", $document_type));            // format data
			//$output .= $document_type;
			//$output .= "Test";
			break;
		case 'privacy':
			// get the custom privacy level
			$privacy = esc_attr__(get_field('simply_document_privacy', $post_id ));
			$privacy = ucwords(str_replace("_", " ", $privacy));
			$output .= $privacy;
			break;
		case 'files':
			// get the custom pdf document
			
			$document_files = '';
			
			$pdf = get_field('simply_document_pdf', $post_id ); 
			$doc = get_field('simply_document_doc', $post_id );
			$xls = get_field('simply_document_xls', $post_id );
			$translation = get_field('simply_document_translation', $post_id );
			
			if ($pdf) : 
				$document_files .= '<a href="' . $pdf . '"><img class="icon" width="25" height="25" src="' . plugins_url( 'images/icon-pdf-en.png', __FILE__ ) . '" title="Download PDF file"></a> ';
			endif;
			if ($doc) : 
				$document_files .= '<a href="' . $doc . '"><img class="icon" width="25" height="25" src="' . plugins_url( 'images/icon-doc.png', __FILE__ ) . '" title="Download MS Word File"></a> ';
			endif;
			if ($xls) :
				$document_files .= '<a href="' . $xls . '"><img class="icon" width="25" height="25" src="' . plugins_url( 'images/icon-xls.png', __FILE__ ) . '" title="Download MS Exel file"></a> ';			
			endif;
			if ($translation) :
				$document_files .= '<a href="' . $translation . '"><img class="icon" width="25" height="25" src="' . plugins_url( 'images/icon-pdf-ch.png', __FILE__ ) . '" title="Download Chinese translation file"></a> ';
			endif;
			
			$output .= $document_files;
			break;
		case 'author':
			// get the custom privacy level
			$author = get_field('the_author', $post_id );
			$output .= $author;
			break;
		case 'date':
			// get the custom privacy level
			$date = get_field('the_date', $post_id );
			$output .= $date;
			break;
		
		
	}
	
	// echo the output
	echo $output;
	
}

// 3.3
function simply_document_type_column_headers( $columns ) {
	
	// creating custom column header data
	$columns = array(
		'cb'=>'<input type="checkbox" />',
		'title'=>__('Document Type'),	
		'shortcode'=>__('Shortcode'),	
	);
	
	// returning new columns
	return $columns;
	
}

// 3.4
function simply_document_type_column_data( $column, $post_id ) {
	
	// setup our return text
	$output = '';
	
	switch( $column ) {
		
		case 'shortcode':
			$output .= '[simply_documents type="'. $post_id .'"]';
			break;
		
	}
	
	// echo the output
	echo $output;
	
}

// 3.5
// hint: registers custom plugin admin menus
function simply_admin_menus() {
	
	/* main menu */
	
		$top_menu_item = 'simply_dashboard_admin_page';
	    
	    add_menu_page( '', 'Simply Settings', 'manage_options', 'simply_dashboard_admin_page', 'simply_dashboard_admin_page', 'dashicons-book' );
    
    /* submenu items */
    
	    // dashboard
	    add_submenu_page( $top_menu_item, '', 'Dashboard', 'manage_options', $top_menu_item, $top_menu_item );
	    
	    // document types
	    add_submenu_page( $top_menu_item, '', 'Document Types', 'manage_options', 'edit.php?post_type=simply_document_type' );

}
		
	
/* !4. EXTERNAL SCRIPTS */

// 4.1
// Include ACF
include_once( plugin_dir_path( __FILE__ ) .'lib/advanced-custom-fields/acf.php' );

// 4.2
// hint: loads external files into PUBLIC website
function simply_public_scripts() {
	
	// register scripts with WordPress's internal library
	wp_register_style('simply-documents-css-public', plugins_url('css/public/simply-documents.css',__FILE__));
	
	// add to que of scripts that get loaded into every page
	wp_enqueue_style('simply-documents-css-public');
	
}

// 4.3
// hint: loads external files into wordpress ADMIN
function simply_admin_scripts() {
	
	// register scripts with WordPress's internal library
	wp_register_style('simply-documents-css-private', plugins_url('css/private/simply-documents.css',__FILE__));
	
	// add to que of scripts that get loaded into every page
	wp_enqueue_style('simply-documents-css-private');
}
		

/* !5. HELPERS */

// hint: retrieves documents from a document_type
function simply_get_document_id( $simply_document_type ) {
	
	// if user can read private posts, query all posts other than 'minutes'
	if ( current_user_can('read_private_posts') ) 
		{
		$args = array(
			'post_type' => 'simply_documents',
			'post_status' => 'publish',
			'meta_key' => 'simply_document_type',
			'meta_value' => $simply_document_type,
			'orderby' => 'simply_document_type date title',
			'order' => 'DESC',
			'posts_per_page' => -1
			);
		}
	
	// if user can only read public posts, query all public posts
	else 
		{
		$args = array(
			'numberposts' => -1,
			'post_type' => 'simply_documents',
			'meta_query'	=> array(
					'relation'		=> 'AND',
					array(
						'key'		=> 'simply_document_type',
						'value'		=> $simply_document_type,
						'compare'	=> 'IN'
					),
					array(
						'key'		=> 'simply_document_privacy',
						'value'		=> '"public"',
						'compare'	=> 'LIKE'
					)
				),
			'orderby' => 'document_type date title',
			'order' => 'DESC',
			'posts_per_page' => -1
		);
		}
	}


/* !6. CUSTOM POST TYPES */

// 6.1
// documents
include_once( plugin_dir_path( __FILE__ ) . 'cpt/simply_documents.php');

// 6.2
// document_types
include_once( plugin_dir_path( __FILE__ ) . 'cpt/simply_document_types.php');
	

/* !7. ADMIN PAGES */

// 7.1
// hint: dashboard admin page
function simply_dashboard_admin_page() {
	
	// get our export link
	//$export_document_href = simply_get_export_document_link();
	//$export_user_href = simply_get_export_user_link();
	
	$output = '
		<div class="wrap">
			
			<h2>Simply Documents</h2>
			
			<p><strong>Simply Documents</strong> is a lightweight plugin for WordPress that uses shortcode to list documents in table format. <br>
			Easily upload documents and post them to the appropriate document type. Then you can display documents in a table format on any page.</p>
			
			<section>
				<h3>Basic Instructions</h3>
				<p>To display documents on your page, follow the instructions below:</p>
				
				<ol>
					<li>Add/Modify custom <a href="edit.php?post_type=simply_document_type">Document Types</a> to group your documents.</li>
					<li>Add the shortcode anywhere on the page you would like to display the table.<br><strong>Shortcode: [simply_documents]</strong></li>
					<li>Add <a href="edit.php?post_type=simply_documents">Documents</a>.</li> 
				</ol>
			</section>
			<br>
			
			<section>
			<h3>Additional Attributes</h3>
			<p>The following features can be added to your tables.</p>
			<table class="simply-feature-list">
				<thead>
				<tr>
					<th>Attribute</th>
					<th>Description</th>
					<th>Example</th>
				</tr>
				</thead>
				<tbody>
				<tr>
					<td class="simply-attribute">type</td>
					<td class="simply-description">By default, all documents are displayed in the table. You can filter by type by using the shortcode provided on the <a href="edit.php?post_type=simply_document_type">Document Types</a> page.</td>
					<td class="simply-example">[simply_documents]<br>[simply_documents <b>type="222"</b>]</td>
				</tr>
				
				<tr>
					<td class="simply-title">title</td>
					<td class="simply-description">The default title of the table is the Document Type. You can set a custom title or choose to have no title by adding this attribute.</td>
					<td class="simply-example">[simply_documents type="222" <b>title="Custom Title"</b>]<br>[simply_documents type="222" <b>title=""</b>]</td>
				</tr>
				</tbody>
			</table>
			<section>
		</div>
	';
	
	echo $output;
	
}

?>