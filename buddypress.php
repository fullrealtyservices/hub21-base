<?php
/**
 * BuddyPress Template
 * Sidebar frame is injected via blocksy:header:after hook.
 *
 * @package hub21-base
 */

get_header();
?>
<main id="primary" class="site-main">
	<div class="buddypress-content-area">
		<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
			<article <?php post_class(); ?>>
				<div class="entry-content">
					<?php the_content(); ?>
				</div>
			</article>
		<?php endwhile; endif; ?>
	</div>
</main>
<?php
get_footer();
