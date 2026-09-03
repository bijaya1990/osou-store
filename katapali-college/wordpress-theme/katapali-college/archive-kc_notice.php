<?php if ( ! defined( 'ABSPATH' ) ) exit; get_header();
kc_banner( 'Notices', 'Notices' );

$all_notices = get_posts( array( 'post_type' => 'kc_notice', 'posts_per_page' => 40 ) );
$recent_ids  = kc_recent_notice_ids( 6 );
?>
<section class="section">
	<div class="container">
		<?php if ( $all_notices ) : ?>
			<div class="notice-scroll-wrap">
				<div class="notice-scroll-track">
					<?php
					foreach ( array( 1, 2 ) as $pass ) : // rendered twice for a seamless upward CSS loop
						foreach ( $all_notices as $n ) :
							$href    = kc_notice_link( $n->ID );
							$is_file = (bool) get_post_meta( $n->ID, 'kc_file_url', true );
							$is_new  = in_array( $n->ID, $recent_ids, true );
							?>
							<div class="notice-scroll-item">
								<i class="fa-solid fa-circle-chevron-right"></i>
								<div>
									<a class="notice-blue-title" href="<?php echo esc_url( $href ); ?>"<?php echo $is_file ? ' target="_blank" rel="noopener"' : ''; ?>><?php echo esc_html( get_the_title( $n ) ); ?></a>
									<?php if ( $is_new ) : ?><span class="new-blink">New</span><?php endif; ?>
									<div class="mini-date"><i class="fa-regular fa-calendar"></i> <?php echo esc_html( get_the_date( 'd M Y', $n ) ); ?></div>
								</div>
							</div>
						<?php endforeach;
					endforeach;
					?>
				</div>
			</div>
		<?php else : ?>
			<p class="empty-msg">No notices published yet — add one under Katapali College &rarr; Notices.</p>
		<?php endif; ?>
	</div>
</section>
<?php get_footer(); ?>
