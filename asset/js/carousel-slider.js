jQuery(document).ready(function($) {
    const swiper = new Swiper('.swiper-container', {
        // デフォでスマホサイズの設定
        slidesPerView: 3,
        spaceBetween: 10,
        freeMode: true,
        centerInsufficientSlides: true,
        lazy: {
            loadPrevNext: true,
        },
        preloadImages: false,
        breakpoints: {
            // スマホ最大幅640px想定
            // 640px以上はタブレット判定
            640: {
                slidesPerView: 5,
            },
            // タブレット最大幅1024px想定
            // 1024px以上はパソコン判定
            1024: {
                slidesPerView: 7,
            },
        }        
    });
});
