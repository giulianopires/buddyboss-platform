<?php
/**
 * BuddyBoss - Media Single Album
 *
 * @since BuddyBoss 1.0.0
 */
?>

<?php
global $document_folder_template;
if  ( function_exists( 'bp_is_group_single' ) && bp_is_group_single() && bp_is_group_folders() ) {
	//$action_variables = bp_action_variables();
	$album_id = (int) bp_action_variable( 1 );
} else  {
	$album_id = (int) bp_action_variable( 0 );
}

$bradcrumbs = bp_document_folder_bradcrumb( $album_id );

if ( bp_has_folders( array( 'include' => $album_id ) ) ) :

	while ( bp_folder() ) :
		bp_the_folder();

	    $total_media = $document_folder_template->folder->document['total'];
		?>
        <div id="bp-media-single-folder">
            <div class="album-single-view" <?php echo $total_media == 0 ? 'no-photos' : ''; ?>>
                <div class="bp-media-header-wrap">
                    <div class="bp-media-header-wrap-inner">
                        <div class="bb-single-album-header text-center">
                            <h4 class="bb-title" id="bp-single-album-title"><?php bp_folder_title(); ?></h4>
                        </div> <!-- .bb-single-album-header -->

                        

                            <div class="bb-media-actions">

                                <div id="search-documents-form" class="media-search-form">
                                    <label for="media_document_search" class="bp-screen-reader-text"><?php _e( 'Search', 'buddyboss' ); ?></label>
                                    <input type="text" name="search" id="media_document_search" value="" placeholder="<?php _e( 'Search Documents', 'buddyboss' ); ?>" class="">
                                </div>

                                <?php if ( bp_is_my_profile() || ( bp_is_group() && groups_can_user_manage_albums( bp_loggedin_user_id(), bp_get_current_group_id() ) ) ) : ?>

                                    <a class="bp-add-document button small outline" id="bp-add-document" href="#" >
                                        <i class="bb-icon-upload"></i><?php _e( 'Add Documents', 'buddyboss' ); ?>
                                    </a>

                                    <a href="#" id="bb-create-folder-child" class="bb-create-folder button small outline">
                                        <i class="bb-icon-plus"></i><?php _e( 'Add Folder', 'buddyboss' ); ?>
                                    </a>

                                    <div class="media-folder_items">
                                        <div class="media-folder_actions">
                                            <a href="#" class="media-folder_action__anchor">
                                                <i class="bb-icon-menu-dots-v"></i>
                                            </a>
                                            <div class="media-folder_action__list">
                                                <ul>
                                                    <li>
                                                        <a id="bp-edit-folder-open" href="#"><i class="bb-icon-edit"></i> <?php _e( 'Edit Folder', 'buddyboss' ); ?></a>
                                                    </li>
                                                    <li><a href="#" id="bb-delete-folder"><i class="bb-icon-trash"></i><?php _e( 'Delete Folder', 'buddyboss' ); ?></a></li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div> <!-- .media-folder_items -->

                                    <?php bp_get_template_part( 'document/document-uploader' ); ?>
                                    <?php bp_get_template_part( 'document/create-child-folder' ); ?>
                                    <?php bp_get_template_part( 'document/edit-child-folder' ); ?>

                                <?php endif; ?>

                            </div> <!-- .bb-media-actions -->
                    </div>

                    <?php
                        if ( '' !== $bradcrumbs ) {
                            ?>

                            <?php echo  $bradcrumbs; ?>

                            <?php
                        }
                    ?>
                        
                </div> <!-- .bp-media-header-wrap -->

                <?php //bp_get_template_part( 'media/actions' ); ?>

                <div id="media-stream" class="media" data-bp-list="document">

                    <div id="bp-ajax-loader"><?php bp_nouveau_user_feedback( 'member-document-loading' ); ?></div>

                </div>

            </div>
        </div>
	<?php endwhile; ?>
<?php
endif;