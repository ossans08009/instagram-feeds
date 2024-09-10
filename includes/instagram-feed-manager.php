<?php
// カスタム投稿タイプ 'instagram-feed' を登録
function create_instagram_feed_post_type() {
    $labels = array(
        'name' => 'Instagram Feeds',
        'singular_name' => 'Instagram Feed',
        'menu_name' => 'Instagram Feeds',
        'name_admin_bar' => 'Instagram Feed',
        'add_new' => 'Add New Feed',
        'add_new_item' => 'Add New Instagram Feed',
        'new_item' => 'New Instagram Feed',
        'edit_item' => 'Edit Instagram Feed',
        'view_item' => 'View Instagram Feed',
        'all_items' => 'All Instagram Feeds',
        'search_items' => 'Search Instagram Feeds',
        'not_found' => 'No Instagram Feeds found.',
        'not_found_in_trash' => 'No Instagram Feeds found in Trash.'
    );

    $args = array(
        'labels' => $labels,
        'public' => true,
        'has_archive' => true,
        'menu_position' => 21,
        'supports' => array('title', 'editor'),
        'show_in_rest' => true, // Gutenberg対応
        'show_in_menu' => true,  // メニューを1つに統一
    );

    register_post_type('instagram_feed', $args);
}
add_action('init', 'create_instagram_feed_post_type');

// Instagram APIからフィードを取得する関数
function fetch_instagram_feed_event() {
    // instagramアカウント全部取る
    $posts = get_all_instagram_account_posts();

    foreach($posts as $post) {
        // post_idってやつよ
        $account_id = $post->ID;

        // 'instagram_account' 投稿のメタデータから API ID と Access Token を取得
        $api_id = get_post_meta($account_id, 'instagram_api_id', true);
        $access_token = get_post_meta($account_id, 'instagram_access_token', true);

        // 一応データチェック
        if (!$api_id || !$access_token) {
            return 'データ足りねぇゾォぉぉおお！！栗原ぁぁああああ！！';
        }

        // instagram feedを取得するためのURL
        $api_url = 'https://graph.instagram.com/me/media?fields=id,caption,media_url,permalink,timestamp&limit=50&access_token=' . $access_token;
        $all_feeds = array();

        // ページネーションで全てのフィードを取得
        while ($api_url) {
            // APIリクエストを送信
            $response = wp_remote_get($api_url);

            // エラーチェック
            if (is_wp_error($response)) {
                return 'feedとれないんですけお！！'; // エラーハンドリング
            }

            // レスポンスの内容を取得
            $body = wp_remote_retrieve_body($response);
            $data = json_decode($body, true);

            if (isset($data['data'])) {
                // 取得したフィードを追加
                $all_feeds = array_merge($all_feeds, $data['data']);
            }

            // 次のページがあるか確認
            $api_url = isset($data['paging']['next']) ? $data['paging']['next'] : null;
        }

        // 取得したfeedをカスタム投稿タイプ「instagram-feed」として保存
        foreach ($all_feeds as $feed_item) {
            // Instagramのフィードがすでに保存されているか確認
            $existing_feed = new WP_Query(array(
                'post_type' => 'instagram-feed',
                'meta_key' => '_instagram_feed_id',
                'meta_value' => $feed_item['id'],
            ));

            // すでに存在する場合はスキップ
            if ($existing_feed->have_posts()) {
                continue; 
            }

            // 新しい投稿を作成
            $post_id = wp_insert_post(array(
                'post_title' => wp_trim_words($feed_item['caption'], 10, '...'),
                'post_content' => $feed_item['caption'],
                'post_status' => 'publish',
                'post_type' => 'instagram-feed',
            ));

            if ($post_id) {
                // カスタムフィールドにデータを保存
                update_post_meta($post_id, '_instagram_api_id', $api_id);   // 誰のfeedかは大切じゃん？
                update_post_meta($post_id, '_instagram_feed_id', $feed_item['id']);
                update_post_meta($post_id, '_instagram_feed_permalink', $feed_item['permalink']);
                update_post_meta($post_id, '_instagram_feed_media_url', $feed_item['media_url']);
                update_post_meta($post_id, '_instagram_feed_timestamp', $feed_item['timestamp']);
            }
        }
    }
}
