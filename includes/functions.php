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
 * 获取前端展示的所有产品（按类别排序）
 *
 * @param int $limit 限制数量
 * @return array 产品列表
 */
function goods_exhibition_get_frontend_products($limit = -1)
{
    // 确保 $limit 是整数
    $limit = intval($limit);
    $cache_key = 'goods_exhibition_frontend_products_' . goods_exhibition_cache_version() . '_' . $limit . '_' . goods_exhibition_code_stamp();
    $cached = get_transient($cache_key);
    if (false !== $cached && !empty($cached)) {
        return $cached;
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'goods_exhibition';
    $categories_table = $wpdb->prefix . 'goods_exhibition_categories';

    // 1. 获取所有分类的自定义排序权重（杜绝 SQL 跨表字符串 ON cat.name = t.category 引发的 Illegal mix of collations 崩溃）
    $cat_weights = array();
    $categories = $wpdb->get_results("SELECT name, sort_order FROM `{$categories_table}` ORDER BY sort_order ASC, id ASC", ARRAY_A);
    if (!empty($categories) && is_array($categories)) {
        foreach ($categories as $cat_row) {
            if (isset($cat_row['name'])) {
                $cat_weights[trim($cat_row['name'])] = intval($cat_row['sort_order']);
            }
        }
    }

    // 2. 独立查询全量产品数据（无外键字符串联查，跨字符集与跨校对规则 100% 绝对可靠）
    $results = $wpdb->get_results("SELECT * FROM `{$table_name}` ORDER BY sort_order ASC, created_at DESC", ARRAY_A);

    if (!is_array($results) || empty($results)) {
        return array();
    }

    // 3. 在 PHP 内存层执行精准分组排序：分类权重 ASC -> 分类名称 ASC -> 组内商品自定义排序 ASC -> 添加时间 DESC
    usort($results, function ($a, $b) use ($cat_weights) {
        $catA = isset($a['category']) ? trim($a['category']) : '';
        $catB = isset($b['category']) ? trim($b['category']) : '';

        $weightA = isset($cat_weights[$catA]) ? $cat_weights[$catA] : 999999;
        $weightB = isset($cat_weights[$catB]) ? $cat_weights[$catB] : 999999;

        if ($weightA !== $weightB) {
            return $weightA - $weightB;
        }
        if ($catA !== $catB) {
            return strcmp($catA, $catB);
        }
        $sortA = isset($a['sort_order']) ? intval($a['sort_order']) : 0;
        $sortB = isset($b['sort_order']) ? intval($b['sort_order']) : 0;
        if ($sortA !== $sortB) {
            return $sortA - $sortB;
        }
        $idA = isset($a['id']) ? intval($a['id']) : 0;
        $idB = isset($b['id']) ? intval($b['id']) : 0;
        return $idB - $idA;
    });

    if ($limit > 0 && count($results) > $limit) {
        $results = array_slice($results, 0, $limit);
    }

    if (!empty($results)) {
        set_transient($cache_key, $results, 12 * HOUR_IN_SECONDS);
    }

    return $results;
}

/**
 * 按后台设置的自定义顺序排列海报产品
 *
 * 顺序存储在选项 goods_exhibition_poster_order 中（产品 ID 数组）。
 * 未设置自定义顺序时原样返回，完全不改变原有排序行为；
 * 设置后：列表内的海报按指定顺序排列，不在列表中的（如新加入的海报）
 * 保持其原有相对次序并追加到末尾，保证新增海报不会"插队"。
 *
 * @param array $rows 海报产品行数据（已按默认规则排序）
 * @return array 重排后的海报产品行数据
 */
function goods_exhibition_apply_poster_order($rows)
{
    if (!is_array($rows) || count($rows) < 2) {
        return $rows;
    }

    $saved = get_option('goods_exhibition_poster_order', array());
    if (empty($saved) || !is_array($saved)) {
        return $rows; // 未设置自定义顺序：保持原有排序
    }

    // 产品 ID => 期望位置
    $position = array();
    foreach (array_values($saved) as $index => $id) {
        $position[intval($id)] = $index;
    }

    $ordered = array();
    $rest = array();
    foreach ($rows as $row) {
        $id = intval($row['id']);
        if (isset($position[$id])) {
            $ordered[$position[$id]] = $row;
        } else {
            $rest[] = $row;
        }
    }

    ksort($ordered);
    return array_merge(array_values($ordered), $rest);
}

/**
 * 获取标记为海报且有海报图片的产品
 *
 * @return array
 */
function goods_exhibition_get_poster_products()
{
    $cache_key = 'goods_exhibition_poster_products_' . goods_exhibition_cache_version() . '_' . goods_exhibition_code_stamp();
    $cached = get_transient($cache_key);
    if (false !== $cached) {
        return $cached;
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'goods_exhibition';

    // 默认排序与商品列表保持一致（跟随后台拖拽保存的 sort_order，id DESC 兜底）；
    // 若后台设置了自定义海报顺序，则由 goods_exhibition_apply_poster_order() 在 PHP 层重排。
    // 先取出全部海报（数量有限）再重排并截取前 6 张，避免标记过多海报时前端加载几十张 1920px 大图
    $results = $wpdb->get_results(
        "SELECT * FROM $table_name WHERE is_poster = 1 AND poster_image_url != '' ORDER BY sort_order ASC, id DESC",
        ARRAY_A
    );

    $results = goods_exhibition_apply_poster_order($results);
    $results = array_slice($results, 0, GOODS_EXHIBITION_POSTER_LIMIT);

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

    // 统一使用主文件中的保护函数写入 .htaccess（防止上传文件被当作脚本执行），
    // 避免与 goods_exhibition_ensure_upload_protection() 维护两份相同逻辑
    goods_exhibition_ensure_upload_protection();

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

/**
 * 数据缓存键的代码戳（本文件的修改时间）
 *
 * 热更新（直接覆盖文件、不升插件版本号）改动了查询逻辑时，
 * 代码戳变化使旧 transient 立即失效，无需等待 12 小时过期，也无需手动清缓存。
 *
 * @return int
 */
function goods_exhibition_code_stamp()
{
    return filemtime(__FILE__);
}

/**
 * 格式化产品价格展示（规范化货币符号与后缀）
 *
 * 将历史数据或手动输入中存在的 RMB、CNY 文本代号统一联动转换为规范货币符号 ¥，
 * 并支持灵活追加“起”或“到手价”等促销修饰词。
 *
 * @param string $price 原始价格字符串
 * @param string $suffix 价格后缀（如“起”、“到手价”，可选）
 * @return string 规范化后的展示价格
 */
function goods_exhibition_format_price_display($price, $suffix = '')
{
    if (empty($price)) {
        return '';
    }
    // 将历史或录入的 RMB、CNY 统一替换为对应的规范货币符号 ¥
    $formatted = preg_replace('/^(RMB|CNY)\s*/iu', '¥', trim($price));

    $suffix = trim($suffix);
    if (!empty($suffix)) {
        // 如果价格文本尚未包含该后缀，则平滑追加
        $has_suffix = function_exists('mb_strpos') ? (mb_strpos($formatted, $suffix) !== false) : (strpos($formatted, $suffix) !== false);
        if (!$has_suffix) {
            $formatted .= $suffix;
        }
    }
    return $formatted;
}


