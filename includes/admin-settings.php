<?php
// カスタム投稿タイプの登録（Feeds）
function instagram_feeds_register_post_type() {
    register_post_type( 'instagram_feed', array(
        'labels' => array(
            'name' => __( 'Feeds', 'instagram-feeds' ),
            'singular_name' => __( 'Feed', 'instagram-feeds' ),
            'add_new' => __( 'Add New Feed', 'instagram-feeds' ),
            'add_new_item' => __( 'Add New Instagram Feed', 'instagram-feeds' ),
            'edit_item' => __( 'Edit Instagram Feed', 'instagram-feeds' ),
            'new_item' => __( 'New Instagram Feed', 'instagram-feeds' ),
            'view_item' => __( 'View Instagram Feed', 'instagram-feeds' ),
            'search_items' => __( 'Search Instagram Feeds', 'instagram-feeds' ),
            'not_found' => __( 'No Instagram Feeds found', 'instagram-feeds' ),
            'not_found_in_trash' => __( 'No Instagram Feeds found in Trash', 'instagram-feeds' ),
        ),
        'public' => true,
        'has_archive' => false,
        'supports' => array( 'title' ),
        'menu_position' => 21,
        'menu_icon' => 'dashicons-images-alt2',
    ));
}
add_action( 'init', 'instagram_feeds_register_post_type' );

// メタボックスの追加（Feeds）
function instagram_feeds_add_meta_boxes() {
    add_meta_box(
        'instagram_feed_settings',
        'Feed Settings',
        'instagram_feeds_render_meta_box',
        'instagram_feed',
        'normal',
        'high'
    );
}
add_action( 'add_meta_boxes', 'instagram_feeds_add_meta_boxes' );

// メタボックスの表示内容を定義
function instagram_feeds_render_meta_box( $post ) {
    $hashtags = get_post_meta( $post->ID, '_instagram_feed_hashtags', true );
    $layout = get_post_meta( $post->ID, '_instagram_feed_layout', true );
    $order = get_post_meta( $post->ID, '_instagram_feed_order', true );

    ?>
    <label for="instagram_feed_hashtags">Hashtags (comma separated):</label>
    <input type="text" id="instagram_feed_hashtags" name="instagram_feed_hashtags" value="<?php echo esc_attr( $hashtags ); ?>" class="widefat" />

    <label for="instagram_feed_layout">Layout:</label>
    <div>
        <label>
            <input type="radio" name="instagram_feed_layout" value="carousel" <?php checked( $layout, 'carousel' ); ?> />
            <img src="<?php echo plugins_url( 'assets/carousel-icon.png', __FILE__ ); ?>" alt="Carousel" />
            Carousel
        </label>
        <label>
            <input type="radio" name="instagram_feed_layout" value="grid" <?php checked( $layout, 'grid' ); ?> />
            <img src="<?php echo plugins_url( 'assets/grid-icon.png', __FILE__ ); ?>" alt="Grid" />
            Grid
        </label>
    </div>

    <label for="instagram_feed_order">Order:</label>
    <select id="instagram_feed_order" name="instagram_feed_order" class="widefat">
        <option value="popular" <?php selected( $order, 'popular' ); ?>>Popular</option>
        <option value="recent" <?php selected( $order, 'recent' ); ?>>Recent</option>
    </select>
    <?php
}

// メタデータの保存処理
function instagram_feeds_save_meta_box( $post_id ) {
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;

    if ( isset( $_POST['instagram_feed_hashtags'] ) ) {
        update_post_meta( $post_id, '_instagram_feed_hashtags', sanitize_text_field( $_POST['instagram_feed_hashtags'] ) );
    }
    if ( isset( $_POST['instagram_feed_layout'] ) ) {
        update_post_meta( $post_id, '_instagram_feed_layout', sanitize_text_field( $_POST['instagram_feed_layout'] ) );
    }
    if ( isset( $_POST['instagram_feed_order'] ) ) {
        update_post_meta( $post_id, '_instagram_feed_order', sanitize_text_field( $_POST['instagram_feed_order'] ) );
    }
}
add_action( 'save_post', 'instagram_feeds_save_meta_box' );

// アカウント管理ページの表示
function instagram_feeds_accounts_page() {
    ?>
    <div class="wrap">
        <h1>Instagram Accounts</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields( 'instagram_feeds_accounts_group' );
            do_settings_sections( 'instagram-feeds-accounts' );
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

// アカウント設定の登録
function instagram_feeds_register_account_settings() {
    register_setting( 'instagram_feeds_accounts_group', 'instagram_user_ids' );

    add_settings_section(
        'instagram_feeds_accounts_section',
        'Instagram Accounts',
        null,
        'instagram-feeds-accounts'
    );

    add_settings_field(
        'instagram_user_ids',
        'User IDs and Access Tokens',
        'instagram_feeds_user_ids_callback',
        'instagram-feeds-accounts',
        'instagram_feeds_accounts_section'
    );
}
add_action( 'admin_init', 'instagram_feeds_register_account_settings' );

function instagram_feeds_user_ids_callback() {
    $value = get_option( 'instagram_user_ids' );
    echo '<textarea name="instagram_user_ids" rows="5" cols="50">' . esc_attr( $value ) . '</textarea>';
}
