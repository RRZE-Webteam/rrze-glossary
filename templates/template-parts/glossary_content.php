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

$posts = get_posts(array('post_type' => 'glossary', 'post_status' => 'publish', 'numberposts' => -1, 'orderby' => 'title', 'order' => 'ASC', 'suppress_filters' => false));
$output = '<div class="fau-glossar">';

$current = "A";
$letters = array();


$accordion = '<div class="accordion">'."\n";

$i = 0;
foreach($posts as $post) {
    $letter = remove_accents(get_the_title($post->ID));
    $letter = mb_substr($letter, 0, 1);
    $letter = mb_strtoupper($letter, 'UTF-8');

    if( $i == 0 || $letter != $current) {
        $accordion .= '<h2 id="letter-'.$letter.'">'.$letter.'</h2>'."\n";
        $current = $letter;
        $letters[] = $letter;
    }
    
    $id = $post->ID.'000'.$i;
    $title = get_the_title($post->ID);

    $content = apply_filters( 'the_content',  get_post_field('post_content',$post->ID) );
    $content = str_replace( ']]>', ']]&gt;', $content );
    if ( isset($content) && (mb_strlen($content) > 1)) {
    $desc = $content;
    } else {
    $desc = get_post_meta( $post->ID, 'description', true );
    }

    $accordion .= getAccordionbyTheme($id,$title,'','','',$desc);

    
    
    $i++;
}

$accordion .= '</div>'."\n";

$output .= '<ul class="letters" aria-hidden="true">'."\n";

$alphabet = range('A', 'Z');
foreach($alphabet as $a)  {
    if(in_array($a, $letters)) {
        $output .= '<li class="filled"><a href="#letter-'.$a.'">'.$a.'</a></li>';
    }  else {
        $output .= '<li>'.$a.'</li>';
    }
}

$output .= '</ul>'."\n";
$output .= $accordion;
$output .= '</div>'."\n";

if ( is_plugin_active( 'rrze-elements/rrze-elements.php' ) ) {
    wp_enqueue_script('rrze-accordions');
}

echo $output;
