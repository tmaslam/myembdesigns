<?php

class MgWpsi {

    protected $option_name = 'mg-wpsi';

    public function __construct() {

        // Admin sub-menu
        add_action('admin_init', array($this, 'admin_init'));
        add_action('admin_menu', array($this, 'add_page'));
        add_shortcode('wp_social_icons', array($this, 'wp_social_icons_shortcode'));
        add_filter( 'widget_text', 'do_shortcode');
    }

    function wp_social_icons_shortcode($atts){
        wp_enqueue_style( 'mg_wpsi_icons', plugins_url() . '/wp-social-icons/assets/icons/style.css', array(), '1.0' );
        $options = get_option($this->option_name);
        $socials = json_decode($options['socials']);
        $output = '<style>.mg-wpsi{ list-style: none !important; min-height:10px; } .mg-wpsi li{ float: left !important; margin: 0px 5px !important; } .mg-wpsi li a{ text-decoration: none; } .mg-wpsi a i { color:'.$options['color'].'; font-size:'.$options['icon_size'].'px !important; } .mg-wpsi a:hover i { color:'.$options['color_hover'].' }</style>';
        $output .= '<ul class="mg-wpsi">';

        if( is_array($socials) && count($socials) > 0 ){
            foreach ($socials as $social) {
                $output .= '<li><a href="'.$social->link.'" target="'.$options['link_target'].'"><i class="'.$social->icon.'"></i></a></li>';
            }
        }
        $output .= '</ul>';
        return $output;
    }	

    // White list our options using the Settings API
    public function admin_init() {
        register_setting('mg_wpsi_options', $this->option_name, array($this, 'validate'));
    }

    // Add entry in the settings menu
    public function add_page() {
        $hook_suffix = add_options_page('WP Social Icons', 'WP Social Icons', 'manage_options', 'wp-social-icons', array($this, 'options_do_page'));
        add_action( 'load-'. $hook_suffix, array( $this, 'wpsi_settings_page_load' ) );
    }

    function wpsi_settings_page_load() {
        //Enqueue styles and scripts
        wp_enqueue_style( 'mg_wpsi_icons', plugins_url() . '/wp-social-icons/assets/icons/style.css', array(), '1.0' );
        wp_enqueue_style( 'mg_wpsi_iconpicker', plugins_url() . '/wp-social-icons/assets/css/jquery.fonticonpicker.min.css', array(),  '1.0' );
        wp_enqueue_style( 'wp-color-picker' );

        // Enqueue icon-picker js
        wp_enqueue_script( 'icon-picker', plugins_url() . '/wp-social-icons/assets/js/jquery.fonticonpicker.min.js', array( 'jquery' ), '1.0' );

        // Enqueue custom option panel JS
        wp_enqueue_script( 'options-custom', plugins_url() . '/wp-social-icons/assets/js/script.js', array( 'jquery','wp-color-picker', 'icon-picker' ), '1.0' );
        $option_var = array( 'options_path' => plugins_url() . '/wp-social-icons/assets' );
        wp_localize_script( 'options-custom', 'mgwpsi' , $option_var );
    }

    // Print the menu page itself
    public function options_do_page() {
        $options = get_option($this->option_name);
        ?>
        <div class="wrap">
            <h2>WP Social Icons</h2>
            <form method="post" action="options.php">
                <?php settings_fields('mg_wpsi_options'); ?>
                <table class="form-table">
                    <tr><td width="60%"><table class="widefat" style="padding:15px;>
                    <tr valign="top"><th scope="row">Icon size(in px):</th>
                        <td><input type="number" style="width:110px;" name="<?php echo $this->option_name?>[icon_size]" value="<?php echo $options['icon_size']; ?>" /></td>
                    </tr>
                    <tr valign="top"><th scope="row">Open links in new tab:</th>
                        <td>
                            <input type="radio" name="<?php echo $this->option_name?>[link_target]" value="_blank" <?php if( $options['link_target'] == '_blank' ) echo 'checked="checked"';?> />Yes &nbsp;
                            <input type="radio" name="<?php echo $this->option_name?>[link_target]" value="_self" <?php if( $options['link_target'] != '_blank' ) echo 'checked="checked"';?> /> No</td>
                    </tr>
                    <tr valign="top"><th scope="row">Icon Color:</th>
                        <td><input type="text" name="<?php echo $this->option_name?>[color]" class="mg-color" value="<?php echo $options['color']; ?>" /></td>
                    </tr>                    <tr valign="top"><th scope="row">Icon Hover Color:</th>
                        <td><input type="text" name="<?php echo $this->option_name?>[color_hover]" class="mg-color" value="<?php echo $options['color_hover']; ?>" /></td>
                    </tr>                    <tr><th scope="row" colspan="2">Social Icons:</th></tr>
                    <tr><td colspan="2">
                        <?php
                        $socials = json_decode($options['socials']);
                        $output = '';
                        $output .='<div class="mg-new-field"><input type="button" class="mg-add-new button button-primary" value="Add New"></div><div class="mg-social-fields">';
                        
                        if( is_array ( $socials ) && count( $socials ) > 0 ) {
                            foreach( $socials as $social ) {
                                
                                $output .= '<div class="mg-new-fields"><input type="text" name="' . esc_attr( $this->option_name . '[socials][icon][]' ) . '" class="mg-icon-picker"  value="'.$social->icon.'">';
                                
                                $output .= '<input type="text" name="' . esc_attr( $this->option_name . '[socials][link][]' ) . '" class="social_link" value="'.$social->link.'"><input type="button" class="button" value="Remove" /></div>';

                            }
                        }
                        else {
                                $output .= '<div class="mg-new-fields"><input type="text" name="' . esc_attr( $this->option_name . '[socials][icon][]' ) . '" class="mg-icon-picker"  value="">';
                                
                                $output .= '<input type="text" placeholder="Enter your url here" name="' . esc_attr( $this->option_name . '[socials][link][]' ) . '" class="' . esc_attr( 'social_link' ) . '"  value=""><input type="button" class="button" value="Remove" /></div>';
                        }
                        $output .= '</div>';                        
                        echo $output;
                        ?>
                    </td></tr>
                </table>
            </td>
            <td style="vertical-align:top">
                <table class="widefat">
                    <tr>
                        <td>
                            <h3 style="text-align:center; color:#ED7023;background:#333;padding:30px;">
                                WP Social Icons Pro is out now. <br><a href="http://magnigenie.com/product/wp-social-icons-pro/">Check out the cool features</a>
                            </h3>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <h3>
                                Need help? <a href="http://magnigenie.com/support/queries/wp-social-icons" target="_blank" class="button support">Visit Support Forum</a>
                            </h3>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            Put this shortcode on any page/post/widget etc to show the social icons on your site.
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <code>[wp_social_icons]</code>
                        </td>
                    </tr>
                </table>
            </td>
        </tr></table>
                <p class="submit">
                    <input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
                </p>
            </form>
        </div>
        <?php
    }

    public function validate($input) {

        $valid = array();
        $out = array();
        if ( is_array( $input['socials'] ) ) {
            foreach ( $input['socials']['icon'] as $k => $icon ) {
                if( $icon != '' && $input['socials']['link'][$k] != '' ) {
                    $out[$k]['icon'] = $icon;
                    $out[$k]['link'] = esc_url( $input['socials']['link'][$k] );
                }
            }
        }

        $valid['color'] = sanitize_text_field($input['color']);
        $valid['link_target'] = sanitize_text_field($input['link_target']);
        $valid['color_hover'] = sanitize_text_field($input['color_hover']);
        $valid['icon_size'] = sanitize_text_field($input['icon_size']);
        $valid['socials'] = json_encode($out);
		
        return $valid;
    }
	

}
