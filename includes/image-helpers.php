<?php
/**
 * 辅助函数 - 图片处理
 *
 * 这个文件包含了图片上传和处理的统一函数
 * 可以在未来版本中替换admin.php中的重复代码
 *
 * @package 好物页面插件
 */

if (!defined('WPINC')) {
    die;
}

/**
 * 统一的图片上传处理函数
 *
 * @param string $file_key $_FILES数组的键名
 * @param array $allowed_extensions 允许的文件扩展名
 * @param array $allowed_mime_types 允许的MIME类型
 * @return array 包含 'success', 'url', 'error' 的关联数组
 */
function goods_exhibition_handle_image_upload($file_key, $allowed_extensions = null, $allowed_mime_types = null)
{
    // 默认允许的文件类型
    if ($allowed_extensions === null) {
        $allowed_extensions = array('.jpg', '.jpeg', '.png', '.gif', '.webp');
    }

    if ($allowed_mime_types === null) {
        $allowed_mime_types = array('image/jpeg', 'image/png', 'image/gif', 'image/webp');
    }

    // 检查文件是否上传
    if (!isset($_FILES[$file_key]) || $_FILES[$file_key]['error'] === UPLOAD_ERR_NO_FILE) {
        return array(
            'success' => false,
            'url' => '',
            'error' => ''
        );
    }

    // 检查上传错误
    if ($_FILES[$file_key]['error'] !== UPLOAD_ERR_OK) {
        return array(
            'success' => false,
            'url' => '',
            'error' => '文件上传时发生错误，错误代码：' . $_FILES[$file_key]['error']
        );
    }

    // 检查上传目录
    if (!goods_exhibition_check_upload_dir()) {
        return array(
            'success' => false,
            'url' => '',
            'error' => '上传目录不可写或不存在，请检查插件文件夹权限。'
        );
    }

    require_once(ABSPATH . 'wp-admin/includes/file.php');
    require_once(ABSPATH . 'wp-admin/includes/image.php');

    $upload_dir = GOODS_EXHIBITION_UPLOAD_DIR;
    $file_info = pathinfo($_FILES[$file_key]['name']);
    $file_extension = !empty($file_info['extension']) ? '.' . strtolower($file_info['extension']) : '';

    // 验证文件扩展名
    if (!in_array($file_extension, $allowed_extensions)) {
        return array(
            'success' => false,
            'url' => '',
            'error' => '只允许上传 ' . implode(', ', $allowed_extensions) . ' 格式的图片。'
        );
    }

    // 验证MIME类型
    if (function_exists('finfo_open')) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($finfo, $_FILES[$file_key]['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mime_type, $allowed_mime_types)) {
            return array(
                'success' => false,
                'url' => '',
                'error' => '上传的文件不是有效的图片格式。'
            );
        }
    }

    // 生成唯一文件名
    $file_name = wp_unique_filename($upload_dir, sanitize_file_name($file_info['filename']) . $file_extension);
    $upload_path = $upload_dir . $file_name;

    // 移动上传的文件
    if (move_uploaded_file($_FILES[$file_key]['tmp_name'], $upload_path)) {
        return array(
            'success' => true,
            'url' => GOODS_EXHIBITION_URL . 'uploads/' . $file_name,
            'error' => ''
        );
    } else {
        return array(
            'success' => false,
            'url' => '',
            'error' => '上传图片失败，请再试一次。'
        );
    }
}

/**
 * 验证产品数据
 *
 * @param array $data 产品数据
 * @return array 错误信息数组，空数组表示验证通过
 */
function goods_exhibition_validate_product_data($data)
{
    $errors = array();

    // 验证产品名称
    if (empty($data['name']) || !is_string($data['name'])) {
        $errors[] = '产品名称不能为空';
    } elseif (strlen($data['name']) > 255) {
        $errors[] = '产品名称不能超过255个字符';
    }

    // 验证产品描述
    if (empty($data['description']) || !is_string($data['description'])) {
        $errors[] = '产品描述不能为空';
    }

    // 验证产品图片
    if (empty($data['image_url']) || !filter_var($data['image_url'], FILTER_VALIDATE_URL)) {
        $errors[] = '请提供有效的产品图片URL';
    }

    // 验证产品URL（如果提供）
    if (!empty($data['url']) && !filter_var($data['url'], FILTER_VALIDATE_URL)) {
        $errors[] = '产品链接格式不正确';
    }

    // 验证价格（如果提供）
    if (!empty($data['price']) && strlen($data['price']) > 50) {
        $errors[] = '产品价格不能超过50个字符';
    }

    // 验证类别（如果提供）
    if (!empty($data['category']) && strlen($data['category']) > 255) {
        $errors[] = '产品类别不能超过255个字符';
    }

    // 验证is_new标志
    if (isset($data['is_new']) && !in_array($data['is_new'], array(0, 1, '0', '1'), true)) {
        $errors[] = '无效的"新产品"标记值';
    }

    // 验证is_poster标志
    if (isset($data['is_poster']) && !in_array($data['is_poster'], array(0, 1, '0', '1'), true)) {
        $errors[] = '无效的"海报"标记值';
    }

    // 如果是海报，必须有海报图片
    if (!empty($data['is_poster']) && $data['is_poster'] == 1) {
        if (empty($data['poster_image_url']) || !filter_var($data['poster_image_url'], FILTER_VALIDATE_URL)) {
            $errors[] = '已标记为海报，请提供有效的海报图片URL';
        }
    }

    return $errors;
}

/**
 * 日志记录函数
 *
 * @param string $message 日志消息
 * @param string $level 日志级别 (info, warning, error)
 */
function goods_exhibition_log($message, $level = 'info')
{
    // 只在开启调试模式时记录日志
    if (defined('GOODS_EXHIBITION_DEBUG') && GOODS_EXHIBITION_DEBUG) {
        $timestamp = current_time('Y-m-d H:i:s');
        $log_message = sprintf('[%s] [%s] %s', $timestamp, strtoupper($level), $message);
        error_log($log_message);
    }
}

/**
 * 获取文件大小的人类可读格式
 *
 * @param int $bytes 字节数
 * @return string 格式化的文件大小
 */
function goods_exhibition_format_bytes($bytes)
{
    $units = array('B', 'KB', 'MB', 'GB', 'TB');
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= (1 << (10 * $pow));
    return round($bytes, 2) . ' ' . $units[$pow];
}

/**
 * 清理旧的未使用图片文件
 * 可以在后台任务中定期调用
 *
 * @return int 删除的文件数量
 */
function goods_exhibition_cleanup_unused_images()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'goods_exhibition';
    $upload_dir = GOODS_EXHIBITION_UPLOAD_DIR;

    // 获取所有数据库中使用的图片URL
    $used_images = array();
    $products = $wpdb->get_results("SELECT image_url, poster_image_url FROM $table_name", ARRAY_A);

    foreach ($products as $product) {
        if (!empty($product['image_url'])) {
            $used_images[] = basename($product['image_url']);
        }
        if (!empty($product['poster_image_url'])) {
            $used_images[] = basename($product['poster_image_url']);
        }
    }

    // 扫描上传目录
    $deleted_count = 0;
    if (is_dir($upload_dir)) {
        $files = scandir($upload_dir);
        foreach ($files as $file) {
            if ($file === '.' || $file === '..' || $file === '.htaccess') {
                continue;
            }

            $file_path = $upload_dir . $file;
            if (is_file($file_path) && !in_array($file, $used_images)) {
                // 只删除超过30天未使用的文件（额外的安全措施）
                if (time() - filemtime($file_path) > 30 * DAY_IN_SECONDS) {
                    if (unlink($file_path)) {
                        $deleted_count++;
                        goods_exhibition_log("已删除未使用的图片: $file", 'info');
                    }
                }
            }
        }
    }

    return $deleted_count;
}
