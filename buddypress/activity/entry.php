<?php
/**
 * BuddyPress Activity Entry Template.
 *
 * @package hub21-base
 */

if ( ! defined( 'ABSPATH' ) ) exit;
?>

<li class="<?php bp_activity_css_class(); ?> <?php echo esc_attr( hub21_bp_get_activity_css_first_class() ); ?>" id="activity-<?php bp_activity_id(); ?>">

	<div class="hub21-activity-entry">
		<div class="hub21-activity-entry__avatar">
			<a href="<?php bp_activity_user_link(); ?>">
				<?php bp_activity_avatar( array( 'width' => 48, 'height' => 48 ) ); ?>
			</a>
		</div>

		<div class="hub21-activity-entry__content">
			<div class="hub21-activity-entry__header">
				<?php bp_activity_action(); ?>
				<time class="hub21-activity-entry__time" datetime="<?php echo esc_attr( bp_get_activity_date_recorded() ); ?>">
					<?php echo esc_html( bp_core_time_since( bp_get_activity_date_recorded() ) ); ?>
				</time>
			</div>

			<div class="hub21-activity-entry__body">
				<?php bp_activity_content_body(); ?>
			</div>

			<div class="hub21-activity-entry__actions">
				<?php if ( bp_activity_can_favorite() ) : ?>
					<?php if ( ! bp_get_activity_is_favorite() ) : ?>
						<a href="<?php bp_activity_favorite_link(); ?>" class="button fav bp-secondary-action" title="<?php esc_attr_e( 'Mark as Favorite', 'hub21-base' ); ?>">
							<?php esc_html_e( 'Favorite', 'hub21-base' ); ?>
						</a>
					<?php else : ?>
						<a href="<?php bp_activity_unfavorite_link(); ?>" class="button unfav bp-secondary-action" title="<?php esc_attr_e( 'Remove Favorite', 'hub21-base' ); ?>">
							<?php esc_html_e( 'Remove Favorite', 'hub21-base' ); ?>
						</a>
					<?php endif; ?>
				<?php endif; ?>

				<?php if ( bp_activity_can_comment() ) : ?>
					<a href="<?php bp_activity_comment_link(); ?>" class="button acomment-reply bp-primary-action" id="acomment-comment-<?php bp_activity_id(); ?>">
						<?php printf( esc_html__( 'Comment (%s)', 'hub21-base' ), bp_activity_get_comment_count() ); ?>
					</a>
				<?php endif; ?>

				<?php if ( bp_activity_user_can_delete() ) : ?>
					<a href="<?php bp_activity_delete_url(); ?>" class="button item-button bp-secondary-action delete-activity confirm" rel="nofollow">
						<?php esc_html_e( 'Delete', 'hub21-base' ); ?>
					</a>
				<?php endif; ?>
			</div>

			<?php if ( bp_activity_can_comment() ) : ?>
				<div class="activity-comments">
					<?php bp_activity_comments(); ?>
				</div>
			<?php endif; ?>
		</div>
	</div>

</li>
