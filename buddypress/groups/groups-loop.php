<?php
/**
 * BuddyPress Groups Loop - Card-based directory layout.
 *
 * @package hub21-base
 */

if ( ! defined( 'ABSPATH' ) ) exit;
?>

<?php if ( bp_has_groups( bp_ajax_querystring( 'groups' ) ) ) : ?>

	<div id="groups-dir-list" class="groups dir-list">

		<div class="bp-group-card-grid">

			<?php while ( bp_groups() ) : bp_the_group(); ?>

				<div class="bp-group-card">
					<div class="bp-group-card__cover">
						<?php echo hub21_render_group_cover_image(); ?>
					</div>

					<div class="bp-group-card__body">
						<div class="bp-group-card__avatar">
							<a href="<?php bp_group_permalink(); ?>">
								<?php bp_group_avatar( array( 'type' => 'full', 'width' => 80, 'height' => 80 ) ); ?>
							</a>
						</div>

						<h3 class="bp-group-card__name">
							<a href="<?php bp_group_permalink(); ?>"><?php bp_group_name(); ?></a>
						</h3>

						<p class="bp-group-card__description">
							<?php bp_group_description_excerpt(); ?>
						</p>

						<p class="bp-group-card__meta">
							<?php bp_group_member_count(); ?>
						</p>

						<div class="bp-group-card__actions">
							<?php bp_group_join_button(); ?>
						</div>
					</div>
				</div>

			<?php endwhile; ?>

		</div>

		<?php if ( bp_groups_has_more_items() ) : ?>
			<div class="bp-pagination">
				<div class="pag-count" id="group-dir-count">
					<?php bp_groups_pagination_count(); ?>
				</div>
				<div class="pagination-links" id="group-dir-pag">
					<?php bp_groups_pagination_links(); ?>
				</div>
			</div>
		<?php endif; ?>

	</div>

<?php else : ?>

	<div id="message" class="info">
		<p><?php esc_html_e( 'Sorry, no groups were found.', 'hub21-base' ); ?></p>
	</div>

<?php endif; ?>
