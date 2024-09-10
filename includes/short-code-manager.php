<?php
// カスタム投稿タイプ 'instagram-feed' からアイキャッチを取得しカルーセルを表示するショートコード
function instagram_feed_carousel_shortcode( $attr ) {
    // 引数
    $attr;

    // クエリでカスタム投稿タイプ 'instagram-feed' の投稿を取得
    $args = array(
        'post_type' => 'instagram-feed',
        'posts_per_page' => 10, // 表示する投稿数
        'meta_query' => array(
            array(
                'key' => '_thumbnail_id', // アイキャッチ画像が設定されている投稿のみ
                'compare' => 'EXISTS',
            ),
        ),
    );

    $query = new WP_Query($args);
    
    // 投稿が存在しない場合、何も表示しない
    if (!$query->have_posts()) {
        return '<p>No Instagram feed available.</p>';
    }

    // カルーセル用のHTML開始
    $output = '<div class="instagram-feed-carousel">';
    
    // 投稿をループして、アイキャッチ画像を表示
    while ($query->have_posts()) {
        $query->the_post();
        
        if (has_post_thumbnail()) {
            $thumbnail_url = get_the_post_thumbnail_url(get_the_ID(), 'full');
            $output .= '<div><img src="' . esc_url($thumbnail_url) . '" alt="' . esc_attr(get_the_title()) . '"></div>';
        }
    }

    // HTML終了
    $output .= '</div>';
    
    // クエリをリセット
    wp_reset_postdata();
    
    return $output;
}
add_shortcode('instagram_feed_carousel', 'instagram_feed_carousel_shortcode');

// Slick Sliderのスクリプトとスタイルの読み込み
function enqueue_slick_slider_scripts() {
    wp_enqueue_style('slick-slider-css', 'https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick.min.css');
    wp_enqueue_style('slick-slider-theme-css', 'https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick-theme.min.css');
    wp_enqueue_script('slick-slider-js', 'https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick.min.js', array('jquery'), null, true);
    
    // カルーセルの初期化スクリプトを追加
    wp_add_inline_script('slick-slider-js', '
        jQuery(document).ready(function($) {
            $(".instagram-feed-carousel").slick({
                infinite: true,
                slidesToShow: 3,
                slidesToScroll: 1,
                autoplay: true,
                autoplaySpeed: 2000,
            });
        });
    ');
}
add_action('wp_enqueue_scripts', 'enqueue_slick_slider_scripts');
