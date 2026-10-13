<?php
/**
 * 插件管理界面 (v2.0.0 现代 SaaS 视觉重构版)
 *
 * @package 好物页面插件
 */

if (!defined('WPINC')) {
    die;
}

/**
 * 每页显示的产品数量
 */
define('GOODS_EXHIBITION_PER_PAGE', 20);

/**
 * 集中管理 SVG 矢量图标
 *
 * @param string $name 图标名称
 * @return string SVG HTML
 */
function goods_exhibition_get_icon($name)
{
    switch ($name) {
        case 'edit':
            // 现代极简铅笔图标
            return '<svg class="ge-icon ge-icon-edit" viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>';
        case 'delete':
            // 现代极简红色垃圾桶图标
            return '<svg class="ge-icon ge-icon-delete" viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>';
        case 'link':
            // 外链图标
            return '<svg class="ge-icon ge-icon-link" viewBox="0 0 24 24" width="15" height="15" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"></path><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"></path></svg>';
        case 'eye':
            // 预览眼睛图标
            return '<svg class="ge-icon ge-icon-eye" viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>';
        case 'upload':
            // 云端上传图标
            return '<svg class="ge-icon ge-icon-upload" viewBox="0 0 24 24" width="38" height="38" stroke="currentColor" stroke-width="1.8" fill="none" stroke-linecap="round" stroke-linejoin="round"><path d="M20.39 18.39A5 5 0 0 0 18 9h-1.26A8 8 0 1 0 3 16.3"></path><polyline points="16 16 12 12 8 16"></polyline><line x1="12" y1="12" x2="12" y2="21"></line></svg>';
        case 'search':
            // 搜索放大镜图标
            return '<svg class="ge-icon ge-icon-search" viewBox="0 0 24 24" width="15" height="15" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>';
        case 'filter':
            // 筛选漏斗图标
            return '<svg class="ge-icon ge-icon-filter" viewBox="0 0 24 24" width="14" height="14" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon></svg>';
        case 'handle':
            // 拖拽手柄 ≡
            return '<svg class="ge-icon ge-icon-handle" viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"><line x1="4" y1="8" x2="20" y2="8"></line><line x1="4" y1="16" x2="20" y2="16"></line></svg>';
        case 'info':
            // 提示信息圆圈 i
            return '<svg class="ge-icon ge-icon-info" viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>';
        case 'plus':
            // 加号 +
            return '<svg class="ge-icon ge-icon-plus" viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>';
        case 'layers':
            // 图层叠放
            return '<svg class="ge-icon ge-icon-layers" viewBox="0 0 24 24" width="18" height="18" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 2 7 12 12 22 7 12 2"></polygon><polyline points="2 17 12 22 22 17"></polyline><polyline points="2 12 12 17 22 12"></polyline></svg>';
        case 'help':
            // 帮助问号
            return '<svg class="ge-icon ge-icon-help" viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>';
        case 'sort':
            // 双向上下箭头
            return '<svg class="ge-icon ge-icon-sort" viewBox="0 0 24 24" width="13" height="13" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"><polyline points="7 15 12 20 17 15"></polyline><polyline points="7 9 12 4 17 9"></polyline></svg>';
        case 'branch':
        case 'move':
            // 分支/批量移动图标 (参考图 3 share-2 节点图标)
            return '<svg class="ge-icon ge-icon-branch" viewBox="0 0 24 24" width="15" height="15" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"><circle cx="18" cy="5" r="3"></circle><circle cx="6" cy="12" r="3"></circle><circle cx="18" cy="19" r="3"></circle><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"></line><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"></line></svg>';
        default:
            return '';
    }
}

/**
 * 注册管理菜单 - 只注册一个顶级菜单，不添加子菜单
 */
function goods_exhibition_add_admin_menu()
{
    add_menu_page(
        '好物页面管理插件',
        '好物页面',
        'manage_options',
        'goods-exhibition',
        'goods_exhibition_admin_router',
        'dashicons-cart',
        30
    );
}
add_action('admin_menu', 'goods_exhibition_add_admin_menu');

/**
 * 路由：根据 tab 参数分发到不同页面内容
 */
function goods_exhibition_admin_router()
{
    if (!current_user_can('manage_options')) {
        return;
    }

    $current_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'list';

    // 输出页面大标题和分页卡
    goods_exhibition_render_admin_header($current_tab);

    // 根据 tab 渲染对应内容
    switch ($current_tab) {
        case 'categories':
            goods_exhibition_render_categories_tab();
            break;
        case 'posters':
            goods_exhibition_render_poster_sort_tab();
            break;
        case 'add':
        case 'list':
        default:
            goods_exhibition_render_product_list_tab();
            break;
    }

    echo '</div>'; // 关闭 .goods-exhibition-app
}

/**
 * 后台动作统一处理器（PRG 模式）
 */
function goods_exhibition_handle_admin_actions()
{
    if (wp_doing_ajax()) {
        return;
    }
    if (!isset($_GET['page']) || $_GET['page'] !== 'goods-exhibition') {
        return;
    }
    if (!current_user_can('manage_options')) {
        return;
    }

    // 优先处理产品添加/编辑表单提交（支持在列表模态弹窗内提交）
    if (isset($_POST['goods_exhibition_submit'])) {
        goods_exhibition_handle_product_form_action();
        return;
    }

    $current_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'list';

    if ($current_tab === 'categories') {
        goods_exhibition_handle_category_actions();
    } elseif ($current_tab === 'posters') {
        // 海报排序通过 AJAX 保存，此处无需处理表单动作
        return;
    } else {
        goods_exhibition_handle_list_actions();
    }
}
add_action('admin_init', 'goods_exhibition_handle_admin_actions');

/**
 * 记录一条在当前请求内显示的提示
 */
function goods_exhibition_admin_notice($type, $text)
{
    if (!isset($GLOBALS['goods_exhibition_admin_notices']) || !is_array($GLOBALS['goods_exhibition_admin_notices'])) {
        $GLOBALS['goods_exhibition_admin_notices'] = array();
    }
    $GLOBALS['goods_exhibition_admin_notices'][] = array('type' => $type, 'text' => $text);
}

/**
 * 动作处理成功后重定向（保留 tab、筛选、分页与编辑态参数）
 */
function goods_exhibition_admin_redirect($msg_code, $extra = array())
{
    $args = array('page' => 'goods-exhibition');

    foreach (array('tab', 's', 'category', 'paged') as $key) {
        if (isset($_GET[$key]) && is_scalar($_GET[$key]) && $_GET[$key] !== '') {
            $args[$key] = (string) $_GET[$key];
        }
    }

    if (isset($args['tab']) && $args['tab'] === 'add') {
        $args['tab'] = 'list';
    }

    $args = array_merge($args, $extra);
    $args['goods_msg'] = $msg_code;

    wp_safe_redirect(add_query_arg($args, admin_url('admin.php')));
    exit;
}

/**
 * 渲染后台提示信息（转化为现代化 SaaS Toast / Banner 样式）
 */
function goods_exhibition_render_admin_notices()
{
    if (!empty($GLOBALS['goods_exhibition_admin_notices']) && is_array($GLOBALS['goods_exhibition_admin_notices'])) {
        foreach ($GLOBALS['goods_exhibition_admin_notices'] as $notice) {
            $class = $notice['type'] === 'error' ? 'ge-alert-danger' : ($notice['type'] === 'warning' ? 'ge-alert-warning' : 'ge-alert-success');
            echo '<div class="ge-alert ' . esc_attr($class) . ' is-dismissible"><div class="ge-alert-content">' . goods_exhibition_get_icon('info') . '<span>' . $notice['text'] . '</span></div><button type="button" class="ge-alert-close">&times;</button></div>';
        }
    }

    if (empty($_GET['goods_msg'])) {
        return;
    }

    $code = sanitize_key(wp_unslash($_GET['goods_msg']));
    $cnt = isset($_GET['cnt']) ? intval($_GET['cnt']) : 0;
    $cat_name = isset($_GET['cat_name']) ? sanitize_text_field(wp_unslash($_GET['cat_name'])) : '';
    $prod_name = isset($_GET['prod_name']) ? sanitize_text_field(wp_unslash($_GET['prod_name'])) : '';

    switch ($code) {
        case 'deleted':
            $type = 'success';
            $text = '产品已成功删除！';
            break;
        case 'delete_failed':
            $type = 'danger';
            $text = '删除产品失败！';
            break;
        case 'bulk_deleted':
            $type = 'success';
            $text = '已成功删除 ' . $cnt . ' 个产品！';
            break;
        case 'added':
            $type = 'success';
            $text = '新产品已成功添加！';
            break;
        case 'updated':
            $type = 'success';
            $text = '产品已成功更新！';
            break;
        case 'product_duplicate':
            $type = 'warning';
            $text = '产品「' . esc_html($prod_name) . '」已存在，添加失败！请勿重复添加同名商品。';
            break;
        case 'product_edit_duplicate':
            $type = 'warning';
            $text = '已存在名为「' . esc_html($prod_name) . '」的其他产品，更新失败！请更换其他名称。';
            break;
        case 'cat_added':
            $type = 'success';
            $text = '分类「' . esc_html($cat_name) . '」已成功添加！';
            break;
        case 'cat_duplicate':
            $type = 'warning';
            $text = '分类「' . esc_html($cat_name) . '」已存在，添加失败！请勿重复添加同名分类。';
            break;
        case 'cat_updated':
            $type = 'success';
            $text = '分类已成功重命名为「' . esc_html($cat_name) . '」！' . ($cnt > 0 ? ('原分类下的 ' . $cnt . ' 个商品已自动同步并入新分类。') : '已自动同步商品关联。');
            break;
        case 'cat_deleted':
            $type = 'success';
            $text = '分类已成功删除！';
            break;
        case 'cat_not_empty':
            $type = 'warning';
            $text = '分类「' . esc_html($cat_name) . '」下当前仍有 ' . $cnt . ' 个商品，无法删除！必须先清空或转移分类目录下的所有商品，才能删除该分类。';
            break;
        default:
            return;
    }

    $class = $type === 'danger' ? 'ge-alert-danger' : ($type === 'warning' ? 'ge-alert-warning' : 'ge-alert-success');
    echo '<div class="ge-alert ' . esc_attr($class) . ' is-dismissible"><div class="ge-alert-content">' . goods_exhibition_get_icon('info') . '<span>' . $text . '</span></div><button type="button" class="ge-alert-close">&times;</button></div>';
}

/**
 * 产品列表页动作：单个删除（GET）、批量删除（POST）
 */
function goods_exhibition_handle_list_actions()
{
    // 单个删除
    if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['product_id']) && isset($_GET['_wpnonce'])) {
        if (wp_verify_nonce($_GET['_wpnonce'], 'delete_product_' . intval($_GET['product_id']))) {
            $product_id = intval($_GET['product_id']);
            if (goods_exhibition_delete_product($product_id)) {
                goods_exhibition_admin_redirect('deleted');
            } else {
                goods_exhibition_admin_redirect('delete_failed');
            }
        } else {
            goods_exhibition_admin_notice('error', '安全验证失败，请重试。');
        }
    }

    // 批量删除
    if (isset($_POST['goods_exhibition_bulk_action']) && $_POST['goods_exhibition_bulk_action'] === 'delete') {
        if (isset($_POST['_wpnonce_bulk']) && wp_verify_nonce($_POST['_wpnonce_bulk'], 'goods_exhibition_bulk_delete')) {
            if (!empty($_POST['product_ids']) && is_array($_POST['product_ids'])) {
                $deleted = goods_exhibition_bulk_delete_products($_POST['product_ids']);
                goods_exhibition_admin_redirect('bulk_deleted', array('cnt' => $deleted));
            }
        } else {
            goods_exhibition_admin_notice('error', '安全验证失败，请重试。');
        }
    }
}

/**
 * 添加/编辑产品表单提交（POST）
 */
function goods_exhibition_handle_product_form_action()
{
    if (!isset($_POST['goods_exhibition_submit'])) {
        return;
    }

    check_admin_referer('goods_exhibition_add_product');

    global $wpdb;
    $table_name = $wpdb->prefix . 'goods_exhibition';

    $is_edit = false;
    $edit_id = 0;
    if (isset($_POST['product_id']) && intval($_POST['product_id']) > 0) {
        $edit_id = intval($_POST['product_id']);
        $exists = $wpdb->get_var($wpdb->prepare("SELECT id FROM `{$table_name}` WHERE id = %d", $edit_id));
        if ($exists) {
            $is_edit = true;
        }
    } elseif (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['product_id'])) {
        $edit_id = intval($_GET['product_id']);
        $exists = $wpdb->get_var($wpdb->prepare("SELECT id FROM `{$table_name}` WHERE id = %d", $edit_id));
        if ($exists) {
            $is_edit = true;
        }
    }

    $product_name = isset($_POST['product_name']) ? trim(sanitize_text_field($_POST['product_name'])) : '';
    $product_description = isset($_POST['product_description']) ? wp_kses_post($_POST['product_description']) : '';
    $product_currency = isset($_POST['product_currency']) ? sanitize_text_field($_POST['product_currency']) : '';
    $raw_price = isset($_POST['product_price']) ? trim(sanitize_text_field($_POST['product_price'])) : '';

    // 智能组合货币符号与价格（去除用户手打的重复前缀）
    $product_price = $raw_price;
    if (!empty($product_currency) && !empty($raw_price)) {
        $clean_price = preg_replace('/^(' . preg_quote($product_currency, '/') . '|RMB|CNY|HKD|USD|NTD|\$|¥|€|£|₩)\s*/iu', '', $raw_price);
        $product_price = $product_currency . $clean_price;
    } elseif (empty($product_currency)) {
        $product_price = $raw_price;
    }

    $product_url = isset($_POST['product_url']) ? esc_url_raw($_POST['product_url']) : '';
    $product_category = isset($_POST['product_category']) ? sanitize_text_field($_POST['product_category']) : '';
    $product_image_url_from_media = isset($_POST['product_image_url']) ? esc_url_raw($_POST['product_image_url']) : '';
    $product_is_new = isset($_POST['product_is_new']) ? 1 : 0;
    $product_is_poster = isset($_POST['product_is_poster']) ? 1 : 0;
    $poster_image_url_from_media = isset($_POST['poster_image_url']) ? esc_url_raw($_POST['poster_image_url']) : '';
    $uploaded_image_url = '';
    $uploaded_poster_image_url = '';
    $errors = array();

    // 产品图片上传处理
    $image_result = goods_exhibition_handle_image_upload('product_image');
    if (!empty($image_result['error'])) {
        $errors[] = $image_result['error'];
    }
    if ($image_result['success']) {
        $uploaded_image_url = $image_result['url'];
    }

    // 海报图片上传处理
    $poster_result = goods_exhibition_handle_image_upload('poster_image');
    if (!empty($poster_result['error'])) {
        $errors[] = $poster_result['error'];
    }
    if ($poster_result['success']) {
        $uploaded_poster_image_url = $poster_result['url'];
    }

    // 确定最终产品图片 URL
    $final_image_url = '';
    if (!empty($uploaded_image_url)) {
        $final_image_url = $uploaded_image_url;
    } elseif (!empty($product_image_url_from_media)) {
        $final_image_url = $product_image_url_from_media;
    }

    // 确定最终海报图片 URL
    $final_poster_image_url = '';
    if (!empty($uploaded_poster_image_url)) {
        $final_poster_image_url = $uploaded_poster_image_url;
    } elseif (!empty($poster_image_url_from_media)) {
        $final_poster_image_url = $poster_image_url_from_media;
    }

    // 表单验证与重名检测
    if (empty($product_name)) {
        $errors[] = '产品名称不能为空';
    } else {
        if (!$is_edit) {
            $name_exists = (int) $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM `{$table_name}` WHERE name = %s", $product_name));
            if ($name_exists > 0) {
                goods_exhibition_admin_redirect('product_duplicate', array('prod_name' => $product_name));
            }
        } else {
            $conflict_exists = (int) $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM `{$table_name}` WHERE name = %s AND id != %d",
                $product_name, $edit_id
            ));
            if ($conflict_exists > 0) {
                goods_exhibition_admin_redirect('product_edit_duplicate', array('prod_name' => $product_name));
            }
        }
    }
    if (empty($product_description)) {
        $errors[] = '产品描述不能为空';
    }
    if (empty($final_image_url)) {
        $errors[] = '请上传产品图片或从媒体库选择图片';
    }
    if (empty($product_category)) {
        $errors[] = '请选择产品类别';
    }
    if ($product_is_poster == 1 && empty($final_poster_image_url)) {
        $errors[] = '标记为海报的产品必须上传海报图片';
    }

    if (empty($errors)) {
        if ($is_edit) {
            $old_product = $wpdb->get_row($wpdb->prepare("SELECT image_url, poster_image_url FROM `{$table_name}` WHERE id = %d", $edit_id), ARRAY_A);

            $update_data = array(
                'name' => $product_name,
                'description' => $product_description,
                'price' => $product_price,
                'image_url' => $final_image_url,
                'url' => $product_url,
                'category' => $product_category,
                'is_new' => $product_is_new,
                'is_poster' => $product_is_poster,
                'poster_image_url' => $final_poster_image_url,
            );

            $result = $wpdb->update($table_name, $update_data, array('id' => $edit_id));

            if ($result !== false) {
                // 清理被替换或移除的旧图
                if ($old_product) {
                    if (!empty($old_product['image_url']) && $old_product['image_url'] !== $final_image_url) {
                        goods_exhibition_maybe_delete_media_attachment($old_product['image_url']);
                    }
                    if (!empty($old_product['poster_image_url']) && $old_product['poster_image_url'] !== $final_poster_image_url) {
                        goods_exhibition_maybe_delete_media_attachment($old_product['poster_image_url']);
                    }
                }
                goods_exhibition_flush_cache();
                goods_exhibition_admin_redirect('updated');
            } else {
                goods_exhibition_admin_notice('error', '更新产品失败，请重试。');
            }
        } else {
            // 获取当前分类的最大排序值
            $max_sort = (int) $wpdb->get_var($wpdb->prepare(
                "SELECT MAX(sort_order) FROM `{$table_name}` WHERE category = %s",
                $product_category
            ));

            $insert_data = array(
                'name' => $product_name,
                'description' => $product_description,
                'price' => $product_price,
                'image_url' => $final_image_url,
                'url' => $product_url,
                'category' => $product_category,
                'is_new' => $product_is_new,
                'is_poster' => $product_is_poster,
                'poster_image_url' => $final_poster_image_url,
                'sort_order' => $max_sort + 1,
            );

            $result = $wpdb->insert($table_name, $insert_data);

            if ($result) {
                $new_id = (int) $wpdb->insert_id;
                goods_exhibition_flush_cache();
                // 成功后跳转到产品列表页
                wp_safe_redirect(add_query_arg(
                    array('page' => 'goods-exhibition', 'tab' => 'list', 'goods_msg' => 'added', 'new_id' => $new_id),
                    admin_url('admin.php')
                ));
                exit;
            } else {
                goods_exhibition_admin_notice('error', '添加产品失败，请重试。');
            }
        }
    } else {
        foreach ($errors as $error) {
            goods_exhibition_admin_notice('error', $error);
        }
    }
}

/**
 * 分类管理页面动作（POST 添加/编辑，GET 删除）
 */
function goods_exhibition_handle_category_actions()
{
    global $wpdb;
    $categories_table = $wpdb->prefix . 'goods_exhibition_categories';
    $products_table = $wpdb->prefix . 'goods_exhibition';

    // 添加新分类
    if (isset($_POST['goods_exhibition_add_category'])) {
        check_admin_referer('goods_exhibition_category_action');
        $new_name = isset($_POST['category_name']) ? trim(sanitize_text_field($_POST['category_name'])) : '';
        if (!empty($new_name)) {
            $exists = (int) $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM `{$categories_table}` WHERE name = %s", $new_name));
            if ($exists > 0) {
                goods_exhibition_admin_redirect('cat_duplicate', array('cat_name' => $new_name));
            } else {
                $max_sort = (int) $wpdb->get_var("SELECT MAX(sort_order) FROM `{$categories_table}`");
                $wpdb->insert($categories_table, array('name' => $new_name, 'sort_order' => $max_sort + 1));
                goods_exhibition_admin_redirect('cat_added', array('cat_name' => $new_name));
            }
        } else {
            goods_exhibition_admin_notice('error', '分类名称不能为空。');
        }
    }

    // 删除分类
    if (isset($_GET['action']) && $_GET['action'] === 'delete_cat' && isset($_GET['cat_id']) && isset($_GET['_wpnonce'])) {
        $cat_id = intval($_GET['cat_id']);
        if (wp_verify_nonce($_GET['_wpnonce'], 'delete_category_' . $cat_id)) {
            $cat_name = $wpdb->get_var($wpdb->prepare("SELECT name FROM `{$categories_table}` WHERE id = %d", $cat_id));
            if ($cat_name) {
                $product_count = (int) $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM `{$products_table}` WHERE category = %s", $cat_name));
                if ($product_count > 0) {
                    goods_exhibition_admin_redirect('cat_not_empty', array('cat_name' => $cat_name, 'cnt' => $product_count));
                } else {
                    $wpdb->delete($categories_table, array('id' => $cat_id), array('%d'));
                    goods_exhibition_admin_redirect('cat_deleted');
                }
            }
        } else {
            goods_exhibition_admin_notice('error', '安全验证失败。');
        }
    }

    // 编辑分类
    if (isset($_POST['goods_exhibition_edit_category'])) {
        check_admin_referer('goods_exhibition_category_action');
        $edit_id = isset($_POST['edit_cat_id']) ? intval($_POST['edit_cat_id']) : 0;
        $edit_name = isset($_POST['edit_category_name']) ? trim(sanitize_text_field($_POST['edit_category_name'])) : '';
        if (!empty($edit_name) && $edit_id > 0) {
            $conflict = (int) $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM `{$categories_table}` WHERE name = %s AND id != %d",
                $edit_name, $edit_id
            ));
            if ($conflict > 0) {
                goods_exhibition_admin_redirect('cat_duplicate', array('cat_name' => $edit_name));
            } else {
                $old_name = $wpdb->get_var($wpdb->prepare("SELECT name FROM `{$categories_table}` WHERE id = %d", $edit_id));
                $wpdb->update($categories_table, array('name' => $edit_name), array('id' => $edit_id));
                $migrated = 0;
                if ($old_name !== $edit_name) {
                    $migrated = (int) $wpdb->query($wpdb->prepare(
                        "UPDATE `{$products_table}` SET category = %s WHERE category = %s",
                        $edit_name, $old_name
                    ));
                    goods_exhibition_flush_cache();
                }
                goods_exhibition_admin_redirect('cat_updated', array('cat_name' => $edit_name, 'cnt' => $migrated));
            }
        }
    }
}

/**
 * 渲染管理页面头部：品牌标题 + 使用帮助 + 下划线 Tab 导航
 */
function goods_exhibition_render_admin_header($current_tab)
{
    ?>
    <div class="wrap goods-exhibition-app">
        <!-- 页面顶部品牌区 -->
        <header class="ge-admin-header">
            <div class="ge-brand">
                <span class="ge-brand-icon">
                    <svg viewBox="0 0 24 24" width="22" height="22" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                        <polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline>
                        <line x1="12" y1="22.08" x2="12" y2="12"></line>
                    </svg>
                </span>
                <h1 class="ge-brand-title">好物页面管理插件</h1>
            </div>
            <div class="ge-header-actions">
                <button type="button" class="ge-btn-help" id="ge-open-help-modal" title="查看使用帮助与说明">
                    <?php echo goods_exhibition_get_icon('help'); ?>
                    <span>使用帮助</span>
                </button>
            </div>
        </header>

        <!-- 下划线 Tab 导航 (3个主Tab，添加新产品已移至产品列表顶部主按钮弹窗) -->
        <nav class="ge-tabs">
            <a href="<?php echo admin_url('admin.php?page=goods-exhibition&tab=list'); ?>"
               class="ge-tab <?php echo ($current_tab === 'list' || $current_tab === 'add') ? 'is-active' : ''; ?>">产品列表</a>
            <a href="<?php echo admin_url('admin.php?page=goods-exhibition&tab=categories'); ?>"
               class="ge-tab <?php echo $current_tab === 'categories' ? 'is-active' : ''; ?>">分类管理</a>
            <a href="<?php echo admin_url('admin.php?page=goods-exhibition&tab=posters'); ?>"
               class="ge-tab <?php echo $current_tab === 'posters' ? 'is-active' : ''; ?>">海报排序</a>
        </nav>

        <!-- 通用二次确认删除弹窗 -->
        <div id="goods-confirm-modal" class="ge-modal" style="display:none;">
            <div class="ge-modal-overlay"></div>
            <div class="ge-modal-dialog">
                <div class="ge-modal-header">
                    <h3 id="goods-confirm-title" class="ge-modal-title">确认删除</h3>
                    <button type="button" class="ge-modal-close goods-confirm-cancel">&times;</button>
                </div>
                <div id="goods-confirm-body" class="ge-modal-body"></div>
                <div class="ge-modal-footer">
                    <button type="button" class="ge-btn ge-btn-secondary goods-confirm-cancel">取消</button>
                    <button type="button" class="ge-btn ge-btn-danger goods-confirm-ok">确认删除</button>
                </div>
            </div>
        </div>

        <!-- 图片大图预览弹窗 -->
        <div id="goods-preview-modal" class="ge-modal" style="display:none;">
            <div class="ge-modal-overlay"></div>
            <div class="ge-modal-dialog ge-modal-preview">
                <div class="ge-modal-header">
                    <h3 id="goods-preview-title" class="ge-modal-title">海报预览</h3>
                    <button type="button" class="ge-modal-close goods-preview-close">&times;</button>
                </div>
                <div class="ge-modal-body ge-preview-body">
                    <img id="goods-preview-img" src="" alt="预览大图">
                </div>
            </div>
        </div>

        <!-- 使用帮助与说明弹窗 -->
        <div id="goods-help-modal" class="ge-modal" style="display:none;">
            <div class="ge-modal-overlay"></div>
            <div class="ge-modal-dialog ge-modal-help-dialog">
                <div class="ge-modal-header">
                    <h3 class="ge-modal-title">使用帮助与说明</h3>
                    <button type="button" class="ge-modal-close goods-help-modal-close" aria-label="关闭">&times;</button>
                </div>
                <div class="ge-modal-body ge-help-modal-body">
                    <div class="ge-help-section">
                        <h4 class="ge-help-section-title">1. 快速接入</h4>
                        <p class="ge-help-section-text">在任何页面、文章或自定义区块中插入短代码：</p>
                        <div class="ge-help-code-pill">
                            <code>[goods_exhibition]</code>
                        </div>
                    </div>

                    <div class="ge-help-section">
                        <h4 class="ge-help-section-title">2. 首页海报尺寸规范</h4>
                        <p class="ge-help-section-text">建议上传宽高比为 16:6（如 1920×720 像素）的高清海报图片。移动端与桌面端保持一致比例，按规范尺寸上传两端均可完整展示无黑边裁切。</p>
                    </div>

                    <div class="ge-help-section">
                        <h4 class="ge-help-section-title">3. 分类拖拽排序与生效机制</h4>
                        <p class="ge-help-section-text">在「分类管理」中拖拽分类的顺序，松开后自动保存，前台横向滚动筛选栏会实时同步标签展示顺序。</p>
                    </div>
                </div>
                <div class="ge-modal-footer ge-help-modal-footer">
                    <button type="button" class="ge-btn ge-btn-primary goods-help-modal-close ge-btn-know">我知道了</button>
                </div>
            </div>
        </div>
    <?php
}

/**
 * 产品列表 Tab 内容 (效果图 1 现代卡片化重构)
 */
function goods_exhibition_render_product_list_tab()
{
    goods_exhibition_render_admin_notices();

    global $wpdb;
    $table_name = $wpdb->prefix . 'goods_exhibition';

    $search = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';
    $filter_category = isset($_GET['category']) ? sanitize_text_field($_GET['category']) : '';

    $where_clauses = array();
    $where_values = array();

    if (!empty($search)) {
        $where_clauses[] = "(name LIKE %s OR description LIKE %s)";
        $like = '%' . $wpdb->esc_like($search) . '%';
        $where_values[] = $like;
        $where_values[] = $like;
    }

    if (!empty($filter_category)) {
        $where_clauses[] = "category = %s";
        $where_values[] = $filter_category;
    }

    $where_sql = '';
    if (!empty($where_clauses)) {
        $where_sql = 'WHERE ' . implode(' AND ', $where_clauses);
    }

    $current_page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
    $per_page = GOODS_EXHIBITION_PER_PAGE;
    $offset = ($current_page - 1) * $per_page;

    $is_category_filtered = !empty($filter_category);
    $order_by = $is_category_filtered ? "ORDER BY sort_order ASC, id DESC" : "ORDER BY id DESC";

    if (!empty($where_values)) {
        $count_query = $wpdb->prepare("SELECT COUNT(*) FROM `{$table_name}` {$where_sql}", ...$where_values);
        $total_items = (int) $wpdb->get_var($count_query);

        $query_values = array_merge($where_values, array($per_page, $offset));
        $products = $wpdb->get_results(
            $wpdb->prepare("SELECT * FROM `{$table_name}` {$where_sql} {$order_by} LIMIT %d OFFSET %d", ...$query_values),
            ARRAY_A
        );
    } else {
        $total_items = (int) $wpdb->get_var("SELECT COUNT(*) FROM `{$table_name}`");
        $products = $wpdb->get_results(
            $wpdb->prepare("SELECT * FROM `{$table_name}` {$order_by} LIMIT %d OFFSET %d", $per_page, $offset),
            ARRAY_A
        );
    }

    $total_pages = max(1, ceil($total_items / $per_page));

    // 获取所有类别
    $categories_table = $wpdb->prefix . 'goods_exhibition_categories';
    $all_categories = $wpdb->get_col("SELECT name FROM `{$categories_table}` ORDER BY sort_order ASC, name ASC");

    // 分页参数
    $base_url_params = array('page' => 'goods-exhibition', 'tab' => 'list');
    if (!empty($search)) $base_url_params['s'] = $search;
    if (!empty($filter_category)) $base_url_params['category'] = $filter_category;
    $page_base_url = admin_url('admin.php?' . http_build_query($base_url_params));

    // 检测是否需要自动弹出产品编辑/添加弹窗
    $auto_open_product = null;
    $auto_open_action = '';
    if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['product_id'])) {
        $auto_id = intval($_GET['product_id']);
        $auto_open_product = $wpdb->get_row($wpdb->prepare("SELECT * FROM `{$table_name}` WHERE id = %d", $auto_id), ARRAY_A);
        if ($auto_open_product) {
            $auto_open_action = 'edit';
        }
    } elseif ((isset($_GET['tab']) && $_GET['tab'] === 'add') || (isset($_GET['action']) && $_GET['action'] === 'add')) {
        $auto_open_action = 'add';
    }
    ?>
    <div class="ge-tab-content ge-list-view">

        <?php if ($auto_open_action === 'edit' && !empty($auto_open_product)): ?>
            <div id="ge-auto-open-modal" data-action="edit" data-product="<?php echo esc_attr(wp_json_encode($auto_open_product)); ?>"></div>
        <?php elseif ($auto_open_action === 'add'): ?>
            <div id="ge-auto-open-modal" data-action="add"></div>
        <?php endif; ?>

        <!-- 顶部工具卡片 -->
        <div class="ge-toolbar-card">
            <!-- 工具栏第一行：左侧[添加新产品 + 分类菜单 + 筛选] 与 右侧[搜索框 + 产品总数 + 分页]，两者保持在同一行，不换行！ -->
            <form method="get" class="ge-toolbar-row ge-toolbar-single-row">
                <input type="hidden" name="page" value="goods-exhibition">
                <input type="hidden" name="tab" value="list">

                <!-- 左侧：添加新产品主按钮 + 分类选择 + 紧跟筛选 -->
                <div class="ge-filter-left">
                    <button type="button" class="ge-btn ge-btn-primary" id="ge-open-add-modal">
                        <?php echo goods_exhibition_get_icon('plus'); ?>
                        <span>添加新产品</span>
                    </button>

                    <div class="ge-select-wrapper ge-category-select-wrapper">
                        <select name="category" class="ge-select ge-filter-select">
                            <option value="">所有类别</option>
                            <?php foreach ($all_categories as $cat): ?>
                                <option value="<?php echo esc_attr($cat); ?>" <?php selected($filter_category, $cat); ?>><?php echo esc_html($cat); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <button type="submit" class="ge-btn ge-btn-secondary ge-btn-filter">
                        <?php echo goods_exhibition_get_icon('filter'); ?>
                        <span>筛选</span>
                    </button>

                    <?php if (!empty($search) || !empty($filter_category)): ?>
                        <a href="<?php echo admin_url('admin.php?page=goods-exhibition&tab=list'); ?>" class="ge-btn ge-btn-secondary ge-btn-clear">清除筛选</a>
                    <?php endif; ?>
                </div>

                <!-- 右侧：搜索框位于「共 29 个产品」之前 -->
                <div class="ge-filter-right">
                    <div class="ge-search-input-wrapper">
                        <input type="search" name="s" class="ge-input ge-search-input" placeholder="搜索产品名称或描述..."
                            value="<?php echo esc_attr($search); ?>">
                        <span class="ge-search-icon">
                            <?php echo goods_exhibition_get_icon('search'); ?>
                        </span>
                    </div>

                    <span class="ge-total-count">共 <strong><?php echo $total_items; ?></strong> 个产品</span>

                    <?php if ($total_pages > 1): ?>
                        <div class="ge-mini-pagination">
                            <?php
                            $prev_url = $current_page > 1 ? $page_base_url . '&paged=' . ($current_page - 1) : '';
                            $next_url = $current_page < $total_pages ? $page_base_url . '&paged=' . ($current_page + 1) : '';
                            ?>
                            <?php if ($prev_url): ?>
                                <a href="<?php echo esc_url($prev_url); ?>" class="ge-page-btn" title="上一页">‹</a>
                            <?php else: ?>
                                <span class="ge-page-btn is-disabled">‹</span>
                            <?php endif; ?>

                            <span class="ge-page-indicator"><?php echo $current_page; ?> / <?php echo $total_pages; ?></span>

                            <?php if ($next_url): ?>
                                <a href="<?php echo esc_url($next_url); ?>" class="ge-page-btn" title="下一页">›</a>
                            <?php else: ?>
                                <span class="ge-page-btn is-disabled">›</span>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </form>

            <!-- 工具栏第二行：批量操作栏 (参考效果图 3: [批量删除] [批量移动]) -->
            <div class="ge-toolbar-subrow">
                <div class="ge-bulk-actions">
                    <button type="button" class="ge-btn ge-btn-secondary ge-btn-sm goods-exhibition-bulk-delete-btn" disabled>
                        <?php echo goods_exhibition_get_icon('delete'); ?>
                        <span>批量删除</span>
                    </button>
                    <button type="button" id="goods-bulk-move-open-btn" class="ge-btn ge-btn-secondary ge-btn-sm goods-exhibition-bulk-move-btn" disabled>
                        <?php echo goods_exhibition_get_icon('branch'); ?>
                        <span>批量移动</span>
                    </button>
                    <span class="ge-selected-count goods-exhibition-selected-count"></span>
                </div>

                <?php if ($is_category_filtered): ?>
                    <div class="ge-sort-hint">
                        <?php echo goods_exhibition_get_icon('sort'); ?>
                        <span>拖动行左侧图标可调整顺序（松开后自动保存）</span>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <?php if (empty($products) && $current_page === 1): ?>
            <div class="ge-card ge-empty-card">
                <?php if (!empty($search) || !empty($filter_category)): ?>
                    <p>没有找到匹配的产品。<a href="<?php echo admin_url('admin.php?page=goods-exhibition&tab=list'); ?>">查看所有产品</a></p>
                <?php else: ?>
                    <p>还没有添加任何产品。<button type="button" class="ge-btn ge-btn-primary ge-btn-sm" id="ge-empty-add-btn" style="margin-left: 8px;">添加一个新产品</button></p>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <form method="post" id="goods-exhibition-bulk-form">
                <?php wp_nonce_field('goods_exhibition_bulk_delete', '_wpnonce_bulk'); ?>
                <input type="hidden" name="goods_exhibition_bulk_action" value="delete">

                <div class="ge-table-card">
                    <table class="ge-table goods-exhibition-table" <?php echo $is_category_filtered ? 'data-sortable="1"' : ''; ?>>
                        <thead>
                            <tr>
                                <th class="col-cb"><input type="checkbox" id="goods-cb-select-all" class="ge-checkbox"></th>
                                <?php if ($is_category_filtered): ?>
                                    <th class="col-sort" style="width: 32px;"></th>
                                <?php endif; ?>
                                <th class="col-thumb">图片</th>
                                <th class="col-name">名称</th>
                                <th class="col-desc">描述</th>
                                <th class="col-price">价格 <?php echo goods_exhibition_get_icon('sort'); ?></th>
                                <th class="col-category">类型</th>
                                <th class="col-tags">标记</th>
                                <th class="col-link">链接</th>
                                <th class="col-date">添加日期</th>
                                <th class="col-actions">操作</th>
                            </tr>
                        </thead>
                        <tbody id="goods-sortable-body">
                            <?php foreach ($products as $product): ?>
                                <tr data-id="<?php echo esc_attr($product['id']); ?>">
                                    <td class="col-cb">
                                        <input type="checkbox" name="product_ids[]" value="<?php echo esc_attr($product['id']); ?>" data-category="<?php echo esc_attr($product['category']); ?>" class="ge-checkbox goods-cb-item">
                                    </td>
                                    <?php if ($is_category_filtered): ?>
                                        <td class="col-sort">
                                            <span class="ge-drag-handle goods-drag-handle" title="按住拖动排序"><?php echo goods_exhibition_get_icon('handle'); ?></span>
                                        </td>
                                    <?php endif; ?>
                                    <td class="col-thumb">
                                        <?php if (!empty($product['image_url'])): ?>
                                            <img src="<?php echo esc_url($product['image_url']); ?>"
                                                alt="<?php echo esc_attr($product['name']); ?>" class="ge-thumb-img" loading="lazy">
                                        <?php else: ?>
                                            <span class="ge-no-thumb">无图</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="col-name">
                                        <strong><?php echo esc_html($product['name']); ?></strong>
                                    </td>
                                    <td class="col-desc"><?php
                                        if (function_exists('mb_strimwidth')) {
                                            echo esc_html(mb_strimwidth($product['description'], 0, 48, '...'));
                                        } else {
                                            echo esc_html(wp_html_excerpt($product['description'], 48, '...'));
                                        }
                                    ?></td>
                                    <td class="col-price"><?php echo esc_html($product['price'] ? goods_exhibition_format_price_display($product['price']) : '—'); ?></td>
                                    <td class="col-category">
                                        <?php if (!empty($product['category'])): ?>
                                            <span class="ge-badge-pill ge-badge-cat"><?php echo esc_html($product['category']); ?></span>
                                        <?php else: ?>
                                            —
                                        <?php endif; ?>
                                    </td>
                                    <td class="col-tags">
                                        <?php if ($product['is_new'] == 1): ?>
                                            <span class="ge-badge-pill ge-badge-new">NEW</span>
                                        <?php endif; ?>
                                        <?php if ($product['is_poster'] == 1): ?>
                                            <span class="ge-badge-pill ge-badge-poster">海报</span>
                                        <?php endif; ?>
                                        <?php if ($product['is_new'] != 1 && $product['is_poster'] != 1): ?>
                                            <span class="ge-text-muted">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="col-link">
                                        <?php if (!empty($product['url'])): ?>
                                            <a href="<?php echo esc_url($product['url']); ?>" target="_blank" class="ge-link-icon" title="<?php echo esc_attr($product['url']); ?>">
                                                <?php echo goods_exhibition_get_icon('link'); ?>
                                            </a>
                                        <?php else: ?>
                                            <span class="ge-text-muted">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="col-date"><?php echo date_i18n('Y/m/d', strtotime($product['created_at'])); ?></td>
                                    <td class="col-actions">
                                        <div class="ge-action-buttons">
                                            <button type="button"
                                                    class="ge-btn-icon ge-btn-edit ge-row-edit-btn"
                                                    data-id="<?php echo esc_attr($product['id']); ?>"
                                                    data-product="<?php echo esc_attr(wp_json_encode($product)); ?>"
                                                    title="编辑产品" aria-label="编辑产品">
                                                <?php echo goods_exhibition_get_icon('edit'); ?>
                                            </button>
                                            <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=goods-exhibition&tab=list&action=delete&product_id=' . $product['id']), 'delete_product_' . $product['id']); ?>"
                                               class="ge-btn-icon ge-btn-delete goods-confirm-link"
                                               data-confirm-type="product"
                                               data-confirm-name="<?php echo esc_attr($product['name']); ?>"
                                               title="删除" aria-label="删除">
                                                <?php echo goods_exhibition_get_icon('delete'); ?>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </form>
        <?php endif; ?>

        <!-- 添加/编辑产品模态弹窗 (Window Modal) -->
        <div id="goods-product-modal" class="ge-modal" style="display:none;">
            <div class="ge-modal-overlay"></div>
            <div class="ge-modal-dialog ge-modal-product-dialog">
                <div class="ge-modal-header">
                    <h3 id="goods-product-modal-title" class="ge-modal-title">添加新产品</h3>
                    <button type="button" class="ge-modal-close goods-product-modal-close" aria-label="关闭">&times;</button>
                </div>

                <form method="post" enctype="multipart/form-data" id="goods-exhibition-product-form" class="ge-modal-form"
                      action="<?php echo admin_url('admin.php?page=goods-exhibition&tab=list'); ?>">
                    <?php wp_nonce_field('goods_exhibition_add_product'); ?>
                    <input type="hidden" name="goods_exhibition_submit" value="1">
                    <input type="hidden" name="product_id" id="product_id" value="0">

                    <div class="ge-modal-body ge-modal-scrollable">
                        <!-- 产品名称 (0/50 计数) -->
                        <div class="ge-form-group">
                            <label for="product_name" class="ge-label">产品名称 <span class="ge-required">*</span></label>
                            <div class="ge-input-with-counter">
                                <input type="text" name="product_name" id="product_name" class="ge-input"
                                    required placeholder="请输入产品名称（建议20字以内）" maxlength="50">
                                <span class="ge-counter" id="product_name_counter">0/50</span>
                            </div>
                            <p class="ge-help-text">卡片封面最多展示2行，超出部分将自动截断并支持前端悬停及详情弹窗查看完整全称。</p>
                        </div>

                        <!-- 产品描述 (0/500 计数) -->
                        <div class="ge-form-group">
                            <label for="product_description" class="ge-label">产品描述 / 核心卖点 <span class="ge-required">*</span></label>
                            <div class="ge-textarea-with-counter">
                                <textarea name="product_description" id="product_description" class="ge-textarea" rows="4"
                                    required placeholder="请输入产品核心卖点或详细介绍（卡片封面展示前两行核心卖点，建议20~35字；完整介绍将在前端详情弹窗中展现）" maxlength="500"></textarea>
                                <span class="ge-counter" id="product_desc_counter">0/500</span>
                            </div>
                            <p class="ge-help-text">卡片封面最多展示前2行核心卖点（约30字）；超出文字将在前端详情弹窗中完整展现，无需担心文字过多溢出。</p>
                        </div>

                        <!-- 产品价格 -->
                        <div class="ge-form-group">
                            <label for="product_price" class="ge-label">产品价格</label>
                            <div class="ge-input-group ge-price-input-group">
                                <div class="ge-currency-select-wrapper">
                                    <select name="product_currency" id="product_currency" class="ge-currency-select" aria-label="货币符号">
                                        <option value="¥">¥ (人民币)</option>
                                        <option value="$">$ (美元)</option>
                                        <option value="HK$">HK$ (港币)</option>
                                        <option value="NT$">NT$ (新台币)</option>
                                        <option value="€">€ (欧元)</option>
                                        <option value="£">£ (英镑)</option>
                                        <option value="₩">₩ (韩元)</option>
                                        <option value="">无 (自定义)</option>
                                    </select>
                                </div>
                                <input type="text" name="product_price" id="product_price" class="ge-input"
                                    placeholder="请输入金额（如：10999起 或 4,199 起）">
                            </div>
                            <p class="ge-help-text">可选择常用货币符号；金额框内支持填写说明，如 "4,199 起（含教育优惠）"</p>
                        </div>

                        <!-- 跳转链接 -->
                        <div class="ge-form-group">
                            <label for="product_url" class="ge-label">跳转链接</label>
                            <input type="url" name="product_url" id="product_url" class="ge-input"
                                placeholder="请输入完整的商品链接（https://...）">
                            <p class="ge-help-text">可选。如果设置了链接，点击产品卡片将跳转到此链接。留空则不跳转。</p>
                        </div>

                        <!-- 产品类别 -->
                        <div class="ge-form-group">
                            <label for="product_category" class="ge-label">产品类别 <span class="ge-required">*</span></label>
                            <div class="ge-select-wrapper">
                                <select name="product_category" id="product_category" class="ge-select" required>
                                    <option value="">请选择类别</option>
                                    <?php foreach ($all_categories as $cat): ?>
                                        <option value="<?php echo esc_attr($cat); ?>"><?php echo esc_html($cat); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <p class="ge-help-text">请选择产品类别。如需添加新类别，请前往「<a href="<?php echo admin_url('admin.php?page=goods-exhibition&tab=categories'); ?>">分类管理</a>」。</p>
                        </div>

                        <!-- 产品图片 (Dropzone 区域) -->
                        <div class="ge-form-group">
                            <label class="ge-label">产品图片 <span class="ge-required">*</span></label>
                            <div class="ge-dropzone product-image-dropzone" id="product-image-dropzone">
                                <input type="hidden" name="product_image_url" id="product_image_url" value="">
                                <input type="file" name="product_image" id="product_image" accept="image/*" style="display:none;">

                                <div class="ge-dropzone-content">
                                    <span class="ge-dropzone-icon">
                                        <?php echo goods_exhibition_get_icon('upload'); ?>
                                    </span>
                                    <div class="ge-dropzone-text">点击上传图片</div>
                                </div>

                                <div class="ge-dropzone-preview product-image-preview is-hidden">
                                    <div class="ge-preview-actions">
                                        <button type="button" class="ge-btn ge-btn-sm ge-btn-secondary ge-replace-img-btn" id="upload_image_button">更换图片</button>
                                        <button type="button" class="ge-btn ge-btn-sm ge-btn-danger-outline goods-image-delete-btn"
                                            data-target="#product_image_url" data-preview=".product-image-preview" data-dropzone="#product-image-dropzone"
                                            data-file="#product_image" data-label="产品图片" data-required="1">删除图片</button>
                                    </div>
                                </div>
                            </div>
                            <p class="ge-help-text">推荐图片尺寸：720x720像素（1:1正方形），图片将等比缩放显示在卡片中</p>
                        </div>

                        <!-- 标记为新产品 -->
                        <div class="ge-form-group ge-form-check">
                            <label class="ge-checkbox-label">
                                <input type="checkbox" name="product_is_new" id="product_is_new" value="1" class="ge-checkbox">
                                <span class="ge-checkbox-text">将此产品标记为"新"</span>
                            </label>
                            <p class="ge-help-text">勾选后，产品卡片上会显示"New"标签。</p>
                        </div>

                        <!-- 标记为海报 -->
                        <div class="ge-form-group ge-form-check">
                            <label class="ge-checkbox-label">
                                <input type="checkbox" name="product_is_poster" id="product_is_poster" value="1" class="ge-checkbox">
                                <span class="ge-checkbox-text">勾选后，海报将在首页滚动显示</span>
                            </label>
                        </div>

                        <!-- 海报图片 (Dropzone 区域) -->
                        <div class="ge-form-group ge-poster-upload-section">
                            <label class="ge-label">海报图片</label>
                            <div class="ge-dropzone poster-image-dropzone" id="poster-image-dropzone">
                                <input type="hidden" name="poster_image_url" id="poster_image_url" value="">
                                <input type="file" name="poster_image" id="poster_image" accept="image/*" style="display:none;">

                                <div class="ge-dropzone-content">
                                    <span class="ge-dropzone-icon">
                                        <?php echo goods_exhibition_get_icon('upload'); ?>
                                    </span>
                                    <div class="ge-dropzone-text">点击上传海报图</div>
                                </div>

                                <div class="ge-dropzone-preview poster-image-preview ge-preview-wide is-hidden">
                                    <div class="ge-preview-actions">
                                        <button type="button" class="ge-btn ge-btn-sm ge-btn-secondary ge-replace-img-btn" id="upload_poster_image_button">更换海报图</button>
                                        <button type="button" class="ge-btn ge-btn-sm ge-btn-danger-outline goods-image-delete-btn"
                                            data-target="#poster_image_url" data-preview=".poster-image-preview" data-dropzone="#poster-image-dropzone"
                                            data-file="#poster_image" data-label="海报图片">删除图片</button>
                                    </div>
                                </div>
                            </div>
                            <p class="ge-help-text">推荐图片尺寸：1920x720像素（比例16:6），图片将自动裁切填满展示区域。</p>
                        </div>

                        <!-- 底部保存安全提示 -->
                        <div class="ge-alert-banner ge-alert-bottom">
                            <?php echo goods_exhibition_get_icon('info'); ?>
                            <span>当前修改（含图片的新增、更换、移除）需点击保存后才会生效；未保存前关闭窗口，不会修改任何数据。</span>
                        </div>
                    </div>

                    <div class="ge-modal-footer">
                        <button type="button" class="ge-btn ge-btn-secondary ge-btn-lg goods-product-modal-close">取消</button>
                        <button type="submit" name="goods_exhibition_submit" id="goods-product-modal-submit" class="ge-btn ge-btn-primary ge-btn-lg">添加产品</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- 删除图片二次确认弹窗 -->
        <div id="goods-image-delete-modal" class="ge-modal" style="display:none;">
            <div class="ge-modal-overlay"></div>
            <div class="ge-modal-dialog">
                <div class="ge-modal-header">
                    <h3 id="goods-image-delete-title" class="ge-modal-title">删除图片</h3>
                    <button type="button" class="ge-modal-close goods-image-modal-close">&times;</button>
                </div>
                <div id="goods-image-delete-body" class="ge-modal-body"></div>
                <div class="ge-modal-footer">
                    <button type="button" class="ge-btn ge-btn-secondary goods-image-modal-close">取消</button>
                    <button type="button" class="ge-btn ge-btn-danger goods-image-delete-confirm">确认删除</button>
                </div>
            </div>
        </div>

        <!-- 批量移动分类模态弹窗 (参考效果图 2) -->
        <div id="goods-bulk-move-modal" class="ge-modal" style="display:none;">
            <div class="ge-modal-overlay"></div>
            <div class="ge-modal-dialog ge-modal-bulk-move-dialog">
                <div class="ge-modal-header">
                    <h3 class="ge-modal-title">批量移动分类</h3>
                    <button type="button" class="ge-modal-close goods-bulk-move-close" aria-label="关闭">&times;</button>
                </div>
                <div class="ge-modal-body ge-bulk-move-body">
                    <div class="ge-bulk-move-count-text">
                        已选择 <span id="goods-bulk-move-modal-count" class="ge-bulk-move-highlight">0</span> 个产品
                    </div>

                    <!-- 源分类（将被替换） -->
                    <div class="ge-form-group">
                        <label for="goods-bulk-move-source-cat" class="ge-label">源分类（将被替换）</label>
                        <div class="ge-select-wrapper ge-select-full">
                            <select id="goods-bulk-move-source-cat" class="ge-select">
                                <option value="all">全部</option>
                            </select>
                        </div>
                        <p class="ge-help-text">选项来自当前勾选产品所挂的分类。选择具体分类：仅替换该分类，产品的其他分类保留；选择"全部"：直接覆盖所选产品的全部分类。</p>
                    </div>

                    <!-- 目标分类 -->
                    <div class="ge-form-group">
                        <label for="goods-bulk-move-target-cat" class="ge-label">目标分类</label>
                        <div class="ge-select-wrapper ge-select-full">
                            <select id="goods-bulk-move-target-cat" class="ge-select">
                                <option value="">请选择目标分类</option>
                                <?php foreach ($all_categories as $cat): ?>
                                    <option value="<?php echo esc_attr($cat); ?>"><?php echo esc_html($cat); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <p class="ge-help-text">选项与「分类设置」中的筛选分类实时同步。</p>
                    </div>
                </div>
                <div class="ge-modal-footer ge-bulk-move-footer">
                    <button type="button" id="goods-bulk-move-confirm-btn" class="ge-btn ge-btn-primary">确认移动</button>
                    <button type="button" class="ge-btn ge-btn-secondary goods-bulk-move-close">取消</button>
                </div>
            </div>
        </div>

    </div>
    <?php
}

/**
 * 添加新产品已升级为模态窗口展现（保留本函数以向下兼容）
 */
function goods_exhibition_render_add_product_tab()
{
    goods_exhibition_render_product_list_tab();
}

/**
 * 分类管理 Tab 内容 (效果图 2 现代双栏 Card 布局与实时检索)
 */
function goods_exhibition_render_categories_tab()
{
    global $wpdb;
    $categories_table = $wpdb->prefix . 'goods_exhibition_categories';
    $products_table = $wpdb->prefix . 'goods_exhibition';

    $table_exists = $wpdb->get_var("SHOW TABLES LIKE '{$categories_table}'");
    if (!$table_exists) {
        echo '<div class="ge-alert ge-alert-danger"><div class="ge-alert-content"><span>分类表不存在，请停用后重新激活插件。</span></div></div>';
        return;
    }

    goods_exhibition_render_admin_notices();

    $categories = $wpdb->get_results(
        "SELECT c.*, (SELECT COUNT(*) FROM `{$products_table}` WHERE category = c.name) as product_count
         FROM `{$categories_table}` c ORDER BY c.sort_order ASC, c.name ASC",
        ARRAY_A
    );
    ?>
    <div class="ge-tab-content ge-categories-layout">
        <!-- 左侧：添加新分类卡片 -->
        <div class="ge-card ge-cat-add-card">
            <div class="ge-card-header">
                <span class="ge-card-icon-badge">
                    <?php echo goods_exhibition_get_icon('plus'); ?>
                </span>
                <h3 class="ge-card-title">添加新分类</h3>
            </div>
            <form method="post" class="ge-form">
                <?php wp_nonce_field('goods_exhibition_category_action'); ?>
                <div class="ge-form-group">
                    <label for="category_name" class="ge-label">分类名称 <span class="ge-required">*</span></label>
                    <div class="ge-input-with-counter">
                        <input type="text" name="category_name" id="category_name" class="ge-input" required placeholder="例如：教育优惠" maxlength="20">
                        <span class="ge-counter" id="category_name_counter">0/20</span>
                    </div>
                    <p class="ge-help-text">新分类默认排在末尾，可在右侧列表中拖动调整顺序。</p>
                </div>
                <button type="submit" name="goods_exhibition_add_category" class="ge-btn ge-btn-primary ge-btn-block">
                    <?php echo goods_exhibition_get_icon('plus'); ?>
                    <span>添加分类</span>
                </button>
            </form>
        </div>

        <!-- 右侧：现有分类列表卡片 -->
        <div class="ge-card ge-cat-list-card">
            <div class="ge-card-header ge-between">
                <h3 class="ge-card-title">现有分类 (<span id="ge-cat-count"><?php echo count($categories); ?></span>)</h3>
                <div class="ge-search-input-wrapper ge-search-sm">
                    <input type="search" id="goods-cat-search-input" class="ge-input ge-input-sm" placeholder="搜索分类名称...">
                    <span class="ge-search-icon">
                        <?php echo goods_exhibition_get_icon('search'); ?>
                    </span>
                </div>
            </div>

            <div class="ge-alert-banner ge-cat-alert-banner">
                <div class="ge-cat-tip-item">
                    <span class="ge-cat-tip-icon"><?php echo goods_exhibition_get_icon('info'); ?></span>
                    <span><strong>排序说明：</strong>拖动分类左侧 ≡ 图标可自由调整展示顺序，松开后自动保存并实时同步前端展示。</span>
                </div>
                <div class="ge-cat-tip-item ge-cat-delete-tip">
                    <span class="ge-cat-tip-icon ge-icon-warn-color"><?php echo goods_exhibition_get_icon('delete'); ?></span>
                    <span><strong>删除规则：</strong>为防止误删商品，<strong>必须先清空或转移该分类下的所有商品后，才能进行删除</strong>；若分类下仍有商品，删除按钮将自动锁定不可用。</span>
                </div>
            </div>

            <div class="ge-table-wrapper">
                <table class="ge-table ge-cat-table">
                    <thead>
                        <tr>
                            <th style="width: 40px;"></th>
                            <th style="width: 60px;">序号</th>
                            <th>分类名称</th>
                            <th style="width: 90px;">产品数</th>
                            <th style="width: 100px;">操作</th>
                        </tr>
                    </thead>
                    <tbody id="goods-category-sort-body">
                        <?php if (empty($categories)): ?>
                            <tr class="ge-empty-row"><td colspan="5" class="ge-empty-td">暂无分类，请在左侧添加。</td></tr>
                        <?php else: ?>
                            <?php foreach ($categories as $index => $cat): ?>
                                <tr data-id="<?php echo esc_attr($cat['id']); ?>" data-name="<?php echo esc_attr($cat['name']); ?>">
                                    <td class="col-drag">
                                        <span class="ge-drag-handle goods-drag-handle" title="按住拖动排序"><?php echo goods_exhibition_get_icon('handle'); ?></span>
                                    </td>
                                    <td class="col-index goods-cat-sort-order"><?php echo $index + 1; ?></td>
                                    <td class="col-cat-name"><strong><?php echo esc_html($cat['name']); ?></strong></td>
                                    <td class="col-cat-count"><span class="ge-count-badge"><?php echo esc_html($cat['product_count']); ?></span></td>
                                    <td class="col-actions">
                                        <div class="ge-action-buttons">
                                            <button type="button" class="ge-btn-icon ge-btn-edit goods-cat-edit-link"
                                                    data-id="<?php echo esc_attr($cat['id']); ?>"
                                                    data-name="<?php echo esc_attr($cat['name']); ?>"
                                                    title="编辑" aria-label="编辑">
                                                <?php echo goods_exhibition_get_icon('edit'); ?>
                                            </button>
                                            <?php if ($cat['product_count'] == 0): ?>
                                                <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=goods-exhibition&tab=categories&action=delete_cat&cat_id=' . $cat['id']), 'delete_category_' . $cat['id']); ?>"
                                                   class="ge-btn-icon ge-btn-delete goods-confirm-link"
                                                   data-confirm-type="category"
                                                   data-confirm-name="<?php echo esc_attr($cat['name']); ?>"
                                                   title="删除" aria-label="删除">
                                                    <?php echo goods_exhibition_get_icon('delete'); ?>
                                                </a>
                                            <?php else: ?>
                                                <button type="button" class="ge-btn-icon ge-btn-delete is-disabled ge-cat-disabled-del"
                                                      title="该分类下仍有 <?php echo $cat['product_count']; ?> 个商品。必须先清空或转移分类目录下的所有商品，才能进行删除。"
                                                      data-count="<?php echo esc_attr($cat['product_count']); ?>"
                                                      data-name="<?php echo esc_attr($cat['name']); ?>"
                                                      aria-label="无法删除：分类下仍有商品">
                                                    <?php echo goods_exhibition_get_icon('delete'); ?>
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- 编辑分类弹窗 -->
    <div id="goods-cat-edit-modal" class="ge-modal" style="display:none;">
        <div class="ge-modal-overlay"></div>
        <div class="ge-modal-dialog">
            <div class="ge-modal-header">
                <h3 class="ge-modal-title">编辑分类</h3>
                <button type="button" class="ge-modal-close goods-cat-modal-close">&times;</button>
            </div>
            <form method="post" class="ge-form">
                <?php wp_nonce_field('goods_exhibition_category_action'); ?>
                <input type="hidden" name="edit_cat_id" id="edit_cat_id" value="">
                <div class="ge-modal-body">
                    <div class="ge-form-group">
                        <label for="edit_category_name" class="ge-label">分类名称 <span class="ge-required">*</span></label>
                        <div class="ge-input-with-counter">
                            <input type="text" name="edit_category_name" id="edit_category_name" class="ge-input" required maxlength="20">
                            <span class="ge-counter" id="edit_category_name_counter">0/20</span>
                        </div>
                        <p class="ge-help-text" style="margin-top: 8px;">
                            <?php echo goods_exhibition_get_icon('info'); ?>
                            <span>提示：修改后，原分类下的所有商品将<strong>自动同步并入新分类</strong>，无需手动调整。</span>
                        </p>
                    </div>
                </div>
                <div class="ge-modal-footer">
                    <button type="button" class="ge-btn ge-btn-secondary goods-cat-modal-close">取消</button>
                    <button type="submit" name="goods_exhibition_edit_category" class="ge-btn ge-btn-primary">保存</button>
                </div>
            </form>
        </div>
    </div>
    <?php
}

/**
 * 海报排序 Tab 内容 (效果图 3 现代立体卡片式排序)
 */
function goods_exhibition_render_poster_sort_tab()
{
    goods_exhibition_render_admin_notices();

    global $wpdb;
    $table_name = $wpdb->prefix . 'goods_exhibition';

    $display_limit = (int) GOODS_EXHIBITION_POSTER_LIMIT;

    $posters = $wpdb->get_results(
        "SELECT id, name, poster_image_url, category FROM `{$table_name}`
         WHERE is_poster = 1 AND poster_image_url != ''
         ORDER BY sort_order ASC, id DESC",
        ARRAY_A
    );
    $posters = goods_exhibition_apply_poster_order($posters);
    ?>
    <div class="ge-tab-content ge-poster-view">
        <div class="ge-card ge-poster-card">
            <div class="ge-card-header ge-between">
                <div class="ge-card-title-group">
                    <span class="ge-card-icon-primary">
                        <?php echo goods_exhibition_get_icon('layers'); ?>
                    </span>
                    <h3 class="ge-card-title">海报排序</h3>
                </div>
                <span class="ge-badge-pill ge-badge-primary">
                    当前已排序 <?php echo min(count($posters), $display_limit); ?> 张 / 最多 <?php echo $display_limit; ?> 张
                </span>
            </div>

            <p class="ge-card-subtitle">
                拖动下方海报可调整首页轮播顺序，前端最多展示 <strong><?php echo $display_limit; ?></strong> 张；将按此顺序取前 <strong><?php echo $display_limit; ?></strong> 张；未在自定义顺序中的新海报会自动排在末尾。
            </p>

            <div class="ge-alert-banner">
                <?php echo goods_exhibition_get_icon('info'); ?>
                <span class="goods-poster-sort-hint" data-limit="<?php echo esc_attr($display_limit); ?>">按住左侧手柄拖动即可排序，松开后自动保存。</span>
            </div>

            <?php if (empty($posters)): ?>
                <div class="ge-empty-state">
                    <p>暂无海报。请先在「添加新产品 / 编辑产品」中勾选「标记为海报」并设置海报图片。</p>
                </div>
            <?php else: ?>
                <div class="ge-poster-list" id="goods-poster-sort-list">
                    <?php foreach ($posters as $index => $poster): ?>
                        <div class="ge-poster-item <?php echo $index >= $display_limit ? 'is-overflow' : ''; ?>" data-id="<?php echo esc_attr($poster['id']); ?>">
                            <div class="ge-poster-left">
                                <span class="ge-drag-handle goods-drag-handle" title="按住拖动排序">
                                    <?php echo goods_exhibition_get_icon('handle'); ?>
                                </span>
                                <span class="ge-poster-index goods-poster-sort-order"><?php echo $index + 1; ?></span>
                                <div class="ge-poster-thumb goods-poster-sort-thumb" title="按住拖动排序">
                                    <img src="<?php echo esc_url($poster['poster_image_url']); ?>" alt="<?php echo esc_attr($poster['name']); ?>" loading="lazy">
                                </div>
                                <div class="ge-poster-info">
                                    <span class="ge-poster-title"><?php echo esc_html($poster['name']); ?></span>
                                    <?php if (!empty($poster['category'])): ?>
                                        <span class="ge-badge-pill ge-badge-cat"><?php echo esc_html($poster['category']); ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="ge-poster-right">
                                <?php if ($index >= $display_limit): ?>
                                    <span class="ge-badge-pill ge-badge-warning goods-poster-sort-badge">超出展示范围</span>
                                <?php endif; ?>
                                <button type="button" class="ge-btn-icon ge-btn-preview goods-poster-preview-btn"
                                        data-url="<?php echo esc_url($poster['poster_image_url']); ?>"
                                        data-title="<?php echo esc_attr($poster['name']); ?>"
                                        title="预览海报" aria-label="预览海报">
                                    <?php echo goods_exhibition_get_icon('eye'); ?>
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <p class="ge-sort-status goods-poster-sort-status"></p>
            <?php endif; ?>
        </div>
    </div>
    <?php
}

/**
 * 批量删除产品
 */
function goods_exhibition_bulk_delete_products($product_ids)
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'goods_exhibition';

    $product_ids = array_unique(array_filter(array_map('intval', (array) $product_ids)));
    if (empty($product_ids)) {
        return 0;
    }

    $ids_sql = implode(',', $product_ids);
    $products = $wpdb->get_results(
        "SELECT image_url, poster_image_url FROM `{$table_name}` WHERE id IN ({$ids_sql})",
        ARRAY_A
    );

    $deleted = $wpdb->query("DELETE FROM `{$table_name}` WHERE id IN ({$ids_sql})");
    if ($deleted === false) {
        return 0;
    }

    $upload_dir = GOODS_EXHIBITION_UPLOAD_DIR;
    $upload_url = GOODS_EXHIBITION_UPLOAD_URL;
    $files_to_delete = array();
    $media_urls = array();
    if (is_array($products)) {
        foreach ($products as $product) {
            foreach (array('image_url', 'poster_image_url') as $field) {
                $url = isset($product[$field]) ? $product[$field] : '';
                if (empty($url)) {
                    continue;
                }
                if (strpos($url, $upload_url) === 0) {
                    $files_to_delete[basename($url)] = true;
                } else {
                    $media_urls[$url] = true;
                }
            }
        }
    }
    foreach (array_keys($files_to_delete) as $filename) {
        goods_exhibition_safe_delete_file($upload_dir . $filename);
    }

    foreach (array_keys($media_urls) as $media_url) {
        goods_exhibition_maybe_delete_media_attachment($media_url);
    }

    goods_exhibition_flush_cache();

    return (int) $deleted;
}

/**
 * 删除单个产品
 */
function goods_exhibition_delete_product($product_id)
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'goods_exhibition';

    $product = $wpdb->get_row($wpdb->prepare("SELECT * FROM `{$table_name}` WHERE id = %d", $product_id), ARRAY_A);

    if ($product) {
        $wpdb->delete($table_name, array('id' => $product_id), array('%d'));

        $upload_dir = GOODS_EXHIBITION_UPLOAD_DIR;
        $upload_url = GOODS_EXHIBITION_UPLOAD_URL;

        $image_url = $product['image_url'];
        if (strpos($image_url, $upload_url) === 0) {
            $file_name = basename($image_url);
            $file_path = $upload_dir . $file_name;
            goods_exhibition_safe_delete_file($file_path);
        }

        $poster_image_url = $product['poster_image_url'];
        if (!empty($poster_image_url) && strpos($poster_image_url, $upload_url) === 0) {
            $file_name = basename($poster_image_url);
            $file_path = $upload_dir . $file_name;
            goods_exhibition_safe_delete_file($file_path);
        }

        goods_exhibition_maybe_delete_media_attachment($image_url);
        if (!empty($poster_image_url) && $poster_image_url !== $image_url) {
            goods_exhibition_maybe_delete_media_attachment($poster_image_url);
        }

        goods_exhibition_flush_cache();

        return true;
    }

    return false;
}
