<?php
/**
 * 插件管理界面
 *
 * @package 好物页面插件
 */

if (!defined('WPINC')) {
    die;
}

function goods_exhibition_add_admin_menu() {
    add_menu_page(
        '好物页面插件',
        '好物页面',
        'manage_options',
        'goods-exhibition',
        'goods_exhibition_admin_page',
        'dashicons-cart',
        30
    );

    add_submenu_page(
        'goods-exhibition',
        '所有产品',
        '所有产品',
        'manage_options',
        'goods-exhibition',
        'goods_exhibition_admin_page'
    );

    add_submenu_page(
        'goods-exhibition',
        '添加新产品',
        '添加新产品',
        'manage_options',
        'goods-exhibition-add',
        'goods_exhibition_add_product_page'
    );
}
add_action('admin_menu', 'goods_exhibition_add_admin_menu');

function goods_exhibition_admin_page() {
    if (!current_user_can('manage_options')) {
        return;
    }

    if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['product_id'])) {
        $product_id = intval($_GET['product_id']);
        goods_exhibition_delete_product($product_id);
        echo '<div class="notice notice-success is-dismissible"><p>产品已成功删除！</p></div>';
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'goods_exhibition';
    $products = $wpdb->get_results("SELECT * FROM $table_name ORDER BY id DESC", ARRAY_A);
    ?>
    <div class="wrap">
        <h1 class="wp-heading-inline">产品列表</h1>
        <a href="<?php echo admin_url('admin.php?page=goods-exhibition-add'); ?>" class="page-title-action">添加新产品</a>
        <hr class="wp-header-end">

        <?php if (empty($products)) : ?>
            <div class="notice notice-info">
                <p>还没有添加任何产品。<a href="<?php echo admin_url('admin.php?page=goods-exhibition-add'); ?>">添加一个新产品</a></p>
            </div>
        <?php else : ?>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>图片</th>
                        <th>名称</th>
                        <th>描述</th>
                        <th>价格</th>
                        <th>跳转链接</th>
                        <th>类别</th>
                        <th>是否为新</th>
                        <th>是否为海报</th>
                        <th>添加日期</th>
                        <th>更新日期</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $product) : ?>
                        <tr>
                            <td><?php echo $product['id']; ?></td>
                            <td>
                                <?php if (!empty($product['image_url'])) : ?>
                                    <img src="<?php echo esc_url($product['image_url']); ?>" alt="<?php echo esc_attr($product['name']); ?>" style="max-width: 100px; max-height: 100px;">
                                <?php else : ?>
                                    无图片
                                <?php endif; ?>
                            </td>
                            <td><?php echo esc_html($product['name']); ?></td>
                            <td><?php echo wp_trim_words(esc_html($product['description']), 20); ?></td>
                            <td><?php echo esc_html($product['price']); ?></td>
                            <td><?php echo !empty($product['url']) ? '<a href="' . esc_url($product['url']) . '" target="_blank">' . esc_url($product['url']) . '</a>' : '无'; ?></td>
                            <td><?php echo esc_html($product['category']); ?></td>
                            <td><?php echo ($product['is_new'] == 1) ? '是' : '否'; ?></td>
                            <td><?php echo ($product['is_poster'] == 1) ? '是' : '否'; ?></td>
                            <td><?php echo date_i18n(get_option('date_format'), strtotime($product['created_at'])); ?></td>
                            <td><?php echo date_i18n(get_option('date_format'), strtotime($product['updated_at'])); ?></td>
                            <td>
                                <a href="<?php echo admin_url('admin.php?page=goods-exhibition-add&action=edit&product_id=' . $product['id']); ?>" class="button button-small">编辑</a>
                                <a href="<?php echo admin_url('admin.php?page=goods-exhibition&action=delete&product_id=' . $product['id']); ?>" class="button button-small button-link-delete" onclick="return confirm('确定要删除此产品吗？此操作不可撤销。');">删除</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
    <?php
}

function goods_exhibition_add_product_page() {
    if (!current_user_can('manage_options')) {
        return;
    }

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
    $page_title = '添加新产品';

    if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['product_id'])) {
        $product_id = intval($_GET['product_id']);
        $db_product = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $product_id), ARRAY_A);
        if ($db_product) {
            $product = $db_product;
            $is_edit = true;
            $page_title = '编辑产品';
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

        if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] == UPLOAD_ERR_OK) {
            if (!function_exists('goods_exhibition_check_upload_dir')) {
                require_once GOODS_EXHIBITION_PATH . 'includes/functions.php';
            }
            if (!goods_exhibition_check_upload_dir()) {
                $errors[] = '上传目录不可写或不存在，请检查插件文件夹权限。';
            } else {
                require_once(ABSPATH . 'wp-admin/includes/file.php');
                require_once(ABSPATH . 'wp-admin/includes/image.php');

                $upload_dir = GOODS_EXHIBITION_UPLOAD_DIR;
                $file_info = pathinfo($_FILES['product_image']['name']);
                $file_extension = !empty($file_info['extension']) ? '.' . strtolower($file_info['extension']) : '';
                $allowed_extensions = array('.jpg', '.jpeg', '.png', '.gif');

                if (!in_array($file_extension, $allowed_extensions)) {
                    $errors[] = '只允许上传 JPG, PNG, GIF 格式的图片。';
                } else {
                    $file_name = wp_unique_filename($upload_dir, sanitize_file_name($file_info['filename']) . $file_extension);
                    $upload_path = $upload_dir . $file_name;

                    if (move_uploaded_file($_FILES['product_image']['tmp_name'], $upload_path)) {
                        $uploaded_image_url = GOODS_EXHIBITION_URL . 'uploads/' . $file_name;
                    } else {
                        $errors[] = '上传图片失败，请再试一次。';
                    }
                }
            }
        } elseif (isset($_FILES['product_image']) && $_FILES['product_image']['error'] != UPLOAD_ERR_NO_FILE) {
            $errors[] = '图片上传时发生错误，错误代码：' . $_FILES['product_image']['error'];
        }

        if (isset($_FILES['poster_image']) && $_FILES['poster_image']['error'] == UPLOAD_ERR_OK) {
            if (!function_exists('goods_exhibition_check_upload_dir')) {
                require_once GOODS_EXHIBITION_PATH . 'includes/functions.php';
            }
            if (!goods_exhibition_check_upload_dir()) {
                $errors[] = '上传目录不可写或不存在，请检查插件文件夹权限。';
            } else {
                require_once(ABSPATH . 'wp-admin/includes/file.php');
                require_once(ABSPATH . 'wp-admin/includes/image.php');

                $upload_dir = GOODS_EXHIBITION_UPLOAD_DIR;
                $file_info = pathinfo($_FILES['poster_image']['name']);
                $file_extension = !empty($file_info['extension']) ? '.' . strtolower($file_info['extension']) : '';
                $allowed_extensions = array('.jpg', '.jpeg', '.png', '.gif');

                if (!in_array($file_extension, $allowed_extensions)) {
                    $errors[] = '只允许上传 JPG, PNG, GIF 格式的海报图片。';
                } else {
                    $file_name = wp_unique_filename($upload_dir, sanitize_file_name($file_info['filename']) . $file_extension);
                    $upload_path = $upload_dir . $file_name;

                    if (move_uploaded_file($_FILES['poster_image']['tmp_name'], $upload_path)) {
                        $uploaded_poster_image_url = GOODS_EXHIBITION_URL . 'uploads/' . $file_name;
                    } else {
                        $errors[] = '上传海报图片失败，请再试一次。';
                    }
                }
            }
        } elseif (isset($_FILES['poster_image']) && $_FILES['poster_image']['error'] != UPLOAD_ERR_NO_FILE) {
            $errors[] = '海报图片上传时发生错误，错误代码：' . $_FILES['poster_image']['error'];
        }

        $final_image_url = !empty($uploaded_image_url) ? $uploaded_image_url : $product_image_url_from_media;
        $final_poster_image_url = !empty($uploaded_poster_image_url) ? $uploaded_poster_image_url : $poster_image_url_from_media;

        if (empty($product_name)) {
            $errors[] = '产品名称不能为空';
        }
        if (empty($product_description)) {
            $errors[] = '产品描述不能为空';
        }
        if (empty($final_image_url)) {
            $errors[] = '请上传产品图片或从媒体库选择';
        }
        if ($product_is_poster && empty($final_poster_image_url)) {
            $errors[] = '已勾选“标记为海报”，请上传或选择海报图片';
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

            if (empty($errors)) {
                echo '<div class="notice notice-success is-dismissible"><p>' . esc_html($success_message) . '</p></div>';

                if ($is_edit) {
                    $product = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $product['id']), ARRAY_A);
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
                    $page_title = '添加新产品';
                }
            } else {
                echo '<div class="notice notice-error is-dismissible"><p>' . implode('</p><p>', array_map('esc_html', $errors)) . '</p></div>';
            }
        } else {
            echo '<div class="notice notice-error is-dismissible"><p>' . implode('</p><p>', array_map('esc_html', $errors)) . '</p></div>';
        }
    }
    ?>
    <div class="wrap">
        <h1 class="wp-heading-inline"><?php echo esc_html($page_title); ?></h1>
        <a href="<?php echo admin_url('admin.php?page=goods-exhibition'); ?>" class="page-title-action">返回产品列表</a>
        <hr class="wp-header-end">

        <form method="post" enctype="multipart/form-data">
            <?php wp_nonce_field('goods_exhibition_add_product'); ?>
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="product_name">产品名称</label></th>
                    <td>
                        <input type="text" name="product_name" id="product_name" class="regular-text" value="<?php echo esc_attr($product['name']); ?>" required>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="product_description">产品描述</label></th>
                    <td>
                        <textarea name="product_description" id="product_description" class="large-text" rows="5" required><?php echo esc_textarea($product['description']); ?></textarea>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="product_price">产品价格</label></th>
                    <td>
                        <input type="text" name="product_price" id="product_price" class="regular-text" value="<?php echo esc_attr($product['price']); ?>" placeholder="例如：HK$4,199 起 (含教育优惠)">
                        <p class="description">建议填写，如“HK$4,199 起 (含教育优惠)”等文本</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="product_url">跳转链接</label></th>
                    <td>
                        <input type="url" name="product_url" id="product_url" class="regular-text" value="<?php echo esc_url($product['url']); ?>">
                        <p class="description">可选。如果设置了链接，点击产品卡片将跳转到此链接。留空则不跳转。</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="product_category">产品类别</label></th>
                    <td>
                        <input type="text" name="product_category" id="product_category" class="regular-text" value="<?php echo esc_attr($product['category']); ?>" placeholder="例如：教育优惠">
                        <p class="description">请输入产品类别，同一类别的产品前端将一起显示。</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="product_image">产品图片</label></th>
                    <td>
                        <div class="product-image-preview">
                            <?php if (!empty($product['image_url'])) : ?>
                                <img src="<?php echo esc_url($product['image_url']); ?>" alt="产品图片预览" style="max-width: 200px; max-height: 200px; margin-bottom: 10px; display: block;">
                            <?php endif; ?>
                        </div>
                        <input type="hidden" name="product_image_url" id="product_image_url" value="<?php echo esc_url($product['image_url']); ?>">
                        <input type="button" id="upload_image_button" class="button" value="从媒体库选择图片">
                        <p class="description">推荐图片尺寸：500x500像素</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="product_is_new">标记为新产品</label></th>
                    <td>
                        <input type="checkbox" name="product_is_new" id="product_is_new" value="1" <?php checked(isset($product['is_new']) ? $product['is_new'] : 0, 1); ?>>
                        <label for="product_is_new">将此产品标记为“新”</label>
                        <p class="description">勾选后，产品卡片上会显示“New”标签。</p>
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
                            <?php if (!empty($product['poster_image_url'])) : ?>
                                <img src="<?php echo esc_url($product['poster_image_url']); ?>" alt="海报图片预览" style="max-width: 200px; max-height: 200px; margin-bottom: 10px; display: block;">
                            <?php endif; ?>
                        </div>
                        <input type="hidden" name="poster_image_url" id="poster_image_url" value="<?php echo esc_url($product['poster_image_url']); ?>">
                        <input type="button" id="upload_poster_image_button" class="button" value="从媒体库选择海报图片">
                        <p class="description">推荐图片尺寸：1200x400像素</p>
                    </td>
                </tr>
            </table>
            <p class="submit">
                <input type="submit" name="goods_exhibition_submit" id="submit" class="button button-primary" value="<?php echo $is_edit ? '更新产品' : '添加产品'; ?>">
            </p>
        </form>

        <hr>
        <!-- 底部感谢文字已移除 -->
    </div>
    <?php
}

function goods_exhibition_delete_product($product_id) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'goods_exhibition';

    $product = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $product_id), ARRAY_A);

    if ($product) {
        $wpdb->delete($table_name, array('id' => $product_id), array('%d'));

        $upload_dir = GOODS_EXHIBITION_UPLOAD_DIR;
        $upload_url = GOODS_EXHIBITION_URL . 'uploads/';

        $image_url = $product['image_url'];
        if (strpos($image_url, $upload_url) === 0) {
            $file_name = basename($image_url);
            $file_path = $upload_dir . $file_name;
            if (file_exists($file_path)) {
                unlink($file_path);
            }
        }

        $poster_image_url = $product['poster_image_url'];
        if (strpos($poster_image_url, $upload_url) === 0) {
            $file_name = basename($poster_image_url);
            $file_path = $upload_dir . $file_name;
            if (file_exists($file_path)) {
                unlink($file_path);
            }
        }

        return true;
    }

    return false;
}
