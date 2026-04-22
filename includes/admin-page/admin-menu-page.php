<?php

add_action('admin_menu', 'imcr_options_page');
function imcr_options_page()
{
    add_menu_page(
        'IMCR Settings', // page title
        'IMCR', //menu title
        'manage_options', // capabilities
        'imcr', //slug
        'imcr_options_page_html', // callback
        'dashicons-superhero', // dashicon
        20
    );

    // Submenu for settings (re-registers the top level to rename the first submenu link)
    add_submenu_page(
        'imcr', 
        'IMCR Settings', 
        'Settings', 
        'manage_options', 
        'imcr', 
        'imcr_options_page_html'
    );

    // Submenu for Reviews List
    add_submenu_page(
        'imcr', 
        'All Reviews', 
        'All Reviews', 
        'manage_options', 
        'imcr-reviews', 
        'imcr_reviews_page_html'
    );
}