<?php

$atts = '';
foreach ($attributes as $key => $value) {
    if (is_array($value)) {
        $value = implode(',', array_map('strval', $value));
    }
    $atts .= sprintf('%s="%s" ', esc_attr($key), esc_attr($value));
}
echo do_shortcode('[glossary ' . trim($atts) . ']');
