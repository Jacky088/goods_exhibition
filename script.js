/**
 * 好物页面插件前端脚本
 */

(function ($) {
    'use strict';

    $(document).ready(function () {
        initGoodsExhibition();
    });

    function initGoodsExhibition() {
        $('.goods-exhibition-wrapper').each(function () {
            var $wrapper = $(this);
            var $slider = $wrapper.find('.goods-exhibition-slider');
            var $items = $slider.find('.goods-exhibition-item');

            // 动态计算单个商品宽度（含margin）
            var itemWidth = $items.outerWidth(true);

            // 左箭头点击事件，向左滚动一个item宽度
            $wrapper.find('.goods-exhibition-arrow-left').on('click', function () {
                $slider.animate({
                    scrollLeft: $slider.scrollLeft() - itemWidth
                }, 400);
            });

            // 右箭头点击事件，向右滚动一个item宽度
            $wrapper.find('.goods-exhibition-arrow-right').on('click', function () {
                $slider.animate({
                    scrollLeft: $slider.scrollLeft() + itemWidth
                }, 400);
            });

            // 初始化时和滚动时，根据滚动位置显示/隐藏箭头
            function toggleArrows() {
                var scrollLeft = $slider.scrollLeft();
                var maxScroll = $slider[0].scrollWidth - $slider.outerWidth();

                if (scrollLeft <= 0) {
                    $wrapper.find('.goods-exhibition-arrow-left').fadeOut(200);
                } else {
                    $wrapper.find('.goods-exhibition-arrow-left').fadeIn(200);
                }

                if (scrollLeft >= maxScroll - 1) {
                    $wrapper.find('.goods-exhibition-arrow-right').fadeOut(200);
                } else {
                    $wrapper.find('.goods-exhibition-arrow-right').fadeIn(200);
                }
            }

            // 绑定滚动事件触发箭头显示切换
            $slider.on('scroll', toggleArrows);

            // 初始化箭头显示
            toggleArrows();
        });
    }
})(jQuery);
