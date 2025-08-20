/**
 * 好物页面插件管理界面脚本
 */
(function($) {
    'use strict';
    
    // 当文档加载完成后执行
    $(document).ready(function() {
        // 初始化媒体上传器
        initMediaUploader();
        
        // 初始化表单验证
        initFormValidation();
    });
    
    /**
     * 初始化媒体上传器
     */
    function initMediaUploader() {
        var mediaUploader;
        
        $('#upload_image_button').on('click', function(e) {
            e.preventDefault();
            
            // 如果上传器已经存在，则打开它
            if (mediaUploader) {
                mediaUploader.open();
                return;
            }
            
            // 创建媒体上传器
            mediaUploader = wp.media({
                title: '选择产品图片',
                button: {
                    text: '使用此图片'
                },
                multiple: false // 只允许选择一张图片
            });
            
            // 当选择了图片时
            mediaUploader.on('select', function() {
                var attachment = mediaUploader.state().get('selection').first().toJSON();
                
                // 更新隐藏字段的值
                $('#product_image_url').val(attachment.url);
                
                // 更新预览图片
                var $preview = $('.product-image-preview');
                if ($preview.find('img').length === 0) {
                    $preview.html('<img src="' + attachment.url + '" alt="产品图片预览" style="max-width: 200px; max-height: 200px; margin-bottom: 10px; display: block;">');
                } else {
                    $preview.find('img').attr('src', attachment.url);
                }
            });
            
            // 打开媒体上传器
            mediaUploader.open();
        });
        
        // 处理直接上传的图片预览
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
                    
                    // 清空隐藏字段，因为我们将使用直接上传的图片
                    $('#product_image_url').val('');
                };
                reader.readAsDataURL(file);
            }
        });
    }
    
    /**
     * 初始化表单验证
     */
    function initFormValidation() {
        $('form').on('submit', function(e) {
            var $form = $(this);
            var $nameField = $('#product_name');
            var $descriptionField = $('#product_description');
            var $imageUrlField = $('#product_image_url');
            var $imageField = $('#product_image');
            var isValid = true;
            
            // 清除之前的错误提示
            $('.form-error').remove();
            
            // 验证产品名称
            if (!$nameField.val().trim()) {
                isValid = false;
                $nameField.after('<p class="form-error" style="color: #dc3232;">请输入产品名称</p>');
            }
            
            // 验证产品描述
            if (!$descriptionField.val().trim()) {
                isValid = false;
                $descriptionField.after('<p class="form-error" style="color: #dc3232;">请输入产品描述</p>');
            }
            
            // 验证产品图片
            if (!$imageUrlField.val() && !$imageField[0].files.length) {
                isValid = false;
                $imageField.after('<p class="form-error" style="color: #dc3232;">请上传产品图片或从媒体库选择图片</p>');
            }
            
            return isValid;
        });
    }
    
})(jQuery);
