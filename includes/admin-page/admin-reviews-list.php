<?php
if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

class IMCR_Reviews_List_Table extends WP_List_Table {

    public function __construct() {
        parent::__construct(array(
            'singular' => 'imcr_review',
            'plural'   => 'imcr_reviews',
            'ajax'     => false
        ));
    }

    public function get_views() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'imcr_post_ratings';
        
        $views = array();
        $current = isset($_GET['status']) ? $_GET['status'] : 'all';
        
        // Ensure table exists
        if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) return $views;

        $total = $wpdb->get_var("SELECT COUNT(id) FROM $table_name");
        $approved = $wpdb->get_var("SELECT COUNT(id) FROM $table_name WHERE status = 'approved'");
        $pending = $wpdb->get_var("SELECT COUNT(id) FROM $table_name WHERE status = 'unapproved'");
        $spam = $wpdb->get_var("SELECT COUNT(id) FROM $table_name WHERE status = 'spam'");
        $trash = $wpdb->get_var("SELECT COUNT(id) FROM $table_name WHERE status = 'trash'");
        
        $base = admin_url('admin.php?page=imcr-reviews');
        
        $views['all'] = '<a href="'.$base.'" class="'.($current === 'all' ? 'current' : '').'">All <span class="count">('.$total.')</span></a>';
        $views['approved'] = '<a href="'.$base.'&status=approved" class="'.($current === 'approved' ? 'current' : '').'">Approved <span class="count">('.$approved.')</span></a>';
        $views['pending'] = '<a href="'.$base.'&status=unapproved" class="'.($current === 'unapproved' ? 'current' : '').'">Pending <span class="count">('.$pending.')</span></a>';
        $views['spam'] = '<a href="'.$base.'&status=spam" class="'.($current === 'spam' ? 'current' : '').'">Spam <span class="count">('.$spam.')</span></a>';
        $views['trash'] = '<a href="'.$base.'&status=trash" class="'.($current === 'trash' ? 'current' : '').'">Trash <span class="count">('.$trash.')</span></a>';
        
        return $views;
    }

    public function get_columns() {
        return array(
            'type'        => 'Type',
            'author'      => 'Author',
            'rating'      => 'Rating',
            'review'      => 'Review',
            'product'     => 'Product',
            'submitted_on'=> 'Submitted On'
        );
    }

    public function single_row( $item ) {
        $status_class = '';
        if ($item['status'] == 'unapproved') $status_class = 'unapproved';
        if ($item['status'] == 'spam') $status_class = 'spam';
        if ($item['status'] == 'trash') $status_class = 'trash';
        
        echo '<tr class="' . esc_attr($status_class) . '">';
        $this->single_row_columns( $item );
        echo '</tr>';
    }

    protected function column_default($item, $column_name) {
        switch ($column_name) {
            case 'type':
                return 'Multi-Criteria<br><small>('.esc_html($item['status']).')</small>';
            case 'rating':
                $ratings = json_decode($item['ratings'], true);
                if (is_array($ratings) && count($ratings) > 0) {
                    $sum = array_sum($ratings);
                    $avg = round($sum / count($ratings), 1);
                    return '<span style="color:#f5b301;font-size:18px;">★</span> <strong>' . $avg . ' / 5</strong>';
                }
                return '-';
            case 'review':
                $html = '<div style="max-width:400px; white-space:pre-wrap;">' . esc_html($item['review']) . '</div>';
                if (!empty($item['admin_reply'])) {
                    $html .= '<div style="margin-top:10px;padding:10px;background:#f0f0f1;border-left:4px solid #72aee6;font-style:italic;"><strong>Admin Reply:</strong><br>' . esc_html($item['admin_reply']) . '</div>';
                }
                return $html;
            case 'product':
                $post = get_post($item['post_id']);
                if ($post) {
                    $edit_link = get_edit_post_link($post->ID);
                    $title = _draft_or_post_title($post->ID);
                    return sprintf('<div><strong><a href="%s">%s</a></strong></div><div class="row-actions"><span class="view"><a href="%s" target="_blank">View</a></span></div>', 
                        esc_url($edit_link), 
                        esc_html($title),
                        get_permalink($post->ID)
                    );
                }
                return 'Post #' . esc_html($item['post_id']);
            case 'submitted_on':
                return wp_date(get_option('date_format') . ' \a\t ' . get_option('time_format'), strtotime($item['created_at']));
            default:
                return print_r($item, true); 
        }
    }

    protected function column_author($item) {
        $actions = array();
        
        if ($item['status'] === 'approved') {
            $actions['unapprove'] = '<a href="?page=imcr-reviews&action=unapprove&review='.$item['id'].'&_wpnonce='.wp_create_nonce('imcr_act_'.$item['id']).'">Unapprove</a>';
        } else {
            $actions['approve'] = '<a href="?page=imcr-reviews&action=approve&review='.$item['id'].'&_wpnonce='.wp_create_nonce('imcr_act_'.$item['id']).'">Approve</a>';
        }
        
        $actions['reply'] = '<a href="#" class="imcr-inline-reply" data-id="'.$item['id'].'">Reply</a>';
        $actions['quickedit'] = '<a href="#" class="imcr-inline-edit" data-id="'.$item['id'].'">Quick Edit</a>';
        $actions['edit'] = '<a href="?page=imcr-reviews&action=edit&review='.$item['id'].'">Edit</a>';
        
        if ($item['status'] !== 'spam') {
            $actions['spam'] = '<a href="?page=imcr-reviews&action=spam&review='.$item['id'].'&_wpnonce='.wp_create_nonce('imcr_act_'.$item['id']).'" class="submitdelete">Spam</a>';
        }
        if ($item['status'] !== 'trash') {
            $actions['trash'] = '<a href="?page=imcr-reviews&action=trash&review='.$item['id'].'&_wpnonce='.wp_create_nonce('imcr_act_'.$item['id']).'" class="submitdelete">Trash</a>';
        } else {
            $actions['untrash'] = '<a href="?page=imcr-reviews&action=untrash&review='.$item['id'].'&_wpnonce='.wp_create_nonce('imcr_act_'.$item['id']).'">Restore</a>';
            $actions['delete'] = '<a href="?page=imcr-reviews&action=delete&review='.$item['id'].'&_wpnonce='.wp_create_nonce('imcr_act_'.$item['id']).'" class="submitdelete">Delete Permanently</a>';
        }
        
        $user = get_userdata($item['user_id']);
        $author_display = 'Anonymous';
        if ($user) {
            $avatar = get_avatar($item['user_id'], 32);
            $author_display = '<div style="display:flex;align-items:center;gap:10px;">' . $avatar . '<strong>' . esc_html($user->display_name) . '</strong></div>';
        }
        
        return $author_display . $this->row_actions($actions);
    }

    public function process_action() {
        if (!isset($_GET['action']) || !isset($_GET['review'])) return;
        
        $action = $_GET['action'];
        $review_id = intval($_GET['review']);
        
        if (!isset($_GET['_wpnonce']) || !wp_verify_nonce($_GET['_wpnonce'], 'imcr_act_' . $review_id)) return;
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'imcr_post_ratings';
        
        if ($action === 'delete') {
            $wpdb->delete($table_name, ['id' => $review_id]);
        } else {
            $status_map = [
                'approve' => 'approved',
                'unapprove' => 'unapproved',
                'spam' => 'spam',
                'trash' => 'trash',
                'untrash' => 'approved'
            ];
            if (isset($status_map[$action])) {
                $wpdb->update($table_name, ['status' => $status_map[$action]], ['id' => $review_id]);
            }
        }
        
        echo '<script>window.location.href="'.admin_url('admin.php?page=imcr-reviews'. (isset($_GET['status']) ? '&status='.esc_js($_GET['status']) : '')).'";</script>';
        exit;
    }

    public function prepare_items() {
        if (isset($_GET['action']) && in_array($_GET['action'], ['approve','unapprove','spam','trash','untrash','delete'])) {
            $this->process_action();
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'imcr_post_ratings';
        
        $per_page = 20;
        $current_page = $this->get_pagenum();
        
        // Ensure table exists
        if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
            $this->items = [];
            return;
        }

        $where = "WHERE 1=1";
        if (isset($_GET['status']) && in_array($_GET['status'], ['approved','unapproved','spam','trash'])) {
            $where .= $wpdb->prepare(" AND status = %s", $_GET['status']);
        } elseif (!isset($_GET['status']) || $_GET['status'] == 'all') {
            // By default typically hide trash
            $where .= " AND status != 'trash'";
        }

        $total_items = $wpdb->get_var("SELECT COUNT(id) FROM $table_name $where");
        $offset = ($current_page - 1) * $per_page;
        
        $results = $wpdb->get_results(
            $wpdb->prepare("SELECT * FROM $table_name $where ORDER BY created_at DESC LIMIT %d OFFSET %d", $per_page, $offset), 
            ARRAY_A
        );

        $this->items = $results;
        
        $this->set_pagination_args(array(
            'total_items' => $total_items,
            'per_page'    => $per_page,
            'total_pages' => ceil($total_items / $per_page)
        ));
        
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = array();
        $this->_column_headers = array($columns, $hidden, $sortable);
    }
    
    public function no_items() {
        _e( 'No IMCR reviews found.', 'ijs-mcr' );
    }
}

function imcr_reviews_page_html() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'imcr_post_ratings';
    
    // Process Edit Form Save
    if (isset($_POST['imcr_save_review']) && isset($_POST['review_id']) && wp_verify_nonce($_POST['imcr_edit_nonce'], 'imcr_edit_review')) {
        $wpdb->update(
            $table_name,
            array(
                'status' => sanitize_text_field($_POST['review_status']),
                'review' => sanitize_textarea_field($_POST['review_text']),
                'admin_reply' => sanitize_textarea_field($_POST['admin_reply_text'])
            ),
            array('id' => intval($_POST['review_id']))
        );
        echo '<div class="notice notice-success is-dismissible"><p>Review updated successfully.</p></div>';
    }
    
    // EDIT PAGE VIEW
    if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['review'])) {
        $review_id = intval($_GET['review']);
        $row = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $review_id));
        if ($row):
            ?>
            <div class="wrap">
                <h1 class="wp-heading-inline">Edit Review</h1>
                <a href="?page=imcr-reviews" class="page-title-action">Back to Reviews</a>
                <form method="post" style="margin-top:20px; background:#fff; padding:20px; border:1px solid #ccd0d4; max-width:800px;">
                    <?php wp_nonce_field('imcr_edit_review', 'imcr_edit_nonce'); ?>
                    <input type="hidden" name="review_id" value="<?php echo esc_attr($row->id); ?>">
                    <table class="form-table">
                        <tr>
                            <th>Status</th>
                            <td>
                                <select name="review_status">
                                    <option value="approved" <?php selected($row->status, 'approved'); ?>>Approved</option>
                                    <option value="unapproved" <?php selected($row->status, 'unapproved'); ?>>Unapproved</option>
                                    <option value="spam" <?php selected($row->status, 'spam'); ?>>Spam</option>
                                    <option value="trash" <?php selected($row->status, 'trash'); ?>>Trash</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th>Review Text</th>
                            <td><textarea name="review_text" rows="8" class="large-text"><?php echo esc_textarea($row->review); ?></textarea></td>
                        </tr>
                        <tr>
                            <th>Admin Reply</th>
                            <td><textarea name="admin_reply_text" rows="5" class="large-text" placeholder="Public response to this review..."><?php echo esc_textarea($row->admin_reply); ?></textarea></td>
                        </tr>
                    </table>
                    <p class="submit">
                        <input type="submit" name="imcr_save_review" class="button button-primary" value="Update Review">
                    </p>
                </form>
            </div>
            <?php
        endif;
        return; // Stop rendering table
    }

    // LIST PAGE VIEW
    ?>
    <style>
        .imcr_review.spam { background-color: #ffeff0; }
        .imcr_review.trash { background-color: #f6f7f7; opacity: 0.8; }
        .imcr_review.unapproved { background-color: #fcf9e8; }
    </style>
    <div class="wrap">
        <h1 class="wp-heading-inline">IMCR Reviews</h1>
        <p>Manage and view all multi-criteria reviews submitted by your customers.</p>
        <hr class="wp-header-end">
        
        <form method="post">
            <?php
            $reviews_table = new IMCR_Reviews_List_Table();
            $reviews_table->prepare_items();
            $reviews_table->views();
            $reviews_table->display();
            ?>
        </form>
    </div>

    <!-- Inline Edit/Reply Javascript -->
    <script>
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('imcr-inline-edit') || e.target.classList.contains('imcr-inline-reply')) {
            e.preventDefault();
            let mode = e.target.classList.contains('imcr-inline-edit') ? 'edit' : 'reply';
            let row = e.target.closest('tr');
            let reviewCol = row.querySelector('.column-review');
            
            if (reviewCol.querySelector('.imcr-quick-save')) return; // Already editing
            
            // Extract raw text before replacing DOM
            let currentText = reviewCol.dataset.reviewText || Array.from(reviewCol.childNodes).find(n => n.nodeType === 1 && n.style.maxWidth === '400px')?.innerText || '';
            let replyDiv = Array.from(reviewCol.childNodes).find(n => n.nodeType === 1 && n.style.backgroundColor === 'rgb(240, 240, 241)');
            let currentReply = reviewCol.dataset.replyText || (replyDiv ? replyDiv.innerText.replace('Admin Reply:\n', '') : '');
            
            if (!reviewCol.dataset.originalHtml) {
                reviewCol.dataset.originalHtml = reviewCol.innerHTML;
                reviewCol.dataset.reviewText = currentText;
                reviewCol.dataset.replyText = currentReply;
            }
            
            reviewCol.innerHTML = `
                <div style="margin-bottom:10px;">
                    <strong>Review:</strong><br>
                    <textarea class="imcr-quick-review" style="width:100%; border:1px solid #ccc; padding:5px;" rows="3">${currentText}</textarea>
                </div>
                <div style="margin-bottom:10px;">
                    <strong>Admin Reply:</strong><br>
                    <textarea class="imcr-quick-reply" style="width:100%; border:1px solid #ccc; padding:5px;" rows="3">${currentReply}</textarea>
                </div>
                <button class="button button-primary button-small imcr-quick-save">Save Response</button>
                <button class="button button-small imcr-quick-cancel">Cancel</button>
            `;
            
            if (mode === 'reply') {
                reviewCol.querySelector('.imcr-quick-reply').focus();
            } else {
                reviewCol.querySelector('.imcr-quick-review').focus();
            }
        }
        
        if (e.target.classList.contains('imcr-quick-cancel')) {
            e.preventDefault();
            let reviewCol = e.target.closest('.column-review');
            reviewCol.innerHTML = reviewCol.dataset.originalHtml;
        }
        
        if (e.target.classList.contains('imcr-quick-save')) {
            e.preventDefault();
            let btn = e.target;
            let reviewCol = btn.closest('.column-review');
            let row = reviewCol.closest('tr');
            let id = row.querySelector('.imcr-inline-edit').dataset.id;
            let newReview = reviewCol.querySelector('.imcr-quick-review').value;
            let newReply = reviewCol.querySelector('.imcr-quick-reply').value;
            
            btn.innerText = 'Saving...';
            btn.disabled = true;
            
            let formData = new FormData();
            formData.append('action', 'imcr_inline_edit');
            formData.append('nonce', '<?php echo wp_create_nonce("imcr_inline_nonce"); ?>');
            formData.append('review_id', id);
            formData.append('review_text', newReview);
            formData.append('admin_reply', newReply);
            
            fetch(ajaxurl, {
                method: 'POST',
                body: formData
            }).then(r=>r.json()).then(res=>{
                if(res.success) {
                    reviewCol.dataset.reviewText = newReview;
                    reviewCol.dataset.replyText = newReply;
                    let replyHtml = newReply ? '<div style="margin-top:10px;padding:10px;background:#f0f0f1;border-left:4px solid #72aee6;font-style:italic;"><strong>Admin Reply:</strong><br>' + newReply + '</div>' : '';
                    reviewCol.innerHTML = '<div style="max-width:400px; white-space:pre-wrap;">' + newReview + '</div>' + replyHtml;
                    reviewCol.dataset.originalHtml = reviewCol.innerHTML;
                } else {
                    alert('Saved failed.');
                    btn.innerText = 'Save Response';
                    btn.disabled = false;
                }
            });
        }
    });
    </script>
    <?php
}
