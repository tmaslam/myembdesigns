<?php
/**
 * Premium features sidebar
 *
 * @package Wt_Smart_Coupon
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
$wbte_custom_tick = '<img src="' . esc_url( WT_SMARTCOUPON_MAIN_URL ) . 'admin/images/prem_crown.svg" alt="' . esc_attr__( 'Crown', 'wt-smart-coupons-for-woocommerce' ) . '" style="margin-right:9.5px;">';
?>
<div class="wt_smart_coupon_pro_features">
	<div class="wt_smart_coupon_premium">
		<div class="wt_sc_upgrade_pro_main">
			<?php
			if ( method_exists( 'Wt_Smart_Coupon_Admin', 'is_bfcm_season' ) && Wt_Smart_Coupon_Admin::is_bfcm_season() ) {
				echo wp_kses_post( '<img style="position: absolute; top: -20px; right: 20px; " src="' . esc_url( WT_SMARTCOUPON_MAIN_URL . 'admin/images/bfcm_tag.svg' ) . '">' );
			}
			?>
			<span><img src="<?php echo esc_url( WT_SMARTCOUPON_MAIN_URL . 'admin/images/upgrade_box_icon.svg' ); ?>"></span>
			<div class="wt_sc_upgrade_pro_main_hd"><?php esc_html_e( 'Make Irresistible Coupon Campaigns with Powerful Features', 'wt-smart-coupons-for-woocommerce' ); ?></div>
		</div>
		<div class="wt_sc_upgrade_pro_content">
			<h3 class="wt_sc_upgrade_pro_content_head"><?php esc_html_e( 'Smart Coupons for WooCommerce Pro', 'wt-smart-coupons-for-woocommerce' ); ?></h3>
			<ul class="ticked-list">
				<li><?php echo wp_kses_post( $wbte_custom_tick ); ?></span><?php esc_html_e( 'Create advanced BOGO coupons', 'wt-smart-coupons-for-woocommerce' ); ?></li>
				<li><?php echo wp_kses_post( $wbte_custom_tick ); ?></span><?php esc_html_e( 'Offer store credits and gift cards', 'wt-smart-coupons-for-woocommerce' ); ?></li>
				<li><?php echo wp_kses_post( $wbte_custom_tick ); ?></span><?php esc_html_e( 'Set up smart giveaway campaigns', 'wt-smart-coupons-for-woocommerce' ); ?></li>
				<div class="wt-sc-pro-features-all-features">
					<li><?php echo wp_kses_post( $wbte_custom_tick ); ?></span><?php esc_html_e( 'Bulk generate coupons', 'wt-smart-coupons-for-woocommerce' ); ?></li>
					<li><?php echo wp_kses_post( $wbte_custom_tick ); ?></span><?php esc_html_e( 'Coupons to boost conversion rate:', 'wt-smart-coupons-for-woocommerce' ); ?></li>
					<ul class="wt-sc-pro-features-all-features-bullet">
						<li><?php esc_html_e( 'Purchase history-based coupons', 'wt-smart-coupons-for-woocommerce' ); ?></li>
						<li><?php esc_html_e( 'Sign up coupons', 'wt-smart-coupons-for-woocommerce' ); ?></li>
						<li><?php esc_html_e( 'Cart abandonment coupons', 'wt-smart-coupons-for-woocommerce' ); ?></li>
					</ul>
					<li><?php echo wp_kses_post( $wbte_custom_tick ); ?></span><?php esc_html_e( 'Create day-specific deals', 'wt-smart-coupons-for-woocommerce' ); ?></li>
					<li><?php echo wp_kses_post( $wbte_custom_tick ); ?></span><?php esc_html_e( 'Display coupon banners and widgets', 'wt-smart-coupons-for-woocommerce' ); ?></li>
					<li><?php echo wp_kses_post( $wbte_custom_tick ); ?></span><?php esc_html_e( 'Import coupons', 'wt-smart-coupons-for-woocommerce' ); ?></li>
				</div>
				<p class="wt-sc-pro-features-view-all"><?php esc_html_e( 'View all powerful options..', 'wt-smart-coupons-for-woocommerce' ); ?></p>
				<p class="wt-sc-pro-features-view-less"><?php esc_html_e( 'View less...', 'wt-smart-coupons-for-woocommerce' ); ?></p>
			</ul>
		</div>
		<div class="wt_sc_upgrade_pro_lower_green">
			<div class="wt_sc_upgrade_pro_button">
				<a style="background:#4750CB; font-size:16px; font-weight:500; border-radius:11px; line-height:58px; width:calc(100% - 32px); color:#fff; border:none; background-color:#4750CB;" class="button button-secondary" href="<?php echo esc_attr( $wbte_premium_url ); ?>" target="_blank"><?php esc_html_e( 'Unlock pro features', 'wt-smart-coupons-for-woocommerce' ); ?> <span class="dashicons dashicons-arrow-right-alt" style="line-height:58px;font-size:14px;"></span> </a>
			</div>
			<div class="wt_sc_upgrade_pro_icon_box" >
				<img src="<?php echo esc_url( WT_SMARTCOUPON_MAIN_URL . 'admin/images/prem_money.svg' ); ?>">
				<p><?php esc_html_e( '30 Day Money Back Guarantee', 'wt-smart-coupons-for-woocommerce' ); ?></p>
			</div>
			<div class="wt_sc_upgrade_pro_icon_box">
				<img src="<?php echo esc_url( WT_SMARTCOUPON_MAIN_URL . 'admin/images/prem_love.svg' ); ?>">
				<p><?php esc_html_e( '99% Customer Satisfaction rating', 'wt-smart-coupons-for-woocommerce' ); ?></p>
			</div>
		</div>
		

	</div>
</div>
<script>
	jQuery(document).ready(function ($) {
		$('.wt-sc-pro-features-view-all, .wt-sc-pro-features-view-less').on('click', function() {
			$('.wt-sc-pro-features-all-features').toggle();
			$('.wt-sc-pro-features-view-all, .wt-sc-pro-features-view-less').toggle();
		});
	});
</script>