<?php
/**
 * Plugin Name: Instagram Feeds
 * Description: Manage Instagram feeds and display them via shortcodes.
 * Version: 1.0.0
 * Author: Your Name
 * License: GPL-2.0-or-later
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

// プラグインのディレクトリパスを定義
define( 'INSTAGRAM_FEEDS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

// 必要なファイルを読み込む
require_once INSTAGRAM_FEEDS_PLUGIN_DIR . 'includes/admin-settings.php';
require_once INSTAGRAM_FEEDS_PLUGIN_DIR . 'includes/feed-functions.php';
require_once INSTAGRAM_FEEDS_PLUGIN_DIR . 'includes/account-functions.php';

// 管理画面メニューとサブメニューの追加
function instagram_feeds_add_admin_menu() {
    // メインメニュー「Instagram Feeds」
    add_menu_page(
        'Instagram Feeds',                    // ページタイトル
        'Instagram Feeds',                    // メニュータイトル
        'manage_options',                     // 権限
        'instagram-feeds',                    // メニューのスラッグ
        'instagram_feeds_overview_page',      // 表示する関数
        'dashicons-instagram',                // アイコン
        20                                    // メニューの位置
    );

    // サブメニュー「Feeds」
    add_submenu_page(
        'instagram-feeds',                    // 親メニューのスラッグ
        'Feeds',                              // ページタイトル
        'Feeds',                              // メニュータイトル
        'manage_options',                     // 権限
        'edit.php?post_type=instagram_feed'   // 投稿タイプの編集ページを開く
    );

    // サブメニュー「Accounts」
    add_submenu_page(
        'instagram-feeds',                    // 親メニューのスラッグ
        'Accounts',                           // ページタイトル
        'Accounts',                           // メニュータイトル
        'manage_options',                     // 権限
        'instagram-feeds-accounts',           // メニューのスラッグ
        'instagram_feeds_accounts_page'       // 表示する関数
    );
}
add_action( 'admin_menu', 'instagram_feeds_add_admin_menu' );

// 「Instagram Feeds」のメインページの表示関数
function instagram_feeds_overview_page() {
    echo '<div class="wrap"><h1>Instagram Feeds Overview</h1><p>Use this page to manage your Instagram feeds and accounts.</p></div>';
}

// スタイルシートとスクリプトの読み込み
function instagram_feeds_enqueue_assets() {
    wp_enqueue_style( 'instagram-feeds-style', plugins_url( 'assets/style.css', __FILE__ ) );
    wp_enqueue_script( 'instagram-feeds-script', plugins_url( 'assets/custom.js', __FILE__ ), array('jquery'), null, true );
}
add_action( 'admin_enqueue_scripts', 'instagram_feeds_enqueue_assets' );

// ショートコードの登録
function instagram_feeds_register_shortcodes() {
    add_shortcode( 'instagram_feed', 'instagram_feeds_render_shortcode' );
}
add_action( 'init', 'instagram_feeds_register_shortcodes' );
