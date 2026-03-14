<?php
/**
 * BuddyPress Members Loop - Card-based directory layout.
 *
 * @package hub21-base
 */

if ( ! defined( 'ABSPATH' ) ) exit;
?>

<?php if ( bp_has_members( bp_ajax_querystring( 'members' ) ) ) : ?>

	<div id="members-dir-list" class="members dir-list">

		<div class="bp-member-card-grid">

			<?php while ( bp_members() ) : bp_the_member(); ?>

				<div class="bp-member-card">
					<div class="bp-member-card__cover">
						<?php echo hub21_render_member_cover_image(); ?>
					</div>

					<div class="bp-member-card__body">
						<div class="bp-member-card__avatar">
							<a href="<?php bp_member_permalink(); ?>">
								<?php bp_member_avatar( array( 'type' => 'full', 'width' => 80, 'height' => 80 ) ); ?>
								<?php echo hub21_user_status( bp_get_member_user_id() ); ?>
							</a>
						</div>

						<h3 class="bp-member-card__name">
							<a href="<?php bp_member_permalink(); ?>"><?php bp_member_name(); ?></a>
						</h3>

						<p class="bp-member-card__meta">
							<?php bp_member_last_active(); ?>
						</p>

						<div class="bp-member-card__actions">
							<?php bp_member_add_friend_button(); ?>

							<?php if ( function_exists( 'bp_send_message_button' ) ) : ?>
								<?php bp_send_message_button(); ?>
							<?php endif; ?>
						</div>
					</div>
				</div>

			<?php endwhile; ?>

		</div>

		<?php if ( bp_member_has_more_items() ) : ?>
			<div class="bp-pagination">
				<div class="pag-count" id="member-dir-count">
					<?php bp_members_pagination_count(); ?>
				</div>
				<div class="pagination-links" id="member-dir-pag">
					<?php bp_members_pagination_links(); ?>
				</div>
			</div>
		<?php endif; ?>

	</div>

<?php else : ?>

	<div id="message" class="info">
		<p><?php esc_html_e( 'Sorry, no members were found.', 'hub21-base' ); ?></p>
	</div>

<?php endif; ?>
