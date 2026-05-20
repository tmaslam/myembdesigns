<?php
/**
 * The header v1 for Electro.
 *
 * Displays all of the <head> section and everything up till <div id="content">
 *
 * @package electro
 */
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="msvalidate.01" content="F7C8041F256C99F797AEB91B7C78E3D1" />
<?php wp_head(); ?>
<link rel="profile" href="http://gmpg.org/xfn/11">
<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>">

<!-- Global site tag (gtag.js) - Google Ads -->
<script async src="https://www.googletagmanager.com/gtag/js?id=AW-311087152"></script>
<script> window.dataLayer = window.dataLayer || []; function gtag(){dataLayer.push(arguments);} gtag('js', new Date()); gtag('config', 'AW-311087152'); </script>

<!-- Global site tag (gtag.js) - Google Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id=UA-126747735-1"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());
  gtag('config', 'UA-126747735-1');
</script>

<script>
    function PopUp(hideOrshow) {
    if (hideOrshow == 'hide') document.getElementById('ac-wrapper').style.display = "none";
    else document.getElementById('ac-wrapper').removeAttribute('style');
}
window.onload = function () {
    setTimeout(function () {
        PopUp('show');
    }, 5000);
}
</script>

<!-- Amazing Carousel fix loaded via functions.php -->

<style>
#ac-wrapper {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(255, 255, 255, .6);
    z-index: 1001;
}
input.closepopup {
  background: none;
    border: none;
    font-size: 1rem;
    z-index: 20000;
    position: relative;
}
div#popup img {
    margin-top: -2.7rem;
    border-radius: 4px;
}
#popup {
     width: 555px;
    height: 554px;
    margin: 0 auto;
    background: black;
    border: 2px solid #fff;
    border-radius: 8px !important;
    -moz-border-radius: 25px;
    -webkit-border-radius: 25px;
    box-shadow: #64686e 0px 0px 3px 3px;
    -moz-box-shadow: #64686e 0px 0px 3px 3px;
    -webkit-box-shadow: #64686e 0px 0px 3px 3px;
    position: relative;
    top: 150px;
    left: inherit;
    background-size: cover;
    text-align: right;
}
.amazingcarousel-list-wrapper{
    margin-top: margin-top;
}
.amazingcarousel-hover-effect {
    display: none !important;
}
div#amazingcarousel-16{
    margin-top: 15px;
}
.amazingcarousel-item-container {
    text-align: centertext
}
    .blog .carousel-indicators {
	left: 0;
	top: auto;
    bottom: -40px;
}
.amazingcarousel-title a {
    color: black;
}

/* The colour of the indicators */
.blog .carousel-indicators li {
    background: #a3a3a3;
    border-radius: 50%;
    width: 8px;
    height: 8px;
}

.blog .carousel-indicators .active {
background: #707070;
}
ac-wrapper {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(255, 255, 255, .6);
    z-index: 1001;
}
</style>

</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<div class="off-canvas-wrapper">
<div id="page" class="hfeed site">
    <?php
    /**
     * @hooked electro_skip_links - 0
     * @hooked electro_top_bar - 10
     */
    do_action( 'electro_before_header' ); ?>

    <header id="masthead" class="site-header header-v1 stick-this">
        
        <div class="container hidden-lg-down">
            <?php
            /**
             * @hooked electro_masthead   - 10
             * @hooked electro_navigation - 20
             */
            do_action( 'electro_header_v1' ); ?>
        </div>

        <?php
        /**
         * @hooked electro_handheld_header - 10
         */
        do_action( 'electro_after_header' ); ?>
        
    </header><!-- #masthead -->

    <?php do_action( 'electro_before_content' ); ?>

    <div id="content" class="site-content" tabindex="-1">
        <div class="container">
        <?php
        /**
         * @hooked woocommerce_breadcrumb - 10
         */
        do_action( 'electro_content_top' ); ?>
