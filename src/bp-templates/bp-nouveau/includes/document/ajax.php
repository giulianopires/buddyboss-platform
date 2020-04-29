<?php
/**
 * Media Ajax functions
 *
 * @since BuddyBoss 1.0.0
 * @package BuddyBoss\Core
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

add_action(
	'admin_init',
	function () {
		$ajax_actions = array(
			array(
				'media_filter' => array(
					'function' => 'bp_nouveau_ajax_object_template_loader',
					'nopriv'   => true,
				),
			),
			array(
				'document_filter' => array(
					'function' => 'bp_nouveau_ajax_object_template_loader',
					'nopriv'   => true,
				),
			),
			array(
				'document_document_upload' => array(
					'function' => 'bp_nouveau_ajax_document_upload',
					'nopriv'   => true,
				),
			),
			array(
				'document_document_save' => array(
					'function' => 'bp_nouveau_ajax_document_document_save',
					'nopriv'   => true,
				),
			),
			array(
				'document_folder_save' => array(
					'function' => 'bp_nouveau_ajax_document_folder_save',
					'nopriv'   => true,
				),
			),
			array(
				'document_move' => array(
					'function' => 'bp_nouveau_ajax_document_move',
					'nopriv'   => true,
				),
			),
			array(
				'document_update_file_name' => array(
					'function' => 'bp_nouveau_ajax_document_update_file_name',
					'nopriv'   => true,
				),
			),
			array(
				'document_edit_folder' => array(
					'function' => 'bp_nouveau_ajax_document_edit_folder',
					'nopriv'   => true,
				),
			),
			array(
				'document_delete' => array(
					'function' => 'bp_nouveau_ajax_document_delete',
					'nopriv'   => true,
				),
			),
			array(
				'document_folder_move' => array(
					'function' => 'bp_nouveau_ajax_document_folder_move',
					'nopriv'   => true,
				),
			),
            array(
            	'document_get_folder_view' => array(
		            'function' => 'bp_nouveau_ajax_document_get_folder_view',
		            'nopriv'   => true,
	            )
            ),
			array(
				'document_save_privacy' => array(
					'function' => 'bp_nouveau_ajax_document_save_privacy',
					'nopriv'   => true,
				)
			),
		);

		foreach ( $ajax_actions as $ajax_action ) {
			$action = key( $ajax_action );

			add_action( 'wp_ajax_' . $action, $ajax_action[ $action ]['function'] );

			if ( ! empty( $ajax_action[ $action ]['nopriv'] ) ) {
				add_action( 'wp_ajax_nopriv_' . $action, $ajax_action[ $action ]['function'] );
			}
		}
	},
	12
);

/**
 * Upload a media via a POST request.
 *
 * @since BuddyBoss 1.0.0
 */
function bp_nouveau_ajax_media_upload() {
	$response = array(
		'feedback' => __( 'There was a problem when trying to upload this file.', 'buddyboss' ),
	);

	// Bail if not a POST action.
	if ( ! bp_is_post_request() ) {
		wp_send_json_error( $response, 500 );
	}

	if ( empty( $_POST['_wpnonce'] ) ) {
		wp_send_json_error( $response, 500 );
	}

	// Use default nonce.
	$nonce = filter_input( INPUT_POST, '_wpnonce', FILTER_SANITIZE_STRING );
	$check = 'bp_nouveau_media';

	// Nonce check!
	if ( empty( $nonce ) || ! wp_verify_nonce( $nonce, $check ) ) {
		wp_send_json_error( $response, 500 );
	}

	// Upload file.
	$result = bp_media_upload();

	if ( is_wp_error( $result ) ) {
		$response['feedback'] = $result->get_error_message();
		wp_send_json_error( $response, $result->get_error_code() );
	}

	wp_send_json_success( $result );
}

/**
 * Upload a document via a POST request.
 *
 * @since BuddyBoss 1.0.0
 */
function bp_nouveau_ajax_document_upload() {
	$response = array(
		'feedback' => __( 'There was a problem when trying to upload this file.', 'buddyboss' ),
	);

	// Bail if not a POST action.
	if ( ! bp_is_post_request() ) {
		wp_send_json_error( $response, 500 );
	}

	if ( empty( $_POST['_wpnonce'] ) ) {
		wp_send_json_error( $response, 500 );
	}

	// Use default nonce.
	$nonce = filter_input( INPUT_POST, '_wpnonce', FILTER_SANITIZE_STRING );
	$check = 'bp_nouveau_media';

	// Nonce check!
	if ( empty( $nonce ) || ! wp_verify_nonce( $nonce, $check ) ) {
		wp_send_json_error( $response, 500 );
	}

	// Upload file.
	$result = bp_document_upload();

	if ( is_wp_error( $result ) ) {
		$response['feedback'] = $result->get_error_message();
		wp_send_json_error( $response, $result->get_error_code() );
	}

	wp_send_json_success( $result );
}

/**
 * Save media
 *
 * @since BuddyBoss 1.0.0
 */
function bp_nouveau_ajax_media_save() {
	$response = array(
		'feedback' => sprintf(
			'<div class="bp-feedback error bp-ajax-message"><span class="bp-icon" aria-hidden="true"></span><p>%s</p></div>',
			esc_html__( 'There was a problem performing this action. Please try again.', 'buddyboss' )
		),
	);

	// Bail if not a POST action.
	if ( ! bp_is_post_request() ) {
		wp_send_json_error( $response );
	}

	if ( empty( $_POST['_wpnonce'] ) ) {
		wp_send_json_error( $response );
	}

	// Use default nonce.
	$nonce = filter_input( INPUT_POST, '_wpnonce', FILTER_SANITIZE_STRING );
	$check = 'bp_nouveau_media';

	// Nonce check!
	if ( empty( $nonce ) || ! wp_verify_nonce( $nonce, $check ) ) {
		wp_send_json_error( $response );
	}

	if ( empty( $_POST['medias'] ) ) {
		$response['feedback'] = sprintf(
			'<div class="bp-feedback error"><span class="bp-icon" aria-hidden="true"></span><p>%s</p></div>',
			esc_html__( 'Please upload media before saving.', 'buddyboss' )
		);

		wp_send_json_error( $response );
	}

	// handle media uploaded.
	$media_ids = bp_media_add_handler();

	$media = '';
	if ( ! empty( $media_ids ) ) {
		ob_start();
		if ( bp_has_media( array( 'include' => implode( ',', $media_ids ) ) ) ) {
			while ( bp_media() ) {
				bp_the_media();
				bp_get_template_part( 'media/entry' );
			}
		}
		$media = ob_get_contents();
		ob_end_clean();
	}

	wp_send_json_success( array( 'media' => $media ) );
}

/**
 * Delete media
 *
 * @since BuddyBoss 1.0.0
 */
function bp_nouveau_ajax_media_delete() {
	$response = array(
		'feedback' => sprintf(
			'<div class="bp-feedback error bp-ajax-message"><span class="bp-icon" aria-hidden="true"></span><p>%s</p></div>',
			esc_html__( 'There was a problem performing this action. Please try again.', 'buddyboss' )
		),
	);

	// Bail if not a POST action.
	if ( ! bp_is_post_request() ) {
		wp_send_json_error( $response );
	}

	if ( empty( $_POST['_wpnonce'] ) ) {
		wp_send_json_error( $response );
	}

	// Use default nonce.
	$nonce = filter_input( INPUT_POST, '_wpnonce', FILTER_SANITIZE_STRING );
	$check = 'bp_nouveau_media';

	// Nonce check!
	if ( empty( $nonce ) || ! wp_verify_nonce( $nonce, $check ) ) {
		wp_send_json_error( $response );
	}

	if ( empty( $_POST['media'] ) ) {
		$response['feedback'] = sprintf(
			'<div class="bp-feedback error"><span class="bp-icon" aria-hidden="true"></span><p>%s</p></div>',
			esc_html__( 'Please select media to delete.', 'buddyboss' )
		);

		wp_send_json_error( $response );
	}

	$media = $_POST['media'];

	$media_ids = array();
	foreach ( $media as $media_id ) {

		if ( bp_media_user_can_delete( $media_id ) ) {

			// delete media.
			if ( bp_media_delete( array( 'id' => $media_id ) ) ) {
				$media_ids[] = $media_id;
			}
		}
	}

	if ( count( $media_ids ) !== count( $media ) ) {
		$response['feedback'] = sprintf(
			'<div class="bp-feedback error"><span class="bp-icon" aria-hidden="true"></span><p>%s</p></div>',
			esc_html__( 'There was a problem deleting media.', 'buddyboss' )
		);
		wp_send_json_error( $response );
	}

	wp_send_json_success(
		array(
			'media' => $media,
		)
	);
}

/**
 * Move media to album
 *
 * @since BuddyBoss 1.0.0
 */
function bp_nouveau_ajax_media_move_to_album() {
	$response = array(
		'feedback' => sprintf(
			'<div class="bp-feedback error bp-ajax-message"><span class="bp-icon" aria-hidden="true"></span><p>%s</p></div>',
			esc_html__( 'There was a problem performing this action. Please try again.', 'buddyboss' )
		),
	);

	// Bail if not a POST action.
	if ( ! bp_is_post_request() ) {
		wp_send_json_error( $response );
	}

	if ( empty( $_POST['_wpnonce'] ) ) {
		wp_send_json_error( $response );
	}

	// Use default nonce.
	$nonce = filter_input( INPUT_POST, '_wpnonce', FILTER_SANITIZE_STRING );
	$check = 'bp_nouveau_media';

	// Nonce check!
	if ( empty( $nonce ) || ! wp_verify_nonce( $nonce, $check ) ) {
		wp_send_json_error( $response );
	}

	if ( empty( $_POST['medias'] ) ) {
		$response['feedback'] = sprintf(
			'<div class="bp-feedback error">%s</div>',
			esc_html__( 'Please upload media before saving.', 'buddyboss' )
		);

		wp_send_json_error( $response );
	}

	if ( empty( $_POST['album_id'] ) ) {
		$response['feedback'] = sprintf(
			'<div class="bp-feedback error">%s</div>',
			esc_html__( 'Please provide album to move media.', 'buddyboss' )
		);

		wp_send_json_error( $response );
	}

	$album_privacy = 'public';
	$album         = new BP_Media_Album( filter_input( INPUT_POST, 'album_id', FILTER_VALIDATE_INT ) );
	if ( ! empty( $album ) ) {
		$album_privacy = $album->privacy;
	}

	// save media.
	$medias    = $_POST['medias'];
	$media_ids = array();
	foreach ( $medias as $media_id ) {

		$media_obj           = new BP_Media( $media_id );
		$media_obj->album_id = (int) $_POST['album_id'];
		$media_obj->group_id = ! empty( $_POST['group_id'] ) ? (int) $_POST['group_id'] : false;
		$media_obj->privacy  = $media_obj->group_id ? 'grouponly' : $album_privacy;

		if ( ! $media_obj->save() ) {
			$response['feedback'] = sprintf(
				'<div class="bp-feedback error"><span class="bp-icon" aria-hidden="true"></span><p>%s</p></div>',
				esc_html__( 'There was a problem when trying to move the media.', 'buddyboss' )
			);

			wp_send_json_error( $response );
		}

		$media_ids[] = $media_id;
	}

	$media = '';
	if ( ! empty( $media_ids ) ) {
		ob_start();
		if ( bp_has_media( array( 'include' => implode( ',', $media_ids ) ) ) ) {
			while ( bp_media() ) {
				bp_the_media();
				bp_get_template_part( 'media/entry' );
			}
		}
		$media = ob_get_contents();
		ob_end_clean();
	}

	wp_send_json_success(
		array(
			'media' => $media,
		)
	);
}

/**
 * Save album
 *
 * @since BuddyBoss 1.0.0
 */
function bp_nouveau_ajax_media_album_save() {
	$response = array(
		'feedback' => sprintf(
			'<div class="bp-feedback error bp-ajax-message"><span class="bp-icon" aria-hidden="true"></span><p>%s</p></div>',
			esc_html__( 'There was a problem performing this action. Please try again.', 'buddyboss' )
		),
	);

	// Bail if not a POST action.
	if ( ! bp_is_post_request() ) {
		wp_send_json_error( $response );
	}

	if ( empty( $_POST['_wpnonce'] ) ) {
		wp_send_json_error( $response );
	}

	// Use default nonce.
	$nonce = filter_input( INPUT_POST, '_wpnonce', FILTER_SANITIZE_STRING );
	$check = 'bp_nouveau_media';

	// Nonce check!
	if ( empty( $nonce ) || ! wp_verify_nonce( $nonce, $check ) ) {
		wp_send_json_error( $response );
	}

	if ( empty( $_POST['title'] ) ) {
		$response['feedback'] = sprintf(
			'<div class="bp-feedback error"><span class="bp-icon" aria-hidden="true"></span><p>%s</p></div>',
			esc_html__( 'Please enter title of album.', 'buddyboss' )
		);

		wp_send_json_error( $response );
	}

	// save media.
	$id       = ! empty( filter_input( INPUT_POST, 'album_id', FILTER_VALIDATE_INT ) ) ? filter_input( INPUT_POST, 'album_id', FILTER_VALIDATE_INT ) : false;
	$group_id = ! empty( filter_input( INPUT_POST, 'group_id', FILTER_VALIDATE_INT ) ) ? filter_input( INPUT_POST, 'group_id', FILTER_VALIDATE_INT ) : false;
	$title    = filter_input( INPUT_POST, 'title', FILTER_SANITIZE_STRING );
	$privacy  = ! empty( filter_input( INPUT_POST, 'privacy', FILTER_SANITIZE_STRING ) ) ? filter_input( INPUT_POST, 'privacy', FILTER_SANITIZE_STRING ) : 'public';

	$album_id = bp_album_add(
		array(
			'id'       => $id,
			'title'    => $title,
			'privacy'  => $privacy,
			'group_id' => $group_id,
		)
	);

	if ( ! $album_id ) {
		$response['feedback'] = sprintf(
			'<div class="bp-feedback error"><span class="bp-icon" aria-hidden="true"></span><p>%s</p></div>',
			esc_html__( 'There was a problem when trying to create the album.', 'buddyboss' )
		);
		wp_send_json_error( $response );
	}

	$medias = $_POST['medias'];
	if ( ! empty( $medias ) && is_array( $medias ) ) {
		// set album id for media.
		foreach ( $medias as $key => $media ) {
			$_POST['medias'][ $key ]['album_id'] = $album_id;
		}
	}

	// save all media uploaded.
	bp_media_add_handler();

	if ( ! empty( $group_id ) && bp_is_active( 'groups' ) ) {
		$group_link   = bp_get_group_permalink( groups_get_group( $group_id ) );
		$redirect_url = trailingslashit( $group_link . '/albums/' . $album_id );
	} else {
		$redirect_url = trailingslashit( bp_loggedin_user_domain() . bp_get_media_slug() . '/albums/' . $album_id );
	}

	wp_send_json_success(
		array(
			'redirect_url' => $redirect_url,
		)
	);
}

/**
 * Delete album
 *
 * @since BuddyBoss 1.0.0
 */
function bp_nouveau_ajax_media_folder_delete() {
	$response = array(
		'feedback' => sprintf(
			'<div class="bp-feedback error bp-ajax-message"><span class="bp-icon" aria-hidden="true"></span><p>%s</p></div>',
			esc_html__( 'There was a problem performing this action. Please try again.', 'buddyboss' )
		),
	);

	// Bail if not a POST action.
	if ( ! bp_is_post_request() ) {
		wp_send_json_error( $response );
	}

	if ( empty( $_POST['_wpnonce'] ) ) {
		wp_send_json_error( $response );
	}

	// Use default nonce.
	$nonce = filter_input( INPUT_POST, '_wpnonce', FILTER_SANITIZE_STRING );
	$check = 'bp_nouveau_media';

	// Nonce check!
	if ( empty( $nonce ) || ! wp_verify_nonce( $nonce, $check ) ) {
		wp_send_json_error( $response );
	}

	if ( empty( $_POST['album_id'] ) ) {
		$response['feedback'] = sprintf(
			'<div class="bp-feedback error"><span class="bp-icon" aria-hidden="true"></span><p>%s</p></div>',
			esc_html__( 'Please provide ID of folder to delete.', 'buddyboss' )
		);

		wp_send_json_error( $response );
	}

	$album_id = filter_input( INPUT_POST, 'album_id', FILTER_VALIDATE_INT );
	if ( ! bp_album_user_can_delete( $album_id ) ) {
		$response['feedback'] = sprintf(
			'<div class="bp-feedback error"><span class="bp-icon" aria-hidden="true"></span><p>%s</p></div>',
			esc_html__( 'You do not have permission to delete this folder.', 'buddyboss' )
		);

		wp_send_json_error( $response );
	}

	// delete album.
	$album_id = bp_folder_delete( array( 'id' => $album_id ) );

	if ( ! $album_id ) {
		wp_send_json_error( $response );
	}

	$group_id = ! empty( filter_input( INPUT_POST, 'group_id', FILTER_VALIDATE_INT ) ) ? (int) filter_input( INPUT_POST, 'group_id', FILTER_VALIDATE_INT ) : false;

	if ( ! empty( $group_id ) && bp_is_active( 'groups' ) ) {
		$group_link   = bp_get_group_permalink( groups_get_group( $group_id ) );
		$redirect_url = trailingslashit( $group_link . '/documents/' );
	} else {
		$redirect_url = trailingslashit( bp_displayed_user_domain() . bp_get_document_slug() );
	}

	wp_send_json_success(
		array(
			'redirect_url' => $redirect_url,
		)
	);
}

/**
 * Get activity for the media
 *
 * @since BuddyBoss 1.0.0
 */
function bp_nouveau_ajax_media_get_activity() {
	$response = array(
		'feedback' => sprintf(
			'<div class="bp-feedback bp-messages error"><span class="bp-icon" aria-hidden="true"></span><p>%s</p></div>',
			esc_html__( 'There was a problem displaying the content. Please try again.', 'buddyboss' )
		),
	);

	// Nonce check!
	$nonce = filter_input( INPUT_POST, 'nonce', FILTER_SANITIZE_STRING );
	if ( empty( $nonce ) || ! wp_verify_nonce( $nonce, 'bp_nouveau_media' ) ) {
		wp_send_json_error( $response );
	}

	remove_action( 'bp_activity_entry_content', 'bp_media_activity_entry' );

	$post_id = filter_input( INPUT_POST, 'id', FILTER_VALIDATE_INT );

	ob_start();
	if ( bp_has_activities(
		array(
			'include'     => $post_id,
			'show_hidden' => true,
		)
	) ) {
		while ( bp_activities() ) {
			bp_the_activity();
			bp_get_template_part( 'activity/entry' );
		}
	}
	$activity = ob_get_contents();
	ob_end_clean();

	add_action( 'bp_activity_entry_content', 'bp_media_activity_entry' );

	wp_send_json_success(
		array(
			'activity' => $activity,
		)
	);
}

/**
 * Delete attachment with its files
 *
 * @since BuddyBoss 1.0.0
 */
function bp_nouveau_ajax_media_delete_attachment() {
	$response = array(
		'feedback' => sprintf(
			'<div class="bp-feedback bp-messages error"><span class="bp-icon" aria-hidden="true"></span><p>%s</p></div>',
			esc_html__( 'There was a problem displaying the content. Please try again.', 'buddyboss' )
		),
	);

	// Nonce check!
	$nonce = filter_input( INPUT_POST, '_wpnonce', FILTER_SANITIZE_STRING );
	if ( empty( $nonce ) || ! wp_verify_nonce( $nonce, 'bp_nouveau_media' ) ) {
		wp_send_json_error( $response );
	}

	if ( empty( $_POST['id'] ) ) {
		$response['feedback'] = sprintf(
			'<div class="bp-feedback error"><span class="bp-icon" aria-hidden="true"></span><p>%s</p></div>',
			esc_html__( 'Please provide attachment id to delete.', 'buddyboss' )
		);

		wp_send_json_error( $response );
	}

	// delete attachment with its meta.
	$post_id = filter_input( INPUT_POST, 'id', FILTER_VALIDATE_INT );
	$deleted = wp_delete_attachment( $post_id, true );

	if ( ! $deleted ) {
		wp_send_json_error( $response );
	}

	wp_send_json_success();
}

add_filter( 'bp_nouveau_object_template_result', 'bp_nouveau_object_template_results_document_tabs', 10, 2 );

/**
 * Object template results media tabs.
 *
 * @param $results
 * @param $object
 *
 * @since BuddyBoss 1.0.0
 *
 * @return mixed
 */
function bp_nouveau_object_template_results_document_tabs( $results, $object ) {
	if ( $object !== 'document' ) {
		return $results;
	}

	$results['scopes'] = array();

	add_filter( 'bp_ajax_querystring', 'bp_nouveau_object_template_results_document_all_scope', 20 );
	bp_has_document( bp_ajax_querystring( 'document' ) );
	$results['scopes']['all'] = $GLOBALS['document_template']->total_document_count;
	remove_filter( 'bp_ajax_querystring', 'bp_nouveau_object_template_results_document_all_scope', 20 );

	add_filter( 'bp_ajax_querystring', 'bp_nouveau_object_template_results_document_personal_scope', 20 );
	bp_has_document( bp_ajax_querystring( 'document' ) );
	$results['scopes']['personal'] = $GLOBALS['document_template']->total_document_count;
	remove_filter( 'bp_ajax_querystring', 'bp_nouveau_object_template_results_document_personal_scope', 20 );

	return $results;
}

/**
 * Object template results document all scope.
 *
 * @param $querystring
 *
 * @since BuddyBoss 1.0.0
 *
 * @return string
 */
function bp_nouveau_object_template_results_document_all_scope( $querystring ) {
	$querystring = wp_parse_args( $querystring );

	$querystring['scope'] = array();

	if ( bp_is_active( 'friends' ) ) {
		$querystring['scope'][] = 'friends';
	}

	if ( bp_is_active( 'groups' ) ) {
		$querystring['scope'][] = 'groups';
	}

	if ( is_user_logged_in() ) {
		$querystring['scope'][] = 'personal';
	}

	$querystring['page']        = 1;
	$querystring['per_page']    = '1';
	$querystring['user_id']     = 0;
	$querystring['count_total'] = true;

	return http_build_query( $querystring );
}

/**
 * Object template results media personal scope.
 *
 * @since BuddyBoss 1.0.0
 */
function bp_nouveau_object_template_results_document_personal_scope( $querystring ) {
	$querystring = wp_parse_args( $querystring );

	$querystring['scope']    = 'personal';
	$querystring['page']     = 1;
	$querystring['per_page'] = '1';
	$querystring['user_id']  = ( bp_displayed_user_id() ) ? bp_displayed_user_id() : bp_loggedin_user_id();
	// $querystring['type']     = 'media';

	$privacy = array( 'public' );
	if ( is_user_logged_in() ) {
		$privacy[] = 'loggedin';
		$privacy[] = 'onlyme';
	}

	$querystring['privacy']     = $privacy;
	$querystring['count_total'] = true;

	return http_build_query( $querystring );
}

add_filter( 'bp_ajax_querystring', 'bp_nouveau_object_template_results_albums_existing_media_query', 20 );

/**
 * Change the querystring based on caller of the albums media query
 *
 * @param $querystring
 *
 */
function bp_nouveau_object_template_results_albums_existing_media_query( $querystring ) {
	$querystring = wp_parse_args( $querystring );

	if ( ! empty( $_POST['caller'] ) && 'bp-existing-media' === $_POST['caller'] ) {
		$querystring['album_id'] = 0;
	}

	return http_build_query( $querystring );
}

add_filter( 'bp_ajax_querystring', 'bp_nouveau_object_template_results_folders_existing_document_query', 20 );

/**
 * Change the querystring based on caller of the albums media query
 *
 * @param $querystring
 *
 */
function bp_nouveau_object_template_results_folders_existing_document_query( $querystring ) {
	$querystring = wp_parse_args( $querystring );

	if ( ! empty( $_POST['caller'] ) && 'bp-existing-document' === $_POST['caller'] ) {
		$querystring['folder_id'] = 0;
	}

	return http_build_query( $querystring );
}

/**
 * Save media
 *
 * @since BuddyBoss 1.0.0
 */
function bp_nouveau_ajax_document_document_save() {
	$response = array(
		'feedback' => sprintf(
			'<div class="bp-feedback error bp-ajax-message"><span class="bp-icon" aria-hidden="true"></span><p>%s</p></div>',
			esc_html__( 'There was a problem performing this action. Please try again.', 'buddyboss' )
		),
	);

	// Bail if not a POST action.
	if ( ! bp_is_post_request() ) {
		wp_send_json_error( $response );
	}

	if ( empty( $_POST['_wpnonce'] ) ) {
		wp_send_json_error( $response );
	}

	// Use default nonce.
	$nonce = filter_input( INPUT_POST, '_wpnonce', FILTER_SANITIZE_STRING );
	$check = 'bp_nouveau_media';

	// Nonce check!
	if ( empty( $nonce ) || ! wp_verify_nonce( $nonce, $check ) ) {
		wp_send_json_error( $response );
	}

	if ( empty( $_POST['medias'] ) ) {
		$response['feedback'] = sprintf(
			'<div class="bp-feedback error"><span class="bp-icon" aria-hidden="true"></span><p>%s</p></div>',
			esc_html__( 'Please upload document before saving.', 'buddyboss' )
		);

		wp_send_json_error( $response );
	}

	if ( isset( $_POST['medias'] ) && !empty( $_POST['medias'] ) && isset( $_POST['folder_id'] ) && (int) $_POST['folder_id'] > 0 ) {
		$documents = $_POST['medias'];
		$album_id  = (int) $_POST['folder_id'];
		if ( ! empty( $documents ) && is_array( $documents ) ) {
			// set album id for media.
			foreach ( $documents as $key => $media ) {
			    if ( 0 === (int) $media['folder_id'] ) {
				    $_POST['medias'][ $key ]['folder_id'] = $album_id;
                }
			}
		}
    }


	// handle media uploaded.
	$media_ids = bp_document_add_handler();

	$media = '';
	if ( ! empty( $media_ids ) ) {
		ob_start();
		if ( bp_has_document( array( 'include' => implode( ',', $media_ids ) ) ) ) {
			while ( bp_document() ) {
				bp_the_document();
				bp_get_template_part( 'document/document-entry' );
			}
		}
		$media = ob_get_contents();
		ob_end_clean();
	}

	wp_send_json_success( array( 'media' => $media ) );
}

/**
 * Save folder
 *
 * @since BuddyBoss 1.0.0
 */
function bp_nouveau_ajax_document_folder_save() {
	$response = array(
		'feedback' => sprintf(
			'<div class="bp-feedback error bp-ajax-message"><span class="bp-icon" aria-hidden="true"></span><p>%s</p></div>',
			esc_html__( 'There was a problem performing this action. Please try again.', 'buddyboss' )
		),
	);

	// Bail if not a POST action.
	if ( ! bp_is_post_request() ) {
		wp_send_json_error( $response );
	}

	if ( empty( $_POST['_wpnonce'] ) ) {
		wp_send_json_error( $response );
	}

	// Use default nonce.
	$nonce = filter_input( INPUT_POST, '_wpnonce', FILTER_SANITIZE_STRING );
	$check = 'bp_nouveau_media';

	// Nonce check!
	if ( empty( $nonce ) || ! wp_verify_nonce( $nonce, $check ) ) {
		wp_send_json_error( $response );
	}

	if ( empty( $_POST['title'] ) ) {
		$response['feedback'] = sprintf(
			'<div class="bp-feedback error"><span class="bp-icon" aria-hidden="true"></span><p>%s</p></div>',
			esc_html__( 'Please enter title of folder.', 'buddyboss' )
		);

		wp_send_json_error( $response );
	}

	// save media.
	$id       = ! empty( filter_input( INPUT_POST, 'album_id', FILTER_VALIDATE_INT ) ) ? filter_input( INPUT_POST, 'album_id', FILTER_VALIDATE_INT ) : false;
	$group_id = ! empty( filter_input( INPUT_POST, 'group_id', FILTER_VALIDATE_INT ) ) ? filter_input( INPUT_POST, 'group_id', FILTER_VALIDATE_INT ) : false;
	$title    = filter_input( INPUT_POST, 'title', FILTER_SANITIZE_STRING );
	$privacy  = ! empty( filter_input( INPUT_POST, 'privacy', FILTER_SANITIZE_STRING ) ) ? filter_input( INPUT_POST, 'privacy', FILTER_SANITIZE_STRING ) : 'public';
	$parent   = ! empty( filter_input( INPUT_POST, 'parent', FILTER_VALIDATE_INT ) ) ? (int) filter_input( INPUT_POST, 'parent', FILTER_VALIDATE_INT ) : 0;

	if ( $parent > 0 ) {
		$id = false;
	}

	$album_id = bp_folder_add(
		array(
			'id'       => $id,
			'title'    => $title,
			'privacy'  => $privacy,
			'group_id' => $group_id,
			'parent'   => $parent,
		)
	);

	if ( ! $album_id ) {
		$response['feedback'] = sprintf(
			'<div class="bp-feedback error"><span class="bp-icon" aria-hidden="true"></span><p>%s</p></div>',
			esc_html__( 'There was a problem when trying to create the folder.', 'buddyboss' )
		);
		wp_send_json_error( $response );
	}

	$medias = $_POST['medias'];
	if ( ! empty( $medias ) && is_array( $medias ) ) {
		// set album id for media.
		foreach ( $medias as $key => $media ) {
			$_POST['medias'][ $key ]['folder_id'] = $album_id;
		}
	}

	// save all media uploaded.
	bp_document_add_handler();

	if ( ! empty( $group_id ) && bp_is_active( 'groups' ) ) {
		$group_link   = bp_get_group_permalink( groups_get_group( $group_id ) );
		$redirect_url = trailingslashit( $group_link . bp_get_document_slug() . '/folders/' . $album_id );
	} else {
		$redirect_url = trailingslashit( bp_loggedin_user_domain() . bp_get_document_slug() . '/folders/' . $album_id );
	}

	wp_send_json_success(
		array(
			'redirect_url' => $redirect_url,
		)
	);
}

/**
 * Ajax document move.
 *
 * @since BuddyBoss 1.3.2
 */
function bp_nouveau_ajax_document_move() {

	$response = array(
		'feedback' => sprintf(
			'<div class="bp-feedback error bp-ajax-message"><span class="bp-icon" aria-hidden="true"></span><p>%s</p></div>',
			esc_html__( 'There was a problem performing this action. Please try again.', 'buddyboss' )
		),
	);

	// Bail if not a POST action.
	if ( ! bp_is_post_request() ) {
		wp_send_json_error( $response );
	}

	if ( empty( $_POST['_wpnonce'] ) ) {
		wp_send_json_error( $response );
	}

	// Use default nonce.
	$nonce = filter_input( INPUT_POST, '_wpnonce', FILTER_SANITIZE_STRING );
	$check = 'bp_nouveau_media';

	// Nonce check!
	if ( empty( $nonce ) || ! wp_verify_nonce( $nonce, $check ) ) {
		wp_send_json_error( $response );
	}

	// Move document.
	$folder_id   = ! empty( $_POST['folder_id'] ) ? (int) $_POST['folder_id'] : 0;
	$document_id = ! empty( $_POST['document_id'] ) ? (int) $_POST['document_id'] : 0;

	if ( 0 === $document_id ) {
		wp_send_json_error( $response );
	}

	$document = bp_document_move_to_folder( $document_id, $folder_id );

	if ( $document > 0 ) {

		$content = '';
		ob_start();

		if ( bp_has_document( bp_ajax_querystring( 'document' ) ) ) :

			if ( empty( filter_input( INPUT_POST, 'page', FILTER_SANITIZE_STRING ) ) || 1 === (int) filter_input( INPUT_POST, 'page', FILTER_SANITIZE_STRING ) ) :
				?>

				<div class="document-data-table-head">
					<span class="data-head-sort-label"><?php esc_html_e( 'Sort By:', 'buddyboss' ); ?></span>
					<div class="data-head data-head-name">
				<span>
					<?php esc_html_e( 'Name', 'buddyboss' ); ?>
					<i class="bb-icon-triangle-fill"></i>
				</span>

					</div>
					<div class="data-head data-head-modified">
				<span>
					<?php esc_html_e( 'Modified', 'buddyboss' ); ?>
					<i class="bb-icon-triangle-fill"></i>
				</span>

					</div>
					<div class="data-head data-head-visibility">
				<span>
					<?php esc_html_e( 'Visibility', 'buddyboss' ); ?>
					<i class="bb-icon-triangle-fill"></i>
				</span>
					</div>
				</div><!-- .document-data-table-head -->

				<div id="media-folder-document-data-table">
				<?php
				bp_get_template_part( 'document/activity-document-move' );
				bp_get_template_part( 'document/activity-document-folder-move' );

			endif;

			while ( bp_document() ) :
				bp_the_document();

				bp_get_template_part( 'document/document-entry' );

			endwhile;

			if ( bp_document_has_more_items() ) :
				?>
				<div class="pager">
					<div class="dt-more-container load-more">
						<a class="button outline full" href="<?php bp_document_load_more_link(); ?>"><?php esc_html_e( 'Load More', 'buddyboss' ); ?></a>
					</div>
				</div>
				<?php
			endif;

			if ( empty( filter_input( INPUT_POST, 'page', FILTER_SANITIZE_STRING ) ) || 1 === (int) filter_input( INPUT_POST, 'page', FILTER_SANITIZE_STRING ) ) :
				?>
				</div> <!-- #media-folder-document-data-table -->
				<?php
			endif;

		else :

			bp_nouveau_user_feedback( 'media-loop-document-none' );

		endif;

		$content .= ob_get_clean();

		wp_send_json_success(
			array(
				'message' => 'success',
				'html'    => $content,
			)
		);

	} else {
		wp_send_json_error( $response );
	}

}

/**
 * Update the document name.
 *
 * @since BuddyBoss 1.3.2
 */
function bp_nouveau_ajax_document_update_file_name() {

	$response = array(
		'feedback' => sprintf(
			'<div class="bp-feedback error bp-ajax-message"><span class="bp-icon" aria-hidden="true"></span><p>%s</p></div>',
			esc_html__( 'There was a problem performing this action. Please try again.', 'buddyboss' )
		),
	);

	// Bail if not a POST action.
	if ( ! bp_is_post_request() ) {
		wp_send_json_error( $response );
	}

	$document_id            = ! empty( filter_input( INPUT_POST, 'document_id', FILTER_SANITIZE_STRING ) ) ? (int) filter_input( INPUT_POST, 'document_id', FILTER_SANITIZE_STRING ) : 0;
	$attachment_document_id = ! empty( filter_input( INPUT_POST, 'attachment_document_id', FILTER_SANITIZE_STRING ) ) ? (int) filter_input( INPUT_POST, 'attachment_document_id', FILTER_SANITIZE_STRING ) : 0;
	$title                  = ! empty( filter_input( INPUT_POST, 'name', FILTER_SANITIZE_STRING ) ) ? filter_input( INPUT_POST, 'name', FILTER_SANITIZE_STRING ) : '';
	$type                   = ! empty( filter_input( INPUT_POST, 'document_type', FILTER_SANITIZE_STRING ) ) ? filter_input( INPUT_POST, 'document_type', FILTER_SANITIZE_STRING ) : '';

	if ( 'document' === $type ) {
		if ( 0 === $document_id || 0 === $attachment_document_id || '' === $title ) {
			wp_send_json_error( $response );
		}

		$document = bp_document_rename_file( $document_id, $attachment_document_id, $title );

		if ( isset( $document['document_id'] ) && $document['document_id'] > 0 ) {
			wp_send_json_success(
				array(
					'message'  => 'success',
					'response' => $document,
				)
			);
		} else {
			wp_send_json_error( $response );
		}
	} else {
		if ( 0 === $document_id || '' === $title ) {
			wp_send_json_error( $response );
		}

		$folder = bp_document_rename_folder( $document_id, $title );

		$response = array(
			'document_id' => $document_id,
			'title'       => $title,
		);

		if ( $folder > 0 ) {
			wp_send_json_success(
				array(
					'message'  => 'success',
					'response' => $response,
				)
			);
		} else {
			wp_send_json_error( $response );
		}
	}

}

/**
 * Rename folder.
 *
 * @since BuddyBoss 1.0.0
 */
function bp_nouveau_ajax_document_edit_folder() {
	$response = array(
		'feedback' => sprintf(
			'<div class="bp-feedback error bp-ajax-message"><span class="bp-icon" aria-hidden="true"></span><p>%s</p></div>',
			esc_html__( 'There was a problem performing this action. Please try again.', 'buddyboss' )
		),
	);

	// Bail if not a POST action.
	if ( ! bp_is_post_request() ) {
		wp_send_json_error( $response );
	}

	if ( empty( $_POST['_wpnonce'] ) ) {
		wp_send_json_error( $response );
	}

	// Use default nonce.
	$nonce = filter_input( INPUT_POST, '_wpnonce', FILTER_SANITIZE_STRING );
	$check = 'bp_nouveau_media';

	// Nonce check!
	if ( empty( $nonce ) || ! wp_verify_nonce( $nonce, $check ) ) {
		wp_send_json_error( $response );
	}

	if ( empty( $_POST['title'] ) ) {
		$response['feedback'] = sprintf(
			'<div class="bp-feedback error"><span class="bp-icon" aria-hidden="true"></span><p>%s</p></div>',
			esc_html__( 'Please enter title of folder.', 'buddyboss' )
		);

		wp_send_json_error( $response );
	}

	// save media.
	$group_id = ! empty( filter_input( INPUT_POST, 'group_id', FILTER_VALIDATE_INT ) ) ? filter_input( INPUT_POST, 'group_id', FILTER_VALIDATE_INT ) : false;
	$title    = filter_input( INPUT_POST, 'title', FILTER_SANITIZE_STRING );
	$privacy  = ! empty( filter_input( INPUT_POST, 'privacy', FILTER_SANITIZE_STRING ) ) ? filter_input( INPUT_POST, 'privacy', FILTER_SANITIZE_STRING ) : 'public';
	$parent   = ! empty( filter_input( INPUT_POST, 'parent', FILTER_VALIDATE_INT ) ) ? (int) filter_input( INPUT_POST, 'parent', FILTER_VALIDATE_INT ) : 0;
	$move_to  = ! empty( filter_input( INPUT_POST, 'moveTo', FILTER_VALIDATE_INT ) ) ? (int) filter_input( INPUT_POST, 'moveTo', FILTER_VALIDATE_INT ) : 0;

	if ( 0 === $move_to ) {
		$move_to = $parent;
	}

	if ( $parent === $move_to ) {
		$move_to = 0;
	}

	$album_id = bp_folder_add(
		array(
			'id'       => $parent,
			'title'    => $title,
			'privacy'  => $privacy,
			'group_id' => $group_id,
			'parent'   => $move_to,
		)
	);

	if ( ! $album_id ) {
		$response['feedback'] = sprintf(
			'<div class="bp-feedback error"><span class="bp-icon" aria-hidden="true"></span><p>%s</p></div>',
			esc_html__( 'There was a problem when trying to create the folder.', 'buddyboss' )
		);
		wp_send_json_error( $response );
	}

	if ( ! empty( $group_id ) && bp_is_active( 'groups' ) ) {
		$group_link   = bp_get_group_permalink( groups_get_group( $group_id ) );
		$redirect_url = trailingslashit( $group_link . bp_get_document_slug() . '/folders/' . $album_id );
	} else {
		$redirect_url = trailingslashit( bp_loggedin_user_domain() . bp_get_document_slug() . '/folders/' . $album_id );
	}

	wp_send_json_success(
		array(
			'redirect_url' => $redirect_url,
		)
	);
}

/**
 * Ajax delete the document.
 *
 * @since BuddyBoss 1.3.2
 */
function bp_nouveau_ajax_document_delete() {

	$response = array(
		'feedback' => sprintf(
			'<div class="bp-feedback error bp-ajax-message"><span class="bp-icon" aria-hidden="true"></span><p>%s</p></div>',
			esc_html__( 'There was a problem performing this action. Please try again.', 'buddyboss' )
		),
	);

	// Bail if not a POST action.
	if ( ! bp_is_post_request() ) {
		wp_send_json_error( $response );
	}

	$id            = ! empty( filter_input( INPUT_POST, 'id', FILTER_VALIDATE_INT ) ) ? (int) filter_input( INPUT_POST, 'id', FILTER_VALIDATE_INT ) : 0;
	$attachment_id = ! empty( filter_input( INPUT_POST, 'attachment_id', FILTER_VALIDATE_INT ) ) ? (int) filter_input( INPUT_POST, 'attachment_id', FILTER_VALIDATE_INT ) : 0;
	$type          = ! empty( filter_input( INPUT_POST, 'type', FILTER_SANITIZE_STRING ) ) ? filter_input( INPUT_POST, 'type', FILTER_SANITIZE_STRING ) : '';

	if ( '' === $type ) {
		wp_send_json_error( $response );
	}

	if ( 'folder' === $type ) {
		bp_folder_delete( array( 'id' => $id ) );
	} else {
		bp_document_delete(
			array(
				'id'            => $id,
				'attachment_id' => $attachment_id,
			)
		);
	}

	$content = '';
	ob_start();

	if ( bp_has_document( bp_ajax_querystring( 'document' ) ) ) :

		if ( empty( filter_input( INPUT_POST, 'page', FILTER_SANITIZE_STRING ) ) || 1 === (int) filter_input( INPUT_POST, 'page', FILTER_SANITIZE_STRING ) ) :
			?>

			<div class="document-data-table-head">
				<span class="data-head-sort-label"><?php esc_html_e( 'Sort By:', 'buddyboss' ); ?></span>
				<div class="data-head data-head-name">
				<span>
					<?php esc_html_e( 'Name', 'buddyboss' ); ?>
					<i class="bb-icon-triangle-fill"></i>
				</span>

				</div>
				<div class="data-head data-head-modified">
				<span>
					<?php esc_html_e( 'Modified', 'buddyboss' ); ?>
					<i class="bb-icon-triangle-fill"></i>
				</span>

				</div>
				<div class="data-head data-head-visibility">
				<span>
					<?php esc_html_e( 'Visibility', 'buddyboss' ); ?>
					<i class="bb-icon-triangle-fill"></i>
				</span>
				</div>
			</div><!-- .document-data-table-head -->

			<div id="media-folder-document-data-table">
			<?php
			bp_get_template_part( 'document/activity-document-move' );
			bp_get_template_part( 'document/activity-document-folder-move' );

		endif;

		while ( bp_document() ) :
			bp_the_document();

			bp_get_template_part( 'document/document-entry' );

		endwhile;

		if ( bp_document_has_more_items() ) :
			?>
			<div class="pager">
				<div class="dt-more-container load-more">
					<a class="button outline full" href="<?php bp_document_load_more_link(); ?>"><?php esc_html_e( 'Load More', 'buddyboss' ); ?></a>
				</div>
			</div>
			<?php
		endif;

		if ( empty( filter_input( INPUT_POST, 'page', FILTER_SANITIZE_STRING ) ) || 1 === (int) filter_input( INPUT_POST, 'page', FILTER_SANITIZE_STRING ) ) :
			?>
			</div> <!-- #media-folder-document-data-table -->
			<?php
		endif;

	else :

		bp_nouveau_user_feedback( 'media-loop-document-none' );

	endif;

	$content .= ob_get_clean();

	wp_send_json_success(
		array(
			'message' => 'success',
			'html'    => $content,
		)
	);

}

/**
 * Move folder to another folder.
 *
 * @since BuddyBoss 1.3.2
 */
function bp_nouveau_ajax_document_folder_move() {

	$response = array(
		'feedback' => sprintf(
			'<div class="bp-feedback error bp-ajax-message"><span class="bp-icon" aria-hidden="true"></span><p>%s</p></div>',
			esc_html__( 'There was a problem performing this action. Please try again.', 'buddyboss' )
		),
	);

	// Bail if not a POST action.
	if ( ! bp_is_post_request() ) {
		wp_send_json_error( $response );
	}

	$destination_folder_id = ! empty( filter_input( INPUT_POST, 'folderMoveToId', FILTER_VALIDATE_INT ) ) ? (int) filter_input( INPUT_POST, 'folderMoveToId', FILTER_VALIDATE_INT ) : 0;
	$folder_id             = ! empty( filter_input( INPUT_POST, 'currentFolderId', FILTER_VALIDATE_INT ) ) ? (int) filter_input( INPUT_POST, 'currentFolderId', FILTER_VALIDATE_INT ) : 0;

	if ( '' === $destination_folder_id ) {
		wp_send_json_error( $response );
	}

	bp_document_move_folder( $folder_id, $destination_folder_id );

	$content = '';
	ob_start();

	if ( bp_has_document( bp_ajax_querystring( 'document' ) ) ) :

		if ( empty( filter_input( INPUT_POST, 'page', FILTER_SANITIZE_STRING ) ) || 1 === (int) filter_input( INPUT_POST, 'page', FILTER_SANITIZE_STRING ) ) :
			?>

			<div class="document-data-table-head">
				<span class="data-head-sort-label">:<?php esc_html_e( 'Sort By:', 'buddyboss' ); ?></span>
				<div class="data-head data-head-name">
				<span>
					<?php esc_html_e( 'Name', 'buddyboss' ); ?>
					<i class="bb-icon-triangle-fill"></i>
				</span>

				</div>
				<div class="data-head data-head-modified">
				<span>
					<?php esc_html_e( 'Modified', 'buddyboss' ); ?>
					<i class="bb-icon-triangle-fill"></i>
				</span>

				</div>
				<div class="data-head data-head-visibility">
				<span>
					<?php esc_html_e( 'Visibility', 'buddyboss' ); ?>
					<i class="bb-icon-triangle-fill"></i>
				</span>
				</div>
			</div><!-- .document-data-table-head -->

			<div id="media-folder-document-data-table">
			<?php
			bp_get_template_part( 'document/activity-document-move' );
			bp_get_template_part( 'document/activity-document-folder-move' );

		endif;

		while ( bp_document() ) :
			bp_the_document();

			bp_get_template_part( 'document/document-entry' );

		endwhile;

		if ( bp_document_has_more_items() ) :
			?>
			<div class="pager">
				<div class="dt-more-container load-more">
					<a class="button outline full" href="<?php bp_document_load_more_link(); ?>"><?php esc_html_e( 'Load More', 'buddyboss' ); ?></a>
				</div>
			</div>
			<?php
		endif;

		if ( empty( filter_input( INPUT_POST, 'page', FILTER_SANITIZE_STRING ) ) || 1 === (int) filter_input( INPUT_POST, 'page', FILTER_SANITIZE_STRING ) ) :
			?>
			</div> <!-- #media-folder-document-data-table -->
			<?php
		endif;

	else :

		bp_nouveau_user_feedback( 'media-loop-document-none' );

	endif;

	$content .= ob_get_clean();

	wp_send_json_success(
		array(
			'message' => 'success',
			'html'    => $content,
		)
	);

}

function bp_nouveau_ajax_document_get_folder_view(){

	$type = filter_input( INPUT_POST, 'type', FILTER_SANITIZE_STRING );
	$id   = filter_input( INPUT_POST, 'id', FILTER_SANITIZE_STRING );

	if ( 'profile' === $type ) {
		$ul = bp_document_user_document_folder_tree_view_li_html( $id );
    } else {
		$ul = bp_document_user_document_folder_tree_view_li_html( 0, $id );
    }

	wp_send_json_success(
		array(
			'message' => 'success',
			'html'    => $ul,
		)
	);
}

function bp_nouveau_ajax_document_save_privacy(){
    global $wpdb, $bp;

	$id      = filter_input( INPUT_POST, 'itemId', FILTER_VALIDATE_INT );
	$type    = filter_input( INPUT_POST, 'type', FILTER_SANITIZE_STRING );
	$privacy = filter_input( INPUT_POST, 'value', FILTER_SANITIZE_STRING );

	if ( 'folder' === $type ) {
		$q = $wpdb->prepare( "UPDATE {$bp->document->table_name_folders} SET privacy = %s WHERE id = %d", $privacy, $id );
    } else {
		$q = $wpdb->prepare( "UPDATE {$bp->document->table_name} SET privacy = %s WHERE id = %d", $privacy, $id );
    }

	$wpdb->query( $q );

	wp_send_json_success(
		array(
			'message' => 'success',
			'html'    => $type,
		)
	);

}