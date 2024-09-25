<?php
// カスタム投稿タイプ 'instagram-feed' からアイキャッチを取得しカルーセルを表示するショートコード
function instagram_feed_carousel_shortcode( $attr ) {
    // 引数
    $attr;

    // クエリでカスタム投稿タイプ 'instagram-feed' の投稿を取得
    $args = array(
        'post_type' => 'instagram_feed',
        'posts_per_page' => 10, // 表示する投稿数
    );

    $query = new WP_Query($args);
    
    // 投稿が存在しない場合、何も表示しない
    if (!$query->have_posts()) {
        return '<p>このワードでの投稿はありますん。</p>';
    }

    // カルーセル用のHTML開始
    $output  = '<div class="instagram-feeds">';
    
    // 投稿をループして、アイキャッチ画像を表示
    while ($query->have_posts()) {
        $query->the_post();

        // feedの情報を取得
        $thumbnail_url = get_post_meta( get_the_ID(), '_instagram_feed_thumbnail_url', true );
        $permalink = get_post_meta( get_the_ID(), '_instagram_feed_permalink', true );
        
        $output .= '<div class="instagram-feed">';
        $output .= '<a target="_blank" href="' . $permalink . '">';
        $output .= '<img src="' . esc_url($thumbnail_url) . '" alt="' . esc_attr(get_the_title()) . '" />';
        $output .= '</a>';
        $output .= '</div>';
    }

    // HTML終了
    $output .= '</div>';
    
    // クエリをリセット
    wp_reset_postdata();
    
    return $output;
}
add_shortcode('instagram_feed_carousel', 'instagram_feed_carousel_shortcode');

// プラグインのCSSを読み込む関数
function my_plugin_enqueue_styles() {
    // slick-sliderのjsとcssを読み込み
    wp_enqueue_style('slick-slider-css', 'https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick.min.css');
    wp_enqueue_style('slick-slider-theme-css', 'https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick-theme.min.css');
    wp_enqueue_script('slick-slider-js', 'https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick.min.js', array('jquery'), null, true);
    
    // プラグインディレクトリからCSSを読み込む
    wp_enqueue_style(
        'instagram-feeds-style', // CSSハンドル名
        plugin_dir_url(__FILE__) . '../asset/css/style.css', // CSSのパス
        array(), // 依存関係（なければ空の配列）
        '1.0.0', // バージョン
        'all' // メディア（全ての画面向け）
    );

    // プラグインディレクトリからJSを読み込む
    wp_enqueue_script(
        'instagram-feeds-script', // JSハンドル名
        plugin_dir_url(__FILE__) . '../asset/js/carousel-slider.js', // パス
        array(), // 依存関係（なければ空の配列）
        '1.0.0', // バージョン
        true, // 読み込み位置指定
    );
}
add_action('wp_enqueue_scripts', 'my_plugin_enqueue_styles');
