<?php
/**
 * This is part of the templates for displaying the glossary entries
 *
 *
 * @package WordPress
 * @subpackage FAU
 * @since FAU 1.0
*/

namespace RRZE\Glossary;

use RRZE\Glossary\Layout;

echo '<div id="post-' . get_the_ID() . '" class="' . implode(' ', get_post_class()) .'">';

?>



<h1 id="droppoint" class="glossary-title" itemprop="title"><?php the_title(); ?></h1>


<?php 

$postID = get_the_ID();
$cats = Layout::getTermLinks( $postID, 'glossary_category' );
$tags = Layout::getTermLinks( $postID, 'glossary_tag' );            
$details = '<article class="news-details">
<!-- rrze-glossary --><p id="rrze-glossary" class="meta-footer">'
. ( $cats ? '<span class="post-meta-categories"> '. __( 'Categories', 'rrze-glossary' ) . ': ' . $cats . '</span>' : '' )
. ( $tags ? '<span class="post-meta-tags"> '. __( 'Tags', 'rrze-glossary' ) . ': ' . $tags . '</span>' : '' )
. '</p></article>';

the_content(); 
echo $details;

echo '</div>';

