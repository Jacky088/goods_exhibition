<?php
/**
 * 短代码功能
 *
 * @package 好物页面插件
 */

if (!defined('WPINC')) {
    die;
}

function goods_exhibition_register_shortcode() {
    add_shortcode('goods_exhibition', 'goods_exhibition_shortcode_callback');
}
add_action('init', 'goods_exhibition_register_shortcode');

function goods_exhibition_render_poster_slider() {
    if (!function_exists('goods_exhibition_get_poster_products')) {
        require_once GOODS_EXHIBITION_PATH . 'includes/functions.php';
    }
    $posters = goods_exhibition_get_poster_products();
    if (empty($posters)) {
        return '';
    }

    ob_start();
    ?>
    <!-- 海报幻灯片容器: 圆角 + 阴影 + overflow隐藏保证阴影圆角视觉 -->
    <div class="goods-exhibition-wrapper poster-slider" style="margin:0 auto; padding: 0 40px; border-radius: 24px; box-shadow: 0 6px 30px rgba(0,0,0,0.08); overflow: hidden;">
        <div class="poster-inner" style="margin: 0 -40px; border-radius: 24px; overflow: hidden; position: relative;">
            <button class="goods-exhibition-arrow goods-exhibition-arrow-left" aria-label="上一个" style="left: 0; z-index: 20;"><span>&#10094;</span></button>
            <div class="goods-exhibition-slider poster-slider" style="display: flex; overflow-x: hidden; scroll-behavior: smooth;">
                <?php foreach ($posters as $poster) :
                    $item_url = !empty($poster['url']) ? esc_url($poster['url']) : '';
                    $name = esc_html($poster['name']);
                    $desc = wp_kses_post($poster['description']);
                    $price = esc_html($poster['price']);
                    $image_url = esc_url($poster['poster_image_url']);
                ?>
                <?php if ($item_url): ?>
                <a href="<?php echo $item_url; ?>" target="_blank" rel="noopener noreferrer" class="goods-exhibition-item poster-item has-link" style="flex: 0 0 100%; max-width: 100%; margin: 0; position: relative; border-radius: 24px; overflow: hidden;">
                    <div class="goods-exhibition-content poster-content" style="padding: 40px; color: white; position: absolute; bottom: 30px; left: 30px; z-index: 10; max-width: 40%; text-shadow: 0 0 5px rgba(0,0,0,0.9);">
                        <h3 class="goods-exhibition-title" style="color: #fff; font-size: 2rem;"><?php echo $name; ?></h3>
                        <div class="goods-exhibition-description" style="font-size: 1.2rem; margin: 10px 0;"><?php echo $desc; ?></div>
                        <?php if ($price) : ?>
                            <div class="goods-exhibition-price" style="font-weight: 700; font-size: 1.4rem; color: #f5a623;"><?php echo $price; ?></div>
                        <?php endif; ?>
                    </div>
                    <img src="<?php echo $image_url; ?>" alt="<?php echo $name; ?>" style="width: 100%; max-height: 400px; height: auto; border-radius: 24px; object-fit: cover; user-select: none; display: block;">
                </a>
                <?php else: ?>
                <div class="goods-exhibition-item poster-item" style="flex: 0 0 100%; max-width: 100%; margin: 0; position: relative; border-radius: 24px; overflow: hidden;">
                    <div class="goods-exhibition-content poster-content" style="padding: 40px; color: white; position: absolute; bottom: 30px; left: 30px; z-index: 10; max-width: 40%; text-shadow: 0 0 5px rgba(0,0,0,0.9);">
                        <h3 class="goods-exhibition-title" style="color: #fff; font-size: 2rem;"><?php echo $name; ?></h3>
                        <div class="goods-exhibition-description" style="font-size: 1.2rem; margin: 10px 0;"><?php echo $desc; ?></div>
                        <?php if ($price) : ?>
                            <div class="goods-exhibition-price" style="font-weight: 700; font-size: 1.4rem; color: #f5a623;"><?php echo $price; ?></div>
                        <?php endif; ?>
                    </div>
                    <img src="<?php echo $image_url; ?>" alt="<?php echo $name; ?>" style="width: 100%; max-height: 400px; height: auto; border-radius: 24px; object-fit: cover; user-select: none; display: block;">
                </div>
                <?php endif; ?>
                <?php endforeach; ?>
            </div>
            <button class="goods-exhibition-arrow goods-exhibition-arrow-right" aria-label="下一个" style="right: 0; z-index: 20;"><span>&#10095;</span></button>
        </div>
    </div>

    <style>
        /* 去除海报项目margin，确保铺满 */
        .goods-exhibition-wrapper.poster-slider .goods-exhibition-item {
            margin: 0 !important;
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 14px 20px rgba(0, 0, 0, 0.06);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        /* 图片100%宽度和高度自适应，圆角24px */
        .goods-exhibition-wrapper.poster-slider .goods-exhibition-item img {
            width: 100%;
            height: auto;
            border-radius: 24px !important;
            object-fit: cover;
            user-select: none;
            display: block;
        }

        /* 箭头按键层级 */
        .goods-exhibition-arrow.goods-exhibition-arrow-left,
        .goods-exhibition-arrow.goods-exhibition-arrow-right {
            z-index: 20;
        }

        /* 移动端调整海报幻灯片文字位置和样式 */
        @media (max-width: 480px) {
            .goods-exhibition-wrapper.poster-slider .goods-exhibition-content.poster-content {
                position: absolute !important;
                bottom: 20px !important;
                left: 15px !important;
                max-width: 45% !important;         /* 限制宽度，防止撑满 */
                padding: 10px 15px !important;
                box-sizing: border-box !important;
                color: #fff !important;
                text-shadow: 0 0 5px rgba(0,0,0,0.9) !important;
                font-size: clamp(1rem, 4vw, 1.8rem) !important;  /* 自适应字体大小 */
                line-height: 1.3 !important;       /* 优化行间距 */
                white-space: normal !important;
                word-break: break-word !important;
            }

            /* 标题 */
            .goods-exhibition-wrapper.poster-slider .goods-exhibition-content.poster-content .goods-exhibition-title {
                font-size: clamp(1.2rem, 5vw, 2rem) !important;
                line-height: 1.2 !important;
                margin-bottom: 0.3em !important;
                word-break: break-word !important;
            }

            /* 描述 */
            .goods-exhibition-wrapper.poster-slider .goods-exhibition-content.poster-content .goods-exhibition-description {
                font-size: clamp(0.85rem, 3vw, 1.2rem) !important;
                line-height: 1.25 !important;
                margin-bottom: 0.25em !important;
                word-break: break-word !important;
            }

            /* 价格 */
            .goods-exhibition-wrapper.poster-slider .goods-exhibition-content.poster-content .goods-exhibition-price {
                font-size: clamp(0.9rem, 3.5vw, 1.3rem) !important;
                line-height: 1.3 !important;
                font-weight: 700 !important;
                color: #f5a623 !important;
                word-break: break-word !important;
            }
        }
    </style>
    <?php
    return ob_get_clean();
}

/**
 * 短代码主要回调，保持不变
 */
function goods_exhibition_shortcode_callback($atts) {
    $atts = shortcode_atts(array('limit' => -1), $atts, 'goods_exhibition');

    $output = goods_exhibition_render_poster_slider();

    global $wpdb;
    $table_name = $wpdb->prefix . 'goods_exhibition';

    $limit = intval($atts['limit']);
    $limit_clause = $limit > 0 ? "LIMIT $limit" : "";

    $products = $wpdb->get_results("SELECT * FROM {$table_name} ORDER BY category ASC, created_at DESC $limit_clause", ARRAY_A);

    if (empty($products)) {
        return $output . '<div class="goods-exhibition-empty">暂无产品</div>';
    }

    $grouped_products = [];
    foreach ($products as $product) {
        $category = trim($product['category']);
        if ($category === '') {
            $category = __('未分类', 'goods-exhibition');
        }
        if (!isset($grouped_products[$category])) {
            $grouped_products[$category] = [];
        }
        $grouped_products[$category][] = $product;
    }

    ob_start();
    foreach ($grouped_products as $category_name => $items) {
        if ($category_name === '海报展示') {
            continue;
        }
        ?>
        <h2 class="goods-exhibition-category-title"><?php echo esc_html($category_name); ?></h2>
        <div class="goods-exhibition-wrapper" style="margin-top: 0;">
            <button class="goods-exhibition-arrow goods-exhibition-arrow-left" aria-label="上一个"><span>&#10094;</span></button>
            <div class="goods-exhibition-slider">
                <?php foreach ($items as $product) :
                    $item_url = !empty($product['url']) ? esc_url($product['url']) : '';
                    if ($item_url) :
                ?>
                    <a href="<?php echo $item_url; ?>" target="_blank" rel="noopener noreferrer" class="goods-exhibition-item has-link">
                        <div class="goods-exhibition-content">
                            <h3 class="goods-exhibition-title"><?php echo esc_html($product['name']); ?></h3>
                            <div class="goods-exhibition-description"><?php echo wp_kses_post($product['description']); ?></div>
                            <?php if (!empty($product['price'])) : ?>
                                <div class="goods-exhibition-price"><?php echo esc_html($product['price']); ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="goods-exhibition-image-container">
                            <img src="<?php echo esc_url($product['image_url']); ?>" alt="<?php echo esc_attr($product['name']); ?>" class="goods-exhibition-image no-lightbox" style="border-radius: 24px;">
                        </div>
                    </a>
                <?php else: ?>
                    <div class="goods-exhibition-item">
                        <div class="goods-exhibition-content">
                            <h3 class="goods-exhibition-title"><?php echo esc_html($product['name']); ?></h3>
                            <div class="goods-exhibition-description"><?php echo wp_kses_post($product['description']); ?></div>
                            <?php if (!empty($product['price'])) : ?>
                                <div class="goods-exhibition-price"><?php echo esc_html($product['price']); ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="goods-exhibition-image-container">
                            <img src="<?php echo esc_url($product['image_url']); ?>" alt="<?php echo esc_attr($product['name']); ?>" class="goods-exhibition-image no-lightbox" style="border-radius: 24px;">
                        </div>
                    </div>
                <?php endif; ?>
                <?php endforeach; ?>
            </div>
            <button class="goods-exhibition-arrow goods-exhibition-arrow-right" aria-label="下一个"><span>&#10095;</span></button>
        </div>
        <?php
    }
    $output .= ob_get_clean();

    return $output;
}
