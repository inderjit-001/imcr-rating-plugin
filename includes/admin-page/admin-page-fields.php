<?php

/**
 * register imcr_settings_init to the admin_init action hook
 */
add_action('admin_init', 'imcr_settings_init');

function imcr_settings_init()
{

    /**
     * 
     *  Register Settings
     * 
     * */

    // enable reviews checkbox
    register_setting(
        'imcr_options_group',
        'imcr_enable_reviews',
        array('default' => 'yes')
    );

    // multi certeria review fields
    register_setting(
        'imcr_options_group',
        'imcr_multi_certieria_fields'
    );

    register_setting('imcr_options_group', 'imcr_rating_label_1');
    register_setting('imcr_options_group', 'imcr_rating_label_2');
    register_setting('imcr_options_group', 'imcr_rating_label_3');
    register_setting('imcr_options_group', 'imcr_rating_label_4');
    register_setting('imcr_options_group', 'imcr_rating_label_5');





    /**
     * 
     *  Register Section
     * 
     * */

    // register section for post type
    add_settings_section(
        'imcr_settings_section',
        'IMCR Reviews',
        'imcr_settings_section_callback',
        'imcr_options'
    );

    //register section for multi criteria fields
    add_settings_section(
        'imcr_multicriteria_section', // section id
        'Add Rating labels',
        'imcr_multicriteria_section_callback',
        'imcr_options'
    );


    /**
     * 
     *  Register fields
     * 
     * */

    // register field for enabling reviews
    add_settings_field(
        'imcr_enable_reviews_field',
        'Enable Reviews System',
        'imcr_enable_reviews_callback',
        'imcr_options',
        'imcr_settings_section'
    );


    // register multi criteria label fields

    // Label field One
    add_settings_field(
        'imcr_rating_label_1',             // Field ID
        'Rating label One',                  // Label
        'imcr_rating_label_1_callback',    // Callback function
        'imcr_options',                 // Page slug
        'imcr_multicriteria_section'         // Section ID
    );

    // Label field Two
    add_settings_field(
        'imcr_rating_label_2',             // Field ID
        'Rating label Two',                  // Label
        'imcr_rating_label_2_callback',    // Callback function
        'imcr_options',                 // Page slug
        'imcr_multicriteria_section'         // Section ID
    );

    // Label field Three
    add_settings_field(
        'imcr_rating_label_3',             // Field ID
        'Rating label Three',                  // Label
        'imcr_rating_label_3_callback',    // Callback function
        'imcr_options',                 // Page slug
        'imcr_multicriteria_section'         // Section ID
    );

    // Label field Four
    add_settings_field(
        'imcr_rating_label_4',             // Field ID
        'Rating label Four',                  // Label
        'imcr_rating_label_4_callback',    // Callback function
        'imcr_options',                 // Page slug
        'imcr_multicriteria_section'         // Section ID
    );

    // Label field Five
    add_settings_field(
        'imcr_rating_label_5',             // Field ID
        'Rating label Five',                  // Label
        'imcr_rating_label_5_callback',    // Callback function
        'imcr_options',                 // Page slug
        'imcr_multicriteria_section'         // Section ID
    );
}



/**
 * callback functions
 */

// post type section callback
function imcr_settings_section_callback()
{
    echo '<p>Configure the general settings for IMCR.</p>';
}

// Multicriteria section callback
function imcr_multicriteria_section_callback()
{
    echo '<p>Add Rating Labels.</p>';
}




// field content cb
function imcr_enable_reviews_callback()
{
    $enabled = get_option('imcr_enable_reviews', 'yes');
    ?>
    <label>
        <input type="checkbox" name="imcr_enable_reviews" value="yes" <?php checked($enabled, 'yes'); ?> />
        Enable the Multi-Criteria Reviews system globally
    </label>
    <?php
}

/**
 * 
 *  Register multi creteria fields callback
 * 
 * */

// field one
function imcr_rating_label_1_callback()
{
    $value = get_option('imcr_rating_label_1');
    echo '<input type="text" name="imcr_rating_label_1" value="' . esc_attr($value) . '" />';
}

// field two
function imcr_rating_label_2_callback()
{
    $value = get_option('imcr_rating_label_2');
    echo '<input type="text" name="imcr_rating_label_2" value="' . esc_attr($value) . '" />';
}

// field three
function imcr_rating_label_3_callback()
{
    $value = get_option('imcr_rating_label_3');
    echo '<input type="text" name="imcr_rating_label_3" value="' . esc_attr($value) . '" />';
}

// field four
function imcr_rating_label_4_callback()
{
    $value = get_option('imcr_rating_label_4');
    echo '<input type="text" name="imcr_rating_label_4" value="' . esc_attr($value) . '" />';
}

// field five
function imcr_rating_label_5_callback()
{
    $value = get_option('imcr_rating_label_5');
    echo '<input type="text" name="imcr_rating_label_5" value="' . esc_attr($value) . '" />';
}
