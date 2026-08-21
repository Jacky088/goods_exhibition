<?php
/**
 * Plugin Name: 好物页面插件
 * Plugin URI: https://github.com/Jacky088/goods_exhibition
 * Description: 一个展示好物商品的WordPress插件（已通过安全审查和优化）
 * Version: 1.4.8
 * Author: 木木
 * Author URI: https://github.com/Jacky088/goods_exhibition
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: goods-exhibition
 * Requires at least: 5.0
 * Requires PHP: 7.4
 */

// 如果直接访问此文件，则中止
if (!defined('WPINC')) {
    die;
}

// 定义插件版本
define('GOODS_EXHIBITION_VERSION', '1.4.8');
// 定义插件路径
define('GOODS_EXHIBITION_PATH', plugin_dir_path(__FILE__));
// 定义插件URL
define('GOODS_EXHIBITION_URL', plugin_dir_url(__FILE__));

// 插件上传目录定义
// 重要：图片必须存储在 wp-content/uploads/goods-exhibition/（而非插件目录内），
// 因为插件更新时其目录会被整体删除重建，存放在插件目录内的图片会全部丢失。
define('GOODS_EXHIBITION_UPLOAD_DIR', trailingslashit(wp_upload_dir()['basedir']) . 'goods-exhibition/');
define('GOODS_EXHIBITION_UPLOAD_URL', trailingslashit(wp_upload_dir()['baseurl']) . 'goods-exhibition/');

// 包含所需文件
require_once GOODS_EXHIBITION_PATH . 'includes/functions.php';
require_once GOODS_EXHIBITION_PATH . 'includes/image-helpers.php';
require_once GOODS_EXHIBITION_PATH . 'admin/admin.php';
require_once GOODS_EXHIBITION_PATH . 'includes/shortcode.php';

// 激活插件时的钩子
register_activation_hook(__FILE__, 'goods_exhibition_activate');
// 停用插件时的钩子
register_deactivation_hook(__FILE__, 'goods_exhibition_deactivate');

/**
 * 在插件列表的"停用"链接旁边添加一个"设置"入口
 */
function goods_exhibition_plugin_action_links($links)
{
    $settings_url = admin_url('admin.php?page=goods-exhibition');
    $settings_link = '<a href="' . esc_url($settings_url) . '">' . esc_html__('设置', 'goods-exhibition') . '</a>';
    $links['settings'] = $settings_link;
    return $links;
}
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'goods_exhibition_plugin_action_links');

/**
 * 插件激活时执行的函数
 */
function goods_exhibition_activate()
{
    goods_exhibition_setup_database();
    goods_exhibition_migrate_legacy_uploads();
    goods_exhibition_ensure_upload_protection();
    update_option('goods_exhibition_version', GOODS_EXHIBITION_VERSION);
}

/**
 * 升级例程：插件通过后台"自动更新"时不经过停用/激活流程，
 * 这里对比数据库记录的版本号，低于当前版本时自动执行表结构升级与数据迁移，
 * 确保老用户更新插件后不会因缺少新字段/新表而报错。
 */
function goods_exhibition_maybe_upgrade()
{
    $installed_version = get_option('goods_exhibition_version', '0');
    if (version_compare($installed_version, GOODS_EXHIBITION_VERSION, '>=')) {
        return;
    }

    goods_exhibition_setup_database();
    goods_exhibition_migrate_legacy_uploads();
    goods_exhibition_ensure_upload_protection();
    update_option('goods_exhibition_version', GOODS_EXHIBITION_VERSION);
}
add_action('admin_init', 'goods_exhibition_maybe_upgrade');

/**
 * 创建/升级数据表（dbDelta 幂等，可安全重复执行）
 */
function goods_exhibition_setup_database()
{
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();
    $table_name = $wpdb->prefix . 'goods_exhibition';

    // 首先创建完整表结构（首次激活或升级）。
    // dbDelta 会自动补齐旧表中缺失的列，因此无需在前面用 ALTER 预先建表。
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        name varchar(255) NOT NULL,
        description text NOT NULL,
        price varchar(50) DEFAULT '' NOT NULL,
        image_url varchar(500) NOT NULL,
        url varchar(500) DEFAULT '' NOT NULL,
        category varchar(255) DEFAULT '' NOT NULL,
        is_new tinyint(1) DEFAULT 0 NOT NULL,
        is_poster tinyint(1) DEFAULT 0 NOT NULL,
        poster_image_url varchar(500) DEFAULT '' NOT NULL,
        sort_order int DEFAULT 0 NOT NULL,
        created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
        updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";

    dbDelta($sql);

    // 补齐可能缺失的索引（dbDelta 对索引的处理不完整，旧版升级时手动补回）
    $indices = array(
        'category_idx' => 'category',
        'is_poster_idx' => 'is_poster',
        'is_new_idx' => 'is_new'
    );
    foreach ($indices as $index_name => $column_name) {
        $index_exists = $wpdb->get_results(
            $wpdb->prepare("SHOW INDEX FROM `{$table_name}` WHERE Key_name = %s", $index_name)
        );
        if (empty($index_exists)) {
            // $column_name 和 $index_name 来自硬编码数组，安全可控
            $wpdb->query("ALTER TABLE `{$table_name}` ADD INDEX `{$index_name}` (`{$column_name}`)");
        }
    }

    // 创建分类表
    $categories_table = $wpdb->prefix . 'goods_exhibition_categories';
    $sql_categories = "CREATE TABLE $categories_table (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        name varchar(255) NOT NULL,
        sort_order int DEFAULT 0 NOT NULL,
        created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
        PRIMARY KEY (id),
        UNIQUE KEY name_unique (name)
    ) $charset_collate;";
    dbDelta($sql_categories);

    // 将现有产品的分类迁移到分类表（如果分类表为空）
    $existing_count = (int) $wpdb->get_var("SELECT COUNT(*) FROM `{$categories_table}`");
    if ($existing_count === 0) {
        $existing_categories = $wpdb->get_col("SELECT DISTINCT category FROM `{$table_name}` WHERE category != '' ORDER BY category ASC");
        foreach ($existing_categories as $index => $cat) {
            $wpdb->insert($categories_table, array(
                'name' => $cat,
                'sort_order' => $index,
            ));
        }
    }
}

/**
 * 迁移旧版上传的图片文件
 *
 * 旧版本（<= 1.3.x）把图片存储在插件目录内的 uploads/ 子目录，
 * 插件一旦更新（目录被整体替换）图片就会丢失。
 * 此函数把旧目录中的图片复制到 wp-content/uploads/goods-exhibition/，
 * 并同步更新数据库中引用的 URL；旧文件保留作为备份（插件更新后旧目录会被自动清除）。
 *
 * 通过 goods_exhibition_upload_migrated 选项保证只完整执行一次。
 */
function goods_exhibition_migrate_legacy_uploads()
{
    if (get_option('goods_exhibition_upload_migrated')) {
        return;
    }

    $legacy_dir = GOODS_EXHIBITION_PATH . 'uploads/';

    // 旧目录不存在（全新安装或从未使用过旧版上传），直接标记完成
    if (!is_dir($legacy_dir)) {
        update_option('goods_exhibition_upload_migrated', 1);
        return;
    }

    $new_dir = GOODS_EXHIBITION_UPLOAD_DIR;
    wp_mkdir_p($new_dir);

    // 复制旧目录中的图片到新目录（已存在的文件跳过，避免覆盖）
    $migrated_all = true;
    foreach (scandir($legacy_dir) as $entry) {
        if ($entry === '.' || $entry === '..' || $entry === '.htaccess') {
            continue;
        }
        $source = $legacy_dir . $entry;
        if (!is_file($source)) {
            continue;
        }
        $target = $new_dir . $entry;
        if (!file_exists($target) && !copy($source, $target)) {
            $migrated_all = false; // 复制失败（如权限问题），下次进入后台时重试
        }
    }

    if (!$migrated_all) {
        return;
    }

    // 更新数据库中引用旧目录 URL 的记录（仅更新已确认复制到新目录的文件）
    global $wpdb;
    $table_name = $wpdb->prefix . 'goods_exhibition';
    $legacy_url = GOODS_EXHIBITION_URL . 'uploads/';
    $like_legacy = $wpdb->esc_like($legacy_url) . '%';
    $rows = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT id, image_url, poster_image_url FROM `{$table_name}` WHERE image_url LIKE %s OR poster_image_url LIKE %s",
            $like_legacy,
            $like_legacy
        ),
        ARRAY_A
    );

    if (is_array($rows)) {
        foreach ($rows as $row) {
            $update = array();
            foreach (array('image_url', 'poster_image_url') as $field) {
                $url = isset($row[$field]) ? $row[$field] : '';
                if (!empty($url) && strpos($url, $legacy_url) === 0) {
                    $filename = basename($url);
                    if (file_exists($new_dir . $filename)) {
                        $update[$field] = GOODS_EXHIBITION_UPLOAD_URL . $filename;
                    }
                }
            }
            if (!empty($update)) {
                $wpdb->update($table_name, $update, array('id' => intval($row['id'])));
            }
        }
    }

    // URL 已变更，清除缓存使其立即生效
    goods_exhibition_flush_cache();
    update_option('goods_exhibition_upload_migrated', 1);
}

/**
 * 插件停用时执行的函数
 */
function goods_exhibition_deactivate()
{
    // 可根据需求删除数据或清理
}

/**
 * 确保上传目录存在并写入 .htaccess 保护文件
 *
 * 防止上传的图片被当作 PHP 等脚本直接执行（即便扩展名被篡改），
 * 同时阻止目录列表浏览。仅在文件不存在时写入，避免覆盖用户自定义规则。
 */
function goods_exhibition_ensure_upload_protection()
{
    $upload_dir = GOODS_EXHIBITION_UPLOAD_DIR;

    if (!file_exists($upload_dir)) {
        wp_mkdir_p($upload_dir);
    }

    if (!is_dir($upload_dir)) {
        return;
    }

    $htaccess_file = $upload_dir . '.htaccess';
    if (!file_exists($htaccess_file)) {
        // 禁止脚本执行、禁止目录浏览、仅允许静态图片资源
        $content = "Options -Indexes\n";
        $content .= "<FilesMatch \"\\.(?i:php|phtml|php3|php4|php5|phar|cgi|pl|py|asp|aspx|jsp|sh)$\">\n";
        $content .= "    Require all denied\n";
        $content .= "</FilesMatch>\n";
        @file_put_contents($htaccess_file, $content);
    }
}

/**
 * 加载插件的文本域
 */
function goods_exhibition_load_textdomain()
{
    load_plugin_textdomain('goods-exhibition', false, dirname(plugin_basename(__FILE__)) . '/languages/');
}
add_action('plugins_loaded', 'goods_exhibition_load_textdomain');

/**
 * 获取资源文件的缓存版本号（基于文件修改时间）
 *
 * 热更新场景（直接覆盖文件、不修改插件版本号）下，
 * 文件修改时间变化会自动使浏览器缓存的 CSS/JS 失效，无需手动升版本。
 * 读取失败时回退到插件版本号。
 *
 * @param string $rel_path 相对插件目录的文件路径
 * @return string|int 用于 wp_enqueue_* 的 version 参数
 */
function goods_exhibition_asset_version($rel_path)
{
    $file = GOODS_EXHIBITION_PATH . $rel_path;
    $mtime = is_readable($file) ? filemtime($file) : false;
    return $mtime !== false ? $mtime : GOODS_EXHIBITION_VERSION;
}

/**
 * 注册插件的样式和脚本
 */
function goods_exhibition_enqueue_scripts()
{
    global $post;
    if (is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'goods_exhibition')) {
        wp_enqueue_style('goods-exhibition-style', GOODS_EXHIBITION_URL . 'assets/css/style.css', array(), goods_exhibition_asset_version('assets/css/style.css'));
        wp_enqueue_script('goods-exhibition-script', GOODS_EXHIBITION_URL . 'assets/js/script.js', array(), goods_exhibition_asset_version('assets/js/script.js'), true);
    }
}
add_action('wp_enqueue_scripts', 'goods_exhibition_enqueue_scripts');

/**
 * 管理界面加载样式和脚本
 */
function goods_exhibition_admin_enqueue_scripts($hook)
{
    if (strpos($hook, 'goods-exhibition') === false) {
        return;
    }
    wp_enqueue_style('goods-exhibition-admin-style', GOODS_EXHIBITION_URL . 'assets/css/admin.css', array(), goods_exhibition_asset_version('assets/css/admin.css'));
    wp_enqueue_script('jquery-ui-sortable');
    wp_enqueue_script('goods-exhibition-admin-script', GOODS_EXHIBITION_URL . 'assets/js/admin.js', array('jquery', 'jquery-ui-sortable'), goods_exhibition_asset_version('assets/js/admin.js'), true);
    wp_localize_script('goods-exhibition-admin-script', 'goodsExhibitionAdmin', array(
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('goods_exhibition_sort_nonce'),
    ));
    wp_enqueue_media();
}
add_action('admin_enqueue_scripts', 'goods_exhibition_admin_enqueue_scripts');

/**
 * AJAX: 保存产品排序
 */
function goods_exhibition_ajax_save_sort()
{
    check_ajax_referer('goods_exhibition_sort_nonce', 'nonce');

    if (!current_user_can('manage_options')) {
        wp_send_json_error('权限不足');
    }

    $order = isset($_POST['order']) ? $_POST['order'] : array();
    if (empty($order) || !is_array($order)) {
        wp_send_json_error('无效数据');
    }

    // 前置清洗：order 应为 "位置 => 商品ID" 的整型数组，杜绝非数字注入
    $order = array_map('intval', (array) $order);

    global $wpdb;
    $table_name = $wpdb->prefix . 'goods_exhibition';

    // 单条 CASE WHEN 批量更新，代替逐行 UPDATE 的 N 次数据库往返
    $cases = array();
    $ids = array();
    foreach ($order as $position => $product_id) {
        $product_id = intval($product_id);
        if ($product_id < 1 || isset($ids[$product_id])) {
            continue; // 跳过无效值与重复 ID
        }
        $ids[$product_id] = $product_id;
        $cases[] = $wpdb->prepare('WHEN %d THEN %d', $product_id, intval($position));
    }

    if (empty($ids)) {
        wp_send_json_error('无效数据');
    }

    $ids_sql = implode(',', $ids); // 均已 intval，安全可控
    $case_sql = implode(' ', $cases);
    $wpdb->query("UPDATE `{$table_name}` SET sort_order = CASE id {$case_sql} END WHERE id IN ({$ids_sql})");

    goods_exhibition_flush_cache();
    wp_send_json_success('排序已保存');
}
add_action('wp_ajax_goods_exhibition_save_sort', 'goods_exhibition_ajax_save_sort');

/**
 * AJAX: 批量移动产品到指定分类
 */
function goods_exhibition_ajax_bulk_move()
{
    check_ajax_referer('goods_exhibition_sort_nonce', 'nonce');

    if (!current_user_can('manage_options')) {
        wp_send_json_error('权限不足');
    }

    $product_ids = isset($_POST['product_ids']) ? $_POST['product_ids'] : array();
    $target_category = isset($_POST['target_category']) ? sanitize_text_field($_POST['target_category']) : '';

    if (empty($product_ids) || !is_array($product_ids) || empty($target_category)) {
        wp_send_json_error('请选择产品和目标分类');
    }

    // 前置清洗：商品 ID 必须为整型
    $product_ids = array_filter(array_map('intval', (array) $product_ids));
    if (empty($product_ids)) {
        wp_send_json_error('无效的产品数据');
    }

    // 校验目标分类真实存在，防止写入任意字符串
    global $wpdb;
    $categories_table = $wpdb->prefix . 'goods_exhibition_categories';
    $category_exists = $wpdb->get_var(
        $wpdb->prepare("SELECT COUNT(*) FROM `{$categories_table}` WHERE name = %s", $target_category)
    );
    if (!$category_exists) {
        wp_send_json_error('目标分类不存在，请先创建该分类');
    }

    $table_name = $wpdb->prefix . 'goods_exhibition';

    // 单条 SQL 批量更新分类，代替逐行 UPDATE 的 N 次数据库往返
    // moved 为实际受影响行数：已在目标分类的产品不会重复计数（提示数量比旧版更准确）
    $ids_sql = implode(',', $product_ids); // 均已 intval，安全可控
    $moved = $wpdb->query(
        $wpdb->prepare(
            "UPDATE `{$table_name}` SET category = %s WHERE id IN ({$ids_sql})",
            $target_category
        )
    );
    if ($moved === false) {
        $moved = 0;
    }

    goods_exhibition_flush_cache();
    wp_send_json_success(array('moved' => $moved, 'message' => "已将 {$moved} 个产品移至「{$target_category}」"));
}
add_action('wp_ajax_goods_exhibition_bulk_move', 'goods_exhibition_ajax_bulk_move');

/*
 * 历史说明：v1.3.x 曾在 wp_body_open 钩子输出"海报展示"幻灯片，
 * 该功能后由 [goods_exhibition] 短代码内部的海报轮播替代，
 * 对应的 goods_exhibition_render_poster_slideshow() 已作为死代码移除。
 */
