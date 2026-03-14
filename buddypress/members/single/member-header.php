<?php
/**
 * BuddyPress Member Profile Header.
 *
 * @package hub21-base
 */

if ( ! defined( 'ABSPATH' ) ) exit;
?>

<div id="item-header" class="hub21-member-header">

	<div id="item-header-cover-image" class="hub21-member-header__cover">
		<?php
		$cover_url = '';
		if ( function_exists( 'bp_attachments_get_attachment' ) ) {
			$cover_url = bp_attachments_get_attachment( 'url', array(
				'object_dir' => 'members',
				'item_id'    => bp_displayed_user_id(),
			) );
		}

		if ( ! empty( $cover_url ) ) :
		?>
			<div class="hub21-member-header__cover-image" style="background-image: url(<?php echo esc_url( $cover_url ); ?>);"></div>
		<?php else : ?>
			<div class="hub21-member-header__cover-image hub21-cover-image--fallback"></div>
		<?php endif; ?>

		<div class="hub21-member-header__cover-overlay"></div>
	</div>

	<div class="hub21-member-header__info">
		<div class="hub21-member-header__avatar">
			<?php bp_displayed_user_avatar( array( 'type' => 'full', 'width' => 150, 'height' => 150 ) ); ?>
			<?php echo hub21_user_status( bp_displayed_user_id() ); ?>
		</div>

		<div class="hub21-member-header__details">
			<h2 class="hub21-member-header__name">
				<?php echo esc_html( bp_get_displayed_user_fullname() ); ?>
			</h2>

			<?php if ( bp_get_displayed_user_mentionname() ) : ?>
				<p class="hub21-member-header__mention">
					@<?php echo esc_html( bp_get_displayed_user_mentionname() ); ?>
				</p>
			<?php endif; ?>

			<p class="hub21-member-header__last-active">
				<?php bp_last_activity( bp_displayed_user_id() ); ?>
			</p>

			<div class="hub21-member-header__actions">
				<?php bp_member_add_friend_button(); ?>

				<?php if ( function_exists( 'bp_send_message_button' ) ) : ?>
					<?php bp_send_message_button(); ?>
				<?php endif; ?>
			</div>
		</div>
	</div>

</div>
