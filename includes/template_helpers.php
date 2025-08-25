<?php
// Minimal helpers to allow template rendering outside WordPress (CLI/tests).
if (!function_exists('esc_html')) {
    function esc_html($text)
    {
        return htmlspecialchars((string) $text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

if (!function_exists('esc_attr')) {
    function esc_attr($text)
    {
        return htmlspecialchars((string) $text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
