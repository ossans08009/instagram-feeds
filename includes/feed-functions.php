<?php
// ショートコードの登録
function instagram_feeds_render_shortcode( $atts ) {
    $attributes = shortcode_atts(
        array(
            'id' => '', // フィードIDを指定するためのショートコード属性
        ),
        $atts
    );

    // 投稿IDからフィードのメタデータを取得
    $post_id = $attributes['id'];
    if (empty($post_id)) {
        return '<p>No feed ID provided.</p>';
    }

    $hashtags = get_post_meta( $post_id, '_instagram_feed_hashtags', true );
    $layout = get_post_meta( $post_id, '_instagram_feed_layout', true );
    $order = get_post_meta( $post_id, '_instagram_feed_order', true );

    // ユーザーIDとアクセストークンを取得
    $user_ids = explode( ',', get_option( 'instagram_user_ids' ) );
    if (empty($user_ids)) {
        return '<p>No Instagram accounts found. Please add an account in the Accounts section.</p>';
    }

    // フィードデータをInstagram APIから取得
    $feed_data = instagram_feeds_get_data( $user_ids, $hashtags, $order );

    // レイアウトに基づいて適切なテンプレートを使用してレンダリング
    if ( $layout === 'carousel' ) {
        return instagram_feeds_render_carousel( $feed_data );
    } else {
        return instagram_feeds_render_grid( $feed_data );
    }
}
add_shortcode( 'instagram_feed', 'instagram_feeds_render_shortcode' );

// Instagram APIからフィードデータを取得する関数
function instagram_feeds_get_data( $user_ids, $hashtags, $order ) {
    $access_token = ''; // 必要に応じてアクセストークンを設定
    $user_id = $user_ids[0]; // デモとして最初のユーザーIDを使用

    // Instagram APIのエンドポイントを設定
    $endpoint = "https://graph.instagram.com/{$user_id}/media?fields=id,caption,media_type,media_url,permalink&access_token={$access_token}";

    // APIリクエストを送信
    $response = wp_remote_get( $endpoint );

    // エラーチェック
    if ( is_wp_error( $response ) ) {
        return array();
    }

    // APIレスポンスを解析
    $data = json_decode( wp_remote_retrieve_body( $response ), true );

    // データの並び替えとフィルタリング
    if ( isset( $data['data'] ) && is_array( $data['data'] ) ) {
        if ( $order === 'popular' ) {
            // 人気順に並び替え（例: ライク数でソートする）
            usort($data['data'], function($a, $b) {
                return $b['like_count'] - $a['like_count'];
            });
        } else {
            // 最近の投稿順に並び替え
            usort($data['data'], function($a, $b) {
                return strtotime($b['timestamp']) - strtotime($a['timestamp']);
            });
        }
        return $data['data'];
    }

    return array();
}

// カルーセル表示のHTMLを生成する関数
function instagram_feeds_render_carousel( $feed_data ) {
    $output = '<div class="swiper-container"><div class="swiper-wrapper">';
    foreach ( $feed_data as $item ) {
        if ($item['media_type'] === 'IMAGE' || $item['media_type'] === 'CAROUSEL_ALBUM') {
            $output .= '<div class="swiper-slide">';
            $output .= '<a href="' . esc_url( $item['permalink'] ) . '" target="_blank">';
            $output .= '<img src="' . esc_url( $item['media_url'] ) . '" alt="' . esc_attr( $item['caption'] ) . '" />';
            $output .= '</a></div>';
        }
    }
    $output .= '</div><div class="swiper-pagination"></div>';
    $output .= '<div class="swiper-button-next"></div><div class="swiper-button-prev"></div></div>';
    return $output;
}

// グリッド表示のHTMLを生成する関数
function instagram_feeds_render_grid( $feed_data ) {
    $output = '<div class="instagram-grid">';
    foreach ( $feed_data as $item ) {
        if ($item['media_type'] === 'IMAGE' || $item['media_type'] === 'CAROUSEL_ALBUM') {
            $output .= '<div class="grid-item">';
            $output .= '<a href="' . esc_url( $item['permalink'] ) . '" target="_blank">';
            $output .= '<img src="' . esc_url( $item['media_url'] ) . '" alt="' . esc_attr( $item['caption'] ) . '" />';
            $output .= '</a></div>';
        }
    }
    $output .= '</div>';
    return $output;
}
