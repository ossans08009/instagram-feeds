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
        'all_items' => 'Instagramアカウント管理',
        'search_items' => 'Search Instagram Accounts',
        'not_found' => 'No Instagram Accounts found.',
        'not_found_in_trash' => 'No Instagram Accounts found in Trash.'
    );

    $args = array(
        'labels' => $labels,
        'public' => false,
        'show_ui' => true,
        'show_in_menu' => 'instagram-feeds',
        'has_archive' => false, 
        'supports' => array('instagram-account', array('title' => true, 'editor' => false, 'autosave' => false)),
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
    // 権限がなければ何もしない
    // 入力されてないフィールドがあっても何もしない
    // 何もするな....黄猿....
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE || !current_user_can('edit_post', $post_id)
     || !isset($_POST['instagram_api_id']) || !isset($_POST['instagram_access_token'])) {
        return;
    }

    // なんとなく変数にする
    $app_id = $_POST['instagram_api_id'];
    $token  = $_POST['instagram_access_token'];

    // instagram基本表示APIからプロフィールを取得するURL
    $api_url = "https://graph.facebook.com/v20.0/" . $app_id . "?fields=name&access_token=" . $token;

    // APIリクエストを送信
    $response = wp_remote_get($api_url);

    // エラーチェック
    if (is_wp_error($response)) {
        return 'プロフィールとれないんですけお！';
    }

    // レスポンスの内容を取得
    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);

    // データが正しく取得できているか確認
    if (!isset($data['name'])) {
        return 'データちゃんととれてないんですけお！';
    }

    // 取得したアカウント名を使用して新しい投稿を作成
    $new_post = array(
        'post_title' => sanitize_text_field($data['name']), // アカウント名を投稿タイトルに設定
        'post_content' => $data['name'] . 'Instagramアカウント', // 投稿のコンテンツを設定
        'post_status' => 'publish',
        'post_type' => 'instagram_account',
    );
    
    // 投稿を作成
    $post_id = wp_insert_post($new_post);

    // カスタムフィールドに他のプロフィール情報を保存
    if (!$post_id) {
        return 'うまく投稿できてなくなぁい？';
    }

    update_post_meta($post_id, '_instagram_api_id', sanitize_text_field($_POST['instagram_api_id']));
    update_post_meta($post_id, '_instagram_access_token', sanitize_text_field($_POST['instagram_access_token']));

    // feed取得のcronを即時実行
    // do_action('fetch_instagram_feed');
}
add_action('save_post', 'instagram_account_save_postdata');

// アクセストークンをリフレッシュする関数
function refresh_instagram_access_token() {
    // アカウント全部取る
    $accounts = get_all_instagram_account_posts();

    foreach($accounts as $account) {
        // 現在のアクセストークンを取得
        $access_token = get_post_meta($account->ID, 'instagram_access_token', true);

        // リフレッシュトークンのAPIエンドポイント
        $api_url = 'https://graph.instagram.com/refresh_access_token?grant_type=ig_refresh_token&access_token=' . $access_token;

        $response = wp_remote_get($api_url);

        // いちいちreturn しない
        // 他のトークンは更新できるかも知れんし
        if (is_wp_error($response)) {
            error_log($account->post_title . ' : アクセストークン更新できんかった;; すでに切れたか、取ったばっかか知らんけど: ' . $response->get_error_message());
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (!isset($data['access_token'])) {
            error_log($account->post_title . ' : ないよぉ！新しいアクセストークンないヨォ！！！: ' . $body);
        }

        // 新しいアクセストークンを保存
        update_post_meta($account->ID, '_instagram_access_token', sanitize_text_field($data['access_token']));
    }

    // feed取得のcronを即時実行
    do_action('fetch_instagram_feed');
}
add_action('refresh_instagram_access_token_event', 'refresh_instagram_access_token');

// アカウントのIDと投稿名取得する
function get_all_instagram_account_posts() {
    // クエリを作成して 'instagram_account' の全投稿を取得
    $query = new WP_Query(array(
        'post_type' => 'instagram_account',
        'posts_per_page' => -1, // すべての投稿を取得
    ));

    // 投稿IDと投稿名のリストを取得
    if ($query->have_posts()) {
        return $query->posts; // すべての投稿オブジェクトを配列として返す
    } else {
        return array(); // 投稿がない場合は空の配列を返す
    }
}
