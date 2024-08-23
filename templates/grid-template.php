<?php
// フィードデータが空でないことを確認
if (!empty($feed_data)) : ?>
    <div class="instagram-grid">
        <?php foreach ($feed_data as $item) : ?>
            <?php if ($item['media_type'] === 'IMAGE' || $item['media_type'] === 'CAROUSEL_ALBUM') : ?>
                <div class="grid-item">
                    <a href="<?php echo esc_url($item['permalink']); ?>" target="_blank">
                        <img src="<?php echo esc_url($item['media_url']); ?>" alt="<?php echo esc_attr($item['caption']); ?>" />
                    </a>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
<?php else : ?>
    <p>No Instagram posts available at the moment.</p>
<?php endif; ?>
