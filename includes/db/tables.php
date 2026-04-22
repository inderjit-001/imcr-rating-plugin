<?php
/**
 * Create the custom table for storing post ratings
 */
function imcr_create_ratings_table() {
    global $wpdb;

    $table_name = $wpdb->prefix . 'imcr_post_ratings';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        post_id BIGINT UNSIGNED NOT NULL,
        user_id BIGINT UNSIGNED NOT NULL,
        ratings JSON NOT NULL,
        review TEXT DEFAULT NULL,
        admin_reply TEXT DEFAULT NULL,
        status VARCHAR(20) DEFAULT 'approved',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY unique_user_post (post_id, user_id),
        INDEX idx_post (post_id),
        INDEX idx_user (user_id)
    ) $charset_collate;";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql);
}

// Add an init hook to force upgrade the table since we added columns after activation
add_action('admin_init', 'imcr_upgrade_db_check');
function imcr_upgrade_db_check() {
    $db_version = get_option('imcr_db_version', '1.0');
    if ($db_version !== '1.1') {
        imcr_create_ratings_table();
        update_option('imcr_db_version', '1.1');
    }
}
