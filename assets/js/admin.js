/**
 * 好物页面插件管理界面脚本
 */
(function($) {
    'use strict';
    
    $(document).ready(function() {
        initMediaUploader();
        initImageDelete();
        initFormValidation();
        initBulkActions();
        initBulkMove();
        initDragSort();
        initPosterSort();
        initCategoryEdit();
        initCategorySort();
    });
    
    /**
     * 初始化媒体上传器
     */
    function initMediaUploader() {
        var mediaUploader;
        var mediaUploaderPoster;
        
        $('#upload_image_button').on('click', function(e) {
            e.preventDefault();
            
            if (mediaUploader) {
                mediaUploader.open();
                return;
            }
            
            mediaUploader = wp.media({
                title: '选择产品图片',
                button: { text: '使用此图片' },
                library: { type: 'image' },
                multiple: false
            });
            
            mediaUploader.on('select', function() {
                var attachment = mediaUploader.state().get('selection').first().toJSON();
                $('#product_image_url').val(attachment.url);
                
                var $preview = $('.product-image-preview');
                if ($preview.find('img').length === 0) {
                    $preview.html('<img src="' + attachment.url + '" alt="产品图片预览" style="max-width: 200px; max-height: 200px; margin-bottom: 10px; display: block;">');
                } else {
                    $preview.find('img').attr('src', attachment.url);
                }
                // 重新选择图片后恢复删除按钮，并移除"已移除"提示
                $preview.find('.goods-image-removed-hint').remove();
                $('.goods-image-delete-btn[data-target="#product_image_url"]').show();
            });
            
            mediaUploader.open();
        });
        
        $('#product_image').on('change', function() {
            var file = this.files[0];
            if (file) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    var $preview = $('.product-image-preview');
                    if ($preview.find('img').length === 0) {
                        $preview.html('<img src="' + e.target.result + '" alt="产品图片预览" style="max-width: 200px; max-height: 200px; margin-bottom: 10px; display: block;">');
                    } else {
                        $preview.find('img').attr('src', e.target.result);
                    }
                    $('#product_image_url').val('');
                };
                reader.readAsDataURL(file);
            }
        });

        $('#upload_poster_image_button').on('click', function(e) {
            e.preventDefault();

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
                $('#poster_image_url').val(attachment.url);

                var $preview = $('.poster-image-preview');
                if ($preview.find('img').length === 0) {
                    $preview.html('<img src="' + attachment.url + '" alt="海报图片预览" style="max-width: 200px; max-height: 200px; margin-bottom: 10px; display: block;">');
                } else {
                    $preview.find('img').attr('src', attachment.url);
                }
                // 重新选择图片后恢复删除按钮，并移除"已移除"提示
                $preview.find('.goods-image-removed-hint').remove();
                $('.goods-image-delete-btn[data-target="#poster_image_url"]').show();
            });

            mediaUploaderPoster.open();
        });

        $('#poster_image').on('change', function() {
            var file = this.files[0];
            if (file) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    var $preview = $('.poster-image-preview');
                    if ($preview.find('img').length === 0) {
                        $preview.html('<img src="' + e.target.result + '" alt="海报图片预览" style="max-width: 200px; max-height: 200px; margin-bottom: 10px; display: block;">');
                    } else {
                        $preview.find('img').attr('src', e.target.result);
                    }
                    $('#poster_image_url').val('');
                };
                reader.readAsDataURL(file);
            }
        });
    }
    
    /**
     * 初始化图片删除（产品图片 / 海报图片）
     *
     * 点击删除按钮先弹出二次确认弹窗，明确说明删除影响（可能同时删除媒体库文件）
     * 以及"必须保存才生效"；确认后仅清空表单中的图片，真正删除在保存时由后端执行。
     */
    function initImageDelete() {
        var $modal = $('#goods-image-delete-modal');
        if (!$modal.length) return;

        var $pendingBtn = null;

        $('.goods-image-delete-btn').on('click', function(e) {
            e.preventDefault();
            $pendingBtn = $(this);

            var label = $pendingBtn.data('label') || '图片';
            var isRequired = String($pendingBtn.data('required')) === '1';

            $('#goods-image-delete-title').text('删除' + label);

            var html = '<p>确定要删除该' + label + '吗？请先阅读以下影响说明。</p>';
            html += '<p><strong>删除带来的影响：</strong></p>';
            html += '<ul class="goods-image-delete-impact">';
            html += '<li>保存后，本产品将不再显示、不再使用该图片。</li>';
            html += '<li>若该图片未被网站其它内容引用（其它产品、文章、页面、小工具等），将<strong>同时从 WordPress 媒体库中永久删除</strong>（含所有尺寸副本），删除后<strong>不可恢复</strong>。</li>';
            html += '<li>若该图片仍被其它内容引用，则仅解除本产品的引用，媒体库中的图片会保留。</li>';
            html += '</ul>';
            if (isRequired) {
                html += '<p class="goods-image-delete-warn"><strong>注意：</strong>' + label + '为必填项，删除后请及时选择新的图片，否则将无法保存。</p>';
            }
            html += '<p class="goods-image-delete-note"><strong>保存后才会真正生效：</strong>此删除仅在点击「更新产品」保存后才会执行；若未保存直接离开本页面，则不会删除任何内容。</p>';

            $('#goods-image-delete-body').html(html);
            $modal.show();
        });

        $modal.find('.goods-image-modal-close, .goods-image-modal-overlay').on('click', function() {
            $modal.hide();
            $pendingBtn = null;
        });

        $modal.find('.goods-image-delete-confirm').on('click', function() {
            if (!$pendingBtn) {
                $modal.hide();
                return;
            }

            var $btn = $pendingBtn;
            var $preview = $($btn.data('preview'));

            // 清空隐藏字段中的图片 URL，并移除预览图
            $($btn.data('target')).val('');
            $preview.find('img').remove();

            // 清空可能存在的本地文件选择（兼容直接上传路径）
            var fileSelector = $btn.data('file');
            if (fileSelector) {
                $(fileSelector).val('');
            }

            // 隐藏删除按钮并给出"待保存"提示
            $btn.hide();
            $preview.find('.goods-image-removed-hint').remove();
            $preview.append('<p class="goods-image-removed-hint">图片已移除，点击「更新产品」保存后生效。</p>');

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
            $('#edit_category_name').val(name);
            $modal.show();
        });

        $('.goods-cat-modal-close, .goods-cat-modal-overlay').on('click', function() {
            $modal.hide();
        });

        $(document).on('keydown', function(e) {
            if (e.key === 'Escape' && $modal.is(':visible')) {
                $modal.hide();
            }
        });
    }

    /**
     * 初始化分类拖拽排序
     *
     * 拖动分类列表的行手柄即可调整顺序，松开自动保存；
     * 排序值即位置，前端分类展示顺序随之同步。
     */
    function initCategorySort() {
        var $tbody = $('#goods-category-sort-body');
        if (!$tbody.length) return;

        $tbody.sortable({
            handle: '.goods-drag-handle',
            items: '> tr',
            axis: 'y',
            tolerance: 'pointer',
            forcePlaceholderSize: true,
            opacity: 0.7,
            cursor: 'grabbing',
            placeholder: 'goods-sort-placeholder',
            update: function() {
                var order = [];
                $tbody.find('tr').each(function(index) {
                    order.push($(this).data('id'));
                    $(this).find('.goods-cat-sort-order').text(index + 1);
                });

                $.post(goodsExhibitionAdmin.ajaxUrl, {
                    action: 'goods_exhibition_save_category_sort',
                    nonce: goodsExhibitionAdmin.nonce,
                    order: order
                }, function(response) {
                    if (response && response.success) {
                        var $notice = $('<div class="notice notice-success goods-sort-notice"><p>分类顺序已保存</p></div>');
                        $tbody.closest('table').before($notice);
                        setTimeout(function() { $notice.fadeOut(function() { $notice.remove(); }); }, 2000);
                    } else {
                        alert('保存失败：' + (response && response.data ? response.data : '未知错误'));
                    }
                }).fail(function() {
                    alert('保存失败：网络错误，请重试');
                });
            }
        });
    }

    /**
     * 初始化拖动排序（仅当表格有 data-sortable 属性时）
     */
    function initDragSort() {
        var $table = $('.goods-exhibition-table[data-sortable="1"]');
        if (!$table.length) return;

        var $tbody = $('#goods-sortable-body');

        $tbody.sortable({
            handle: '.goods-drag-handle',
            axis: 'y',
            opacity: 0.7,
            cursor: 'grabbing',
            placeholder: 'goods-sort-placeholder',
            update: function() {
                var order = [];
                $tbody.find('tr').each(function() {
                    order.push($(this).data('id'));
                });

                // AJAX 保存排序
                $.post(goodsExhibitionAdmin.ajaxUrl, {
                    action: 'goods_exhibition_save_sort',
                    nonce: goodsExhibitionAdmin.nonce,
                    order: order
                }, function(response) {
                    if (response.success) {
                        // 短暂显示成功提示
                        var $notice = $('<div class="notice notice-success goods-sort-notice"><p>排序已保存</p></div>');
                        $table.before($notice);
                        setTimeout(function() { $notice.fadeOut(function() { $notice.remove(); }); }, 2000);
                    }
                });
            }
        });
    }

    /**
     * 初始化海报排序（海报排序 Tab）
     *
     * 与产品列表的分类内排序（sort_order）独立，保存至独立选项，
     * 因此不会影响原有产品排序功能。
     */
    function initPosterSort() {
        var $list = $('#goods-poster-sort-list');
        if (!$list.length) return;

        var $status = $('.goods-poster-sort-status');
        var displayLimit = parseInt($('.goods-poster-sort-hint').data('limit'), 10) || 0;

        function renumber() {
            $list.find('.goods-poster-sort-item').each(function(index) {
                $(this).find('.goods-poster-sort-order').text(index + 1);
                // 同步"超出展示范围"标记
                if (displayLimit > 0) {
                    var $badge = $(this).find('.goods-poster-sort-badge');
                    if (index >= displayLimit && !$badge.length) {
                        $(this).append('<span class="goods-poster-sort-badge">超出展示范围</span>');
                    } else if (index < displayLimit && $badge.length) {
                        $badge.remove();
                    }
                }
            });
        }

        $list.sortable({
            // 手柄与缩略图均可抓取，拖拽更直观
            handle: '.goods-drag-handle, .goods-poster-sort-thumb',
            items: '> .goods-poster-sort-item',
            axis: 'y',
            tolerance: 'pointer',
            forcePlaceholderSize: true,
            opacity: 0.7,
            cursor: 'grabbing',
            placeholder: 'goods-sort-placeholder',
            update: function() {
                var order = [];
                $list.find('.goods-poster-sort-item').each(function() {
                    order.push($(this).data('id'));
                });
                renumber();

                $status.text('保存中...').css('color', '#646970');

                $.post(goodsExhibitionAdmin.ajaxUrl, {
                    action: 'goods_exhibition_save_poster_sort',
                    nonce: goodsExhibitionAdmin.nonce,
                    order: order
                }, function(response) {
                    if (response && response.success) {
                        $status.text('海报顺序已保存').css('color', '#2271b1');
                        setTimeout(function() { $status.text(''); }, 2000);
                    } else {
                        $status.text('保存失败：' + (response && response.data ? response.data : '未知错误')).css('color', '#dc3232');
                    }
                }).fail(function() {
                    $status.text('保存失败：网络错误，请重试').css('color', '#dc3232');
                });
            }
        });
    }

    /**
     * 初始化批量移动功能
     */
    function initBulkMove() {
        var $moveSelect = $('#goods-bulk-move-category');
        var $moveBtn = $('#goods-bulk-move-btn');

        $moveBtn.on('click', function() {
            var targetCategory = $moveSelect.val();
            if (!targetCategory) {
                alert('请选择目标分类');
                return;
            }

            var selectedIds = [];
            $('.goods-cb-item:checked').each(function() {
                selectedIds.push($(this).val());
            });

            if (selectedIds.length === 0) {
                alert('请先选择要移动的产品');
                return;
            }

            if (!confirm('确定要将选中的 ' + selectedIds.length + ' 个产品移动到「' + targetCategory + '」吗？')) {
                return;
            }

            $.post(goodsExhibitionAdmin.ajaxUrl, {
                action: 'goods_exhibition_bulk_move',
                nonce: goodsExhibitionAdmin.nonce,
                product_ids: selectedIds,
                target_category: targetCategory
            }, function(response) {
                if (response.success) {
                    alert(response.data.message);
                    location.reload();
                } else {
                    alert('操作失败：' + response.data);
                }
            });
        });
    }

    /**
     * 初始化批量操作
     */
    function initBulkActions() {
        var $selectAll = $('#goods-cb-select-all');
        var $items = $('.goods-cb-item');
        var $bulkBtn = $('.goods-exhibition-bulk-delete-btn');
        var $moveSelect = $('#goods-bulk-move-category');
        var $moveBtn = $('#goods-bulk-move-btn');
        var $countLabel = $('.goods-exhibition-selected-count');

        function updateBulkState() {
            var checked = $items.filter(':checked').length;
            $bulkBtn.prop('disabled', checked === 0);
            $moveSelect.prop('disabled', checked === 0);
            $moveBtn.prop('disabled', checked === 0);

            if (checked > 0) {
                $countLabel.text('已选 ' + checked + ' 项');
            } else {
                $countLabel.text('');
            }

            if (checked === $items.length && $items.length > 0) {
                $selectAll.prop('checked', true).prop('indeterminate', false);
            } else if (checked > 0) {
                $selectAll.prop('checked', false).prop('indeterminate', true);
            } else {
                $selectAll.prop('checked', false).prop('indeterminate', false);
            }
        }

        $selectAll.on('change', function() {
            $items.prop('checked', $(this).is(':checked'));
            updateBulkState();
        });

        $items.on('change', updateBulkState);
    }

    /**
     * 初始化表单验证
     */
    function initFormValidation() {
        // 仅绑定产品表单：此前 $('form') 会匹配搜索/批量/分类等所有表单，
        // 在非产品表单页面上 $('#product_name').val() 为 undefined 会导致 JS 报错
        $('#goods-exhibition-product-form').on('submit', function(e) {
            var $nameField = $('#product_name');
            var $descriptionField = $('#product_description');
            var $categoryField = $('#product_category');
            var $imageUrlField = $('#product_image_url');
            var $imageField = $('#product_image');
            var $posterImageUrlField = $('#poster_image_url');
            var $posterImageField = $('#poster_image');
            var $isPosterCheckbox = $('#product_is_poster');
            var errors = [];
            
            // 清除之前的错误提示
            $('.form-error').remove();
            
            // 验证产品名称
            if (!$nameField.val().trim()) {
                errors.push('产品名称');
                $nameField.after('<p class="form-error" style="color: #dc3232; margin: 5px 0;">请输入产品名称</p>');
            }
            
            // 验证产品描述
            if (!$descriptionField.val().trim()) {
                errors.push('产品描述');
                $descriptionField.after('<p class="form-error" style="color: #dc3232; margin: 5px 0;">请输入产品描述</p>');
            }

            // 验证产品类别
            if (!$categoryField.val().trim()) {
                errors.push('产品类别');
                $categoryField.after('<p class="form-error" style="color: #dc3232; margin: 5px 0;">请输入产品类别</p>');
            }
            
            // 验证产品图片
            if (!$imageUrlField.val() && (!$imageField.length || !$imageField[0].files.length)) {
                errors.push('产品图片');
                $('#upload_image_button').after('<p class="form-error" style="color: #dc3232; margin: 5px 0;">请上传产品图片或从媒体库选择图片</p>');
            }

            // 如果勾选了标记为海报，必须有海报图片
            if ($isPosterCheckbox.is(':checked')) {
                if (!$posterImageUrlField.val() && (!$posterImageField.length || !$posterImageField[0].files.length)) {
                    errors.push('海报图片');
                    $('#upload_poster_image_button').after('<p class="form-error" style="color: #dc3232; margin: 5px 0;">请上传海报图片或从媒体库选择海报图片</p>');
                }
            }

            if (errors.length > 0) {
                alert('以下必填项未填写：\n\n' + errors.join('、') + '\n\n请补充完整后再提交。');
                return false;
            }
            
            return true;
        });
    }
    
})(jQuery);
