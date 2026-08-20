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

// 删除插件选项（含缓存版本号与上传迁移标记）
delete_option('goods_exhibition_version');
delete_option('goods_exhibition_cache_version');
delete_option('goods_exhibition_upload_migrated');

// 清除所有相关的 transient 缓存
// 缓存键带有版本号与 limit 后缀（如 goods_exhibition_all_products_3_10），
// 无法逐个枚举，直接按前缀清理 options 表中的全部 transient 记录（含 timeout 记录）。
$wpdb->query(
    "DELETE FROM {$wpdb->options}
     WHERE option_name LIKE '\\_transient\\_goods\\_exhibition%'
        OR option_name LIKE '\\_transient\\_timeout\\_goods\\_exhibition%'"
);

// 删除上传目录及其中的文件
$remove_upload_dir = function ($dir) {
    if (!is_dir($dir)) {
        return;
    }
    $files = glob($dir . '*');
    if ($files) {
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }
    // 删除 .htaccess
    $htaccess = $dir . '.htaccess';
    if (file_exists($htaccess)) {
        unlink($htaccess);
    }
    rmdir($dir);
};

// 新版上传目录：wp-content/uploads/goods-exhibition/
$remove_upload_dir(trailingslashit(wp_upload_dir()['basedir']) . 'goods-exhibition/');
// 旧版上传目录（插件目录内，可能残留备份文件）
$remove_upload_dir(plugin_dir_path(__FILE__) . 'uploads/');
