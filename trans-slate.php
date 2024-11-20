<?php
/*
Plugin Name: Trans Slate 
Description: A customizable plugin to manage translations for Slovenian, English, German, and Croatian.
Version: 1.0
Author: Primož Frelih, Agital.si
*/

// Add rewrite rules for language-specific URLs
add_filter('rewrite_rules_array', function($rules) {
    $new_rules = [
        'en/(.+)/?$' => 'index.php?pagename=$matches[1]&lang=en',
        'de/(.+)/?$' => 'index.php?pagename=$matches[1]&lang=de',
        'hr/(.+)/?$' => 'index.php?pagename=$matches[1]&lang=hr',
    ];
    return $new_rules + $rules;
});

// Add 'lang' to query vars
add_filter('query_vars', function($vars) {
    $vars[] = 'lang';
    return $vars;
});

// Template redirect based on the language
add_action('template_redirect', function() {
    $lang = get_query_var('lang');
    if ($lang) {
        global $wp_query;
        $post_id = get_post_meta(get_the_ID(), "translation_$lang", true);
        if ($post_id) {
            $post = get_post($post_id);
            if ($post) {
                setup_postdata($post);
                include(get_template_part('single'));
                exit;
            }
        }
    }
});

// Add meta box for translations
add_action('add_meta_boxes', function() {
    add_meta_box('translations', 'Translations', function($post) {
        $languages = ['en', 'de', 'hr'];
        foreach ($languages as $lang) {
            $translation_id = get_post_meta($post->ID, "translation_$lang", true);
            echo '<label>' . strtoupper($lang) . ' Translation</label>';
            echo '<input type="text" name="translation_' . $lang . '" value="' . esc_attr($translation_id) . '" style="width: 100%; margin-bottom: 10px;"><br>';
        }
    }, 'post');
});

// Save translation metadata
add_action('save_post', function($post_id) {
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    $languages = ['en', 'de', 'hr'];
    foreach ($languages as $lang) {
        if (isset($_POST["translation_$lang"])) {
            update_post_meta($post_id, "translation_$lang", sanitize_text_field($_POST["translation_$lang"]));
        }
    }
});

// Add language switcher
function language_switcher() {
    $languages = ['sl' => '', 'en' => '/en', 'de' => '/de', 'hr' => '/hr'];
    echo '<ul class="language-switcher">';
    foreach ($languages as $lang => $prefix) {
        echo '<li><a href="' . esc_url(site_url($prefix . $_SERVER['REQUEST_URI'])) . '">' . strtoupper($lang) . '</a></li>';
    }
    echo '</ul>';
}
add_action('wp_footer', 'language_switcher');