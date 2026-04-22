<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

add_action('wp_ajax_submit_imcr_review', 'imcr_handle_review_submission');

function imcr_handle_review_submission() {
    // 1. Verify Nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'imcr_review_nonce')) {
        wp_send_json_error(array('message' => 'Security check failed. Please refresh and try again.'));
    }

    // 2. Check if User is Logged In
    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => 'You must be logged in to submit a review.'));
    }

    $user_id = get_current_user_id();

    // 3. Obtain and sanitize input data
    $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
    $ratings = isset($_POST['ratings']) ? $_POST['ratings'] : '';
    $review  = isset($_POST['review']) ? sanitize_textarea_field($_POST['review']) : '';

    if ($post_id <= 0) {
        wp_send_json_error(array('message' => 'Invalid Post ID.'));
    }

    // Decode tags from JSON string and ensure it's valid
    $ratings_array = json_decode(stripslashes($ratings), true);
    if (!is_array($ratings_array) || empty($ratings_array)) {
        wp_send_json_error(array('message' => 'Invalid ratings data.'));
    }

    // Optional: sanitize individual rating values (expecting integers 1-5 or based on settings)
    $clean_ratings = array();
    foreach ($ratings_array as $key => $val) {
        $clean_ratings[sanitize_key($key)] = intval($val);
    }
    $ratings_json = wp_json_encode($clean_ratings);

    // 4. Save to Database
    global $wpdb;
    $table_name = $wpdb->prefix . 'imcr_post_ratings';

    // Check for existing review by this user on this post just in case
    $existing = $wpdb->get_var($wpdb->prepare(
        "SELECT id FROM $table_name WHERE post_id = %d AND user_id = %d",
        $post_id,
        $user_id
    ));

    if ($existing) {
        wp_send_json_error(array('message' => 'You have already submitted a review for this post.'));
    }

    $inserted = $wpdb->insert(
        $table_name,
        array(
            'post_id' => $post_id,
            'user_id' => $user_id,
            'ratings' => $ratings_json,
            'review'  => $review,
            'created_at' => current_time('mysql'),
            'updated_at' => current_time('mysql'),
        ),
        array('%d', '%d', '%s', '%s', '%s', '%s')
    );

    if ($inserted) {
        wp_send_json_success(array('message' => 'Your review was submitted successfully!'));
    } else {
        wp_send_json_error(array('message' => 'Failed to save your review to the database.'));
    }
}

add_action('wp_ajax_imcr_inline_edit', 'imcr_inline_edit_handler');
function imcr_inline_edit_handler() {
    check_ajax_referer('imcr_inline_nonce', 'nonce');
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Unauthorized');
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'imcr_post_ratings';
    
    $id = intval($_POST['review_id']);
    $review_text = sanitize_textarea_field($_POST['review_text']);
    $admin_reply = sanitize_textarea_field($_POST['admin_reply']);

    $updated = $wpdb->update($table_name, [
        'review' => $review_text,
        'admin_reply' => $admin_reply
    ], ['id' => $id]);

    if ($updated !== false) {
        wp_send_json_success();
    } else {
        wp_send_json_error();
    }
}
