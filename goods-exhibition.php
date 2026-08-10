<?php
/**
 * Plugin Name: 好物页面插件
 * Plugin URI: https://github.com/Jacky088/goods_exhibition
 * Description: 一个展示好物商品的WordPress插件（已通过安全审查和优化）
 * Version: 1.3.2
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
define('GOODS_EXHIBITION_VERSION', '1.3.2');
// 定义插件路径
define('GOODS_EXHIBITION_PATH', plugin_dir_path(__FILE__));
// 定义插件URL
define('GOODS_EXHIBITION_URL', plugin_dir_url(__FILE__));

// 插件上传目录定义（新增）
define('GOODS_EXHIBITION_UPLOAD_DIR', GOODS_EXHIBITION_PATH . 'uploads/');

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
 * 插件激活时执行的函数 - 新增 price、category、is_poster、poster_image_url 字段
 */
function goods_exhibition_activate()
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
        image_url varchar(255) NOT NULL,
        url varchar(255) DEFAULT '' NOT NULL,
        category varchar(255) DEFAULT '' NOT NULL,
        is_new tinyint(1) DEFAULT 0 NOT NULL,
        is_poster tinyint(1) DEFAULT 0 NOT NULL,
        poster_image_url varchar(255) DEFAULT '' NOT NULL,
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

    // 创建上传目录并写入 .htaccess 保护（防止直接执行上传文件）
    goods_exhibition_ensure_upload_protection();

    // 添加插件版本号选项
    add_option('goods_exhibition_version', GOODS_EXHIBITION_VERSION);
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
 * 注册插件的样式和脚本
 */
function goods_exhibition_enqueue_scripts()
{
    global $post;
    if (is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'goods_exhibition')) {
        wp_enqueue_style('goods-exhibition-style', GOODS_EXHIBITION_URL . 'assets/css/style.css', array(), GOODS_EXHIBITION_VERSION);
        wp_enqueue_script('goods-exhibition-script', GOODS_EXHIBITION_URL . 'assets/js/script.js', array(), GOODS_EXHIBITION_VERSION, true);
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
    wp_enqueue_style('goods-exhibition-admin-style', GOODS_EXHIBITION_URL . 'assets/css/admin.css', array(), GOODS_EXHIBITION_VERSION);
    wp_enqueue_script('jquery-ui-sortable');
    wp_enqueue_script('goods-exhibition-admin-script', GOODS_EXHIBITION_URL . 'assets/js/admin.js', array('jquery', 'jquery-ui-sortable'), GOODS_EXHIBITION_VERSION, true);
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

    foreach ($order as $position => $product_id) {
        $wpdb->update(
            $table_name,
            array('sort_order' => $position),
            array('id' => $product_id),
            array('%d'),
            array('%d')
        );
    }

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

    $moved = 0;
    foreach ($product_ids as $pid) {
        $result = $wpdb->update(
            $table_name,
            array('category' => $target_category),
            array('id' => $pid),
            array('%s'),
            array('%d')
        );
        if ($result !== false) {
            $moved++;
        }
    }

    goods_exhibition_flush_cache();
    wp_send_json_success(array('moved' => $moved, 'message' => "已将 {$moved} 个产品移至「{$target_category}」"));
}
add_action('wp_ajax_goods_exhibition_bulk_move', 'goods_exhibition_ajax_bulk_move');

/**
 * 前端首页显示标记为海报的产品幻灯片
 * 【已注释关闭此钩子，隐藏最上方“海报展示”块】
 */
// add_action('wp_body_open', 'goods_exhibition_render_poster_slideshow');
function goods_exhibition_render_poster_slideshow()
{
    $posters = goods_exhibition_get_poster_products();
    if (empty($posters)) {
        return;
    }
    ?>
    <div class="goods-exhibition-poster-wrapper" style="max-width:1200px;margin:20px auto;">
        <h2 class="goods-exhibition-category-title">海报展示</h2>
        <div class="goods-exhibition-wrapper">
            <button class="goods-exhibition-arrow goods-exhibition-arrow-left"
                aria-label="上一个"><span aria-hidden="true">&#10094;</span></button>
            <div class="goods-exhibition-slider">
                <?php foreach ($posters as $poster):
                    $url = esc_url($poster['url']);
                    $name = esc_html($poster['name']);
                    $desc = wp_kses_post($poster['description']);
                    $price = esc_html($poster['price']);
                    $image = esc_url($poster['poster_image_url']);
                    ?>
                    <?php if ($url): ?>
                        <a href="<?php echo $url; ?>" target="_blank" rel="noopener noreferrer"
                            class="goods-exhibition-item has-link">
                            <div class="goods-exhibition-content">
                                <h3 class="goods-exhibition-title"><?php echo $name; ?></h3>
                                <div class="goods-exhibition-description"><?php echo $desc; ?></div>
                                <?php if ($price): ?>
                                    <div class="goods-exhibition-price"><?php echo $price; ?></div><?php endif; ?>
                            </div>
                            <div class="goods-exhibition-image-container">
                                <img src="<?php echo $image; ?>" alt="<?php echo $name; ?>"
                                    width="500" height="500"
                                    class="goods-exhibition-image no-lightbox">
                            </div>
                        </a>
                    <?php else: ?>
                        <div class="goods-exhibition-item">
                            <div class="goods-exhibition-content">
                                <h3 class="goods-exhibition-title"><?php echo $name; ?></h3>
                                <div class="goods-exhibition-description"><?php echo $desc; ?></div>
                                <?php if ($price): ?>
                                    <div class="goods-exhibition-price"><?php echo $price; ?></div><?php endif; ?>
                            </div>
                            <div class="goods-exhibition-image-container">
                                <img src="<?php echo $image; ?>" alt="<?php echo $name; ?>"
                                    width="500" height="500"
                                    class="goods-exhibition-image no-lightbox">
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
            <button class="goods-exhibition-arrow goods-exhibition-arrow-right"
                aria-label="下一个"><span aria-hidden="true">&#10095;</span></button>
        </div>
    </div>
    <?php
}
