/**
 * 好物页面插件管理界面脚本
 */
(function($) {
    'use strict';
    
    $(document).ready(function() {
        initMediaUploader();
        initFormValidation();
        initBulkActions();
        initBulkMove();
        initDragSort();
        initCategoryEdit();
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
     * 初始化分类编辑弹窗
     */
    function initCategoryEdit() {
        var $modal = $('#goods-cat-edit-modal');
        if (!$modal.length) return;

        $('.goods-cat-edit-link').on('click', function(e) {
            e.preventDefault();
            var id = $(this).data('id');
            var name = $(this).data('name');
            var sort = $(this).data('sort');

            $('#edit_cat_id').val(id);
            $('#edit_category_name').val(name);
            $('#edit_category_sort').val(sort);
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
