<?php
/**
 * Electro Child
 *
 * @package electro-child
 */

/**
 * Include all your custom code here
 */

/* =====================================================
 * PERFORMANCE OPTIMIZATIONS
 * ===================================================== */

/** 1. Disable WordPress Emojis (saves HTTP request + JS) */
remove_action('wp_head', 'print_emoji_detection_script', 7);
remove_action('admin_print_scripts', 'print_emoji_detection_script');
remove_action('wp_print_styles', 'print_emoji_styles');
remove_action('admin_print_styles', 'print_emoji_styles');
remove_filter('the_content_feed', 'wp_staticize_emoji');
remove_filter('comment_text_rss', 'wp_staticize_emoji');
remove_filter('wp_mail', 'wp_staticize_emoji_for_email');
add_filter('emoji_svg_url', '__return_false');

/** 2. Disable WordPress Embeds (saves JS + prevents slow external embeds) */
function myemb_disable_embeds() {
    wp_deregister_script('wp-embed');
}
add_action('wp_enqueue_scripts', 'myemb_disable_embeds');
remove_action('wp_head', 'wp_oembed_add_discovery_links');
remove_action('wp_head', 'wp_oembed_add_host_js');
add_filter('embed_oembed_discover', '__return_false');

/** 3. Limit Heartbeat API (reduces admin-ajax.php load) */
add_filter('heartbeat_settings', function($settings) {
    $settings['interval'] = 60; // seconds
    return $settings;
});

/** 4. Remove query strings from static resources (better caching) */
add_filter('script_loader_src', 'myemb_remove_query_strings', 15);
add_filter('style_loader_src', 'myemb_remove_query_strings', 15);
function myemb_remove_query_strings($src) {
    if (strpos($src, '?ver=')) {
        $src = remove_query_arg('ver', $src);
    }
    return $src;
}

/** 5. Disable XML-RPC (security + performance) */
add_filter('xmlrpc_enabled', '__return_false');
remove_action('wp_head', 'rsd_link');
remove_action('wp_head', 'wlwmanifest_link');

/** 6. Disable pingbacks */
add_filter('wp_headers', function($headers) {
    unset($headers['X-Pingback']);
    return $headers;
});
add_filter('pings_open', '__return_false');

/** 7. Remove unused WordPress header bloat */
remove_action('wp_head', 'wp_generator');
remove_action('wp_head', 'feed_links', 2);
remove_action('wp_head', 'feed_links_extra', 3);
remove_action('wp_head', 'rest_output_link_wp_head');
remove_action('wp_head', 'wp_shortlink_wp_head');

/** 8. Disable Dashicons on frontend for non-logged-in users */
add_action('wp_enqueue_scripts', function() {
    if (!is_user_logged_in()) {
        wp_deregister_style('dashicons');
    }
});

/** 9. Preconnect to external domains (faster DNS + TCP) */
add_action('wp_head', function() {
    echo "<link rel='dns-prefetch' href='//fonts.googleapis.com'>\n";
    echo "<link rel='dns-prefetch' href='//fonts.gstatic.com'>\n";
    echo "<link rel='preconnect' href='https://fonts.gstatic.com' crossorigin>\n";
}, 1);

/** 10. Disable WooCommerce cart fragments on non-cart pages */
add_action('wp_enqueue_scripts', function() {
    if (!is_cart() && !is_checkout()) {
        wp_dequeue_script('wc-cart-fragments');
    }
}, 11);

/** 11. Disable WooCommerce block styles on frontend (not using blocks) */
add_action('wp_enqueue_scripts', function() {
    wp_dequeue_style('wc-blocks-style');
    wp_dequeue_style('wc-blocks-vendors-style');
}, 100);

/** 12. Remove WooCommerce admin bloat (marketing, onboarding) */
add_filter('woocommerce_admin_features', function($features) {
    $remove = ['marketing', 'onboarding', 'wc-pay-promotion', 'wc-pay-promotion'];
    return array_diff($features, $remove);
});

/** 13. Disable WooCommerce password strength meter on non-account pages */
add_action('wp_enqueue_scripts', function() {
    if (!is_account_page()) {
        wp_dequeue_script('wc-password-strength-meter');
    }
}, 99);

/** 14. Limit post revisions to 3 (prevents DB bloat) */
if (!defined('WP_POST_REVISIONS')) {
    define('WP_POST_REVISIONS', 3);
}

/** 15. Increase autosave interval (reduces DB writes) */
if (!defined('AUTOSAVE_INTERVAL')) {
    define('AUTOSAVE_INTERVAL', 120);
}

/** 16. Disable WordPress auto-update emails */
add_filter('auto_core_update_send_email', function($send, $type) {
    return $type === 'success' ? false : $send;
}, 10, 2);

/* =====================================================
 * END PERFORMANCE OPTIMIZATIONS
 * ===================================================== */



add_action('woocommerce_cart_calculate_fees', function() {
	if (is_admin() && !defined('DOING_AJAX')) {
		return;
	}
	$products_in_cart = count(WC()->cart->get_cart_contents());
	WC()->cart->add_fee(__('Processing fee', 'txtdomain'), $products_in_cart);
});





add_action( 'wp_enqueue_scripts', 'my_theme_enqueue_styles' );
function my_theme_enqueue_styles() {
 
    $parent_style = 'parent-style'; // This is 'twentyfifteen-style' for the Twenty Fifteen theme.
 
    wp_enqueue_style( $parent_style, get_template_directory_uri() . '/style.min.css' );
    wp_enqueue_style( 'child-style',
        get_stylesheet_directory_uri() . '/style.min.css',
        array( $parent_style ),
        wp_get_theme()->get('Version')
    );
}

// Initialize Bootstrap tooltips once per page
add_action( 'wp_footer', 'myemb_init_tooltips', 99 );
function myemb_init_tooltips() {
    ?>
    <script>
    (function($) {
        $(function() {
            $('[data-toggle="tooltip"]').tooltip();
        });
    })(jQuery);
    </script>
    <?php
}

/*function custom_pre_get_posts_query( $q ) {

    $tax_query = (array) $q->get( 'tax_query' );

    $tax_query[] = array(
           'taxonomy' => 'product_cat',
           'field' => 'slug',
           'terms' => array( 'free-designs' ), // Don't display products in the clothing category on the shop page.
           'operator' => 'NOT IN'
    );
    $tax_query[] = array(
           'taxonomy' => 'product_cat',
           'field' => 'slug',
           'terms' => array( 'design-packs' ), // Don't display products in the clothing category on the shop page.
           'operator' => 'NOT IN'
    );

    $q->set( 'tax_query', $tax_query );

}
add_action( 'woocommerce_product_query', 'custom_pre_get_posts_query' );  */
function vComp(){

				 
				    $terms = get_terms(
    array(
        'taxonomy'   => 'events',
        'hide_empty' => false,
    )
);
				    if ( ! empty( $terms ) && ! is_wp_error( $terms ) ){ 
				    $i=0;?>
<div class="container" style="display:none;">
            <div class="row blog">
                <div class="col-md-12">
                    <div id="blogCarousel" class="carousel slide" data-ride="carousel" style="display:none;">

                      <div class="carousel-inner carouseltabs">
                      
                      		
<?php
					 foreach ( $terms as $term ) {
					$term_id = $term->term_id;
				    $meta_image = get_wp_term_image($term_id);  
				    
				    $i++;	?>
		<div class="signletab">
    <div class="col-md-4">
 <a href="<?php echo get_term_link($term); ?>">
    <h2 class="woocommerce-loop-product__title eventtile"><?php echo  $term->name; ?></h2>
   </a>
   </div>
   	</div>
 <?php }  ?>

                      		
                      
                      </div>

                        <!-- Carousel items -->
              
                        <!--.carousel-inner-->
                    </div>
                    <!--.Carousel-->

                </div>
            </div>
</div>
   <?php	 } ?>



          
        <?php
                     if ( ! empty( $terms ) && ! is_wp_error( $terms ) ){ ?>
    
    <div id="amazingcarousel-container-16">
    <div id="amazingcarousel-16" style="display: block; position: relative; width: 100%; max-width: 1200px; margin: 0px auto; direction: ltr;">
        <div class="amazingcarousel-list-container" style="position: relative; margin: 0px auto; overflow: visible; width: 840px;">
            <div class="amazingcarousel-list-wrapper" style="overflow: hidden; width: 840px;">
             <ul class="amazingcarousel-list" style="display: block; position: relative; list-style-type: none; list-style-image: none; background-image: none; background-color: transparent; padding: 0px; margin: 0px 0px 0px -1563.55px; width: 3360px;">
             
                 <?php
                 $i = 0;
                  foreach ( $terms as $term ) {
					$term_id = $term->term_id;
				    $meta_image = get_wp_term_image($term_id);
				    if($meta_image !=""){?>
                <li class="amazingcarousel-item amazingcarousel-item-<?php echo $i; ?>" style="display: block; position: relative; background-image: none; background-color: transparent; margin: 0px; padding: 0px; float: left; width: 120px;">
                <div class="amazingcarousel-item-container" style="position: relative; margin: 0px 2px;">
					<div class="amazingcarousel-images">
						<a href="<?php echo get_term_link($term); ?>" title="Camel" target="_blank"><img src="<?php echo $meta_image; ?>" alt="Camel" style="visibility: visible;"></a>
				</div>
					<div class="amazingcarousel-title"><a href="<?php echo get_term_link($term); ?>" target="_blank"><?php echo  $term->name; ?></a></div>               
				</div>
                </li>
    <?php 
    $i++;
     }
    } ?>
                </ul>


</div>
            <div class="amazingcarousel-prev"></div>
            <div class="amazingcarousel-next"></div>
        </div>
        <div class="amazingcarousel-nav"></div>
    </div>
</div>


      <?php } ?>
      
<?php  }
add_shortcode( 'vShortcode', 'vComp' );



function vEvent(){

				 
				    $terms = get_terms(
    array(
        'taxonomy'   => 'events',
        'hide_empty' => false,
    )
);
				    if ( ! empty( $terms ) && ! is_wp_error( $terms ) ){ 
				    $i=0;?>
<ul class="eventsli">
                      		
<?php
					  foreach ( $terms as $term ) {
					$term_id = $term->term_id;
				    $meta_image = get_wp_term_image($term_id);
				    if($meta_image !=""){?>
       <li>
                <div class="amazingcarousel-item-container" style="position: relative; margin: 0px 2px;">
					<div class="amazingcarousel-image">
						<a href="<?php echo get_term_link($term); ?>" title="Camel" class="html5lightbox" data-group="amazingcarousel-1"><img src="<?php echo $meta_image; ?>" alt="Camel" ></a>
				</div>
					<div class="amazingcarousel-title"><a href="<?php echo get_term_link($term); ?>"><?php echo  $term->name; ?></a></div>                    
				</div>
      </li>
    <?php 
    $i++;
     }
    } ?>
                      
                    
</ul>
   <?php	 } ?>



      
<?php  }
add_shortcode( 'vEventShortcode', 'vEvent' );


function popup(){
?>

 <div id="ac-wrapper" style='display:none'>
    <div id="popup">
          <input type="submit" class="close" name="submit"  placeholder="X" onClick="PopUp('hide')" />
        <center>
            	<div class="newsform">
            <h2>Sign Up for our newsletter</h2>
            <p>Grab the ultimate quality designs today!</p>
            <p>Bespoke embroidery designs will worth for your unique project concept.</p>
            	<?php footer_newsletter_form(); ?></div>
        </center>
    </div>
</div>
				 

				 <?php	
				 
				 
 }
add_shortcode( 'popupShortcode', 'popup' );

add_filter( 'big_image_size_threshold', '__return_false' );

if ( ! function_exists( 'electro_top_bar' ) ) {
    function electro_top_bar() {

        if ( is_page_template( 'template-homepage-v5.php' ) ) {
            $top_bar_classes = 'top-bar top-bar-v1';
        } else {
            $top_bar_classes = 'top-bar';
        }

        if ( apply_filters( 'electro_enable_top_bar', true ) ) : ?>

        <?php

        if ( has_electro_mobile_header() ) {
            if ( apply_filters( 'electro_hide_top_bar_in_mobile', true ) ) {
                $top_bar_classes .= ' hidden-lg-down d-none d-xl-block';
            }
        }

        ?>

        <div class="<?php echo esc_attr( $top_bar_classes ); ?>">
            <div class="container clearfix">
                <div class="topphone">
                    <p>
                        <i class="fa fa-phone" aria-hidden="true"></i> +44 777 888 2231 
                        <i class="fa fa-envelope" ></i> <a href="mailto:sales@myembdesigns.com" target="_blank">sales@myembdesigns.com</a>
                    </p>
                </div>
            <?php
                wp_nav_menu( array(
                    'theme_location'    => 'topbar-right',
                    'container'         => false,
                    'depth'             => 2,
                    'menu_class'        => 'nav nav-inline pull-right electro-animate-dropdown flip',
                    'fallback_cb'       => 'wp_bootstrap_navwalker::fallback',
                    'walker'            => new wp_bootstrap_navwalker()
                ) );
            ?>
            </div>
        </div><!-- /.top-bar -->

        <?php endif;
    }
}

add_filter( 'woocommerce_show_page_title', '__return_false' );

function mytheme_add_woocommerce_support() {
add_theme_support( 'woocommerce' );
}
add_action( 'after_setup_theme', 'mytheme_add_woocommerce_support' );

add_action( 'woocommerce_before_checkout_form', 'checkout_message' );
function checkout_message() {
echo '<p>Please fill all required fields. Thank you!</p>';
}

add_filter( 'woocommerce_billing_fields', 'my_optional_fields' );
function my_optional_fields( $address_fields ) {
$address_fields['billing_phone']['required'] = false;
return $address_fields;
}

add_filter( 'woocommerce_checkout_fields' , 'custom_override_checkout_fields' );
function custom_override_checkout_fields( $fields ) {
unset($fields['order']['order_comments']);
return $fields;
}


// function remove_image_zoom_support() {
//     remove_theme_support('wc-product-gallery-zoom');
// }
// add_action('wp', 'remove_image_zoom_support', 100);


// function remove_extrapreview () {
//     remove_theme_support('wc-product-gallery-zoom');
// }
// add_action ('after_setup_theme', 'remove_extrapreview');



add_action('woocommerce_email_after_order_table', 'add_download_links_to_email', 10, 4);

function add_download_links_to_email($order, $sent_to_admin, $plain_text, $email) {

    if ($sent_to_admin) return;

    foreach ($order->get_items() as $item) {

        $product = $item->get_product();
        if (!$product) continue;

        $files = $product->get_downloads(); // WooCommerce helper

        if (!empty($files)) {
            echo '<p><strong>Download for ' . esc_html($product->get_name()) . ':</strong></p>';

            foreach ($files as $file) {
                echo '<p><a href="' . esc_url($file['file']) . '">' . esc_html($file['name']) . '</a></p>';
            }
        }
    }
}	
?>
/** Fix: Events carousel and logo slider visibility (missing amazingcarousel JS files) */
add_action('wp_enqueue_scripts', function() {
    wp_enqueue_script(
        'myemb-carousel-fix',
        '/myemb/wp-content/uploads/amazingcarousel-fix/initcarousel.js',
        array(),
        '1.0',
        true // Load in footer
    );
});
