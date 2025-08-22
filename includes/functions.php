<?php
/**
 * 辅助函数
 *
 * @package 好物页面插件
 */

// 如果直接访问此文件，则中止
if (!defined('WPINC')) {
    die;
}

/**
 * 获取单个产品信息
 *
 * @param int $product_id 产品ID
 * @return array|null 产品信息或null
 */
function goods_exhibition_get_product($product_id) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'goods_exhibition';

    return $wpdb->get_row(
        $wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $product_id),
        ARRAY_A
    );
}

/**
 * 获取所有产品
 *
 * @param int $limit 限制数量，-1表示不限制
 * @return array 产品列表
 */
function goods_exhibition_get_products($limit = -1) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'goods_exhibition';

    $limit_clause = $limit > 0 ? "LIMIT $limit" : "";

    return $wpdb->get_results(
        "SELECT * FROM $table_name ORDER BY id DESC $limit_clause",
        ARRAY_A
    );
}

/**
 * 获取标记为海报且有海报图片的产品
 *
 * @return array
 */
function goods_exhibition_get_poster_products() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'goods_exhibition';

    return $wpdb->get_results(
        "SELECT * FROM $table_name WHERE is_poster = 1 AND poster_image_url != '' ORDER BY id DESC",
        ARRAY_A
    );
}

/**
 * 检查产品是否是新产品（一个月内添加的）
 * 【此函数维持，可做为备用，不影响NEW标签显示】
 *
 * @param string $created_at 创建日期
 * @return bool 是否是新产品
 */
function goods_exhibition_is_new_product($created_at) {
    $current_time = current_time('timestamp');
    $one_month_ago = strtotime('-1 month', $current_time);

    return strtotime($created_at) > $one_month_ago;
}

/**
 * 生成产品图片的HTML（不输出NEW标签，避免样式冲突）
 *
 * @param array $product 产品信息
 * @return string HTML代码
 */
function goods_exhibition_get_product_image_html($product) {
    $html = '<div class="goods-exhibition-image-container">';
    $html .= '<img src="' . esc_url($product['image_url']) . '" alt="' . esc_attr($product['name']) . '" class="goods-exhibition-image">';
    $html .= '</div>';
    return $html;
}

/**
 * 检查插件上传目录是否存在并可写
 *
 * @return bool 是否可写
 */
function goods_exhibition_check_upload_dir() {
    $upload_dir = GOODS_EXHIBITION_UPLOAD_DIR;

    if (!file_exists($upload_dir)) {
        return wp_mkdir_p($upload_dir);
    }

    return is_writable($upload_dir);
}

/**
 * 安全地删除文件
 *
 * @param string $file_path 文件路径
 * @return bool 是否成功删除
 */
function goods_exhibition_safe_delete_file($file_path) {
    // 确保文件在插件上传目录中
    $upload_dir = GOODS_EXHIBITION_UPLOAD_DIR;

    if (strpos($file_path, $upload_dir) !== 0) {
        return false;
    }

    if (file_exists($file_path)) {
        return unlink($file_path);
    }

    return false;
}

