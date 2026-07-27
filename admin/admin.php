<?php
/**
 * 插件管理界面
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
        case 'add':
            goods_exhibition_render_add_product_tab();
            break;
        case 'categories':
            goods_exhibition_render_categories_tab();
            break;
        case 'list':
        default:
            goods_exhibition_render_product_list_tab();
            break;
    }

    echo '</div>'; // 关闭 .wrap
}

/**
 * 渲染管理页面头部：大标题 + 分页卡导航
 */
function goods_exhibition_render_admin_header($current_tab)
{
    ?>
    <div class="wrap">
        <h1 class="goods-exhibition-admin-title">好物页面管理插件</h1>

        <nav class="nav-tab-wrapper goods-exhibition-tabs">
            <a href="<?php echo admin_url('admin.php?page=goods-exhibition&tab=list'); ?>"
               class="nav-tab <?php echo $current_tab === 'list' ? 'nav-tab-active' : ''; ?>">产品列表</a>
            <a href="<?php echo admin_url('admin.php?page=goods-exhibition&tab=add'); ?>"
               class="nav-tab <?php echo $current_tab === 'add' ? 'nav-tab-active' : ''; ?>">添加新产品</a>
            <a href="<?php echo admin_url('admin.php?page=goods-exhibition&tab=categories'); ?>"
               class="nav-tab <?php echo $current_tab === 'categories' ? 'nav-tab-active' : ''; ?>">分类管理</a>
        </nav>
    <?php
}

/**
 * 产品列表 Tab 内容
 */
function goods_exhibition_render_product_list_tab()
{
    // 处理单个删除操作
    if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['product_id']) && isset($_GET['_wpnonce'])) {
        if (wp_verify_nonce($_GET['_wpnonce'], 'delete_product_' . intval($_GET['product_id']))) {
            $product_id = intval($_GET['product_id']);
            if (goods_exhibition_delete_product($product_id)) {
                echo '<div class="notice notice-success is-dismissible"><p>产品已成功删除！</p></div>';
            } else {
                echo '<div class="notice notice-error is-dismissible"><p>删除产品失败！</p></div>';
            }
        } else {
            echo '<div class="notice notice-error is-dismissible"><p>安全验证失败，请重试。</p></div>';
        }
    }

    // 处理批量删除
    if (isset($_POST['goods_exhibition_bulk_action']) && $_POST['goods_exhibition_bulk_action'] === 'delete') {
        if (isset($_POST['_wpnonce_bulk']) && wp_verify_nonce($_POST['_wpnonce_bulk'], 'goods_exhibition_bulk_delete')) {
            if (!empty($_POST['product_ids']) && is_array($_POST['product_ids'])) {
                $deleted = 0;
                foreach ($_POST['product_ids'] as $pid) {
                    if (goods_exhibition_delete_product(intval($pid))) {
                        $deleted++;
                    }
                }
                echo '<div class="notice notice-success is-dismissible"><p>已成功删除 ' . $deleted . ' 个产品！</p></div>';
            }
        } else {
            echo '<div class="notice notice-error is-dismissible"><p>安全验证失败，请重试。</p></div>';
        }
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'goods_exhibition';

    // 搜索和筛选参数
    $search = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';
    $filter_category = isset($_GET['category']) ? sanitize_text_field($_GET['category']) : '';

    // 构建查询条件
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

    // 分页逻辑
    $current_page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
    $per_page = GOODS_EXHIBITION_PER_PAGE;
    $offset = ($current_page - 1) * $per_page;

    // 确定排序方式：筛选了分类时按 sort_order，否则按 id DESC
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

    $total_pages = ceil($total_items / $per_page);

    // 获取所有类别用于筛选下拉菜单
    $all_categories = $wpdb->get_col("SELECT DISTINCT category FROM `{$table_name}` WHERE category != '' ORDER BY category ASC");

    // 构建分页基础URL
    $base_url_params = array('page' => 'goods-exhibition', 'tab' => 'list');
    if (!empty($search)) $base_url_params['s'] = $search;
    if (!empty($filter_category)) $base_url_params['category'] = $filter_category;
    ?>
    <div class="goods-exhibition-tab-content">

        <!-- 搜索和筛选栏 -->
        <div class="goods-exhibition-toolbar">
            <form method="get" class="goods-exhibition-search-form">
                <input type="hidden" name="page" value="goods-exhibition">
                <input type="hidden" name="tab" value="list">

                <select name="category" class="goods-exhibition-filter-select">
                    <option value="">所有类别</option>
                    <?php foreach ($all_categories as $cat): ?>
                        <option value="<?php echo esc_attr($cat); ?>" <?php selected($filter_category, $cat); ?>><?php echo esc_html($cat); ?></option>
                    <?php endforeach; ?>
                </select>

                <input type="search" name="s" class="goods-exhibition-search-input" placeholder="搜索产品名称或描述..."
                    value="<?php echo esc_attr($search); ?>">
                <input type="submit" class="button" value="筛选">

                <?php if (!empty($search) || !empty($filter_category)): ?>
                    <a href="<?php echo admin_url('admin.php?page=goods-exhibition&tab=list'); ?>" class="button">清除筛选</a>
                <?php endif; ?>
            </form>

            <span class="goods-exhibition-product-count">共 <strong><?php echo $total_items; ?></strong> 个产品</span>
        </div>

        <?php if (empty($products) && $current_page === 1): ?>
            <?php if (!empty($search) || !empty($filter_category)): ?>
                <div class="notice notice-warning">
                    <p>没有找到匹配的产品。<a href="<?php echo admin_url('admin.php?page=goods-exhibition&tab=list'); ?>">查看所有产品</a></p>
                </div>
            <?php else: ?>
                <div class="notice notice-info">
                    <p>还没有添加任何产品。<a href="<?php echo admin_url('admin.php?page=goods-exhibition&tab=add'); ?>">添加一个新产品</a></p>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <form method="post" id="goods-exhibition-bulk-form">
                <?php wp_nonce_field('goods_exhibition_bulk_delete', '_wpnonce_bulk'); ?>
                <input type="hidden" name="goods_exhibition_bulk_action" value="delete">

                <div class="tablenav top">
                    <div class="alignleft actions">
                        <button type="submit" class="button action goods-exhibition-bulk-delete-btn" disabled
                            onclick="return confirm('确定要删除所选产品吗？此操作不可撤销。');">批量删除</button>

                        <!-- 批量移动分类 -->
                        <select id="goods-bulk-move-category" class="goods-exhibition-filter-select" disabled>
                            <option value="">移动至分类...</option>
                            <?php foreach ($all_categories as $cat): ?>
                                <option value="<?php echo esc_attr($cat); ?>"><?php echo esc_html($cat); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button type="button" id="goods-bulk-move-btn" class="button" disabled>移动</button>

                        <span class="goods-exhibition-selected-count"></span>
                    </div>

                    <?php if ($is_category_filtered): ?>
                        <div class="alignright">
                            <span class="goods-sort-hint">&#x2195; 拖动行可排序（仅当前分类内）</span>
                        </div>
                    <?php endif; ?>
                </div>

                <table class="wp-list-table widefat fixed striped goods-exhibition-table" <?php echo $is_category_filtered ? 'data-sortable="1"' : ''; ?>>
                    <thead>
                        <tr>
                            <th class="check-column"><input type="checkbox" id="goods-cb-select-all"></th>
                            <?php if ($is_category_filtered): ?>
                                <th class="column-sort" style="width:30px;"></th>
                            <?php endif; ?>
                            <th class="column-thumb">图片</th>
                            <th class="column-name">名称</th>
                            <th class="column-desc">描述</th>
                            <th class="column-price">价格</th>
                            <th class="column-category">类别</th>
                            <th class="column-tags">标记</th>
                            <th class="column-link">链接</th>
                            <th class="column-date">添加日期</th>
                            <th class="column-actions">操作</th>
                        </tr>
                    </thead>
                    <tbody id="goods-sortable-body">
                        <?php foreach ($products as $product): ?>
                            <tr data-id="<?php echo esc_attr($product['id']); ?>">
                                <td class="check-column">
                                    <input type="checkbox" name="product_ids[]" value="<?php echo esc_attr($product['id']); ?>" class="goods-cb-item">
                                </td>
                                <?php if ($is_category_filtered): ?>
                                    <td class="column-sort"><span class="goods-drag-handle" title="拖动排序">&#x2630;</span></td>
                                <?php endif; ?>
                                <td class="column-thumb">
                                    <?php if (!empty($product['image_url'])): ?>
                                        <img src="<?php echo esc_url($product['image_url']); ?>"
                                            alt="<?php echo esc_attr($product['name']); ?>">
                                    <?php else: ?>
                                        <span class="goods-no-image">无</span>
                                    <?php endif; ?>
                                </td>
                                <td class="column-name">
                                    <strong><?php echo esc_html($product['name']); ?></strong>
                                </td>
                                <td class="column-desc"><?php echo esc_html(mb_strimwidth($product['description'], 0, 40, '...')); ?></td>
                                <td class="column-price"><?php echo esc_html($product['price']); ?></td>
                                <td class="column-category"><span class="goods-category-badge"><?php echo esc_html($product['category']); ?></span></td>
                                <td class="column-tags">
                                    <?php if ($product['is_new'] == 1): ?>
                                        <span class="goods-tag goods-tag-new">NEW</span>
                                    <?php endif; ?>
                                    <?php if ($product['is_poster'] == 1): ?>
                                        <span class="goods-tag goods-tag-poster">海报</span>
                                    <?php endif; ?>
                                    <?php if ($product['is_new'] != 1 && $product['is_poster'] != 1): ?>
                                        <span class="goods-tag-none">—</span>
                                    <?php endif; ?>
                                </td>
                                <td class="column-link">
                                    <?php if (!empty($product['url'])): ?>
                                        <a href="<?php echo esc_url($product['url']); ?>" target="_blank" title="<?php echo esc_attr($product['url']); ?>">&#128279;</a>
                                    <?php else: ?>
                                        —
                                    <?php endif; ?>
                                </td>
                                <td class="column-date"><?php echo date_i18n('Y/m/d', strtotime($product['created_at'])); ?></td>
                                <td class="column-actions">
                                    <a href="<?php echo admin_url('admin.php?page=goods-exhibition&tab=add&action=edit&product_id=' . $product['id']); ?>" class="goods-action-link">编辑</a>
                                    <span class="goods-action-sep">|</span>
                                    <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=goods-exhibition&tab=list&action=delete&product_id=' . $product['id']), 'delete_product_' . $product['id']); ?>"
                                        class="goods-action-link goods-action-delete"
                                        onclick="return confirm('确定要删除此产品吗？');">删除</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </form>

            <?php if ($total_pages > 1): ?>
                <div class="tablenav bottom">
                    <div class="tablenav-pages">
                        <span class="displaying-num"><?php echo $total_items; ?> 个项目</span>
                        <span class="pagination-links">
                            <?php
                            $page_url = admin_url('admin.php?' . http_build_query($base_url_params));
                            if ($current_page > 1): ?>
                                <a class="first-page button" href="<?php echo $page_url . '&paged=1'; ?>">«</a>
                                <a class="prev-page button" href="<?php echo $page_url . '&paged=' . ($current_page - 1); ?>">‹</a>
                            <?php else: ?>
                                <span class="tablenav-pages-navspan button disabled">«</span>
                                <span class="tablenav-pages-navspan button disabled">‹</span>
                            <?php endif; ?>

                            <span class="paging-input">
                                <span class="tablenav-paging-text">
                                    第 <?php echo $current_page; ?> / <?php echo $total_pages; ?> 页
                                </span>
                            </span>

                            <?php if ($current_page < $total_pages): ?>
                                <a class="next-page button" href="<?php echo $page_url . '&paged=' . ($current_page + 1); ?>">›</a>
                                <a class="last-page button" href="<?php echo $page_url . '&paged=' . $total_pages; ?>">»</a>
                            <?php else: ?>
                                <span class="tablenav-pages-navspan button disabled">›</span>
                                <span class="tablenav-pages-navspan button disabled">»</span>
                            <?php endif; ?>
                        </span>
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
    <?php
}

/**
 * 添加/编辑产品 Tab 内容
 */
function goods_exhibition_render_add_product_tab()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'goods_exhibition';

    $product = array(
        'id' => 0,
        'name' => '',
        'description' => '',
        'price' => '',
        'image_url' => '',
        'url' => '',
        'is_new' => 0,
        'is_poster' => 0,
        'category' => '',
        'poster_image_url' => '',
    );

    $is_edit = false;
    $form_title = '添加新产品';

    if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['product_id'])) {
        $product_id = intval($_GET['product_id']);
        $db_product = $wpdb->get_row($wpdb->prepare("SELECT * FROM `{$table_name}` WHERE id = %d", $product_id), ARRAY_A);
        if ($db_product) {
            $product = $db_product;
            $is_edit = true;
            $form_title = '编辑产品';
        }
    }

    if (isset($_POST['goods_exhibition_submit'])) {
        check_admin_referer('goods_exhibition_add_product');

        $product_name = sanitize_text_field($_POST['product_name']);
        $product_description = wp_kses_post($_POST['product_description']);
        $product_price = sanitize_text_field($_POST['product_price']);
        $product_url = esc_url_raw($_POST['product_url']);
        $product_category = sanitize_text_field($_POST['product_category']);
        $product_image_url_from_media = esc_url_raw($_POST['product_image_url']);
        $product_is_new = isset($_POST['product_is_new']) ? 1 : 0;
        $product_is_poster = isset($_POST['product_is_poster']) ? 1 : 0;
        $poster_image_url_from_media = esc_url_raw($_POST['poster_image_url']);
        $uploaded_image_url = '';
        $uploaded_poster_image_url = '';
        $errors = array();

        // 使用统一的图片上传处理函数
        $image_result = goods_exhibition_handle_image_upload('product_image');
        if (!empty($image_result['error'])) {
            $errors[] = $image_result['error'];
        }
        if ($image_result['success']) {
            $uploaded_image_url = $image_result['url'];
        }

        // 海报图片上传
        $poster_result = goods_exhibition_handle_image_upload('poster_image');
        if (!empty($poster_result['error'])) {
            $errors[] = $poster_result['error'];
        }
        if ($poster_result['success']) {
            $uploaded_poster_image_url = $poster_result['url'];
        }

        $final_image_url = !empty($uploaded_image_url) ? $uploaded_image_url : $product_image_url_from_media;
        $final_poster_image_url = !empty($uploaded_poster_image_url) ? $uploaded_poster_image_url : $poster_image_url_from_media;

        if (empty($product_name)) {
            $errors[] = '产品名称不能为空';
        }
        if (empty($product_description)) {
            $errors[] = '产品描述不能为空';
        }
        if (empty($product_category)) {
            $errors[] = '产品类别不能为空';
        }
        if (empty($final_image_url)) {
            $errors[] = '请上传产品图片或从媒体库选择';
        }
        if ($product_is_poster && empty($final_poster_image_url)) {
            $errors[] = '已勾选"标记为海报"，请上传或选择海报图片';
        }

        if (empty($errors)) {
            $data = array(
                'name' => $product_name,
                'description' => $product_description,
                'price' => $product_price,
                'image_url' => $final_image_url,
                'url' => $product_url,
                'category' => $product_category,
                'is_new' => $product_is_new,
                'is_poster' => $product_is_poster,
                'poster_image_url' => $final_poster_image_url,
                'updated_at' => current_time('mysql'),
            );

            if ($is_edit) {
                $result = $wpdb->update(
                    $table_name,
                    $data,
                    array('id' => $product['id'])
                );
                if ($result === false) {
                    $errors[] = '更新产品时出错: ' . $wpdb->last_error;
                } else {
                    $success_message = '产品已成功更新！';
                }
            } else {
                $data['created_at'] = current_time('mysql');
                $result = $wpdb->insert(
                    $table_name,
                    $data
                );
                if ($result === false) {
                    $errors[] = '添加新产品时出错: ' . $wpdb->last_error;
                } else {
                    $product['id'] = $wpdb->insert_id;
                    $success_message = '新产品已成功添加！';
                }
            }

            // 清除缓存
            goods_exhibition_flush_cache();

            if (empty($errors)) {
                echo '<div class="notice notice-success is-dismissible"><p>' . esc_html($success_message) . '</p></div>';

                if ($is_edit) {
                    $product = $wpdb->get_row($wpdb->prepare("SELECT * FROM `{$table_name}` WHERE id = %d", $product['id']), ARRAY_A);
                } else {
                    $product = array(
                        'id' => 0,
                        'name' => '',
                        'description' => '',
                        'price' => '',
                        'image_url' => '',
                        'url' => '',
                        'category' => '',
                        'is_new' => 0,
                        'is_poster' => 0,
                        'poster_image_url' => '',
                    );
                    $is_edit = false;
                    $form_title = '添加新产品';
                }
            } else {
                echo '<div class="notice notice-error is-dismissible"><p>' . implode('</p><p>', array_map('esc_html', $errors)) . '</p></div>';
            }
        } else {
            echo '<div class="notice notice-error is-dismissible"><p>' . implode('</p><p>', array_map('esc_html', $errors)) . '</p></div>';
        }
    }
    ?>
    <div class="goods-exhibition-tab-content">
        <?php if ($is_edit): ?>
            <h2><?php echo esc_html($form_title); ?> (ID: <?php echo esc_html($product['id']); ?>)</h2>
        <?php endif; ?>

        <form method="post" enctype="multipart/form-data" action="<?php echo admin_url('admin.php?page=goods-exhibition&tab=add' . ($is_edit ? '&action=edit&product_id=' . $product['id'] : '')); ?>">
            <?php wp_nonce_field('goods_exhibition_add_product'); ?>
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="product_name">产品名称 <span class="required">*</span></label></th>
                    <td>
                        <input type="text" name="product_name" id="product_name" class="regular-text"
                            value="<?php echo esc_attr($product['name']); ?>" required>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="product_description">产品描述 <span class="required">*</span></label></th>
                    <td>
                        <textarea name="product_description" id="product_description" class="large-text" rows="5"
                            required><?php echo esc_textarea($product['description']); ?></textarea>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="product_price">产品价格</label></th>
                    <td>
                        <input type="text" name="product_price" id="product_price" class="regular-text"
                            value="<?php echo esc_attr($product['price']); ?>" placeholder="例如：HK$4,199 起 (含教育优惠)">
                        <p class="description">建议填写，如"HK$4,199 起 (含教育优惠)"等文本</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="product_url">跳转链接</label></th>
                    <td>
                        <input type="url" name="product_url" id="product_url" class="regular-text"
                            value="<?php echo esc_url($product['url']); ?>">
                        <p class="description">可选。如果设置了链接，点击产品卡片将跳转到此链接。留空则不跳转。</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="product_category">产品类别 <span class="required">*</span></label></th>
                    <td>
                        <?php
                        $categories_table = $wpdb->prefix . 'goods_exhibition_categories';
                        $all_cats = $wpdb->get_results("SELECT * FROM `{$categories_table}` ORDER BY sort_order ASC, name ASC", ARRAY_A);
                        ?>
                        <select name="product_category" id="product_category" class="regular-text" required>
                            <option value="">— 请选择类别 —</option>
                            <?php foreach ($all_cats as $cat): ?>
                                <option value="<?php echo esc_attr($cat['name']); ?>" <?php selected($product['category'], $cat['name']); ?>><?php echo esc_html($cat['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <p class="description">请选择产品类别。如需添加新类别，请前往「<a href="<?php echo admin_url('admin.php?page=goods-exhibition&tab=categories'); ?>">分类管理</a>」。</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="product_image">产品图片 <span class="required">*</span></label></th>
                    <td>
                        <div class="product-image-preview">
                            <?php if (!empty($product['image_url'])): ?>
                                <img src="<?php echo esc_url($product['image_url']); ?>" alt="产品图片预览"
                                    style="max-width: 200px; max-height: 200px; margin-bottom: 10px; display: block;">
                            <?php endif; ?>
                        </div>
                        <input type="hidden" name="product_image_url" id="product_image_url"
                            value="<?php echo esc_url($product['image_url']); ?>">
                        <input type="button" id="upload_image_button" class="button" value="从媒体库选择图片">
                        <p class="description">推荐图片尺寸：720x720像素（1:1正方形），图片将等比缩放显示在卡片中</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="product_is_new">标记为新产品</label></th>
                    <td>
                        <input type="checkbox" name="product_is_new" id="product_is_new" value="1" <?php checked(isset($product['is_new']) ? $product['is_new'] : 0, 1); ?>>
                        <label for="product_is_new">将此产品标记为"新"</label>
                        <p class="description">勾选后，产品卡片上会显示"New"标签。</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="product_is_poster">标记为海报</label></th>
                    <td>
                        <input type="checkbox" name="product_is_poster" id="product_is_poster" value="1" <?php checked(isset($product['is_poster']) ? $product['is_poster'] : 0, 1); ?>>
                        <label for="product_is_poster">勾选后，海报将在首页滚动显示</label>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="poster_image_url">海报图片</label></th>
                    <td>
                        <div class="poster-image-preview">
                            <?php if (!empty($product['poster_image_url'])): ?>
                                <img src="<?php echo esc_url($product['poster_image_url']); ?>" alt="海报图片预览"
                                    style="max-width: 200px; max-height: 200px; margin-bottom: 10px; display: block;">
                            <?php endif; ?>
                        </div>
                        <input type="hidden" name="poster_image_url" id="poster_image_url"
                            value="<?php echo esc_url($product['poster_image_url']); ?>">
                        <input type="button" id="upload_poster_image_button" class="button" value="从媒体库选择海报图片">
                        <p class="description">推荐图片尺寸：1920x640像素（比例3:1），图片将自动裁切铺满展示区域</p>
                    </td>
                </tr>
            </table>
            <p class="submit">
                <input type="submit" name="goods_exhibition_submit" id="submit" class="button button-primary"
                    value="<?php echo $is_edit ? '更新产品' : '添加产品'; ?>">
                <?php if ($is_edit): ?>
                    <a href="<?php echo admin_url('admin.php?page=goods-exhibition&tab=list'); ?>" class="button">取消编辑</a>
                <?php endif; ?>
            </p>
        </form>
    </div>
    <?php
}

/**
 * 分类管理 Tab 内容
 */
function goods_exhibition_render_categories_tab()
{
    global $wpdb;
    $categories_table = $wpdb->prefix . 'goods_exhibition_categories';
    $products_table = $wpdb->prefix . 'goods_exhibition';

    // 确保分类表存在
    $table_exists = $wpdb->get_var("SHOW TABLES LIKE '{$categories_table}'");
    if (!$table_exists) {
        echo '<div class="notice notice-error"><p>分类表不存在，请停用后重新激活插件。</p></div>';
        return;
    }

    // 处理添加分类
    if (isset($_POST['goods_exhibition_add_category'])) {
        check_admin_referer('goods_exhibition_category_action');
        $new_name = sanitize_text_field($_POST['category_name']);
        if (!empty($new_name)) {
            $exists = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM `{$categories_table}` WHERE name = %s", $new_name));
            if ($exists > 0) {
                echo '<div class="notice notice-warning is-dismissible"><p>分类「' . esc_html($new_name) . '」已存在。</p></div>';
            } else {
                $sort = isset($_POST['category_sort']) ? intval($_POST['category_sort']) : 0;
                $wpdb->insert($categories_table, array('name' => $new_name, 'sort_order' => $sort));
                echo '<div class="notice notice-success is-dismissible"><p>分类「' . esc_html($new_name) . '」已成功添加！</p></div>';
            }
        } else {
            echo '<div class="notice notice-error is-dismissible"><p>分类名称不能为空。</p></div>';
        }
    }

    // 处理删除分类
    if (isset($_GET['action']) && $_GET['action'] === 'delete_cat' && isset($_GET['cat_id']) && isset($_GET['_wpnonce'])) {
        $cat_id = intval($_GET['cat_id']);
        if (wp_verify_nonce($_GET['_wpnonce'], 'delete_category_' . $cat_id)) {
            // 获取分类名称
            $cat_name = $wpdb->get_var($wpdb->prepare("SELECT name FROM `{$categories_table}` WHERE id = %d", $cat_id));
            if ($cat_name) {
                // 检查该分类下是否有产品
                $product_count = (int) $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM `{$products_table}` WHERE category = %s", $cat_name));
                if ($product_count > 0) {
                    echo '<div class="notice notice-warning is-dismissible"><p>分类「' . esc_html($cat_name) . '」下还有 ' . $product_count . ' 个产品，无法删除。请先将产品移至其他分类。</p></div>';
                } else {
                    $wpdb->delete($categories_table, array('id' => $cat_id), array('%d'));
                    echo '<div class="notice notice-success is-dismissible"><p>分类已成功删除！</p></div>';
                }
            }
        } else {
            echo '<div class="notice notice-error is-dismissible"><p>安全验证失败。</p></div>';
        }
    }

    // 处理编辑分类
    if (isset($_POST['goods_exhibition_edit_category'])) {
        check_admin_referer('goods_exhibition_category_action');
        $edit_id = intval($_POST['edit_cat_id']);
        $edit_name = sanitize_text_field($_POST['edit_category_name']);
        $edit_sort = intval($_POST['edit_category_sort']);
        if (!empty($edit_name) && $edit_id > 0) {
            // 检查名称是否与其他分类冲突
            $conflict = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM `{$categories_table}` WHERE name = %s AND id != %d",
                $edit_name, $edit_id
            ));
            if ($conflict > 0) {
                echo '<div class="notice notice-warning is-dismissible"><p>已存在同名分类。</p></div>';
            } else {
                // 同步更新产品表中的分类名
                $old_name = $wpdb->get_var($wpdb->prepare("SELECT name FROM `{$categories_table}` WHERE id = %d", $edit_id));
                $wpdb->update($categories_table, array('name' => $edit_name, 'sort_order' => $edit_sort), array('id' => $edit_id));
                if ($old_name !== $edit_name) {
                    $wpdb->update($products_table, array('category' => $edit_name), array('category' => $old_name));
                    goods_exhibition_flush_cache();
                }
                echo '<div class="notice notice-success is-dismissible"><p>分类已更新！</p></div>';
            }
        }
    }

    // 获取所有分类及产品数量
    $categories = $wpdb->get_results(
        "SELECT c.*, (SELECT COUNT(*) FROM `{$products_table}` WHERE category = c.name) as product_count
         FROM `{$categories_table}` c ORDER BY c.sort_order ASC, c.name ASC",
        ARRAY_A
    );
    ?>
    <div class="goods-exhibition-tab-content">
        <div class="goods-exhibition-categories-layout">
            <!-- 左侧：添加新分类 -->
            <div class="goods-exhibition-cat-add">
                <h3>添加新分类</h3>
                <form method="post">
                    <?php wp_nonce_field('goods_exhibition_category_action'); ?>
                    <div class="goods-cat-field">
                        <label for="category_name">分类名称 <span class="required">*</span></label>
                        <input type="text" name="category_name" id="category_name" class="regular-text" required placeholder="例如：教育优惠">
                    </div>
                    <div class="goods-cat-field">
                        <label for="category_sort">排序（数字越小越靠前）</label>
                        <input type="number" name="category_sort" id="category_sort" class="small-text" value="0" min="0">
                    </div>
                    <p class="submit">
                        <input type="submit" name="goods_exhibition_add_category" class="button button-primary" value="添加分类">
                    </p>
                </form>
            </div>

            <!-- 右侧：分类列表 -->
            <div class="goods-exhibition-cat-list">
                <h3>现有分类 (<?php echo count($categories); ?>)</h3>
                <?php if (empty($categories)): ?>
                    <p class="description">暂无分类，请在左侧添加。</p>
                <?php else: ?>
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th>分类名称</th>
                                <th style="width: 80px;">排序</th>
                                <th style="width: 80px;">产品数</th>
                                <th style="width: 120px;">操作</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($categories as $cat): ?>
                                <tr>
                                    <td><strong><?php echo esc_html($cat['name']); ?></strong></td>
                                    <td><?php echo esc_html($cat['sort_order']); ?></td>
                                    <td><?php echo esc_html($cat['product_count']); ?></td>
                                    <td>
                                        <a href="#" class="goods-action-link goods-cat-edit-link"
                                           data-id="<?php echo esc_attr($cat['id']); ?>"
                                           data-name="<?php echo esc_attr($cat['name']); ?>"
                                           data-sort="<?php echo esc_attr($cat['sort_order']); ?>">编辑</a>
                                        <span class="goods-action-sep">|</span>
                                        <?php if ($cat['product_count'] == 0): ?>
                                            <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=goods-exhibition&tab=categories&action=delete_cat&cat_id=' . $cat['id']), 'delete_category_' . $cat['id']); ?>"
                                               class="goods-action-link goods-action-delete"
                                               onclick="return confirm('确定要删除此分类吗？');">删除</a>
                                        <?php else: ?>
                                            <span class="description" title="该分类下还有产品，无法删除">删除</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>

        <!-- 编辑分类弹窗 -->
        <div id="goods-cat-edit-modal" style="display:none;">
            <div class="goods-cat-modal-overlay"></div>
            <div class="goods-cat-modal-content">
                <h3>编辑分类</h3>
                <form method="post">
                    <?php wp_nonce_field('goods_exhibition_category_action'); ?>
                    <input type="hidden" name="edit_cat_id" id="edit_cat_id" value="">
                    <div class="goods-cat-field">
                        <label for="edit_category_name">分类名称</label>
                        <input type="text" name="edit_category_name" id="edit_category_name" class="regular-text" required>
                    </div>
                    <div class="goods-cat-field">
                        <label for="edit_category_sort">排序</label>
                        <input type="number" name="edit_category_sort" id="edit_category_sort" class="small-text" value="0" min="0">
                    </div>
                    <p class="submit">
                        <input type="submit" name="goods_exhibition_edit_category" class="button button-primary" value="保存">
                        <button type="button" class="button goods-cat-modal-close">取消</button>
                    </p>
                </form>
            </div>
        </div>
    </div>
    <?php
}

/**
 * 删除产品（使用安全的文件删除函数）
 *
 * @param int $product_id 产品ID
 * @return bool 是否成功删除
 */
function goods_exhibition_delete_product($product_id)
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'goods_exhibition';

    $product = $wpdb->get_row($wpdb->prepare("SELECT * FROM `{$table_name}` WHERE id = %d", $product_id), ARRAY_A);

    if ($product) {
        $wpdb->delete($table_name, array('id' => $product_id), array('%d'));

        $upload_dir = GOODS_EXHIBITION_UPLOAD_DIR;
        $upload_url = GOODS_EXHIBITION_URL . 'uploads/';

        // 使用安全删除函数，防止路径遍历攻击
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

        // 清除缓存
        goods_exhibition_flush_cache();

        return true;
    }

    return false;
}
