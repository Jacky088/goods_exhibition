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

    // 根据图片用途确定最大边长：海报图 1920，产品图 720（README 推荐尺寸）
    $max_dimension = ($file_key === 'poster_image') ? 1920 : 720;

    // 移动上传的文件
    if (move_uploaded_file($_FILES[$file_key]['tmp_name'], $upload_path)) {
        // 超大图片等比缩小并压缩，减少前端加载体积（文件名与 URL 不变）
        goods_exhibition_optimize_image($upload_path, $max_dimension);
        return array(
            'success' => true,
            'url' => GOODS_EXHIBITION_UPLOAD_URL . $file_name,
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
 * 优化上传的图片文件
 *
 * 超过最大边长的图片等比缩小后以质量 82 重新保存，避免用户直接上传
 * 数 MB 的相机原图导致前端加载缓慢。
 * 设计约束（保证对显示零影响）：
 * - 仅缩小不放大、不裁切：页面上的裁切铺满仍由 CSS 完成，视觉不变
 * - 尺寸未超标的图片原样保留，避免二次压缩损失
 * - GIF 跳过（重新编码会丢失动画帧）
 * - 图像库不可用或处理失败时保留原文件，不影响上传功能
 *
 * @param string $file_path 图片绝对路径
 * @param int $max_dimension 最大边长（像素）
 */
function goods_exhibition_optimize_image($file_path, $max_dimension)
{
    // GIF 可能为动画，重新编码会丢失动画帧，跳过
    if (strtolower(pathinfo($file_path, PATHINFO_EXTENSION)) === 'gif') {
        return;
    }

    $editor = wp_get_image_editor($file_path);
    if (is_wp_error($editor)) {
        return; // GD/Imagick 不可用等情况，保留原图
    }

    $size = $editor->get_size();
    if (empty($size) || ($size['width'] <= $max_dimension && $size['height'] <= $max_dimension)) {
        return; // 尺寸未超标，保留原图
    }

    // 等比缩小到最大边长内（第三个参数 false 表示不裁切）
    $resized = $editor->resize($max_dimension, $max_dimension, false);
    if (is_wp_error($resized)) {
        return;
    }

    $editor->set_quality(82);
    $editor->save($file_path); // 覆盖保存到原路径，文件名与 URL 保持不变
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
