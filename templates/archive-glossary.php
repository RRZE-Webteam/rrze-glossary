<?php
/**
 * The template for displaying all Glossary entries
 *
 *
 * @package WordPress
 * @subpackage FAU
 * @since FAU 1.0
*/


include_once('template-parts/head.php');

if ( have_posts() ) : while ( have_posts() ) : the_post();

include('template-parts/faq_content.php');

endwhile; endif;

include_once('template-parts/foot.php');
