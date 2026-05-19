<?php
/**
 * Description tab
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product/tabs/description.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 2.0.0
 */

defined( 'ABSPATH' ) || exit;

global $post, $product;

$pack1 = get_field('pack_1');
$pack1size = get_post_meta( $post->ID, 'pack_1_size', true);
$pack2 = get_field('pack_2_image');
$pack2size = get_post_meta( $post->ID, 'pack_2_size', true);
$pack3 = get_field('pack_3_image');
$pack3size = get_post_meta( $post->ID, 'pack_3_size', true);
$pack4 = get_field('pack_4_image');
$pack4size = get_post_meta( $post->ID, 'pack_4_size', true);
$pack5 = get_field('pack_5_image');
$pack5size = get_post_meta( $post->ID, 'pack_5_size', true);
$pack6 = get_field('pack_6_image');
$pack6size = get_post_meta( $post->ID, 'pack_6_size', true);
$pack7 = get_field('pack_7_image');
$pack7size = get_post_meta( $post->ID, 'pack_7_size', true);
$pack8 = get_field('pack_8_image');
$pack8size = get_post_meta( $post->ID, 'pack_8_size', true);
$pack9 = get_field('pack_9_image');
$pack9size = get_post_meta( $post->ID, 'pack_9_size', true);
$pack10 = get_field('pack_10_image');
$pack10size = get_post_meta( $post->ID, 'pack_10_size', true);


$heading = apply_filters( 'woocommerce_product_description_heading', __( 'Description', 'woocommerce' ) );

?>

<?php if ( $heading ) : ?>
	<h2><?php echo esc_html( $heading ); ?></h2>
<?php endif; ?>

<?php // the_content(); ?>
<?php if( !empty( $pack1 ) ): ?>
<div class="entrylogo">
   <div class="entryimg"> <img src="<?php echo esc_url($pack1['url']); ?>" alt="<?php echo esc_attr($pack1['alt']); ?>" /></div>
    <div class="entrysize"><b>Size: </b><?php echo $pack1size; ?></div>
</div>
<?php endif; ?>
<?php if( !empty( $pack2 ) ): ?>
<div class="entrylogo">
   <div class="entryimg"> <img src="<?php echo esc_url($pack2['url']); ?>" alt="<?php echo esc_attr($pack2['alt']); ?>" /></div>
    <div class="entrysize"><b>Size: </b><?php echo $pack2size; ?></div>
</div>
<?php endif; ?>
<?php if( !empty( $pack3 ) ): ?>
<div class="entrylogo">
   <div class="entryimg"> <img src="<?php echo esc_url($pack3['url']); ?>" alt="<?php echo esc_attr($pack3['alt']); ?>" /></div>
    <div class="entrysize"><b>Size: </b><?php echo $pack3size; ?></div>
</div>
<?php endif; ?>
<?php if( !empty( $pack4 ) ): ?>
<div class="entrylogo">
   <div class="entryimg"> <img src="<?php echo esc_url($pack4['url']); ?>" alt="<?php echo esc_attr($pack4['alt']); ?>" /></div>
    <div class="entrysize"><b>Size: </b><?php echo $pack4size; ?></div>
</div>
<?php endif; ?>
<?php if( !empty( $pack5 ) ): ?>
<div class="entrylogo">
   <div class="entryimg"> <img src="<?php echo esc_url($pack5['url']); ?>" alt="<?php echo esc_attr($pack5['alt']); ?>" /></div>
    <div class="entrysize"><b>Size: </b><?php echo $pack5size; ?></div>
</div>
<?php endif; ?>
<?php if( !empty( $pack6 ) ): ?>
<div class="entrylogo">
   <div class="entryimg"> <img src="<?php echo esc_url($pack6['url']); ?>" alt="<?php echo esc_attr($pack6['alt']); ?>" /></div>
    <div class="entrysize"><b>Size: </b><?php echo $pack6size; ?></div>
</div>
<?php endif; ?>
<?php if( !empty( $pack7 ) ): ?>
<div class="entrylogo">
   <div class="entryimg"> <img src="<?php echo esc_url($pack7['url']); ?>" alt="<?php echo esc_attr($pack7['alt']); ?>" /></div>
    <div class="entrysize"><b>Size: </b><?php echo $pack7size; ?></div>
</div>
<?php endif; ?>
<?php if( !empty( $pack8 ) ): ?>
<div class="entrylogo">
   <div class="entryimg"> <img src="<?php echo esc_url($pack8['url']); ?>" alt="<?php echo esc_attr($pack8['alt']); ?>" /></div>
    <div class="entrysize"><b>Size: </b><?php echo $pack8size; ?></div>
</div>
<?php endif; ?>
<?php if( !empty( $pack9 ) ): ?>
<div class="entrylogo">
   <div class="entryimg"> <img src="<?php echo esc_url($pack9['url']); ?>" alt="<?php echo esc_attr($pack9['alt']); ?>" /></div>
    <div class="entrysize"><b>Size: </b><?php echo $pack9size; ?></div>
</div>
<?php endif; ?>
<?php if( !empty( $pack10 ) ): ?>
<div class="entrylogo">
   <div class="entryimg"> <img src="<?php echo esc_url($pack10['url']); ?>" alt="<?php echo esc_attr($pack10['alt']); ?>" /></div>
    <div class="entrysize"><b>Size: </b><?php echo $pack10size; ?></div>
</div>
<?php endif; ?>
