<?php
/**
 * This is part of the templates for displaying the Glossary
 *
 *
 * @package WordPress
 * @subpackage FAU
 * @since FAU 1.0
*/

namespace RRZE\Glossary;


$posts = get_posts(array('post_type' => 'glossary', 'post_status' => 'publish', 'numberposts' => -1, 'orderby' => 'title', 'order' => 'ASC', 'suppress_filters' => false));


$current = "A";
$letters = array();


$accordion = '[collapsibles]';

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

    $tmp = str_replace( ']]>', ']]&gt;', get_post_field( 'post_content', $post->ID ));
    if ( !isset( $tmp ) || ( mb_strlen( $tmp ) < 1 ) ) {
        $tmp = get_post_meta( $post->ID, 'description', true );
    }

    $accordion .= '[collapse title="' . $title . '" name="ID-' . $id . '"]' . $tmp . '[/collapse]';
    $i++;
}

$accordion .= '[/collapsibles]';

$register = '<div class="fau-glossary"><div class="fau-glossar"><ul class="letters" aria-hidden="true">';

$alphabet = range('A', 'Z');
foreach($alphabet as $a)  {
    if(in_array($a, $letters)) {
        $register .= '<li class="filled"><a href="#letter-'.$a.'">'.$a.'</a></li>';
    }  else {
        $register .= '<li>'.$a.'</li>';
    }
}

$register .= '</ul></div>';

echo '<h2>' . __('Glossary', 'rrze-glossary') . '</h2>';
echo $register;
echo do_shortcode($accordion);
