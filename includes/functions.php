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
    $cache_key = 'goods_exhibition_all_products_' . goods_exhibition_cache_version() . '_' . $limit;

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
    $cache_key = 'goods_exhibition_frontend_products_' . goods_exhibition_cache_version() . '_' . $limit;
    $cached = get_transient($cache_key);
    if (false !== $cached) {
        return $cached;
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'goods_exhibition';

    // 使用prepare防止SQL注入
    if ($limit > 0) {
        $results = $wpdb->get_results(
            $wpdb->prepare("SELECT * FROM {$table_name} ORDER BY category ASC, sort_order ASC, created_at DESC LIMIT %d", $limit),
            ARRAY_A
        );
    } else {
        $results = $wpdb->get_results(
            "SELECT * FROM {$table_name} ORDER BY category ASC, sort_order ASC, created_at DESC",
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
    $cache_key = 'goods_exhibition_poster_products_' . goods_exhibition_cache_version();
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
    $current_time = current_datetime()->getTimestamp();
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
        if (!wp_mkdir_p($upload_dir)) {
            return false;
        }
    }

    // 确保 .htaccess 保护存在（防止上传文件被当作脚本执行）
    $htaccess_file = $upload_dir . '.htaccess';
    if (!file_exists($htaccess_file)) {
        $content = "Options -Indexes\n";
        $content .= "<FilesMatch \"\\.(?i:php|phtml|php3|php4|php5|phar|cgi|pl|py|asp|aspx|jsp|sh)$\">\n";
        $content .= "    Require all denied\n";
        $content .= "</FilesMatch>\n";
        @file_put_contents($htaccess_file, $content);
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
 * 获取当前缓存版本号
 *
 * 通过递增版本号即可让所有带版本前缀的 transient 整体失效，
 * 避免逐个删除缓存键带来的性能开销，也解决了 limit > 100 时缓存无法清除的问题。
 *
 * @return int 当前缓存版本
 */
function goods_exhibition_cache_version()
{
    $version = (int) get_option('goods_exhibition_cache_version', 0);
    if ($version < 1) {
        $version = 1;
        update_option('goods_exhibition_cache_version', $version);
    }
    return $version;
}

/**
 * 清除产品缓存
 *
 * 通过递增缓存版本号使所有带版本前缀的 transient 立即整体失效，
 * 一次性操作代替原先逐个删除 200 个键的暴力循环。
 */
function goods_exhibition_flush_cache()
{
    $version = (int) get_option('goods_exhibition_cache_version', 0);
    update_option('goods_exhibition_cache_version', $version + 1);

    // 如果使用对象缓存，也清除对象缓存
    if (function_exists('wp_cache_flush_group')) {
        wp_cache_flush_group('goods_exhibition');
    }
}

