<?php
// フィードデータが空でないことを確認
if (!empty($feed_data)) : ?>
    <div class="instagram-carousel swiper-container">
        <div class="swiper-wrapper">
            <?php foreach ($feed_data as $item) : ?>
                <?php if ($item['media_type'] === 'IMAGE' || $item['media_type'] === 'CAROUSEL_ALBUM') : ?>
                    <div class="swiper-slide">
                        <a href="<?php echo esc_url($item['permalink']); ?>" target="_blank">
                            <img src="<?php echo esc_url($item['media_url']); ?>" alt="<?php echo esc_attr($item['caption']); ?>" />
                        </a>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
        <!-- ページネーション -->
        <div class="swiper-pagination"></div>
        <!-- ナビゲーションボタン -->
        <div class="swiper-button-next"></div>
        <div class="swiper-button-prev"></div>
    </div>
<?php else : ?>
    <p>No Instagram posts available at the moment.</p>
<?php endif; ?>
