<?php 
/*
Plugin Name: Redirect to URL
Plugin URI: http://lon.gr
Description: This simple and smart Plugin allows to redirect from a Page or an Article to an external source / URL by clicking on the Page-Link or the article.
Version: 0.8
Author: Marcel Fagas
Author URI: http://lon.gr
Update Server: http://lon.gr
Min WP Version: 3.9.2
Text Domain: redirect2url
*/


/**
 * Adds a box to the main column on the Post and Page edit screens.
 */
add_action( 'add_meta_boxes', 'redirect2url_add_meta_box' );
add_action( 'wp', 'redirect2url_check_for_redirection' );


#Check for Redirection on Page open
function redirect2url_check_for_redirection(){
	update_option(get_the_ID(), "");

	if(get_option("redirect2url_id_".get_the_ID()."_url")!=""){
		wp_redirect( get_option("redirect2url_id_".get_the_ID()."_url"), 307 ); exit;
	}
}



function redirect2url_add_meta_box() {
	$screens = array( 'post', 'page' );

	foreach ( $screens as $screen ) {

		add_meta_box(
			'redirect2url_sectionid',
			__( 'Redirect to URL', 'redirect2url_textdomain' ),
			'redirect2url_meta_box_callback',
			$screen
		);
	}
}


/**
 * Prints the box content.
 * 
 * @param WP_Post $post The object for the current post/page.
 */
function redirect2url_meta_box_callback( $post ) {

	// Add an nonce field so we can check for it later.
	wp_nonce_field( 'redirect2url_meta_box', 'redirect2url_meta_box_nonce' );

	/*
	 * Use get_post_meta() to retrieve an existing value
	 * from the database and use the value for the form.
	 */
	$value = get_post_meta( $post->ID, 'redirect2url_meta_value_key', true );

	
	//MetaBox Output
	if(get_option("redirect2url_id_".get_the_ID()."_url")!=""){
		echo '<label for="redirect2url_new_field" >Redirection is <strong style="color:green;">enabled</strong> to: <br />
			<input style="margin-bottom:5px; margin-top:5px;" type="text" id="redirect2url_new_field" name="redirect2url_new_field" value="' . esc_attr( get_option("redirect2url_id_".get_the_ID()."_url") ) . '" size="25" /></label> <br/>';
		echo '(Delete Redirection by removing the URL)';

	}else{
		echo '<label for="redirect2url_new_field">Redirection is <strong>disabled</strong>.</label> <br/>';
		echo 'Enable Redirection by entering the URL<br/>';
		echo '<input type="text" style="margin-bottom:5px; margin-top:5px;" id="redirect2url_new_field" name="redirect2url_new_field" value="' . esc_attr( get_option("redirect2url_id_".get_the_ID()."_url") ) . '" size="25" />';
		echo '<br/>(E.g.: http://www.example.com )';
	}
	
}

/**
 * When the post is saved, saves our custom data.
 *
 * @param int $post_id The ID of the post being saved.
 */
function redirect2url_save_meta_box_data( $post_id ) {

	/*
	 * We need to verify this came from our screen and with proper authorization,
	 * because the save_post action can be triggered at other times.
	 */

	// Check if our nonce is set.
	if ( ! isset( $_POST['redirect2url_meta_box_nonce'] ) ) {
		return;
	}

	// Verify that the nonce is valid.
	if ( ! wp_verify_nonce( $_POST['redirect2url_meta_box_nonce'], 'redirect2url_meta_box' ) ) {
		return;
	}

	// If this is an autosave, our form has not been submitted, so we don't want to do anything.
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	// Check the user's permissions.
	if ( isset( $_POST['post_type'] ) && 'page' == $_POST['post_type'] ) {

		if ( ! current_user_can( 'edit_page', $post_id ) ) {
			return;
		}

	} else {

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
	}

	/* OK, it's safe for us to save the data now. */
	
	// Make sure that it is set.
	if ( ! isset( $_POST['redirect2url_new_field'] ) ) {
		return;
	}

	// Sanitize user input.
	$redirect2url_url = sanitize_text_field( $_POST['redirect2url_new_field'] );
	
	// Update the meta field in the database.
	update_option("redirect2url_id_".get_the_ID()."_url", $redirect2url_url );
}
add_action( 'save_post', 'redirect2url_save_meta_box_data' );