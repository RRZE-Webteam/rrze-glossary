<?php
/**
 * This is part of the templates for displaying the FAQ
 *
 *
 * @package WordPress
 * @subpackage FAU
 * @since FAU 1.0
*/

namespace RRZE\Glossary;

$postID = get_the_ID();

echo do_shortcode('[glossary id=' . $postID . ']');
