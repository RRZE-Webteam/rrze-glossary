<?php
/**
 * The template for displaying Glossary 
 *
 *
 * @package WordPress
 * @subpackage FAU
 * @since FAU 1.0
*/

use RRZE\Glossary\Layout;

$thisThemeGroup = Layout::getThemeGroup();

get_header();
if ($thisThemeGroup == 'fauthemes') {
    $currentTheme = wp_get_theme();		
    $vers = $currentTheme->get( 'Version' );
      if (version_compare($vers, "2.3", '<')) {      
        get_template_part('template-parts/hero', 'small'); 
      }
?>

    <div id="content">
        <div class="container">
            <div class="row">
                <div class="col-xs-12">
                    <main id="droppoint">
                        <h1 class="screen-reader-text"><?php echo __('Index','fau'); ?></h1>

<?php } elseif ($thisThemeGroup == 'rrzethemes') {

if (!is_front_page()) { ?>
    <div id="sidebar" class="sidebar">
        <?php get_sidebar('page'); ?>
    </div><!-- .sidebar -->
<?php } ?>

<div id="primary" class="content-area">
    <div id="content" class="site-content" role="main">

<?php }else{ ?>

<div id="sidebar" class="sidebar">

    <?php get_sidebar(); ?>

</div>
<div id="primary" class="content-area">
    <main id="main" class="site-main">

<?php }
