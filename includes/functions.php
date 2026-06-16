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
function goods_exhibition_get_product($product_id)
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'goods_exhibition';

    $result = $wpdb->get_row(
        $wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", intval($product_id)),
        ARRAY_A
    );

    // 记录数据库错误
    if ($wpdb->last_error) {
        error_log('Goods Exhibition Plugin Error: ' . $wpdb->last_error);
    }

    return $result;
}

/**
 * 获取所有产品
 *
 * @param int $limit 限制数量，-1表示不限制
 * @return array 产品列表
 */
function goods_exhibition_get_products($limit = -1, $force_refresh = false)
{
    global $wpdb;

    // 确保 $limit 是整数
    $limit = intval($limit);
    $cache_key = 'goods_exhibition_all_products_' . $limit;

    if (!$force_refresh) {
        $cached = get_transient($cache_key);
        if (false !== $cached) {
            return $cached;
        }
    }

    $table_name = $wpdb->prefix . 'goods_exhibition';

    // 使用prepare防止SQL注入
    if ($limit > 0) {
        $results = $wpdb->get_results(
            $wpdb->prepare("SELECT * FROM $table_name ORDER BY id DESC LIMIT %d", $limit),
            ARRAY_A
        );
    } else {
        $results = $wpdb->get_results(
            "SELECT * FROM $table_name ORDER BY id DESC",
            ARRAY_A
        );
    }

    set_transient($cache_key, $results, 12 * HOUR_IN_SECONDS);
    return $results;
}

/**
 * 获取前端展示的所有产品（按类别排序）
 *
 * @param int $limit 限制数量
 * @return array 产品列表
 */
function goods_exhibition_get_frontend_products($limit = -1)
{
    // 确保 $limit 是整数
    $limit = intval($limit);
    $cache_key = 'goods_exhibition_frontend_products_' . $limit;
    $cached = get_transient($cache_key);
    if (false !== $cached) {
        return $cached;
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'goods_exhibition';

    // 使用prepare防止SQL注入
    if ($limit > 0) {
        $results = $wpdb->get_results(
            $wpdb->prepare("SELECT * FROM {$table_name} ORDER BY category ASC, created_at DESC LIMIT %d", $limit),
            ARRAY_A
        );
    } else {
        $results = $wpdb->get_results(
            "SELECT * FROM {$table_name} ORDER BY category ASC, created_at DESC",
            ARRAY_A
        );
    }

    set_transient($cache_key, $results, 12 * HOUR_IN_SECONDS);
    return $results;
}

/**
 * 获取标记为海报且有海报图片的产品
 *
 * @return array
 */
function goods_exhibition_get_poster_products()
{
    $cache_key = 'goods_exhibition_poster_products';
    $cached = get_transient($cache_key);
    if (false !== $cached) {
        return $cached;
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'goods_exhibition';

    $results = $wpdb->get_results(
        "SELECT * FROM $table_name WHERE is_poster = 1 AND poster_image_url != '' ORDER BY id DESC",
        ARRAY_A
    );

    set_transient($cache_key, $results, 12 * HOUR_IN_SECONDS);
    return $results;
}

/**
 * 检查产品是否是新产品（一个月内添加的）
 * 【此函数维持，可做为备用，不影响NEW标签显示】
 *
 * @param string $created_at 创建日期
 * @return bool 是否是新产品
 */
function goods_exhibition_is_new_product($created_at)
{
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
function goods_exhibition_get_product_image_html($product)
{
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
function goods_exhibition_check_upload_dir()
{
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
function goods_exhibition_safe_delete_file($file_path)
{
    // 确保文件在插件上传目录中
    $upload_dir = realpath(GOODS_EXHIBITION_UPLOAD_DIR);
    $real_file_path = realpath($file_path);

    // 检查realpath是否成功（文件是否存在）
    if ($real_file_path === false) {
        return false;
    }

    // 使用realpath后的路径进行比较，防止路径遍历攻击
    if (strpos($real_file_path, $upload_dir) !== 0) {
        return false;
    }

    if (file_exists($real_file_path) && is_file($real_file_path)) {
        return unlink($real_file_path);
    }

    return false;
}

/**
 * 清除产品缓存
 */
function goods_exhibition_flush_cache()
{
    global $wpdb;

    // 清除所有相关的transient缓存
    // 使用数据库查询清除所有以特定前缀开头的transient
    $wpdb->query(
        $wpdb->prepare(
            "DELETE FROM {$wpdb->options}
            WHERE option_name LIKE %s
            OR option_name LIKE %s",
            $wpdb->esc_like('_transient_goods_exhibition_') . '%',
            $wpdb->esc_like('_transient_timeout_goods_exhibition_') . '%'
        )
    );

    // 如果使用对象缓存，也清除对象缓存
    if (function_exists('wp_cache_flush_group')) {
        wp_cache_flush_group('goods_exhibition');
    }
}

