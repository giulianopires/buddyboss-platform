<?php
/**
 * BuddyPress Members Widgets.
 *
 * @package BuddyBoss\Members\Widgets
 * @since BuddyPress 2.2.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Register bp-members widgets.
 *
 * Previously, these widgets were registered in bp-core.
 *
 * @since BuddyPress 2.2.0
 */
function bp_members_register_widgets() {
	add_action(
		'widgets_init',
		function() {
			return register_widget( 'BP_Core_Members_Widget' );
		}
	);
	add_action(
		'widgets_init',
		function() {
			return register_widget( 'BP_Core_Whos_Online_Widget' );
		}
	);
	add_action(
		'widgets_init',
		function() {
			return register_widget( 'BP_Core_Recently_Active_Widget' );
		}
	);
}
add_action( 'bp_register_widgets', 'bp_members_register_widgets' );

/**
 * AJAX request handler for Members widgets.
 *
 * @since BuddyPress 1.0.0
 *
 * @see BP_Core_Members_Widget
 */
function bp_core_ajax_widget_members() {
	global $members_template;
	check_ajax_referer( 'bp_core_widget_members' );

	// Setup some variables to check.
	$filter      = ! empty( $_POST['filter'] ) ? $_POST['filter'] : 'recently-active-members';
	$max_members = ! empty( $_POST['max-members'] ) ? absint( $_POST['max-members'] ) : 5;

	// Determine the type of members query to perform.
	switch ( $filter ) {

		// Newest activated.
		case 'newest-members':
			$type = 'newest';
			break;

		// Popular by friends.
		case 'popular-members':
			if ( bp_is_active( 'friends' ) ) {
				$type = 'popular';
			} else {
				$type = 'active';
			}
			break;

		// Default.
		case 'recently-active-members':
		default:
			$type = 'active';
			break;
	}

	// Setup args for querying members.
	$members_args = array(
		'user_id'         => 0,
		'type'            => $type,
		'per_page'        => $max_members,
		'max'             => $max_members,
		'populate_extras' => true,
		'search_terms'    => false,
		'exclude'         => bp_get_users_of_removed_member_types(),
	);

	// Query for members.
	if ( bp_has_members( $members_args ) ) : ?>
		<?php echo '0[[SPLIT]]'; // Return valid result. TODO: remove this. ?>
		<?php
		while ( bp_members() ) :
			bp_the_member();
			?>
			<li class="vcard">
				<div class="item-avatar">
					<a href="<?php bp_member_permalink(); ?>">
						<?php bp_member_avatar(); ?>
						<?php
						$current_time = current_time( 'mysql', 1 );
						$diff         = strtotime( $current_time ) - strtotime( $members_template->member->last_activity );
						if ( $diff < 300 ) { // 5 minutes  =  5 * 60
							?>
							<span class="member-status online"></span>
						<?php } ?>
					</a>
				</div>

				<div class="item">
					<div class="item-title fn"><a href="<?php bp_member_permalink(); ?>"><?php bp_member_name(); ?></a></div>
					<div class="item-meta">
						<?php if ( 'newest' == $settings['member_default'] ) : ?>
							<span class="activity" data-livestamp="<?php bp_core_iso8601_date( bp_get_member_registered( array( 'relative' => false ) ) ); ?>"><?php bp_member_registered(); ?></span>
						<?php elseif ( 'active' == $settings['member_default'] ) : ?>
							<span class="activity" data-livestamp="<?php bp_core_iso8601_date( bp_get_member_last_active( array( 'relative' => false ) ) ); ?>"><?php bp_member_last_active(); ?></span>
						<?php else : ?>
							<span class="activity"><?php bp_member_total_friend_count(); ?></span>
						<?php endif; ?>
					</div>
				</div>
				<div class="member_last_visit"></div>
			</li>

		<?php endwhile; ?>

	<?php else : ?>
		<?php echo '-1[[SPLIT]]<li>'; ?>
		<?php esc_html_e( 'There were no members found, please try another filter.', 'buddyboss' ); ?>
		<?php echo '</li>'; ?>
		<?php
	endif;
}
add_action( 'wp_ajax_widget_members', 'bp_core_ajax_widget_members' );
add_action( 'wp_ajax_nopriv_widget_members', 'bp_core_ajax_widget_members' );
