<?php
/**
 * Plugin Name: 好物页面插件
 * Plugin URI: https://github.com/Jacky088/goods_exhibition
 * Description: 一个展示好物商品的WordPress插件
 * Version: 1.0.1
 * Author: 木木
 * Author URI: https://github.com/Jacky088/goods_exhibition
 * License: 
 * Text Domain: goods-exhibition
 */

// 如果直接访问此文件，则中止
if (!defined('WPINC')) {
    die;
}

// 定义插件版本
define('GOODS_EXHIBITION_VERSION', '1.0.4');
// 定义插件路径
define('GOODS_EXHIBITION_PATH', plugin_dir_path(__FILE__));
// 定义插件URL
define('GOODS_EXHIBITION_URL', plugin_dir_url(__FILE__));

// 插件上传目录定义（新增）
define('GOODS_EXHIBITION_UPLOAD_DIR', GOODS_EXHIBITION_PATH . 'uploads/');

// 包含所需文件
require_once GOODS_EXHIBITION_PATH . 'admin/admin.php';
require_once GOODS_EXHIBITION_PATH . 'includes/shortcode.php';
require_once GOODS_EXHIBITION_PATH . 'includes/functions.php';

// 激活插件时的钩子
register_activation_hook(__FILE__, 'goods_exhibition_activate');
// 停用插件时的钩子
register_deactivation_hook(__FILE__, 'goods_exhibition_deactivate');

/**
 * 插件激活时执行的函数 - 新增 price 和 category 字段
 */
function goods_exhibition_activate() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();
    $table_name = $wpdb->prefix . 'goods_exhibition';

    // 检查category字段是否存在
    $column = $wpdb->get_results("SHOW COLUMNS FROM `$table_name` LIKE 'category'");
    if (empty($column)) {
        // 如果表存在且无category字段，新增category字段
        $wpdb->query("ALTER TABLE `$table_name` ADD COLUMN `category` varchar(255) NOT NULL DEFAULT '' AFTER `url`");
    }

    // 新建表结构（首次激活）
    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        name varchar(255) NOT NULL,
        description text NOT NULL,
        price varchar(50) DEFAULT '' NOT NULL,
        image_url varchar(255) NOT NULL,
        url varchar(255) DEFAULT '' NOT NULL,
        category varchar(255) DEFAULT '' NOT NULL,
        is_new tinyint(1) DEFAULT 0 NOT NULL,
        created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
        updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);

    // 添加一个选项，用于存储插件版本
    add_option('goods_exhibition_version', GOODS_EXHIBITION_VERSION);
}

/**
 * 插件停用时执行的函数
 */
function goods_exhibition_deactivate() {
    // 可根据需求删除数据或清理
}

/**
 * 加载插件的文本域
 */
function goods_exhibition_load_textdomain() {
    load_plugin_textdomain('goods-exhibition', false, dirname(plugin_basename(__FILE__)) . '/languages/');
}
add_action('plugins_loaded', 'goods_exhibition_load_textdomain');

/**
 * 注册插件的样式和脚本
 */
function goods_exhibition_enqueue_scripts() {
    wp_enqueue_style('goods-exhibition-style', GOODS_EXHIBITION_URL . 'assets/css/style.css', array(), GOODS_EXHIBITION_VERSION);
    wp_enqueue_script('goods-exhibition-script', GOODS_EXHIBITION_URL . 'assets/js/script.js', array('jquery'), GOODS_EXHIBITION_VERSION, true);
}
add_action('wp_enqueue_scripts', 'goods_exhibition_enqueue_scripts');

/**
 * 管理界面加载样式和脚本
 */
function goods_exhibition_admin_enqueue_scripts($hook) {
    if (strpos($hook, 'goods-exhibition') === false) {
        return;
    }
    wp_enqueue_style('goods-exhibition-admin-style', GOODS_EXHIBITION_URL . 'assets/css/admin.css', array(), GOODS_EXHIBITION_VERSION);
    wp_enqueue_script('goods-exhibition-admin-script', GOODS_EXHIBITION_URL . 'assets/js/admin.js', array('jquery'), GOODS_EXHIBITION_VERSION, true);
    wp_enqueue_media();
}
add_action('admin_enqueue_scripts', 'goods_exhibition_admin_enqueue_scripts');
