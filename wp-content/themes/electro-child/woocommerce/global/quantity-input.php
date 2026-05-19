<?php
/**
 * Product quantity inputs
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/global/quantity-input.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 7.8.0
 *
 * @var bool   $readonly If the input should be set to readonly mode.
 * @var string $type     The input type attribute.
 */

defined( 'ABSPATH' ) || exit;

if ( empty( $classes ) ) {
	$classes = array( 'input-text', 'qty', 'text' );
}

/* translators: %s: Quantity. */
$labelledby = ! empty( $args['product_name'] ) ? sprintf( __( '%s quantity', 'electro' ), wp_strip_all_tags( $args['product_name'] ) ) : '';

if ( $max_value && $min_value === $max_value ) {
	?>
	<div class="quantity hidden hidden-xs-up">
		<input type="hidden" id="<?php echo esc_attr( $input_id ); ?>" class="qty" name="<?php echo esc_attr( $input_name ); ?>" value="<?php echo esc_attr( $min_value ); ?>" />
	</div>
	<?php
} else {
	?>
	<div class="quantity">
		<?php
		/**
		 * Hook to output something before the quantity input field.
		 *
		 * @since 7.2.0
		 */
		do_action( 'woocommerce_before_quantity_input_field' );
		?>
		<label for="<?php echo esc_attr( $input_id ); ?>"><?php esc_html_e( 'Quantity', 'electro' ); ?></label>
		<input
			type="<?php echo esc_attr( $type ); ?>"
			<?php echo ( isset( $readonly ) && $readonly ) ? 'readonly="readonly"' : ''; ?>
			id="<?php echo esc_attr( $input_id ); ?>"
			class="<?php echo esc_attr( join( ' ', (array) $classes ) ); ?>"
			step="<?php echo esc_attr( $step ); ?>"
			min="<?php echo esc_attr( $min_value ); ?>"
			max="<?php echo esc_attr( 0 < $max_value ? $max_value : '' ); ?>"
			name="<?php echo esc_attr( $input_name ); ?>"
			value="<?php echo esc_attr( $input_value ); ?>"
			title="<?php echo esc_attr_x( 'Qty', 'Product quantity input tooltip', 'electro' ); ?>"
			size="4"
			inputmode="<?php echo esc_attr( $inputmode ); ?>"
			<?php if ( ! empty( $labelledby ) ) { ?>
			aria-labelledby="<?php echo esc_attr( $labelledby ); ?>"
			<?php } ?>
		/>
		<?php
		/**
		 * Hook to output something after the quantity input field.
		 *
		 * @since 3.6.0
		 */
		do_action( 'woocommerce_after_quantity_input_field' );
		?>
	</div>
	<?php
}
