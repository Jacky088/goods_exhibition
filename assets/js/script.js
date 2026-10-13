/**
 * 好物页面插件前端脚本（Vanilla JS，无jQuery依赖）
 */

(function () {
    'use strict';

    function initAll() {
        initProductSliders();
        initPosterSlider();
        initNoLightbox();
        initMobilePeekHint();
        initQuickViewModal();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAll);
    } else {
        initAll();
    }

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
                var cleanup = function () {
                    slider.classList.remove('peek-hint');
                };
                slider.addEventListener('animationend', cleanup, { once: true });
                // 增加安全兜底定时器，防止移动端特殊情况下未派发 animationend 导致样式残留
                setTimeout(cleanup, 1200);
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

    /**
     * 初始化商品详情全貌弹窗（Quick View）
     */
    function initQuickViewModal() {
        function getModal() {
            return document.getElementById('goods-exhibition-quickview-modal');
        }

        function openModal(data, triggerCard, triggerBtn) {
            var modal = getModal();
            if (!modal || !data) return;

            var catEl = document.getElementById('goods-quickview-category');
            var newEl = document.getElementById('goods-quickview-new');
            var titleEl = document.getElementById('goods-quickview-title');
            var priceEl = document.getElementById('goods-quickview-price');
            var descEl = document.getElementById('goods-quickview-desc');
            var actionsEl = document.getElementById('goods-quickview-actions');
            var linkEl = document.getElementById('goods-quickview-link');

            // 先将弹窗设为可见，保证容器在视口中完成真实尺寸与布局计算
            modal.style.display = 'flex';
            modal.classList.add('is-active');
            modal.setAttribute('aria-hidden', 'false');
            document.body.classList.add('goods-modal-open');

            // 重置弹窗内部滚动条至顶部，避免上次浏览后的滚动位置残留
            var modalBody = modal.querySelector('.goods-quickview-body');
            if (modalBody) {
                modalBody.scrollTop = 0;
            }
            if (descEl) {
                descEl.scrollTop = 0;
            }

            // 1. 图片处理：多通道提取最稳定可靠的商品大图 URL，并动态构建干净的新 img 节点
            var finalImgUrl = '';

            // 通道 A：直接从触发按钮的 data-img 提取（原生属性，无 JSON 字符转义开销）
            if (triggerBtn && triggerBtn.getAttribute('data-img')) {
                finalImgUrl = triggerBtn.getAttribute('data-img');
            }
            // 通道 B：从父级卡片容器的 data-img 提取
            if (!finalImgUrl && triggerCard && triggerCard.getAttribute('data-img')) {
                finalImgUrl = triggerCard.getAttribute('data-img');
            }
            // 通道 C：从卡片当前实际已经渲染的 <img> 节点中提取（此时图片已在浏览器缓存中）
            if (!finalImgUrl && triggerCard) {
                var cardImg = triggerCard.querySelector('.goods-exhibition-image');
                if (cardImg) {
                    finalImgUrl = cardImg.currentSrc || cardImg.src || '';
                }
            }
            // 通道 D：从 JSON 解析出来的 image_url 兜底
            if (!finalImgUrl && data.image_url) {
                finalImgUrl = data.image_url;
            }

            var imgCol = document.getElementById('goods-quickview-img-col') || modal.querySelector('.goods-quickview-img-col');
            if (imgCol) {
                imgCol.innerHTML = ''; // 清除任何残留内容或异常占位
                if (finalImgUrl) {
                    imgCol.style.display = '';
                    var newImg = document.createElement('img');
                    newImg.id = 'goods-quickview-img';
                    newImg.src = finalImgUrl;
                    newImg.alt = data.name || '';
                    newImg.className = 'goods-quickview-img-rendered no-lightbox no-zoom viewer-ignore ignore-lightbox';
                    newImg.setAttribute('data-no-viewer', 'true');
                    newImg.setAttribute('data-no-lightbox', 'true');
                    newImg.setAttribute('data-lightbox', 'false');
                    newImg.setAttribute('data-fancybox', 'none');
                    newImg.setAttribute('draggable', 'false');
                    // 异常兜底重试：若主图加载失败，尝试回退到卡片现有 img.src
                    newImg.onerror = function () {
                        if (triggerCard) {
                            var fallbackImg = triggerCard.querySelector('.goods-exhibition-image');
                            if (fallbackImg && fallbackImg.src && newImg.src !== fallbackImg.src) {
                                newImg.src = fallbackImg.src;
                            }
                        }
                    };
                    imgCol.appendChild(newImg);
                } else {
                    imgCol.style.display = 'none';
                }
            }

            // 2. 分类标签
            if (catEl) {
                if (data.category) {
                    catEl.textContent = data.category;
                    catEl.style.display = 'inline-flex';
                    if (catEl.parentElement) {
                        catEl.parentElement.style.display = 'flex';
                    }
                } else {
                    catEl.style.display = 'none';
                    if (catEl.parentElement) {
                        catEl.parentElement.style.display = 'none';
                    }
                }
            }

            // 3. NEW 标签（紧随商品标题右侧同行）
            if (newEl) {
                newEl.style.display = (data.is_new && parseInt(data.is_new, 10) === 1) ? 'inline-flex' : 'none';
            }

            // 4. 完整品名标题（完整展现不截断）
            if (titleEl) {
                titleEl.textContent = data.name || '';
            }

            // 5. 价格
            if (priceEl) {
                if (data.price) {
                    priceEl.textContent = data.price;
                    priceEl.style.display = 'block';
                } else {
                    priceEl.style.display = 'none';
                }
            }

            // 6. 完整详细介绍（支持多达 500 字，完整呈现）
            if (descEl) {
                descEl.textContent = data.description || '';
            }

            // 7. 外链跳转（如果配置了外链则显示直达按钮）
            if (actionsEl && linkEl) {
                if (data.url) {
                    linkEl.href = data.url;
                    actionsEl.style.display = 'block';
                } else {
                    actionsEl.style.display = 'none';
                }
            }
        }

        function closeModal() {
            var modal = getModal();
            if (!modal) return;
            modal.style.display = 'none';
            modal.classList.remove('is-active');
            modal.setAttribute('aria-hidden', 'true');
            document.body.classList.remove('goods-modal-open');
        }

        // 核心：使用事件捕获阶段 (Capture Phase: true) 优先拦截所有商品卡片点击！
        // 彻底阻断原生/主题的链接跳转或大图画廊行为，确保点击图片、详情按钮或卡片任何位置，均唯一唤起详情弹窗。
        document.addEventListener('click', function (e) {
            // 1. 判断是否点击了商品卡片（排除海报轮播 poster-item）
            var card = e.target.closest('.goods-exhibition-item:not(.poster-item)');
            if (card) {
                e.preventDefault();
                e.stopPropagation();
                if (e.stopImmediatePropagation) {
                    e.stopImmediatePropagation();
                }

                if (card.blur) card.blur();
                var triggerBtn = card.querySelector('.goods-exhibition-quickview-btn');
                if (triggerBtn && triggerBtn.blur) triggerBtn.blur();
                if (document.activeElement && document.activeElement.blur) {
                    document.activeElement.blur();
                }

                try {
                    var rawData = card.getAttribute('data-product') || (triggerBtn ? triggerBtn.getAttribute('data-product') : '{}');
                    var data = typeof rawData === 'string' ? JSON.parse(rawData || '{}') : rawData;
                    openModal(data, card, triggerBtn);
                } catch (err) {
                    console.error('[Goods Exhibition] Failed to parse product JSON:', err);
                }
                return false;
            }

            // 2. 点击关闭按钮或背景遮罩
            if (e.target.closest('.goods-quickview-close') || e.target.classList.contains('goods-quickview-overlay')) {
                e.preventDefault();
                e.stopPropagation();
                if (e.stopImmediatePropagation) {
                    e.stopImmediatePropagation();
                }
                closeModal();
                return false;
            }
        }, true);

        // 阻止商品卡片上的鼠标中键在新标签打开
        document.addEventListener('auxclick', function (e) {
            if (e.target.closest('.goods-exhibition-item:not(.poster-item)')) {
                e.preventDefault();
                e.stopPropagation();
                if (e.stopImmediatePropagation) {
                    e.stopImmediatePropagation();
                }
            }
        }, true);

        // 键盘无障碍支持：Enter / Space 激活商品详情弹窗，ESC 键关闭
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' || e.key === ' ') {
                var card = e.target.closest('.goods-exhibition-item:not(.poster-item)');
                if (card) {
                    e.preventDefault();
                    e.stopPropagation();
                    if (e.stopImmediatePropagation) {
                        e.stopImmediatePropagation();
                    }
                    var triggerBtn = card.querySelector('.goods-exhibition-quickview-btn');
                    try {
                        var rawData = card.getAttribute('data-product') || (triggerBtn ? triggerBtn.getAttribute('data-product') : '{}');
                        var data = typeof rawData === 'string' ? JSON.parse(rawData || '{}') : rawData;
                        openModal(data, card, triggerBtn);
                    } catch (err) {}
                    return;
                }
            }

            // ESC 键关闭
            if (e.key === 'Escape') {
                var modal = getModal();
                if (modal && modal.style.display !== 'none') {
                    closeModal();
                }
            }
        });

        // 移动端前进后退或切换页面时保证重置弹窗及页面滚动锁
        window.addEventListener('popstate', closeModal);
        window.addEventListener('pagehide', closeModal);
    }
})();
