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
require_once INSTAGRAM_FEEDS_PLUGIN_DIR . 'includes/cron-manager/cron-manager.php';
require_once INSTAGRAM_FEEDS_PLUGIN_DIR . 'includes/access-token-manager/access-token-manager.php';
require_once INSTAGRAM_FEEDS_PLUGIN_DIR . 'includes/instagram-feed-manager/instagram-feed-manager.php';
require_once INSTAGRAM_FEEDS_PLUGIN_DIR . 'includes/instagram-feed-carousel/instagram-feed-carousel.php';

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

    // サブメニュー「アカウント管理」
    add_submenu_page(
        'instagram-feeds',                    // 親メニューのスラッグ
        'アカウント管理',                           // ページタイトル
        'アカウント管理',                           // メニュータイトル
        'manage_options',                     // 権限
        'access-token-manager',           // メニューのスラッグ
        'edit.php?post_type=instagram_account'       // 表示する関数
    );

    // サブメニュー「Feed管理」
    add_submenu_page(
        'instagram-feeds',                    // 親メニューのスラッグ
        'Feed管理',                              // ページタイトル
        'Feed管理',                              // メニュータイトル
        'manage_options',                     // 権限
        'feed-manager',           // メニューのスラッグ
        'edit.php?post_type=instagram_feed'   // 投稿タイプの編集ページを開く
    );

    /*
    // サブメニュー「ショートコード管理」
    add_submenu_page(
        'instagram-feeds',                    // 親メニューのスラッグ
        'ショートコード管理',                           // ページタイトル
        'Short Codes',                           // メニュータイトル
        'manage_options',                     // 権限
        'short-code-manager',           // メニューのスラッグ
        'edit.php?post_type=instagram_feed'       // 表示する関数
    );

    // サブメニュー「Cron管理」
    add_submenu_page(
        'instagram-feeds',                    // 親メニューのスラッグ
        'ショートコード管理',                           // ページタイトル
        'Short Codes',                           // メニュータイトル
        'manage_options',                     // 権限
        'cron-manager',                           // メニューのスラッグ
        'edit.php?post_type=instagram_feed'       // 表示する関数
    );
     */
}
add_action( 'admin_menu', 'instagram_feeds_add_admin_menu' );

// 「Instagram Feeds」のメインページの表示関数
function instagram_feeds_overview_page() {
    ?>
    <div class="wrap">
        <h1>Instagram Feeds 概要</h1>
        <p>
            instagramのアプリIDと長期access tokenを設定するし、<br />
            投稿記事、固定ページでショートコードを入力するだけで<br />
            instagramのfeedがカルーセル表示されます！<br />
            <br />
            長期アクセストークンの取得方法は<a href="https://www.google.com/">こちら！</a>
        </p>
    </div>
    <?php
}

// スタイルシートとスクリプトの読み込み
function instagram_feeds_enqueue_assets() {
    wp_enqueue_style( 'instagram-feeds-style', plugins_url( 'assets/style.css', __FILE__ ) );
    wp_enqueue_script( 'instagram-feeds-script', plugins_url( 'assets/custom.js', __FILE__ ), array('jquery'), null, true );
}
add_action( 'admin_enqueue_scripts', 'instagram_feeds_enqueue_assets' );
