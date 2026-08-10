<?php
/**
 * 插件卸载时执行的清理操作
 *
 * 当用户从WordPress后台删除插件时，此文件会被自动调用。
 * 它会清理插件创建的所有数据，包括数据库表、选项和上传的文件。
 *
 * @package 好物页面插件
 */

// 如果不是通过WordPress卸载流程调用，则中止
if (!defined('WP_UNINSTALL_PLUGIN')) {
    die;
}

global $wpdb;

// 删除插件数据表
$table_name = $wpdb->prefix . 'goods_exhibition';
$wpdb->query("DROP TABLE IF EXISTS `{$table_name}`");

$categories_table = $wpdb->prefix . 'goods_exhibition_categories';
$wpdb->query("DROP TABLE IF EXISTS `{$categories_table}`");

// 删除插件选项（含缓存版本号）
delete_option('goods_exhibition_version');
delete_option('goods_exhibition_cache_version');

// 清除所有相关的 transient 缓存（新版本号机制 + 兼容旧 key）
delete_transient('goods_exhibition_poster_products');
delete_transient('goods_exhibition_frontend_products_-1');
delete_transient('goods_exhibition_all_products_-1');

for ($i = 1; $i <= 100; $i++) {
    delete_transient('goods_exhibition_all_products_' . $i);
    delete_transient('goods_exhibition_frontend_products_' . $i);
}

// 删除插件上传目录及其中的文件
$upload_dir = plugin_dir_path(__FILE__) . 'uploads/';
if (is_dir($upload_dir)) {
    $files = glob($upload_dir . '*');
    if ($files) {
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }
    // 删除 .htaccess
    $htaccess = $upload_dir . '.htaccess';
    if (file_exists($htaccess)) {
        unlink($htaccess);
    }
    rmdir($upload_dir);
}
