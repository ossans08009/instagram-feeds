<?php
/**
 * feed の表示に必要なデータを cron で取得、更新する処理を書く
 */
// Instagramのアクセストークンを保存するオプションキー
define('INSTAGRAM_ACCESS_TOKEN_OPTION', 'instagram_access_token');
define('INSTAGRAM_ACCESS_TOKEN', 'XXX'); // 初期アクセストークン

// プラグインが有効化された時に実行される関数
function instagram_token_refresher_activate() {
    // もし保存されたアクセストークンがなければ、初期値を保存
    if (!get_option(INSTAGRAM_ACCESS_TOKEN_OPTION)) {
        update_option(INSTAGRAM_ACCESS_TOKEN_OPTION, INSTAGRAM_ACCESS_TOKEN);
    }

    // 2ヶ月ごとのスケジュールをセット
    if (!wp_next_scheduled('refresh_instagram_access_token_event')) {
        wp_schedule_event(time(), 'bi_monthly', 'refresh_instagram_access_token_event');
    }
}
register_activation_hook(__FILE__, 'instagram_token_refresher_activate');

// プラグインが無効化された時に実行される関数
function instagram_token_refresher_deactivate() {
    // スケジュールされたイベントを削除
    $timestamp = wp_next_scheduled('refresh_instagram_access_token_event');
    if ($timestamp) {
        wp_unschedule_event($timestamp, 'refresh_instagram_access_token_event');
    }
}
register_deactivation_hook(__FILE__, 'instagram_token_refresher_deactivate');

// カスタムスケジュールの追加（2ヶ月ごと）
function add_custom_cron_schedule($schedules) {
    $schedules['bi_monthly'] = array(
        'interval' => 60 * 60 * 24 * 60, // 2ヶ月 (60日)
        'display' => __('Every 2 Months')
    );
    return $schedules;
}
add_filter('cron_schedules', 'add_custom_cron_schedule');

// アクセストークンをリフレッシュする関数
function refresh_instagram_access_token() {
    // 現在のアクセストークンを取得
    $access_token = get_option(INSTAGRAM_ACCESS_TOKEN_OPTION);

    // リフレッシュトークンのAPIエンドポイント
    $api_url = 'https://graph.instagram.com/refresh_access_token?grant_type=ig_refresh_token&access_token=' . $access_token;

    $response = wp_remote_get($api_url);

    if (is_wp_error($response)) {
        error_log('Failed to refresh Instagram access token: ' . $response->get_error_message());
        return;
    }

    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);

    if (isset($data['access_token'])) {
        // 新しいアクセストークンを保存
        update_option(INSTAGRAM_ACCESS_TOKEN_OPTION, $data['access_token']);
        error_log('Instagram access token refreshed successfully.');
    } else {
        error_log('Failed to refresh Instagram access token: ' . $body);
    }
}
add_action('refresh_instagram_access_token_event', 'refresh_instagram_access_token');
