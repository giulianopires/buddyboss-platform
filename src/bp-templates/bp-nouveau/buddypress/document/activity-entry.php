<?php
/**
 * BuddyBoss - Activity Document
 *
 * @since BuddyBoss 1.0.0
 * @package BuddyBoss\Core
 */

global $document_template;

$attachment_id     = bp_get_document_attachment_id();
$extension         = bp_document_extension( $attachment_id );
$svg_icon          = bp_document_svg_icon( $extension );
$svg_icon_download = bp_document_svg_icon( 'download' );
$url               = wp_get_attachment_url( $attachment_id );
$filename          = basename( get_attached_file( $attachment_id ) );
$size              = size_format( filesize( get_attached_file( $attachment_id ) ) );
$download_url      = bp_document_download_link( $attachment_id );
$document_privacy  = bp_document_user_can_manage_document( bp_get_document_id(), bp_loggedin_user_id() );
$can_download_btn  = ( true === (bool) $document_privacy['can_download'] ) ? true : false;
$can_manage_btn    = ( true === (bool) $document_privacy['can_manage'] ) ? true : false;
$can_view          = ( true === (bool) $document_privacy['can_view'] ) ? true : false;


$group_id = bp_get_document_group_id();
if ( $group_id > 0 ) {
	$move_id = 'group_' . $group_id;
} else {
	$move_id = 'profile_' . bp_get_document_user_id();
}

?>

<div class="bb-activity-media-elem document-activity <?php echo wp_is_mobile() ? 'is-mobile' : ''; ?>" data-id="<?php bp_document_id(); ?>">
	<div class="document-description-wrap">
		<a href="<?php echo esc_url( $download_url ); ?>" class="entry-img" data-id="<?php bp_document_id(); ?>" data-activity-id="<?php bp_document_activity_id(); ?>">
			<i class="<?php echo esc_attr( $svg_icon ); ?>" ></i>
		</a>
		<a href="<?php echo esc_url( $download_url ); ?>" class="document-detail-wrap">
			<span class="document-title"><?php echo esc_html( $filename ); ?></span>
			<span class="document-description"><?php echo esc_html( $size ); ?></span>
			<span class="document-helper-text"><?php esc_html_e( '- Click to Download', 'buddyboss' ); ?></span>
		</a>
	</div>
	<div class="document-action-wrap">
		<a href="<?php echo esc_url( $download_url ); ?>" class="document-action_download" data-id="<?php bp_document_id(); ?>" data-activity-id="<?php bp_document_activity_id(); ?>" data-balloon-pos="up" data-balloon="<?php esc_html_e( 'Download', 'buddyboss' ); ?>">
			<i class="bb-icon-download"></i>
		</a>
		<?php
		if ( bp_loggedin_user_id() === bp_get_document_user_id() ) {
			?>
			<a href="#" target="_blank" class="document-action_more" data-balloon-pos="up" data-balloon="<?php esc_html_e( 'More actions', 'buddyboss' ); ?>">
				<i class="bb-icon-menu-dots-v"></i>
			</a>
			<div class="document-action_list">
				<ul>
					<li class="move_file"><a href="#" id="<?php echo esc_attr( $move_id ); ?>" class="ac-document-move"><?php esc_html_e( 'Move', 'buddyboss' ); ?></a></li>
					<li class="delete_file"><a href="#"><?php esc_html_e( 'Delete', 'buddyboss' ); ?></a></li>
				</ul>
			</div>
			<?php
		}
		?>
	</div>
	<?php
	if ( 'mp3' === $extension || 'wav' === $extension || 'ogg' === $extension ) {
		?>
		<div class="document-audio-wrap">
			<audio controls>
				<source src="<?php echo esc_url( $url ); ?>" type="audio/mpeg">
				<?php esc_html_e( 'Your browser does not support the audio element.', 'buddyboss' ); ?>
			</audio>
		</div>
		<?php
	}
	if ( 'pdf' === $extension || 'pptx' === $extension || 'pps' === $extension || 'xls' === $extension || 'xlsx' === $extension || 'pps' === $extension || 'ppt' === $extension || 'pptx' === $extension || 'doc' === $extension || 'docx' === $extension || 'dot' === $extension || 'rtf' === $extension || 'wps' === $extension || 'wpt' === $extension || 'dotx' === $extension || 'potx' === $extension || 'xlsm' === $extension ) {
		$attachment_url = wp_get_attachment_url( bp_get_document_preview_attachment_id() );
		?>
		<div class="document-preview-wrap">
			<img src="<?php echo esc_url( $attachment_url ); ?>" alt="" />
		</div><!-- .document-preview-wrap -->
		<?php
	}
	if ( filesize( get_attached_file( $attachment_id ) ) / 1e+6 < 2 ) {
		if ( 'css' === $extension || 'txt' === $extension || 'html' === $extension || 'htm' === $extension || 'js' === $extension || 'csv' === $extension ) {
			$data      = bp_document_get_preview_text_from_attachment( $attachment_id );
			$file_data = $data['text'];
			$more_text = $data['more_text']
			?>
			<div class="document-text-wrap">
				<div class="document-text" data-extension="<?php echo esc_attr( $extension ); ?>">
					<textarea class="document-text-file-data-hidden" style="display: none;"><?php echo esc_html( $file_data ); ?></textarea>
				</div>
				<div class="document-expand">
					<a href="#" class="document-expand-anchor"><i class="bb-icon-plus document-icon-plus"></i> <?php esc_html_e( 'Click to expand', 'buddyboss' ); ?></a>
				</div>
			</div> <!-- .document-text-wrap -->
			<div class="document-action-wrap">
				<a href="#" class="document-action_collapse" data-balloon-pos="down" data-balloon="<?php esc_html_e( 'Collapse', 'buddyboss' ); ?>"><i class="bb-icon-arrow-up document-icon-collapse"></i></a>
				<a href="<?php echo esc_url( $download_url ); ?>" class="document-action_download" data-id="<?php bp_document_id(); ?>" data-activity-id="<?php bp_document_activity_id(); ?>" data-balloon-pos="up" data-balloon="<?php esc_html_e( 'Download', 'buddyboss' ); ?>">
					<i class="bb-icon-download document-icon-download"></i>
				</a>
				<?php
				if ( bp_loggedin_user_id() === bp_get_document_user_id() ) {
					?>
					<a href="#" target="_blank" class="document-action_more" data-balloon-pos="up" data-balloon="<?php esc_html_e( 'More actions', 'buddyboss' ); ?>">
						<i class="bb-icon-menu-dots-v document-icon-download-more"></i>
					</a>
					<div class="document-action_list">
						<ul>
							<li class="move_file"><a href="#" id="<?php echo esc_attr( $move_id ); ?>" class="ac-document-move"><?php esc_html_e( 'Move', 'buddyboss' ); ?></a></li>
							<li class="delete_file"><a href="#"><?php esc_html_e( 'Delete', 'buddyboss' ); ?></a></li>
						</ul>
					</div>
					<?php
				}
				?>
			</div> <!-- .document-action-wrap -->
			<?php
			if ( true === $more_text ) {

				printf(
					/* translators: %s: download string */
					'<div class="more_text_view">%s</div>',
					sprintf(
						/* translators: %s: download url */
						esc_html__( 'This file was truncated for preview. Please <a href="%s">download</a> to view the full file.', 'buddyboss' ),
						esc_url( $download_url )
					)
				);
			}
		}
	}
	?>
</div> <!-- .bb-activity-media-elem -->