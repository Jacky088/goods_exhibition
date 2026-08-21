/**
 * 好物页面插件前端脚本（Vanilla JS，无jQuery依赖）
 */

(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        initProductSliders();
        initPosterSlider();
        initNoLightbox();
        initMobilePeekHint();
    });

    /**
     * 阻止图片的默认查看行为
     */
    function initNoLightbox() {
        var images = document.querySelectorAll('.goods-exhibition-image.no-lightbox');
        images.forEach(function (img) {
            img.addEventListener('click', function (e) {
                e.stopPropagation();
            });
        });
    }

    /**
     * 移动端滑动提示（Peek 动画）
     * 首次加载时，商品列表轻微左移再弹回，暗示可横滑
     */
    function initMobilePeekHint() {
        if (window.innerWidth > 480) return;

        var sliders = document.querySelectorAll('.goods-exhibition-wrapper:not(.poster-slider) .goods-exhibition-slider');
        sliders.forEach(function (slider) {
            // 延迟 500ms 后播放 peek 动画
            setTimeout(function () {
                slider.classList.add('peek-hint');
                // 动画结束后移除 class
                slider.addEventListener('animationend', function () {
                    slider.classList.remove('peek-hint');
                }, { once: true });
            }, 500);
        });
    }

    /**
     * 初始化商品列表滑动器（非海报）
     */
    function initProductSliders() {
        var wrappers = document.querySelectorAll('.goods-exhibition-wrapper:not(.poster-slider)');

        wrappers.forEach(function (wrapper) {
            var slider = wrapper.querySelector('.goods-exhibition-slider');
            if (!slider) return;

            var items = slider.querySelectorAll('.goods-exhibition-item');
            if (items.length === 0) return;

            var leftArrow = wrapper.querySelector('.goods-exhibition-arrow-left');
            var rightArrow = wrapper.querySelector('.goods-exhibition-arrow-right');

            function getItemWidth() {
                var itemStyle = window.getComputedStyle(items[0]);
                return items[0].offsetWidth
                    + parseInt(itemStyle.marginLeft, 10)
                    + parseInt(itemStyle.marginRight, 10);
            }

            var itemWidth = getItemWidth();

            if (leftArrow) {
                leftArrow.addEventListener('click', function () {
                    slider.scrollBy({ left: -itemWidth, behavior: 'smooth' });
                });
            }

            if (rightArrow) {
                rightArrow.addEventListener('click', function () {
                    slider.scrollBy({ left: itemWidth, behavior: 'smooth' });
                });
            }

            function toggleArrows() {
                var scrollLeft = slider.scrollLeft;
                var maxScroll = slider.scrollWidth - slider.offsetWidth;

                if (leftArrow) {
                    leftArrow.style.visibility = scrollLeft <= 1 ? 'hidden' : 'visible';
                }
                if (rightArrow) {
                    rightArrow.style.visibility = scrollLeft >= maxScroll - 1 ? 'hidden' : 'visible';
                }
            }

            slider.addEventListener('scroll', toggleArrows);
            toggleArrows();

            window.addEventListener('resize', function () {
                itemWidth = getItemWidth();
                toggleArrows();
            });
        });
    }

    /**
     * 初始化海报幻灯片：自动轮播 + 圆点指示器 + 箭头
     * 支持页面存在多个海报轮播实例（多个短代码场景），逐个初始化
     */
    function initPosterSlider() {
        var wrappers = document.querySelectorAll('.goods-exhibition-wrapper.poster-slider');

        wrappers.forEach(function (wrapper) {
            initSinglePosterSlider(wrapper);
        });
    }

    /**
     * 初始化单个海报轮播实例
     */
    function initSinglePosterSlider(wrapper) {
        var track = wrapper.querySelector('.poster-slider-track');
        if (!track) return;

        var items = track.querySelectorAll('.goods-exhibition-item.poster-item');
        if (items.length <= 1) return; // 只有一张海报不需要轮播

        var leftArrow = wrapper.querySelector('.goods-exhibition-arrow-left');
        var rightArrow = wrapper.querySelector('.goods-exhibition-arrow-right');
        var dots = wrapper.querySelectorAll('.goods-exhibition-dot');
        var currentIndex = 0;
        var autoPlayInterval = null;
        var autoPlayDelay = 5000; // 5秒
        var isAutoScrolling = false; // 程序化平滑滚动进行中标记

        /**
         * 跳转到指定索引的海报
         */
        function goToSlide(index) {
            if (index < 0) index = items.length - 1;
            if (index >= items.length) index = 0;
            currentIndex = index;

            var slideWidth = items[0].offsetWidth;
            // 标记程序化滚动：滚动中途的 scroll 事件不再回写索引，防止圆点回闪
            isAutoScrolling = true;
            track.scrollTo({ left: slideWidth * currentIndex, behavior: 'smooth' });

            updateDots();
        }

        /**
         * 更新圆点指示器状态（同步 aria-selected 供屏幕阅读器识别）
         */
        function updateDots() {
            dots.forEach(function (dot, i) {
                var isActive = i === currentIndex;
                dot.classList.toggle('active', isActive);
                dot.setAttribute('aria-selected', isActive ? 'true' : 'false');
            });
        }

        // 系统开启"减弱动态效果"的用户不自动轮播（箭头/圆点手动操作不受影响）
        var prefersReducedMotion = window.matchMedia
            && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

        /**
         * 开始自动轮播
         */
        function startAutoPlay() {
            stopAutoPlay();
            if (prefersReducedMotion) return;
            autoPlayInterval = setInterval(function () {
                goToSlide(currentIndex + 1);
            }, autoPlayDelay);
        }

        /**
         * 停止自动轮播
         */
        function stopAutoPlay() {
            if (autoPlayInterval) {
                clearInterval(autoPlayInterval);
                autoPlayInterval = null;
            }
        }

        /**
         * 根据滚动位置更新当前索引和圆点状态
         */
        function updateIndexFromScroll() {
            var slideWidth = items[0].offsetWidth;
            var index = Math.round(track.scrollLeft / slideWidth);
            if (index < 0) index = 0;
            if (index >= items.length) index = items.length - 1;
            if (index !== currentIndex) {
                currentIndex = index;
                updateDots();
            }
        }

        var scrollTimeout = null;
        track.addEventListener('scroll', function () {
            if (scrollTimeout) {
                clearTimeout(scrollTimeout);
            }
            scrollTimeout = setTimeout(function () {
                if (isAutoScrolling) {
                    // 滚动事件停止触发，说明程序化滚动已结束：解除标记后校正索引
                    isAutoScrolling = false;
                }
                updateIndexFromScroll();
            }, 100);
        });

        // 箭头点击
        if (leftArrow) {
            leftArrow.addEventListener('click', function () {
                goToSlide(currentIndex - 1);
                startAutoPlay(); // 点击后重置计时器
            });
        }

        if (rightArrow) {
            rightArrow.addEventListener('click', function () {
                goToSlide(currentIndex + 1);
                startAutoPlay();
            });
        }

        // 圆点点击
        dots.forEach(function (dot, i) {
            dot.addEventListener('click', function () {
                goToSlide(i);
                startAutoPlay();
            });
        });

        // 鼠标悬停暂停自动轮播
        wrapper.addEventListener('mouseenter', stopAutoPlay);
        wrapper.addEventListener('mouseleave', startAutoPlay);

        // 触摸时暂停
        wrapper.addEventListener('touchstart', stopAutoPlay, { passive: true });
        wrapper.addEventListener('touchend', function () {
            // 触摸结束后延迟恢复自动轮播
            setTimeout(startAutoPlay, 2000);
        });

        // 初始化
        updateDots();
        startAutoPlay();

        // 页面不可见时暂停，可见时恢复
        document.addEventListener('visibilitychange', function () {
            if (document.hidden) {
                stopAutoPlay();
            } else {
                startAutoPlay();
            }
        });
    }
})();
