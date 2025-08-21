/**
 * 好物页面插件前端脚本
 */

(function ($) {
    'use strict';

    $(document).ready(function () {
        initGoodsExhibition();

        // 阻止图片的默认查看行为，防止主题Lightbox等干扰弹窗
        $('.goods-exhibition-image.no-lightbox').on('click', function (e) {
            // 阻止事件冒泡，避免被外部绑定的图片查看事件捕获
            e.stopPropagation();
            // 如果第三方插件尝试阻止链接跳转，这里不preventDefault保证链接跳转正常
        });
    });

    function initGoodsExhibition() {
        $('.goods-exhibition-wrapper').each(function () {
            var $wrapper = $(this);
            var $slider = $wrapper.find('.goods-exhibition-slider');
            var $items = $slider.find('.goods-exhibition-item');

            // 使用第一个可见的商品宽度作为单个商品宽度（包含外边距）
            // 注意：因为flex布局，有时outerWidth(true)取最近的一个即可
            var itemWidth = $items.first().outerWidth(true);

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

            // 根据滚动位置显示/隐藏箭头
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

            $slider.on('scroll', toggleArrows);
            toggleArrows();
        });
    }
})(jQuery);
