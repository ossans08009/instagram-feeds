document.addEventListener('DOMContentLoaded', function () {
    const swiper = new Swiper('.swiper-container', {
        loop: true,
        pagination: {
            el: '.swiper-pagination',
            clickable: true,
        },
        navigation: {
            nextEl: '.swiper-button-next',
            prevEl: '.swiper-button-prev',
        },
    });
});
document.addEventListener('DOMContentLoaded', function () {
    // Swiperの初期化
    const swiper = new Swiper('.swiper-container', {
        loop: true, // スライドをループ
        pagination: {
            el: '.swiper-pagination',
            clickable: true, // ページネーションをクリック可能にする
        },
        navigation: {
            nextEl: '.swiper-button-next',
            prevEl: '.swiper-button-prev',
        },
        slidesPerView: 1, // 一度に表示するスライド数
        spaceBetween: 10, // スライド間のスペース
        autoplay: {
            delay: 3000, // 自動再生の遅延時間（ミリ秒）
            disableOnInteraction: false, // ユーザーが操作しても自動再生を停止しない
        },
        breakpoints: {
            // ウィンドウ幅に応じた設定
            640: {
                slidesPerView: 1,
                spaceBetween: 20,
            },
            768: {
                slidesPerView: 2,
                spaceBetween: 30,
            },
            1024: {
                slidesPerView: 3,
                spaceBetween: 40,
            },
        },
    });

    // グリッド表示の画像にホバーエフェクトを追加
    const gridItems = document.querySelectorAll('.grid-item img');
    gridItems.forEach(function (item) {
        item.addEventListener('mouseenter', function () {
            item.style.transform = 'scale(1.05)';
        });

        item.addEventListener('mouseleave', function () {
            item.style.transform = 'scale(1)';
        });
    });
});
