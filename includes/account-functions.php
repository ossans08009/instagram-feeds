<?php
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
    register_setting( 'instagram_feeds_accounts_group', 'instagram_accounts', 'instagram_feeds_sanitize_accounts' );

    add_settings_section(
        'instagram_feeds_accounts_section',
        'Instagram Accounts',
        'instagram_feeds_accounts_section_callback',
        'instagram-feeds-accounts'
    );

    add_settings_field(
        'instagram_accounts',
        'User IDs and Access Tokens',
        'instagram_feeds_accounts_field_callback',
        'instagram-feeds-accounts',
        'instagram_feeds_accounts_section'
    );
}
add_action( 'admin_init', 'instagram_feeds_register_account_settings' );

// アカウント設定セクションの説明
function instagram_feeds_accounts_section_callback() {
    echo '<p>Enter the User IDs and Access Tokens for the Instagram accounts you want to use. Each entry should be in the format <code>user_id:access_token</code>, one per line.</p>';
}

// アカウントフィールドの表示
function instagram_feeds_accounts_field_callback() {
    $accounts = get_option( 'instagram_accounts' );
    echo '<textarea name="instagram_accounts" rows="10" cols="50" class="large-text">' . esc_textarea( $accounts ) . '</textarea>';
}

// アカウント情報のサニタイズ処理
function instagram_feeds_sanitize_accounts( $input ) {
    // 入力されたアカウント情報を一行ごとに分割し、配列に変換
    $lines = explode( "\n", $input );
    $sanitized = array();

    foreach ( $lines as $line ) {
        $line = trim( $line );
        if ( ! empty( $line ) ) {
            // user_id:access_token の形式を保持する
            if ( strpos( $line, ':' ) !== false ) {
                $sanitized[] = $line;
            }
        }
    }

    // 配列を改行で再結合して返す
    return implode( "\n", $sanitized );
}
