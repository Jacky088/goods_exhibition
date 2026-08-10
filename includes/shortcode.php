<?php
/**
 * 短代码功能
 *
 * @package 好物页面插件
 */

if (!defined('WPINC')) {
    die;
}

/**
 * 注册短代码
 */
function goods_exhibition_register_shortcode()
{
    add_shortcode('goods_exhibition', 'goods_exhibition_shortcode_callback');
}
add_action('init', 'goods_exhibition_register_shortcode');

/**
 * 渲染单个商品卡片（复用模板）
 *
 * @param array $product 产品数据
 * @param bool $is_new 是否为新品
 * @return string HTML
 */
function goods_exhibition_render_product_card($product, $is_new = false)
{
    $item_url = !empty($product['url']) ? esc_url($product['url']) : '';
    $name = esc_html($product['name']);
    $desc = wp_kses_post($product['description']);
    $price = esc_html($product['price']);
    $image_url = esc_url($product['image_url']);

    // 构建卡片内部内容
    $inner = '<div class="goods-exhibition-content">';
    $inner .= '<h3 class="goods-exhibition-title">' . $name;
    if ($is_new) {
        $inner .= ' <svg class="goods-exhibition-new-badge-svg" xmlns="http://www.w3.org/2000/svg" aria-label="NEW"'
            . ' role="img" width="48" height="24" viewBox="0 0 48 24">'
            . '<rect x="0" y="0" width="48" height="24" rx="12" ry="12" fill="#d9534f"/>'
            . '<text x="24" y="16" font-family="Helvetica, Arial, sans-serif" font-size="14" font-weight="700"'
            . ' fill="white" text-anchor="middle" dominant-baseline="middle">NEW</text>'
            . '</svg>';
    }
    $inner .= '</h3>';
    $inner .= '<div class="goods-exhibition-description">' . $desc . '</div>';
    if (!empty($price)) {
        $inner .= '<div class="goods-exhibition-price">' . $price . '</div>';
    }
    // 有链接时显示"了解更多"提示
    if ($item_url) {
        $inner .= '<span class="goods-exhibition-more-link">了解更多 →</span>';
    }
    $inner .= '</div>';
    $inner .= '<div class="goods-exhibition-image-container">';
    $inner .= '<img src="' . $image_url . '" alt="' . esc_attr($product['name']) . '"'
        . ' width="500" height="500" loading="lazy" class="goods-exhibition-image">';
    $inner .= '</div>';

    // 根据是否有链接选择包裹元素
    if ($item_url) {
        return '<a href="' . $item_url . '" target="_blank" rel="noopener noreferrer"'
            . ' class="goods-exhibition-item has-link">' . $inner . '</a>';
    }

    return '<div class="goods-exhibition-item">' . $inner . '</div>';
}

/**
 * 渲染单个海报卡片（复用模板）
 *
 * @param array $poster 海报产品数据
 * @param bool $is_first 是否为第一张海报（首屏优先加载）
 * @return string HTML
 */
function goods_exhibition_render_poster_card($poster, $is_first = false)
{
    $item_url = !empty($poster['url']) ? esc_url($poster['url']) : '';
    $name = esc_html($poster['name']);
    $desc = wp_kses_post($poster['description']);
    $price = esc_html($poster['price']);
    $image_url = esc_url($poster['poster_image_url']);

    // 首张海报用 eager 加载，后续用 lazy
    $loading_attr = $is_first ? 'eager' : 'lazy';
    $priority_attr = $is_first ? ' fetchpriority="high"' : '';

    // 构建海报内部内容：图片容器包含图片和文字叠加
    $inner = '<div class="goods-exhibition-poster-img-wrap">';
    $inner .= '<img src="' . $image_url . '" alt="' . esc_attr($poster['name']) . '"'
        . ' width="1920" height="640" loading="' . $loading_attr . '"' . $priority_attr
        . ' class="goods-exhibition-poster-img">';
    $inner .= '<div class="goods-exhibition-content poster-content">';
    $inner .= '<h3 class="goods-exhibition-title">' . $name . '</h3>';
    $inner .= '<div class="goods-exhibition-description">' . $desc . '</div>';
    if (!empty($price)) {
        $inner .= '<div class="goods-exhibition-price">' . $price . '</div>';
    }
    $inner .= '</div>';
    $inner .= '</div>';

    // 根据是否有链接选择包裹元素
    if ($item_url) {
        return '<a href="' . $item_url . '" target="_blank" rel="noopener noreferrer"'
            . ' class="goods-exhibition-item poster-item has-link">' . $inner . '</a>';
    }

    return '<div class="goods-exhibition-item poster-item">' . $inner . '</div>';
}

/**
 * 渲染首页海报幻灯片（含圆点指示器）
 *
 * @return string
 */
function goods_exhibition_render_poster_slider()
{
    $posters = goods_exhibition_get_poster_products();
    if (empty($posters)) {
        return '';
    }

    $poster_count = count($posters);

    ob_start();
    ?>
    <div class="goods-exhibition-wrapper poster-slider">
        <div class="poster-inner">
            <?php if ($poster_count > 1): ?>
                <button class="goods-exhibition-arrow goods-exhibition-arrow-left" aria-label="上一个"><span aria-hidden="true">&#10094;</span></button>
            <?php endif; ?>

            <div class="goods-exhibition-slider poster-slider-track">
                <?php foreach ($posters as $index => $poster): ?>
                    <?php echo goods_exhibition_render_poster_card($poster, $index === 0); ?>
                <?php endforeach; ?>
            </div>

            <?php if ($poster_count > 1): ?>
            <button class="goods-exhibition-arrow goods-exhibition-arrow-right" aria-label="下一个"><span aria-hidden="true">&#10095;</span></button>

            <!-- 圆点指示器 - 在图片底部与文字同区域 -->
            <div class="goods-exhibition-dots" role="tablist" aria-label="海报导航">
                <?php for ($i = 0; $i < $poster_count; $i++): ?>
                    <button class="goods-exhibition-dot<?php echo $i === 0 ? ' active' : ''; ?>"
                            role="tab"
                            aria-label="第 <?php echo $i + 1; ?> 张海报"
                            aria-selected="<?php echo $i === 0 ? 'true' : 'false'; ?>"></button>
                <?php endfor; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

/**
 * 短代码主要回调
 *
 * @param array $atts
 * @return string
 */
function goods_exhibition_shortcode_callback($atts)
{
    $atts = shortcode_atts(array('limit' => -1), $atts, 'goods_exhibition');

    // 验证并清理limit参数
    $limit = intval($atts['limit']);

    $output = goods_exhibition_render_poster_slider();

    // 使用缓存的查询函数
    $products = goods_exhibition_get_frontend_products($limit);

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
        // 跳过所有海报产品分类（任意分类下，只要产品被标记为海报就不在前端分组列表中显示）
        $has_non_poster = false;
        foreach ($items as $it) {
            if (intval($it['is_poster']) !== 1) {
                $has_non_poster = true;
                break;
            }
        }
        if (!$has_non_poster) {
            continue;
        }
        ?>
        <h2 class="goods-exhibition-category-title"><?php echo esc_html($category_name); ?></h2>
        <div class="goods-exhibition-wrapper">
            <button class="goods-exhibition-arrow goods-exhibition-arrow-left" aria-label="上一个"><span aria-hidden="true">&#10094;</span></button>
            <div class="goods-exhibition-slider">
                <?php foreach ($items as $product):
                    $is_new = isset($product['is_new']) && intval($product['is_new']) === 1;
                    echo goods_exhibition_render_product_card($product, $is_new);
                endforeach; ?>
            </div>
            <button class="goods-exhibition-arrow goods-exhibition-arrow-right" aria-label="下一个"><span aria-hidden="true">&#10095;</span></button>
        </div>
        <?php
    }
    $output .= ob_get_clean();

    return $output;
}
