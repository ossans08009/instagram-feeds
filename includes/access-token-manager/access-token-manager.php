<?php
// カスタム投稿タイプ「Instagramアカウント」の登録
function create_instagram_account_post_type() {
    $labels = array(
        'name' => 'Instagram Accounts',
        'singular_name' => 'Instagram Account',
        'menu_name' => 'Instagram Accounts',
        'name_admin_bar' => 'Instagram Account',
        'add_new' => 'Add New',
        'add_new_item' => 'Add New Instagram Account',
        'new_item' => 'New Instagram Account',
        'edit_item' => 'Edit Instagram Account',
        'view_item' => 'View Instagram Account',
        'all_items' => 'All Instagram Accounts',
        'search_items' => 'Search Instagram Accounts',
        'not_found' => 'No Instagram Accounts found.',
        'not_found_in_trash' => 'No Instagram Accounts found in Trash.'
    );

    $args = array(
        'labels' => $labels,
        'public' => true,
        'has_archive' => true,
        'menu_position' => 20,
        'supports' => array('title'),
        'show_in_rest' => true, // Gutenberg対応
        'show_in_menu' => true,  // メニューを1つに統一
    );

    register_post_type('instagram_account', $args);
}
add_action('init', 'create_instagram_account_post_type');

// カスタムメタボックスの追加
function instagram_account_add_meta_boxes() {
    add_meta_box(
        'instagram_account_meta_box', // HTML ID
        'Instagram API Details',      // 表示タイトル
        'instagram_account_meta_box_callback', // コールバック関数
        'instagram_account',           // 投稿タイプ
        'normal',                      // 表示する位置
        'default'                      // 表示の優先度
    );
}
add_action('add_meta_boxes', 'instagram_account_add_meta_boxes');

// メタボックスの内容
function instagram_account_meta_box_callback($post) {
    // 保存されているデータの取得
    $instagram_api_id = get_post_meta($post->ID, '_instagram_api_id', true);
    $instagram_access_token = get_post_meta($post->ID, '_instagram_access_token', true);

    ?>
    <label for="instagram_api_id">Instagram API ID:</label>
    <input type="text" id="instagram_api_id" name="instagram_api_id" value="<?php echo esc_attr($instagram_api_id); ?>" style="width:100%;"><br><br>

    <label for="instagram_access_token">Instagram 長期 Access Token:</label>
    <input type="text" id="instagram_access_token" name="instagram_access_token" value="<?php echo esc_attr($instagram_access_token); ?>" style="width:100%;"><br>
    <?php
}

// メタデータの保存
function instagram_account_save_postdata($post_id) {
    // 自動保存時には何もしない
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    // 権限チェック
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    // POSTされたデータの保存
    if (isset($_POST['instagram_api_id'])) {
        update_post_meta($post_id, '_instagram_api_id', sanitize_text_field($_POST['instagram_api_id']));
    }

    if (isset($_POST['instagram_access_token'])) {
        update_post_meta($post_id, '_instagram_access_token', sanitize_text_field($_POST['instagram_access_token']));
    }
}
add_action('save_post', 'instagram_account_save_postdata');

