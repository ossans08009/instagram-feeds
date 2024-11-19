jQuery(document).ready(function($) {
    $(".instagram-feeds").slick({
        swipeToSlide: true,
        slidesToShow: 6,
        slidesToScroll: 99,
        infinite: true,
        arrows: false, // 前へ・次へのページ送りボタンを表示
        dots: false, // ナビゲーションドットを非表示
        lazyLoad: 'progressive',
        cssEase: 'linear',
        speed: 0,
        responsive: [{
            breakpoint: 769,
            settings: {
                slidesToShow: 3,
                slidesToScroll: 99,
            }
        }]
    }); 
});
