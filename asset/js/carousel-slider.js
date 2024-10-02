jQuery(document).ready(function($) {
    $(".instagram-feeds").slick({
        slidesToShow: 5, // 横に3つのスライドを表示
        arrows: true, // 前へ・次へのページ送りボタンを表示
        dots: false, // ナビゲーションドットを非表示
        speed: 800, // 切り替えアニメーションの時間を800ms
        cssEase: 'ease', // イージングをリニアに設定
        centerMode: true, // 中央寄せ表示
        centerPadding: '100px', // 前後のスライドの見切れ幅
        adaptiveHeight: true
    }); 
});
