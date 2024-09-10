<?php
// Instagramのアクセストークン
define('INSTAGRAM_ACCESS_TOKEN', 'XXX');

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
        'menu_position' => 20,
        'supports' => array('title', 'editor'),
        'show_in_rest' => true, // Gutenberg対応
        'show_in_menu' => true,  // メニューを1つに統一
    );

    register_post_type('instagram-feed', $args);
}
add_action('init', 'create_instagram_feed_post_type');

// Instagram APIからフィードを取得する関数
function fetch_instagram_feed() {
    $api_url = 'https://graph.instagram.com/me/media?fields=id,caption,media_url,permalink,timestamp&limit=50&access_token=' . $access_token;

    $response = wp_remote_get($api_url);
    
    if (is_wp_error($response)) {
        return; // エラーハンドリング
    }

    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);

    if (!empty($data['data'])) {
        foreach ($data['data'] as $feed_item) {
            // Instagramのフィードがすでに保存されているか確認
            $existing_feed = new WP_Query(array(
                'post_type' => 'instagram-feed',
                'meta_key' => '_instagram_feed_id',
                'meta_value' => $feed_item['id'],
            ));

            if ($existing_feed->have_posts()) {
                continue; // すでに存在する場合はスキップ
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
                update_post_meta($post_id, '_instagram_feed_id', $feed_item['id']);
                update_post_meta($post_id, '_instagram_feed_permalink', $feed_item['permalink']);
                update_post_meta($post_id, '_instagram_feed_media_url', $feed_item['media_url']);
                update_post_meta($post_id, '_instagram_feed_timestamp', $feed_item['timestamp']);
            }
        }
    }
}

// 1時間ごとにInstagramのフィードを取得するCronジョブをスケジュール
function instagram_feed_schedule_cron() {
    if (!wp_next_scheduled('fetch_instagram_feed_event')) {
        wp_schedule_event(time(), 'hourly', 'fetch_instagram_feed_event');
    }
}
add_action('wp', 'instagram_feed_schedule_cron');

// プラグイン無効化時にCronジョブを削除
function instagram_feed_remove_cron() {
    $timestamp = wp_next_scheduled('fetch_instagram_feed_event');
    wp_unschedule_event($timestamp, 'fetch_instagram_feed_event');
}
register_deactivation_hook(__FILE__, 'instagram_feed_remove_cron');

// Cronジョブのイベントにfetch_instagram_feed関数を登録
add_action('fetch_instagram_feed_event', 'fetch_instagram_feed');
