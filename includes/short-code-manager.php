<?php
// カスタム投稿タイプ 'instagram-feed' からアイキャッチを取得しカルーセルを表示するショートコード
function instagram_feed_carousel_shortcode( $attr ) {
    // 引数
    $attr;

    // クエリでカスタム投稿タイプ 'instagram-feed' の投稿を取得
    $args = array(
        'post_type' => 'instagram_feed',
        'posts_per_page' => 10, // 表示する投稿数
        'post_status' => 'publish', // 表示する投稿数
        'meta_key'      => '_instagram_feed_timestamp',
        'orderby'       => 'meta_value',
        'order'         => 'DESC',
        's'         => $attr['word'],
        'exclude_word' => $attr['exclude_word'],
    );

    $query = new WP_Query($args);
    
    // 投稿が存在しない場合、何も表示しない
    if (!$query->have_posts()) {
        return '<p>このワードでの投稿はありますん。</p>';
    }

    // カルーセル用のHTML開始
    if($query->found_posts >= 5) {
        $output  = '<div class="instagram-feeds">';
    }else {
        $output  = '<div class="instagram-feeds few-feeds">';
    }
    
    // 投稿をループして、アイキャッチ画像を表示
    while ($query->have_posts()) {
        $query->the_post();
        $post_id = get_the_ID();

        // feedの情報を取得
        $thumbnail_url = get_post_meta( $post_id, '_instagram_feed_thumbnail_url', true );
        $permalink     = get_post_meta( $post_id, '_instagram_feed_permalink', true );
        $youtube_url   = get_post_meta( $post_id, '_youtube_url', true );
        
        $output .= '<div class="instagram-feed">';
        $output .= '<a target="_blank" href="' . $permalink . '">';
        $output .= '<img src="' . esc_url($thumbnail_url) . '" alt="' . esc_attr(get_the_title()) . '" />';
        $output .= '</a>';
        $output .= '<div class="buttons-area">';
        $output .= '<a target="_blank" href="' . $permalink . '">';
        $output .= '<i class="fab fa-instagram"></i>';
        $output .= '</a>';
        if($youtube_url) {
            $output .= '<a target="_blank" href="' . $youtube_url . '">';
            $output .= '<i class="fab fa-youtube"></i>';
            $output .= '</a>';
        }
        $output .= '</div>';
        $output .= '</div>';
    }

    // HTML終了
    $output .= '</div>';
    
    // クエリをリセット
    wp_reset_postdata();
    
    return $output;
}
add_shortcode('instagram_feed_carousel', 'instagram_feed_carousel_shortcode');

// instagram_feedを検索する場合スペース区切りをAND検索にする
function custom_search_where_for_instagram_feed($where, $wp_query) {
    // WHERE句をカスタマイズしてAND検索を実行
    $where = '';

    // 検索ワード
    if ($wp_query->is_search && !empty($wp_query->query_vars['s']) && $wp_query->get('post_type') === 'instagram_feed') {
        $search_terms = explode(' ', $wp_query->query_vars['s']);
        if ($search_terms) {
            foreach ($search_terms as $term) {
                $where .= " AND (post_title LIKE '%$term%' OR post_content LIKE '%$term%')";
            }
        }
    }
    
    // 除外ワード
    $exclude_terms = array( 'あらたつ先生の個別指導', );
    $exclude_terms = !empty($wp_query->query_vars['exclude_word'])
                 ? array_merge($exclude_terms, explode(' ', $wp_query->query_vars['exclude_word']))
                 : $exclude_terms;
    if ($wp_query->is_search && $wp_query->get('post_type') === 'instagram_feed') {
        foreach ($exclude_terms as $term) {
            $where .= " AND post_title NOT LIKE '%$term%'";
            $where .= " AND post_content NOT LIKE '%$term%'";
        }
    }

    return $where;
}
add_filter('posts_search', 'custom_search_where_for_instagram_feed', 10, 2);

// プラグインのCSSを読み込む関数
function my_plugin_enqueue_styles() {
    // slick-sliderのjsとcssを読み込み
    wp_enqueue_style('slick-slider-css', 'https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick.min.css', '1.0', true);
    wp_enqueue_style('slick-slider-theme-css', 'https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick-theme.min.css', '1.0', true);
    wp_enqueue_script('slick-slider-js', 'https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick.min.js', array('jquery'), '1.0', true);
    
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
