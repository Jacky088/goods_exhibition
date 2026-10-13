/**
 * 好物页面管理插件后台交互脚本 (v2.0.0 现代 SaaS 交互系统)
 */
(function($) {
    'use strict';

    $(document).ready(function() {
        initMediaUploader();
        initImageDelete();
        initConfirmModal();
        initCounters();
        initCategorySearch();
        initPosterPreviewModal();
        initHelpModal();
        initAlertDismiss();
        initFormState();
        initFormValidation();
        initBulkActions();
        initBulkMove();
        initDragSort();
        initPosterSort();
        initCategoryEdit();
        initCategorySort();
        initProductModal();
    });

    /**
     * 初始化媒体上传器与 Dropzone 点击/拖拽支持
     */
    function initMediaUploader() {
        var mediaUploaderProduct;
        var mediaUploaderPoster;

        // 打开产品图片选择媒体库
        function openProductMedia() {
            if (mediaUploaderProduct) {
                mediaUploaderProduct.open();
                return;
            }

            mediaUploaderProduct = wp.media({
                title: '选择产品图片',
                button: { text: '使用此图片' },
                library: { type: 'image' },
                multiple: false
            });

            mediaUploaderProduct.on('select', function() {
                var attachment = mediaUploaderProduct.state().get('selection').first().toJSON();
                var url = attachment.url;
                $('#product_image_url').val(url);

                var $dropzone = $('#product-image-dropzone');
                var $preview = $dropzone.find('.product-image-preview');
                var $content = $dropzone.find('.ge-dropzone-content');

                if ($preview.find('img').length === 0) {
                    $preview.prepend('<img src="' + url + '" alt="产品图片预览">');
                } else {
                    $preview.find('img').attr('src', url);
                }

                $content.addClass('is-hidden');
                $preview.removeClass('is-hidden');
            });

            mediaUploaderProduct.open();
        }

        // 打开海报图片选择媒体库
        function openPosterMedia() {
            if (mediaUploaderPoster) {
                mediaUploaderPoster.open();
                return;
            }

            mediaUploaderPoster = wp.media({
                title: '选择海报图片',
                button: { text: '使用此图片' },
                library: { type: 'image' },
                multiple: false
            });

            mediaUploaderPoster.on('select', function() {
                var attachment = mediaUploaderPoster.state().get('selection').first().toJSON();
                var url = attachment.url;
                $('#poster_image_url').val(url);

                var $dropzone = $('#poster-image-dropzone');
                var $preview = $dropzone.find('.poster-image-preview');
                var $content = $dropzone.find('.ge-dropzone-content');

                if ($preview.find('img').length === 0) {
                    $preview.prepend('<img src="' + url + '" alt="海报图片预览">');
                } else {
                    $preview.find('img').attr('src', url);
                }

                $content.addClass('is-hidden');
                $preview.removeClass('is-hidden');
            });

            mediaUploaderPoster.open();
        }

        // 产品图片 Dropzone 点击交互
        $('#product-image-dropzone').on('click', function(e) {
            // 如果点击的是按钮（更换/删除），由按钮事件处理
            if ($(e.target).closest('button').length) {
                return;
            }
            openProductMedia();
        });

        $('#upload_image_button').on('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            openProductMedia();
        });

        // 海报图片 Dropzone 点击交互
        $('#poster-image-dropzone').on('click', function(e) {
            if ($(e.target).closest('button').length) {
                return;
            }
            openPosterMedia();
        });

        $('#upload_poster_image_button').on('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            openPosterMedia();
        });
    }

    /**
     * 初始化图片删除（产品图片 / 海报图片）
     */
    function initImageDelete() {
        var $modal = $('#goods-image-delete-modal');
        if (!$modal.length) return;

        var $pendingBtn = null;

        $(document).on('click', '.goods-image-delete-btn', function(e) {
            e.preventDefault();
            e.stopPropagation();
            $pendingBtn = $(this);

            var label = $pendingBtn.data('label') || '图片';
            var isRequired = String($pendingBtn.data('required')) === '1';

            $('#goods-image-delete-title').text('删除' + label);

            var html = '<p>确定要删除该' + label + '吗？请先阅读以下影响说明：</p>';
            html += '<ul class="ge-modal-impact">';
            html += '<li>保存后，本产品将不再使用该' + label + '。</li>';
            html += '<li>若该图片未被网站其它内容引用，将<strong>同时从 WordPress 媒体库中永久删除</strong>，不可恢复。</li>';
            html += '<li>若仍被其它内容引用，则仅解除本产品的关联，媒体库中会保留。</li>';
            html += '</ul>';
            if (isRequired) {
                html += '<p style="color: #ef4444; margin-top: 8px;"><strong>注意：</strong>' + label + '为必填项，删除后请及时选择新图片，否则将无法保存。</p>';
            }
            html += '<p style="color: #1e40af; background: #eff6ff; padding: 8px 12px; border-radius: 6px; margin-top: 10px;"><strong>提示：</strong>此删除在点击保存后才会真正生效；若未保存离开页面，不会删除任何图片。</p>';

            $('#goods-image-delete-body').html(html);
            $modal.show();
        });

        $modal.find('.goods-image-modal-close, .ge-modal-overlay').on('click', function() {
            $modal.hide();
            $pendingBtn = null;
        });

        $modal.find('.goods-image-delete-confirm').on('click', function() {
            if (!$pendingBtn) {
                $modal.hide();
                return;
            }

            var $btn = $pendingBtn;
            var targetField = $btn.data('target');
            var dropzoneSelector = $btn.data('dropzone');
            var fileSelector = $btn.data('file');

            // 清空隐藏 URL 输入
            $(targetField).val('');
            if (fileSelector) {
                $(fileSelector).val('');
            }

            // 重置 Dropzone 视觉态
            var $dropzone = $(dropzoneSelector);
            var $preview = $dropzone.find('.ge-dropzone-preview');
            var $content = $dropzone.find('.ge-dropzone-content');

            $preview.find('img').remove();
            $preview.addClass('is-hidden');
            $content.removeClass('is-hidden');

            $modal.hide();
            $pendingBtn = null;
        });

        $(document).on('keydown', function(e) {
            if (e.key === 'Escape' && $modal.is(':visible')) {
                $modal.hide();
                $pendingBtn = null;
            }
        });
    }

    /**
     * 初始化通用二次确认弹窗（产品删除 / 批量删除 / 分类删除）
     */
    function initConfirmModal() {
        var $modal = $('#goods-confirm-modal');
        if (!$modal.length) return;

        var pendingAction = null;

        function closeModal() {
            $modal.hide();
            pendingAction = null;
        }

        function openModal(body, onConfirm) {
            $('#goods-confirm-title').text(body.title);
            $('#goods-confirm-body').html(body.html);
            $modal.find('.goods-confirm-ok').text(body.okText || '确认删除');
            pendingAction = onConfirm;
            $modal.show();
        }

        function esc(text) {
            return $('<div>').text(text == null ? '' : text).html();
        }

        function buildBody(type, name, count) {
            if (type === 'product') {
                return {
                    title: '确认删除产品',
                    okText: '确认删除',
                    html: '<p>确定要删除产品「<strong>' + esc(name) + '</strong>」吗？</p>' +
                        '<ul class="ge-modal-impact">' +
                        '<li>该产品将立即从展示页与后台列表中移除。</li>' +
                        '<li>若其关联的图片未被其它内容引用，将<strong>同时从 WordPress 媒体库中删除</strong>。</li>' +
                        '</ul>' +
                        '<p style="color: #991b1b; background: #fef2f2; padding: 8px 12px; border-radius: 6px;"><strong>注意：</strong>删除操作确认后立即生效，且不可恢复。</p>'
                };
            }

            if (type === 'bulk') {
                return {
                    title: '确认批量删除',
                    okText: '确认删除全部',
                    html: '<p>确定要删除选中的 <strong>' + count + '</strong> 个产品吗？</p>' +
                        '<ul class="ge-modal-impact">' +
                        '<li>选中的 ' + count + ' 个产品将立即被删除。</li>' +
                        '<li>无其它引用的图片将同步从媒体库中清理。</li>' +
                        '</ul>' +
                        '<p style="color: #991b1b; background: #fef2f2; padding: 8px 12px; border-radius: 6px;"><strong>注意：</strong>批量删除确认后立即生效，且不可恢复。</p>'
                };
            }

            return {
                title: '确认删除分类',
                okText: '确认删除',
                html: '<p>确定要删除分类「<strong>' + esc(name) + '</strong>」吗？</p>' +
                    '<ul class="ge-modal-impact">' +
                    '<li>该分类将从分类列表及筛选下拉中移除。</li>' +
                    '<li>仅当分类下无任何产品时允许删除。</li>' +
                    '</ul>' +
                    '<p style="color: #991b1b; background: #fef2f2; padding: 8px 12px; border-radius: 6px;"><strong>注意：</strong>分类删除确认后立即生效。</p>'
            };
        }

        // 绑定删除链接（单个产品 / 分类）
        $(document).on('click', '.goods-confirm-link', function(e) {
            var $link = $(this);
            var href = $link.attr('href');
            if (!href) return;

            e.preventDefault();
            var body = buildBody($link.data('confirm-type'), $link.data('confirm-name'), 1);
            openModal(body, function() {
                window.location.href = href;
            });
        });

        // 绑定禁用态分类删除按钮：点击弹出明确引导说明
        $(document).on('click', '.ge-cat-disabled-del', function(e) {
            e.preventDefault();
            var count = $(this).data('count') || 0;
            var name = $(this).data('name') || '';
            alert('【删除受限】无法删除分类「' + name + '」\n\n该分类目录下当前仍关联有 ' + count + ' 个商品。\n为保障数据完整性与安全，必须先清空或将该分类目录下的所有商品转移至其他分类后，才能进行删除。');
        });

        // 绑定批量删除按钮
        $('.goods-exhibition-bulk-delete-btn').on('click', function(e) {
            e.preventDefault();
            var count = $('.goods-cb-item:checked').length;
            if (count === 0) return;

            var $form = $('#goods-exhibition-bulk-form');
            openModal(buildBody('bulk', '', count), function() {
                $form.trigger('submit');
            });
        });

        $modal.find('.goods-confirm-ok').on('click', function() {
            var action = pendingAction;
            closeModal();
            if (typeof action === 'function') {
                action();
            }
        });

        $modal.find('.goods-confirm-cancel, .ge-modal-overlay, .ge-modal-close').on('click', closeModal);

        $(document).on('keydown', function(e) {
            if (e.key === 'Escape' && $modal.is(':visible')) {
                closeModal();
            }
        });
    }

    /**
     * 初始化表单实时字符计数器 (0/50, 0/500, 0/20)
     */
    function initCounters() {
        function bindCounter($input, $counter, max) {
            if (!$input.length || !$counter.length) return;

            function update() {
                var len = $input.val() ? $input.val().length : 0;
                $counter.text(len + '/' + max);
                if (len >= max) {
                    $counter.css('color', '#ef4444');
                } else {
                    $counter.css('color', '');
                }
            }

            $input.on('input propertychange', update);
            update();
        }

        bindCounter($('#product_name'), $('#product_name_counter'), 50);
        bindCounter($('#product_description'), $('#product_desc_counter'), 500);
        bindCounter($('#category_name'), $('#category_name_counter'), 20);
        bindCounter($('#edit_category_name'), $('#edit_category_name_counter'), 20);
    }

    /**
     * 初始化分类实时前端搜索过滤
     */
    function initCategorySearch() {
        var $searchInput = $('#goods-cat-search-input');
        var $rows = $('#goods-category-sort-body tr:not(.ge-empty-row)');
        var $countEl = $('#ge-cat-count');

        if (!$searchInput.length || !$rows.length) return;

        $searchInput.on('input', function() {
            var val = $.trim($(this).val()).toLowerCase();
            var visibleCount = 0;

            $rows.each(function() {
                var name = $(this).data('name') || $(this).find('strong').text();
                name = String(name).toLowerCase();

                if (!val || name.indexOf(val) !== -1) {
                    $(this).show();
                    visibleCount++;
                } else {
                    $(this).hide();
                }
            });

            $countEl.text(visibleCount);
        });
    }

    /**
     * 初始化海报大图预览弹窗
     */
    function initPosterPreviewModal() {
        var $modal = $('#goods-preview-modal');
        if (!$modal.length) return;

        $('.goods-poster-preview-btn').on('click', function(e) {
            e.preventDefault();
            var url = $(this).data('url');
            var title = $(this).data('title') || '海报预览';

            if (!url) return;

            $('#goods-preview-title').text(title);
            $('#goods-preview-img').attr('src', url);
            $modal.show();
        });

        $modal.find('.goods-preview-close, .ge-modal-overlay').on('click', function() {
            $modal.hide();
        });

        $(document).on('keydown', function(e) {
            if (e.key === 'Escape' && $modal.is(':visible')) {
                $modal.hide();
            }
        });
    }

    /**
     * 初始化使用帮助与说明弹窗
     */
    function initHelpModal() {
        var $modal = $('#goods-help-modal');
        if (!$modal.length) return;

        $('#ge-open-help-modal').on('click', function(e) {
            e.preventDefault();
            $modal.show();
            $('body').addClass('ge-modal-open');
        });

        $modal.find('.goods-help-modal-close, .ge-modal-overlay').on('click', function() {
            $modal.hide();
            $('body').removeClass('ge-modal-open');
        });

        $(document).on('keydown', function(e) {
            if (e.key === 'Escape' && $modal.is(':visible')) {
                $modal.hide();
                $('body').removeClass('ge-modal-open');
            }
        });
    }

    /**
     * 初始化通知横幅关闭交互
     */
    function initAlertDismiss() {
        $('.ge-alert-close').on('click', function() {
            $(this).closest('.ge-alert').fadeOut(150, function() {
                $(this).remove();
            });
        });
    }

    /**
     * 初始化批量操作（全选、状态同步）
     */
    function initBulkActions() {
        var $selectAll = $('#goods-cb-select-all');
        var $items = $('.goods-cb-item');
        var $bulkDeleteBtn = $('.goods-exhibition-bulk-delete-btn');
        var $moveBtn = $('#goods-bulk-move-open-btn, .goods-exhibition-bulk-move-btn');
        var $countLabel = $('.goods-exhibition-selected-count');

        function updateState() {
            var checkedCount = $items.filter(':checked').length;
            var hasChecked = checkedCount > 0;

            $bulkDeleteBtn.prop('disabled', !hasChecked);
            $moveBtn.prop('disabled', !hasChecked);

            if (hasChecked) {
                $countLabel.text('已选 ' + checkedCount + ' 项');
            } else {
                $countLabel.text('');
            }

            if (checkedCount === $items.length && $items.length > 0) {
                $selectAll.prop('checked', true).prop('indeterminate', false);
            } else if (checkedCount > 0) {
                $selectAll.prop('checked', false).prop('indeterminate', true);
            } else {
                $selectAll.prop('checked', false).prop('indeterminate', false);
            }
        }

        $selectAll.on('change', function() {
            $items.prop('checked', $(this).is(':checked'));
            updateState();
        });

        $(document).on('change', '.goods-cb-item', updateState);
    }

    /**
     * 初始化批量移动分类（弹窗形式，参考效果图 2）
     */
    function initBulkMove() {
        var $modal = $('#goods-bulk-move-modal');
        if (!$modal.length) return;

        var $openBtn = $('#goods-bulk-move-open-btn, .goods-exhibition-bulk-move-btn');
        var $sourceCat = $('#goods-bulk-move-source-cat');
        var $targetCat = $('#goods-bulk-move-target-cat');
        var $countSpan = $('#goods-bulk-move-modal-count');
        var $confirmBtn = $('#goods-bulk-move-confirm-btn');

        function openModal() {
            var $checked = $('.goods-cb-item:checked');
            var count = $checked.length;
            if (count === 0) {
                alert('请先选择要移动的产品');
                return;
            }

            $countSpan.text(count);

            // 提取勾选产品所关联的源分类
            var catMap = {};
            $checked.each(function() {
                var cat = $(this).data('category');
                if (cat && typeof cat === 'string') {
                    cat = $.trim(cat);
                    if (cat) {
                        catMap[cat] = true;
                    }
                }
            });

            // 填充源分类下拉框（默认"全部"，并包含当前勾选产品所挂的具体分类）
            $sourceCat.empty();
            $sourceCat.append('<option value="all">全部</option>');
            var cats = Object.keys(catMap);
            cats.forEach(function(cat) {
                $sourceCat.append($('<option></option>').val(cat).text(cat));
            });
            $sourceCat.val('all');

            // 重置目标分类选择
            $targetCat.val('');

            $modal.show();
            $('body').addClass('ge-modal-open');
        }

        function closeModal() {
            $modal.hide();
            $('body').removeClass('ge-modal-open');
        }

        $openBtn.on('click', function(e) {
            e.preventDefault();
            if ($(this).prop('disabled')) return;
            openModal();
        });

        $modal.find('.goods-bulk-move-close, .ge-modal-overlay').on('click', function(e) {
            e.preventDefault();
            closeModal();
        });

        $(document).on('keydown', function(e) {
            if (e.key === 'Escape' && $modal.is(':visible')) {
                closeModal();
            }
        });

        $confirmBtn.on('click', function(e) {
            e.preventDefault();
            var targetCategory = $targetCat.val();
            if (!targetCategory) {
                alert('请选择目标分类');
                $targetCat.focus();
                return;
            }

            var sourceCategory = $sourceCat.val() || 'all';
            var selectedIds = [];
            $('.goods-cb-item:checked').each(function() {
                selectedIds.push($(this).val());
            });

            if (selectedIds.length === 0) {
                alert('请先选择要移动的产品');
                return;
            }

            $confirmBtn.prop('disabled', true).text('移动中...');

            $.post(goodsExhibitionAdmin.ajaxUrl, {
                action: 'goods_exhibition_bulk_move',
                nonce: goodsExhibitionAdmin.nonce,
                product_ids: selectedIds,
                source_category: sourceCategory,
                target_category: targetCategory
            }, function(response) {
                $confirmBtn.prop('disabled', false).text('确认移动');
                if (response && response.success) {
                    closeModal();
                    location.reload();
                } else {
                    alert('移动失败：' + (response && response.data ? response.data : '未知错误'));
                }
            }).fail(function() {
                $confirmBtn.prop('disabled', false).text('确认移动');
                alert('网络错误，请重试');
            });
        });
    }

    /**
     * 初始化分类列表拖拽排序
     */
    function initCategorySort() {
        var $tbody = $('#goods-category-sort-body');
        if (!$tbody.length) return;

        $tbody.sortable({
            handle: '.ge-drag-handle',
            items: '> tr:not(.ge-empty-row)',
            axis: 'y',
            tolerance: 'pointer',
            forcePlaceholderSize: true,
            opacity: 0.75,
            cursor: 'grabbing',
            placeholder: 'goods-sort-placeholder',
            update: function() {
                var order = [];
                $tbody.find('tr:not(.ge-empty-row)').each(function(index) {
                    order.push($(this).data('id'));
                    $(this).find('.goods-cat-sort-order').text(index + 1);
                });

                $.post(goodsExhibitionAdmin.ajaxUrl, {
                    action: 'goods_exhibition_save_category_sort',
                    nonce: goodsExhibitionAdmin.nonce,
                    order: order
                }, function(response) {
                    if (response && response.success) {
                        var $notice = $('<div class="ge-alert ge-alert-success"><div class="ge-alert-content"><span>分类顺序已更新保存</span></div></div>');
                        $tbody.closest('.ge-table-wrapper').before($notice);
                        setTimeout(function() { $notice.fadeOut(function() { $notice.remove(); }); }, 2000);
                    } else {
                        alert('保存失败：' + (response && response.data ? response.data : '未知错误'));
                    }
                }).fail(function() {
                    alert('网络异常，排序保存失败');
                });
            }
        });
    }

    /**
     * 初始化产品列表拖拽排序（仅在分类筛选状态下可用）
     */
    function initDragSort() {
        var $table = $('.goods-exhibition-table[data-sortable="1"]');
        if (!$table.length) return;

        var $tbody = $('#goods-sortable-body');

        $tbody.sortable({
            handle: '.ge-drag-handle',
            axis: 'y',
            opacity: 0.75,
            cursor: 'grabbing',
            placeholder: 'goods-sort-placeholder',
            update: function() {
                var order = [];
                $tbody.find('tr').each(function() {
                    order.push($(this).data('id'));
                });

                $.post(goodsExhibitionAdmin.ajaxUrl, {
                    action: 'goods_exhibition_save_sort',
                    nonce: goodsExhibitionAdmin.nonce,
                    order: order
                }, function(response) {
                    if (response && response.success) {
                        var $notice = $('<div class="ge-alert ge-alert-success"><div class="ge-alert-content"><span>产品排序已保存</span></div></div>');
                        $table.closest('.ge-table-card').before($notice);
                        setTimeout(function() { $notice.fadeOut(function() { $notice.remove(); }); }, 2000);
                    }
                });
            }
        });
    }

    /**
     * 初始化海报拖拽排序
     */
    function initPosterSort() {
        var $list = $('#goods-poster-sort-list');
        if (!$list.length) return;

        var $status = $('.goods-poster-sort-status');
        var displayLimit = parseInt($('.goods-poster-sort-hint').data('limit'), 10) || 6;

        function renumber() {
            $list.find('.ge-poster-item').each(function(index) {
                $(this).find('.goods-poster-sort-order').text(index + 1);
                var isOver = index >= displayLimit;
                $(this).toggleClass('is-overflow', isOver);

                var $badge = $(this).find('.goods-poster-sort-badge');
                if (isOver && !$badge.length) {
                    $(this).find('.ge-poster-right').prepend('<span class="ge-badge-pill ge-badge-warning goods-poster-sort-badge">超出展示范围</span>');
                } else if (!isOver && $badge.length) {
                    $badge.remove();
                }
            });
        }

        $list.sortable({
            handle: '.ge-drag-handle, .ge-poster-thumb',
            items: '> .ge-poster-item',
            axis: 'y',
            tolerance: 'pointer',
            forcePlaceholderSize: true,
            opacity: 0.8,
            cursor: 'grabbing',
            placeholder: 'goods-sort-placeholder',
            update: function() {
                var order = [];
                $list.find('.ge-poster-item').each(function() {
                    order.push($(this).data('id'));
                });
                renumber();

                $status.text('正在保存排序...').css('color', '#64748b');

                $.post(goodsExhibitionAdmin.ajaxUrl, {
                    action: 'goods_exhibition_save_poster_sort',
                    nonce: goodsExhibitionAdmin.nonce,
                    order: order
                }, function(response) {
                    if (response && response.success) {
                        $status.text('海报轮播顺序已保存').css('color', '#2563eb');
                        setTimeout(function() { $status.text(''); }, 2000);
                    } else {
                        $status.text('保存失败：' + (response && response.data ? response.data : '未知错误')).css('color', '#ef4444');
                    }
                }).fail(function() {
                    $status.text('保存失败：网络错误').css('color', '#ef4444');
                });
            }
        });
    }

    /**
     * 初始化分类编辑弹窗
     */
    function initCategoryEdit() {
        var $modal = $('#goods-cat-edit-modal');
        if (!$modal.length) return;

        $('.goods-cat-edit-link').on('click', function(e) {
            e.preventDefault();
            var id = $(this).data('id');
            var name = $(this).data('name');

            $('#edit_cat_id').val(id);
            $('#edit_category_name').val(name).trigger('input');
            $modal.show();
        });

        $modal.find('.goods-cat-modal-close, .ge-modal-overlay').on('click', function() {
            $modal.hide();
        });

        $(document).on('keydown', function(e) {
            if (e.key === 'Escape' && $modal.is(':visible')) {
                $modal.hide();
            }
        });
    }

    /**
     * 初始化表单未保存状态提醒与还原修改
     */
    function initFormState() {
        var $form = $('#goods-exhibition-product-form');
        if (!$form.length) return;

        var fieldValues = [];
        $form.find('input, select, textarea').each(function() {
            var $el = $(this);
            if ($el.is(':checkbox') || $el.is(':radio')) {
                fieldValues.push($el.prop('checked'));
            } else {
                fieldValues.push($el.val());
            }
        });

        var previewHtml = [];
        $('.product-image-preview, .poster-image-preview').each(function() {
            previewHtml.push($(this).html());
        });

        var initialSerialized = $form.serialize();

        window.geSyncFormBaseline = function() {
            initialSerialized = $form.serialize();
        };

        // 还原修改按钮
        $('.goods-form-reset').on('click', function(e) {
            e.preventDefault();
            if (!confirm('确定要放弃当前所有修改，恢复到进入页面时的状态吗？')) {
                return;
            }

            var index = 0;
            $form.find('input, select, textarea').each(function() {
                var $el = $(this);
                if ($el.is(':checkbox') || $el.is(':radio')) {
                    $el.prop('checked', fieldValues[index]);
                } else {
                    $el.val(fieldValues[index]);
                }
                index++;
            });

            index = 0;
            $('.product-image-preview, .poster-image-preview').each(function() {
                $(this).html(previewHtml[index]);
                index++;
            });

            // 重新触发计数器更新
            $('#product_name, #product_description').trigger('input');
        });

        // 离开页面提醒 (仅在弹窗打开且内容改动时提醒)
        window.addEventListener('beforeunload', function(e) {
            if ($form.data('goodsSubmitting')) {
                return;
            }
            var $modal = $('#goods-product-modal');
            if ($modal.length && !$modal.is(':visible')) {
                return;
            }
            if ($form.serialize() !== initialSerialized) {
                e.preventDefault();
                e.returnValue = '';
                return '';
            }
        });
    }

    /**
     * 初始化表单提交验证
     */
    function initFormValidation() {
        $('#goods-exhibition-product-form').on('submit', function() {
            var currentId = parseInt($('#product_id').val(), 10) || 0;
            var name = $.trim($('#product_name').val());
            var desc = $.trim($('#product_description').val());
            var cat = $.trim($('#product_category').val());
            var imgUrl = $('#product_image_url').val();
            var imgFile = $('#product_image')[0];
            var hasImg = imgUrl || (imgFile && imgFile.files && imgFile.files.length);
            var isPoster = $('#product_is_poster').is(':checked');
            var posterUrl = $('#poster_image_url').val();
            var posterFile = $('#poster_image')[0];
            var hasPoster = posterUrl || (posterFile && posterFile.files && posterFile.files.length);

            var errors = [];
            if (!name) errors.push('产品名称');
            if (!desc) errors.push('产品描述');
            if (!cat) errors.push('产品类别');
            if (!hasImg) errors.push('产品图片');
            if (isPoster && !hasPoster) errors.push('海报图片（已勾选标记为海报）');

            if (errors.length > 0) {
                alert('以下必填项尚未完整填写：\n\n' + errors.join('、') + '\n\n请补充完整后再保存。');
                return false;
            }

            // 前端同名产品即时拦截 (检查当前页面商品列表中是否已有同名)
            var duplicateFound = false;
            $('.ge-row-edit-btn').each(function() {
                var pData = $(this).data('product');
                if (typeof pData === 'string') {
                    try { pData = JSON.parse(pData); } catch (e) { pData = null; }
                }
                if (pData && pData.name && $.trim(pData.name).toLowerCase() === name.toLowerCase()) {
                    var otherId = parseInt(pData.id, 10);
                    if (currentId === 0 || otherId !== currentId) {
                        duplicateFound = true;
                        return false;
                    }
                }
            });

            if (duplicateFound) {
                alert('【添加失败】已存在名为「' + name + '」的产品。\n为避免商品重复，产品名称不能相同，请修改后再保存。');
                $('#product_name').focus();
                return false;
            }

            $(this).data('goodsSubmitting', true);
            return true;
        });

        // 分类添加表单前端即时重名拦截
        $('.ge-cat-add-card form').on('submit', function() {
            var catName = $.trim($('#category_name').val());
            if (!catName) {
                alert('请输入分类名称');
                return false;
            }

            var duplicateFound = false;
            $('#goods-category-sort-body tr[data-name]').each(function() {
                var existingName = $.trim($(this).data('name') || '');
                if (existingName.toLowerCase() === catName.toLowerCase()) {
                    duplicateFound = true;
                    return false;
                }
            });

            if (duplicateFound) {
                alert('【添加失败】已存在名为「' + catName + '」的分类，请勿重复添加。');
                $('#category_name').focus();
                return false;
            }

            return true;
        });

        // 分类编辑表单前端即时重名拦截
        $('#goods-cat-edit-modal form').on('submit', function() {
            var catId = parseInt($('#edit_cat_id').val(), 10) || 0;
            var catName = $.trim($('#edit_category_name').val());
            if (!catName) {
                alert('请输入分类名称');
                return false;
            }

            var duplicateFound = false;
            $('#goods-category-sort-body tr[data-name]').each(function() {
                var thisId = parseInt($(this).data('id'), 10) || 0;
                var existingName = $.trim($(this).data('name') || '');
                if (thisId !== catId && existingName.toLowerCase() === catName.toLowerCase()) {
                    duplicateFound = true;
                    return false;
                }
            });

            if (duplicateFound) {
                alert('【保存失败】已存在名为「' + catName + '」的分类，无法修改为重名分类。');
                $('#edit_category_name').focus();
                return false;
            }

            return true;
        });
    }

    /**
     * 初始化产品添加与编辑模态弹窗系统 (Window Modal)
     */
    function initProductModal() {
        var $modal = $('#goods-product-modal');
        if (!$modal.length) return;

        var $form = $('#goods-exhibition-product-form');

        function openModal() {
            $modal.show();
            $('body').addClass('ge-modal-open');
            if (typeof window.geSyncFormBaseline === 'function') {
                window.geSyncFormBaseline();
            }
        }

        function closeModal() {
            $modal.hide();
            $('body').removeClass('ge-modal-open');
        }

        function populateForm(product) {
            $('#product_id').val(product.id || '0');
            $('#product_name').val(product.name || '').trigger('input');
            $('#product_description').val(product.description || '').trigger('input');

            // 解析货币与价格
            var rawPrice = product.price ? $.trim(product.price) : '';
            var detectedCurrency = '¥';
            var cleanPrice = rawPrice;

            var currencyPrefixes = ['HK$', 'NT$', 'RMB', '¥', '$', '€', '£', '₩'];
            var matched = false;
            for (var i = 0; i < currencyPrefixes.length; i++) {
                var prefix = currencyPrefixes[i];
                if (cleanPrice.indexOf(prefix) === 0) {
                    detectedCurrency = (prefix === 'RMB') ? '¥' : prefix;
                    cleanPrice = $.trim(cleanPrice.substring(prefix.length));
                    matched = true;
                    break;
                }
            }
            if (!matched && cleanPrice) {
                if (!/^[0-9,.]/.test(cleanPrice)) {
                    detectedCurrency = '';
                }
            }
            $('#product_currency').val(detectedCurrency);
            $('#product_price').val(cleanPrice);
            $('#product_badge_tag').val(product.badge_tag || '');
            syncTagBoxFromInput('#product_badge_tag');

            $('#product_brand').val(product.brand || '');
            syncTagBoxFromInput('#product_brand');

            $('#product_custom_brand').val(product.custom_brand || '').trigger('input');
            $('#product_brand_logo').val(product.brand_logo || '').trigger('input');

            $('#product_url').val(product.url || '');
            $('#product_category').val(product.category || '');

            $('#product_is_new').prop('checked', String(product.is_new) === '1');
            $('#product_is_poster').prop('checked', String(product.is_poster) === '1');

            // 产品图片回填与预览
            var imgUrl = product.image_url || '';
            $('#product_image_url').val(imgUrl);
            $('#product_image').val('');

            var $productDropzone = $('#product-image-dropzone');
            var $productPreview = $productDropzone.find('.product-image-preview');
            var $productContent = $productDropzone.find('.ge-dropzone-content');

            if (imgUrl) {
                if ($productPreview.find('img').length === 0) {
                    $productPreview.prepend('<img src="' + imgUrl + '" alt="产品图片预览">');
                } else {
                    $productPreview.find('img').attr('src', imgUrl);
                }
                $productContent.addClass('is-hidden');
                $productPreview.removeClass('is-hidden');
            } else {
                $productPreview.find('img').remove();
                $productPreview.addClass('is-hidden');
                $productContent.removeClass('is-hidden');
            }

            // 海报图片回填与预览
            var posterUrl = product.poster_image_url || '';
            $('#poster_image_url').val(posterUrl);
            $('#poster_image').val('');

            var $posterDropzone = $('#poster-image-dropzone');
            var $posterPreview = $posterDropzone.find('.poster-image-preview');
            var $posterContent = $posterDropzone.find('.ge-dropzone-content');

            if (posterUrl) {
                if ($posterPreview.find('img').length === 0) {
                    $posterPreview.prepend('<img src="' + posterUrl + '" alt="海报图片预览">');
                } else {
                    $posterPreview.find('img').attr('src', posterUrl);
                }
                $posterContent.addClass('is-hidden');
                $posterPreview.removeClass('is-hidden');
            } else {
                $posterPreview.find('img').remove();
                $posterPreview.addClass('is-hidden');
                $posterContent.removeClass('is-hidden');
            }
        }

        function resetForm() {
            $('#product_id').val('0');
            $('#goods-product-modal-title').text('添加新产品');
            $('#goods-product-modal-submit').text('添加产品');

            $form[0].reset();
            $('#product_currency').val('¥');
            $('#product_price').val('');

            $('#product_badge_tag').val('');
            syncTagBoxFromInput('#product_badge_tag');

            $('#product_brand').val('');
            syncTagBoxFromInput('#product_brand');

            $('#product_custom_brand').val('').trigger('input');
            $('#product_brand_logo').val('').trigger('input');
            $('#product_image_url').val('');
            $('#poster_image_url').val('');
            $('#product_image').val('');
            $('#poster_image').val('');

            var $productDropzone = $('#product-image-dropzone');
            $productDropzone.find('.product-image-preview img').remove();
            $productDropzone.find('.product-image-preview').addClass('is-hidden');
            $productDropzone.find('.ge-dropzone-content').removeClass('is-hidden');

            var $posterDropzone = $('#poster-image-dropzone');
            $posterDropzone.find('.poster-image-preview img').remove();
            $posterDropzone.find('.poster-image-preview').addClass('is-hidden');
            $posterDropzone.find('.ge-dropzone-content').removeClass('is-hidden');

            $('#product_name').trigger('input');
            $('#product_description').trigger('input');
            $('.ge-quick-tag-btn').removeClass('is-active ge-btn-primary').addClass('ge-btn-secondary');
            $('.ge-field-clear-btn').hide();
        }

        function escapeTagHtml(text) {
            var div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        // 同步标签输入与 Tag Box 芯片容器展示
        function syncTagBoxFromInput(targetSelector) {
            var $input = $(targetSelector);
            if (!$input.length) return;
            var boxId = targetSelector.replace('#', '') + '_box';
            var $box = $('#' + boxId);
            if (!$box.length) return;

            var max = parseInt($box.data('max'), 10) || 1;
            var rawVal = $.trim($input.val());
            var tags = [];

            if (targetSelector === '#product_badge_tag') {
                if (rawVal) {
                    // 严格按标点分隔，不要按空格切分，保留带有 emoji 的标签整体（如「新品首发 🚀」）
                    var parts = rawVal.split(/[,，、;；|]+/);
                    for (var i = 0; i < parts.length; i++) {
                        var p = $.trim(parts[i]);
                        if (p && tags.indexOf(p) === -1) {
                            tags.push(p);
                        }
                        if (tags.length >= max) break;
                    }
                }
            } else if (targetSelector === '#product_brand') {
                if (rawVal) {
                    tags.push(rawVal);
                }
            }

            var cleanVal = tags.join(', ');
            $input.val(cleanVal);

            var $chips = $box.find('.ge-tag-box-chips');
            var $placeholder = $box.find('.ge-tag-box-placeholder');
            $chips.empty();

            if (tags.length > 0) {
                $placeholder.hide();
                for (var j = 0; j < tags.length; j++) {
                    var tagText = tags[j];
                    var $chip = $('<span class="ge-tag-chip" data-val="' + escapeTagHtml(tagText) + '">' +
                        '<span class="ge-tag-chip-label">' + escapeTagHtml(tagText) + '</span>' +
                        '<button type="button" class="ge-tag-chip-remove" title="移除此标签">&times;</button>' +
                        '</span>');
                    $chips.append($chip);
                }
            } else {
                $placeholder.show();
            }

            // 同步预设胶囊按钮的高亮选中状态
            if (targetSelector === '#product_badge_tag') {
                $('.ge-quick-tag-btn[data-target="' + targetSelector + '"]').each(function() {
                    var btnVal = $.trim($(this).data('val'));
                    if (btnVal && tags.indexOf(btnVal) !== -1) {
                        $(this).addClass('is-active ge-btn-primary').removeClass('ge-btn-secondary');
                    } else {
                        $(this).removeClass('is-active ge-btn-primary').addClass('ge-btn-secondary');
                    }
                });
            } else if (targetSelector === '#product_brand') {
                var currentLower = cleanVal.toLowerCase();
                $('.ge-quick-tag-btn[data-target="' + targetSelector + '"]').each(function() {
                    var btnVal = $.trim($(this).data('val')).toLowerCase();
                    if (btnVal && btnVal === currentLower) {
                        $(this).addClass('is-active ge-btn-primary').removeClass('ge-btn-secondary');
                    } else {
                        $(this).removeClass('is-active ge-btn-primary').addClass('ge-btn-secondary');
                    }
                });
            }
        }

        // 品牌 Logo 媒体库选择器
        $(document).on('click', '#ge_select_brand_logo_btn', function(e) {
            e.preventDefault();
            var brandLogoFrame = wp.media({
                title: '选择品牌 Logo 图标',
                button: { text: '使用此图标' },
                library: { type: 'image' },
                multiple: false
            });
            brandLogoFrame.on('select', function() {
                var attachment = brandLogoFrame.state().get('selection').first().toJSON();
                $('#product_brand_logo').val(attachment.url).trigger('input');
            });
            brandLogoFrame.open();
        });

        // 输入框右侧 X 快捷清除按钮 (仅用于自定义品牌文字与Logo输入框)
        $(document).on('click', '.ge-field-clear-btn', function(e) {
            e.preventDefault();
            var targetSelector = $(this).data('target');
            if (targetSelector) {
                var $target = $(targetSelector);
                $target.val('').trigger('input').trigger('change');
            }
            $(this).hide();
        });

        // 快捷标签点击注入与双向同步
        $(document).on('click', '.ge-quick-tag-btn', function(e) {
            e.preventDefault();
            var $btn = $(this);
            var target = $btn.data('target');
            var val = $.trim($btn.data('val'));
            if (!target || !val) return;

            var $input = $(target);
            var currentVal = $.trim($input.val());

            if (target === '#product_badge_tag') {
                // 特色卖点标签：支持多选，最多 2 个
                var tags = currentVal ? currentVal.split(/[,，、;；|]+/).map($.trim).filter(Boolean) : [];
                var existingIndex = tags.indexOf(val);

                if (existingIndex !== -1) {
                    // 已选中则取消
                    tags.splice(existingIndex, 1);
                } else {
                    // 最多 2 个，若已有 2 个则替换第 2 个
                    if (tags.length < 2) {
                        tags.push(val);
                    } else {
                        tags[1] = val;
                    }
                }
                $input.val(tags.join(', '));
                syncTagBoxFromInput(target);
            } else if (target === '#product_brand') {
                // 所属品牌：单选内置品牌，再次点击反选清空
                if (currentVal.toLowerCase() === val.toLowerCase()) {
                    $input.val('');
                } else {
                    $input.val(val);
                }
                syncTagBoxFromInput(target);
            }
        });

        // 标签胶囊右上角 X 移除按钮点击
        $(document).on('click', '.ge-tag-chip-remove', function(e) {
            e.preventDefault();
            e.stopPropagation();
            var $chip = $(this).closest('.ge-tag-chip');
            var valToRemove = $chip.data('val');
            var $box = $chip.closest('.ge-tag-box');
            var target = $box.data('target');
            if (!target) return;

            var $input = $(target);
            var currentVal = $.trim($input.val());

            if (target === '#product_badge_tag') {
                var tags = currentVal ? currentVal.split(/[,，、;；|]+/).map($.trim).filter(Boolean) : [];
                var idx = tags.indexOf(valToRemove);
                if (idx !== -1) {
                    tags.splice(idx, 1);
                }
                $input.val(tags.join(', '));
                syncTagBoxFromInput(target);
            } else if (target === '#product_brand') {
                $input.val('');
                syncTagBoxFromInput(target);
            }
        });

        // 监听带清除按钮的输入框，动态显示/隐藏右上角 X 按钮
        $(document).on('input change', '.ge-input-with-clear', function() {
            var $field = $(this);
            var val = $.trim($field.val());
            var $clearBtn = $field.closest('.ge-input-with-clear-wrap').find('.ge-field-clear-btn');
            if (val.length > 0) {
                $clearBtn.show();
            } else {
                $clearBtn.hide();
            }
        });

        // 监听外部对隐藏标签域的修改驱动同步
        $(document).on('change', '#product_badge_tag, #product_brand', function() {
            var targetSelector = '#' + $(this).attr('id');
            syncTagBoxFromInput(targetSelector);
        });

        // 打开添加新产品弹窗（顶部主按钮 或 空状态按钮）
        $(document).on('click', '#ge-open-add-modal, #ge-empty-add-btn', function(e) {
            e.preventDefault();
            resetForm();
            openModal();
        });

        // 点击表格每一行的编辑铅笔按钮，以模态弹窗形式打开编辑
        $(document).on('click', '.ge-row-edit-btn', function(e) {
            e.preventDefault();
            var productData = $(this).data('product');
            if (typeof productData === 'string') {
                try {
                    productData = JSON.parse(productData);
                } catch (err) {
                    console.error('Failed to parse product data', err);
                    return;
                }
            }

            if (!productData) return;

            $('#goods-product-modal-title').text('编辑产品 (ID: ' + productData.id + ')');
            $('#goods-product-modal-submit').text('更新产品');
            populateForm(productData);
            openModal();
        });

        // 关闭弹窗
        $modal.find('.goods-product-modal-close, .ge-modal-overlay').on('click', function() {
            closeModal();
        });

        $(document).on('keydown', function(e) {
            if (e.key === 'Escape' && $modal.is(':visible')) {
                // 若图片删除确认框开启，优先关闭图片删除确认框，保留产品弹窗
                if ($('#goods-image-delete-modal').is(':visible')) {
                    return;
                }
                closeModal();
            }
        });

        // 检测页面初始是否需自动弹出（适配直接访问带 action=edit 或 tab=add 的链接）
        var $autoOpen = $('#ge-auto-open-modal');
        if ($autoOpen.length) {
            var action = $autoOpen.data('action');
            if (action === 'edit') {
                var autoProduct = $autoOpen.data('product');
                if (typeof autoProduct === 'string') {
                    try { autoProduct = JSON.parse(autoProduct); } catch (e) {}
                }
                if (autoProduct) {
                    $('#goods-product-modal-title').text('编辑产品 (ID: ' + autoProduct.id + ')');
                    $('#goods-product-modal-submit').text('更新产品');
                    populateForm(autoProduct);
                    openModal();
                }
            } else if (action === 'add') {
                resetForm();
                openModal();
            }
        }
    }

})(jQuery);
