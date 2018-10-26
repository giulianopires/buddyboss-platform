<?php
/**
 * BuddyBoss Forum Loader.
 *
 * The forums component allow groups to have discusstion..
 *
 * @package BuddyBoss
 * @subpackage Forum
 * @since BuddyBoss 3.1.1
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Set up the bp-forums component.
 *
 * @since BuddyPress 1.6.0
 */
function bp_setup_forums() {
	buddypress()->forums = bbpress()->extend->buddypress = new BP_Forums_Component();
}
add_action( 'bp_setup_components', 'bp_setup_forums', 6 );
