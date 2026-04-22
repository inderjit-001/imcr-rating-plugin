<?php

class imcr {
    public function  __construct()
    {
        $this->dependencies();
        add_action('wp_enqueue_scripts', [$this, 'load_scripts']);
    }

    public function dependencies()
    {
        /**
         * 
         * Includes admin page files
         * 
         * */ 
        require_once IMCR_PATH . 'includes/admin-page/admin-page-settings.php';
        require_once IMCR_PATH . 'includes/admin-page/admin-menu-page.php';
        require_once IMCR_PATH . 'includes/admin-page/admin-page-fields.php';
        require_once IMCR_PATH . 'includes/admin-page/admin-reviews-list.php';
        require_once IMCR_PATH . 'includes/frontend/display.php';
        require_once IMCR_PATH . 'includes/frontend/ajax-handler.php';
    }

    public function load_scripts()
    {
        $enabled = get_option('imcr_enable_reviews', 'yes');

        if ($enabled === 'yes' && is_singular()) {
            //styles
            wp_enqueue_style('imcr-frontend-style', IMCR_URL . 'assets/css/frontend.css', array(), filemtime(IMCR_PATH . 'assets/css/frontend.css'));

            //scripts
            wp_enqueue_script('imcr-frontend-script', IMCR_URL . 'assets/js/frontend.js', array(), filemtime(IMCR_PATH . 'assets/js/frontend.js'), true);

            // Pass AJAX URL and Nonce to frontend.js
            wp_localize_script('imcr-frontend-script', 'imcr_ajax_obj', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce'    => wp_create_nonce('imcr_review_nonce'),
            ));
        }
    }
}

new imcr();