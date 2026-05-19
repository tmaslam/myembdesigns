<?php
/**
 * Single product short description
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product/short-description.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 3.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

global $post, $product;

$short_description = apply_filters( 'woocommerce_short_description', $post->post_excerpt );

if ( ! $short_description ) {
	return;
}

$tcolor = get_post_meta( $post->ID, 'thread_colors', true);
$designf = get_post_meta( $post->ID, 'design_format', true);
$benefits = get_post_meta( $post->ID, 'benefits_and_notes', true);


?>


<div class="woocommerce-product-details__short-description">
	<table class="table">
<tbody>
<tr>
<td colspan="2"><?php 
  $content = get_post_meta($post->ID, 'size' , true );
    $content = htmlspecialchars_decode($content);
    $content = wpautop( $content );
    echo $content;
 ?>
</td>
</tr>

<?php if($tcolor !=""){?>
<tr>
<th>	
Thread Colors</th>
<td><?php echo $tcolor; ?>
</td> 
</tr> 	
<?php } ?>

<?php if($designf !=""){?>	
 <tr>
<th>		
Design Format</th>
<td><?php echo $designf; ?>
</td> 
</tr> 
<?php } ?>		
</tbody>
</table>
<div id="tags" style="margin-left: 8px;">
<b>Tags:</b><br>
<?php global $product; 
// get product_tags of the current product
$current_tags = get_the_terms( get_the_ID(), 'product_tag' );

//only start if we have some tags
if ( $current_tags && ! is_wp_error( $current_tags ) ) { 

    //create a list to hold our tags

    //for each tag we create a list item
    foreach ($current_tags as $tag) {

        $tag_title = $tag->name; // tag name
        $tag_link = get_term_link( $tag );// tag archive link

        echo '<a href="'.$tag_link.'">'.$tag_title.'</a> &nbsp';
    }

}
?></div>
<div class="shortdis" style="margin-left: 8px;margin-top: 13px;text-align: justify;">
	<?php // echo $short_description; ?>
</div>
</div>



