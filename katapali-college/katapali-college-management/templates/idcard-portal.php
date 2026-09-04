<?php
if ( ! defined( 'ABSPATH' ) ) exit;
/** @var object $card */
$idcard_url = add_query_arg( array( 'kcms_print' => 'idcard', 'kcms_id' => $card->id_card_id ), home_url( '/' ) );
$libcard_url = add_query_arg( array( 'kcms_print' => 'librarycard', 'kcms_id' => $card->id_card_id ), home_url( '/' ) );
?>
<div class="kcms-box">
	<h3>My ID Card</h3>
	<div class="kcms-idcard-preview-row">
		<div class="kcms-idcard-preview-item">
			<?php if ( $card->photo_path ) : ?><img src="<?php echo esc_url( $card->photo_path ); ?>" class="kcms-idcard-thumb"><?php endif; ?>
			<div>
				<div><strong><?php echo esc_html( $card->name ); ?></strong> (Roll: <?php echo esc_html( $card->roll_number ); ?>)</div>
				<div class="kcms-status"><?php echo $card->id_card_generated ? '<span class="kcms-badge kcms-badge-approved">Ready</span>' : '<span class="kcms-badge kcms-badge-submitted">Processing</span>'; ?></div>
				<p><a class="kcms-btn kcms-btn-outline" href="<?php echo esc_url( $idcard_url ); ?>" target="_blank">Preview / Download ID Card</a></p>
			</div>
		</div>
		<div class="kcms-idcard-preview-item">
			<div>
				<div><strong>Library Card</strong></div>
				<div class="kcms-status"><?php echo $card->library_card_generated ? '<span class="kcms-badge kcms-badge-approved">Ready</span>' : '<span class="kcms-badge kcms-badge-submitted">Processing</span>'; ?></div>
				<p><a class="kcms-btn kcms-btn-outline" href="<?php echo esc_url( $libcard_url ); ?>" target="_blank">Preview / Download Library Card</a></p>
			</div>
		</div>
	</div>
	<p class="kcms-hint">Original card is available from the Principal's office. Laminated copies can be requested there. This card is non-transferable and must be returned at the end of the session.</p>
</div>
