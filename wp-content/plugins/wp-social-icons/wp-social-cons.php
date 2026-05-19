<?php

/*
Plugin Name: WP Social Icons
Plugin URI: http://magnigenie.com/wp-social-icons-easily-add-social-icons-site/
Description: Easily add social icons to your site form lots of different font icons. You can put the social icons on any page/post/sidebar/header/footer etc.
Version: 1.1
Author: Magnigenie
Author URI: http://magnigenie.com
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */


define('MG_WPSI_FILE', __FILE__);
define('MG_WPSI_PATH', plugin_dir_path(__FILE__));

require MG_WPSI_PATH . 'inc/wpsi.php';

new MgWpsi();
?>
