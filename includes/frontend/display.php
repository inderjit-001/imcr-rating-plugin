<?php

function frontend_html() {
    global $wpdb;
    $post_id = get_the_ID();
    $user_id = get_current_user_id();
    $table_name = $wpdb->prefix . 'imcr_post_ratings';
    
    // Collect dynamic rating labels
    $rating_labels = [];
    for ($i = 1; $i <= 5; $i++) {
        $label = get_option("imcr_rating_label_$i");
        if (!empty($label)) {
            $rating_labels[] = $label;
        }
    }

    if (empty($rating_labels)) return ''; // No criteria configured

    // Fetch existing reviews
    $existing_reviews = [];
    if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name) {
        $existing_reviews = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table_name WHERE post_id = %d AND status = 'approved' ORDER BY created_at DESC", 
            $post_id
        ));
    }
    
    $user_already_reviewed = false;
    $criteria_totals = array();
    $criteria_counts = array();
    
    foreach ($existing_reviews as $review_row) {
        if ($user_id > 0 && $review_row->user_id == $user_id) {
            $user_already_reviewed = true;
        }
        $ratings = json_decode($review_row->ratings, true);
        if (is_array($ratings)) {
            foreach ($ratings as $field_index => $val) {
                if (!isset($criteria_totals[$field_index])) {
                    $criteria_totals[$field_index] = 0;
                    $criteria_counts[$field_index] = 0;
                }
                $criteria_totals[$field_index] += (int)$val;
                $criteria_counts[$field_index]++;
            }
        }
    }

    $averages = array();
    foreach ($rating_labels as $index => $label) {
        $field_key = $index + 1;
        $avg = 0;
        if (isset($criteria_totals[$field_key]) && $criteria_counts[$field_key] > 0) {
            $avg = round($criteria_totals[$field_key] / $criteria_counts[$field_key], 1);
        }
        $averages[$field_key] = $avg;
    }

    ob_start();
    ?>
    <div id="imcr-reviews-container">
        
        <?php if (!empty($existing_reviews)): ?>
        <div class="imcr-averages-summary">
            <h3 class="imcr-title">Overall Ratings</h3>
            <div class="imcr-overall-grid">
            <?php foreach ($rating_labels as $index => $label): ?>
                <?php 
                $field_key = $index + 1; 
                $avg = $averages[$field_key]; 
                $percentage = ($avg / 5) * 100;
                ?>
                <div class="imcr-progress-item">
                    <div class="imcr-progress-label">
                        <strong><?php echo esc_html($label); ?></strong>
                        <span><?php echo number_format($avg, 1); ?></span>
                    </div>
                    <div class="imcr-progress-bar">
                        <div class="imcr-progress-fill" style="width: <?php echo $percentage; ?>%;"></div>
                    </div>
                </div>
            <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
        
        <div id="imcr-rating-box" data-post-id="<?php echo esc_attr($post_id); ?>">
            <h3 class="imcr-title">Leave a Review</h3>
            
            <?php if (!is_user_logged_in()): ?>
                <div class="imcr-alert imcr-alert-info">You must be logged in to leave a review.</div>
            <?php elseif ($user_already_reviewed): ?>
                <div class="imcr-alert imcr-alert-success">You have already reviewed this product. Thank you!</div>
            <?php else: ?>
                <div class="imcr-rating-grid">
                <?php foreach ($rating_labels as $index => $label): ?>
                    <div class="imcr-criteria" data-field="<?php echo esc_attr($index + 1); ?>">
                        <label><?php echo esc_html($label); ?></label>
                        <div class="imcr-stars">
                            <?php for ($star = 1; $star <= 5; $star++): ?>
                                <span class="imcr-star" data-value="<?php echo $star; ?>">
                                    <svg viewBox="0 0 24 24" width="28" height="28" fill="currentColor"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>
                                </span>
                            <?php endfor; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
                </div>

                <div class="imcr-review">
                    <label>Your Feedback</label>
                    <textarea placeholder="Tell us about your experience..."></textarea>
                </div>

                <button id="imcr-submit"><span>Submit Rating</span></button>
                <div id="imcr-response"></div>
            <?php endif; ?>
        </div>

        <?php if (!empty($existing_reviews)): ?>
            <div class="imcr-submitted-reviews">
                <h3 class="imcr-title">Customer Feedback</h3>
                <div class="imcr-reviews-grid">
                <?php foreach ($existing_reviews as $review_row): ?>
                    <?php 
                        $user_info = get_userdata($review_row->user_id);
                        $author_name = $user_info ? $user_info->display_name : 'Anonymous';
                        $date = wp_date((get_option('date_format') ?: 'F j, Y'), strtotime($review_row->created_at));
                        $initials = strtoupper(substr($author_name, 0, 1));
                    ?>
                    <div class="imcr-review-card">
                        <div class="imcr-review-header">
                            <div class="imcr-avatar"><?php echo esc_html($initials); ?></div>
                            <div class="imcr-meta">
                                <strong><?php echo esc_html($author_name); ?></strong>
                                <small><?php echo esc_html($date); ?></small>
                            </div>
                        </div>
                        <?php if (!empty($review_row->review)): ?>
                            <div class="imcr-review-body">
                                <p><?php echo nl2br(esc_html($review_row->review)); ?></p>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($review_row->admin_reply)): ?>
                            <div class="imcr-admin-reply" style="margin-top: 15px; padding: 15px; background: rgba(16, 185, 129, 0.05); border-left: 4px solid var(--imcr-primary); border-radius: 4px;">
                                <strong style="display: block; margin-bottom: 5px; color: var(--imcr-primary);">Store Manager</strong>
                                <p style="margin: 0; color: var(--imcr-text-muted); font-style: italic;"><?php echo nl2br(esc_html($review_row->admin_reply)); ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
    <?php
    return ob_get_clean();
}

// Append the rating box to the content
add_action('wp', 'imcr_add_custom_content'); // Use wp hook as template_redirect might be too late for some WooCommerce hooks
function imcr_add_custom_content() {
    $enabled = get_option('imcr_enable_reviews', 'yes');
    if ($enabled !== 'yes') return;

    if (is_singular('product') && class_exists('WooCommerce')) {
        // WooCommerce: Hook into tabs
        add_filter('woocommerce_product_tabs', 'imcr_woo_override_reviews_tab', 98);
        
        // Disable WooCommerce default rating stars under title on single product and loops
        remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_rating', 10);
        remove_action('woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_rating', 5);
    } else {
        // Standard WP
        add_filter('the_content', 'imcr_append_message_to_content');
    }
}

function imcr_woo_override_reviews_tab($tabs) {
    if (isset($tabs['reviews'])) {
        // Override the default WooCommerce review tab content
        $tabs['reviews']['callback'] = 'imcr_woo_reviews_tab_callback';
    } else {
        // If reviews tab is disabled in Woo settings, create a new one
        $tabs['imcr_reviews'] = array(
            'title'    => __('Reviews', 'ijs-mcr'),
            'priority' => 30,
            'callback' => 'imcr_woo_reviews_tab_callback'
        );
    }
    return $tabs;
}

function imcr_woo_reviews_tab_callback() {
    echo frontend_html();
}

function imcr_append_message_to_content($content) {
    if (is_singular()) {
        $content .= frontend_html();
    }
    return $content;
}
