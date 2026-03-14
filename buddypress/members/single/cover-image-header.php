<?php
/**
 * BuddyPress Cover Image Header Variant.
 *
 * Full-width cover image container using BP cover image API
 * with gradient fallback using theme colors.
 *
 * @package hub21-base
 */

if ( ! defined( 'ABSPATH' ) ) exit;
?>

<div id="header-cover-image" class="hub21-cover-header">

	<?php
	$cover_url = '';
	if ( function_exists( 'bp_attachments_get_attachment' ) ) {
		$cover_url = bp_attachments_get_attachment( 'url', array(
			'object_dir' => 'members',
			'item_id'    => bp_displayed_user_id(),
		) );
	}
	?>

	<div class="hub21-cover-header__image <?php echo empty( $cover_url ) ? 'hub21-cover-image--fallback' : ''; ?>"
		<?php if ( ! empty( $cover_url ) ) : ?>
			style="background-image: url(<?php echo esc_url( $cover_url ); ?>);"
		<?php endif; ?>
	>
		<div class="hub21-cover-header__overlay"></div>
	</div>

	<div class="hub21-cover-header__content">
		<div class="hub21-cover-header__avatar">
			<?php bp_displayed_user_avatar( array( 'type' => 'full', 'width' => 150, 'height' => 150 ) ); ?>
			<?php echo hub21_user_status( bp_displayed_user_id() ); ?>
		</div>

		<div class="hub21-cover-header__details">
			<h2 class="hub21-cover-header__name">
				<?php echo esc_html( bp_get_displayed_user_fullname() ); ?>
			</h2>

			<?php if ( bp_get_displayed_user_mentionname() ) : ?>
				<p class="hub21-cover-header__mention">
					@<?php echo esc_html( bp_get_displayed_user_mentionname() ); ?>
				</p>
			<?php endif; ?>
		</div>
	</div>

</div>
