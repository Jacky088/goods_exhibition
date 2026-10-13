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
    $desc_plain = esc_attr(wp_strip_all_tags($product['description']));
    $price = esc_html(goods_exhibition_format_price_display($product['price']));
    $image_url = esc_url($product['image_url']);

    // 存储完整商品数据供详情弹窗提取（完整 500 字介绍、大图、价格等）
    $product_json = esc_attr(wp_json_encode(array(
        'name' => $product['name'],
        'description' => $product['description'],
        'price' => goods_exhibition_format_price_display($product['price']),
        'image_url' => $image_url,
        'url' => !empty($product['url']) ? $product['url'] : '',
        'category' => !empty($product['category']) ? $product['category'] : '',
        'is_new' => $is_new ? 1 : 0,
    )));

    // 构建卡片内部内容
    $inner = '<div class="goods-exhibition-content">';
    if ($is_new) {
        $inner .= '<span class="goods-exhibition-new-badge">NEW</span>';
    }
    // title 属性支持原生悬浮提示：超长品名悬浮即可显示全名（若有外链，标题支持直接点击跳转）
    if ($item_url) {
        $inner .= '<h3 class="goods-exhibition-title" title="' . esc_attr($product['name']) . '"><a href="' . $item_url . '" target="_blank" rel="noopener noreferrer" class="goods-exhibition-title-link">' . $name . '</a></h3>';
    } else {
        $inner .= '<h3 class="goods-exhibition-title" title="' . esc_attr($product['name']) . '">' . $name . '</h3>';
    }
    // 描述同样增加 title 属性支持原生悬浮提示
    $inner .= '<div class="goods-exhibition-description" title="' . $desc_plain . '">' . $desc . '</div>';

    // 价格与详情按钮同行显示（详情按钮位于右侧，独立按钮彻底杜绝误触打开外链）
    $inner .= '<div class="goods-exhibition-price-row">';
    if (!empty($price)) {
        $inner .= '<div class="goods-exhibition-price">' . $price . '</div>';
    } else {
        $inner .= '<div class="goods-exhibition-price-placeholder"></div>';
    }
    $inner .= '<button type="button" class="goods-exhibition-quickview-btn" aria-label="查看商品详情" title="查看完整详情" data-img="' . $image_url . '" data-product="' . $product_json . '">详情</button>';
    $inner .= '</div>';
    $inner .= '</div>';

    // 图片容器：点击图片直接唤起详情弹窗（与详情按钮保持一致的交互效果）
    // 增加 no-lightbox / viewer-ignore 等防主题大图画廊拦截属性，防止主题自带灯箱产生大图或黑框
    $inner .= '<div class="goods-exhibition-image-container goods-exhibition-image-trigger no-lightbox no-zoom viewer-ignore ignore-lightbox" role="button" tabindex="0" aria-label="' . esc_attr($product['name']) . ' 详情" data-no-viewer="true" data-no-lightbox="true" data-lightbox="false" data-fancybox="none" data-no-zoom="true">';
    $inner .= '<img src="' . $image_url . '" alt="' . esc_attr($product['name']) . '"'
        . ' width="500" height="500" loading="lazy" class="goods-exhibition-image no-lightbox no-zoom viewer-ignore ignore-lightbox"'
        . ' data-no-viewer="true" data-no-lightbox="true" data-lightbox="false" data-fancybox="none" data-no-zoom="true" draggable="false">';
    $inner .= '</div>';

    // 卡片最外层统一为 div 容器（不再用 <a> 包裹整卡，解决点击详情按钮时误打开外部链接的问题）
    $item_classes = 'goods-exhibition-item' . ($item_url ? ' has-link' : ' is-clickable has-quickview') . ($is_new ? ' is-new' : '');
    return '<div class="' . $item_classes . '" data-id="' . intval($product['id']) . '" data-img="' . $image_url . '" data-url="' . $item_url . '" data-product="' . $product_json . '">' . $inner . '</div>';
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
    $price = esc_html(goods_exhibition_format_price_display($poster['price']));
    $image_url = esc_url($poster['poster_image_url']);

    // 首张海报用 eager 加载，后续用 lazy
    $loading_attr = $is_first ? 'eager' : 'lazy';
    $priority_attr = $is_first ? ' fetchpriority="high"' : '';

    // 构建海报内部内容：图片容器包含图片和文字叠加
    $inner = '<div class="goods-exhibition-poster-img-wrap">';
    $inner .= '<img src="' . $image_url . '" alt="' . esc_attr($poster['name']) . '"'
        . ' width="1920" height="720" loading="' . $loading_attr . '"' . $priority_attr
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
        // 说明：已取消"全部产品均为海报的分类整组隐藏"规则。
        // 现在所有分类（含全部为海报的分类）都会正常显示在分组列表中；
        // 标记为海报的产品仍会同时出现在顶部的海报轮播中（轮播最多展示 6 张）。
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

    // 追加商品详情全貌弹窗 (Quick View Modal)，页面单例输出
    static $modal_rendered = false;
    if (!$modal_rendered) {
        $modal_rendered = true;
        ob_start();
        ?>
        <div id="goods-exhibition-quickview-modal" class="goods-quickview-modal" style="display:none;" aria-hidden="true" role="dialog" aria-modal="true">
            <div class="goods-quickview-overlay"></div>
            <div class="goods-quickview-dialog">
                <button type="button" class="goods-quickview-close" aria-label="关闭窗口">&times;</button>
                <div class="goods-quickview-body">
                    <div id="goods-quickview-img-col" class="goods-quickview-img-col no-lightbox no-zoom viewer-ignore ignore-lightbox" data-no-viewer="true" data-no-lightbox="true" data-lightbox="false" data-fancybox="none">
                        <img id="goods-quickview-img" src="" alt="" style="display:none;" class="goods-quickview-img-rendered no-lightbox no-zoom viewer-ignore ignore-lightbox" data-no-viewer="true" data-no-lightbox="true" data-lightbox="false" data-fancybox="none" draggable="false">
                    </div>
                    <div class="goods-quickview-info-col">
                        <div class="goods-quickview-tags">
                            <span id="goods-quickview-category" class="goods-quickview-badge-cat" style="display:none;"></span>
                        </div>
                        <div class="goods-quickview-title-wrap">
                            <h3 id="goods-quickview-title" class="goods-quickview-title"></h3>
                            <span id="goods-quickview-new" class="goods-quickview-badge-new" style="display:none;">NEW</span>
                        </div>
                        <div id="goods-quickview-price" class="goods-quickview-price"></div>
                        <div class="goods-quickview-desc-wrapper">
                            <h4 class="goods-quickview-desc-heading">商品详情与卖点</h4>
                            <div id="goods-quickview-desc" class="goods-quickview-desc"></div>
                        </div>
                        <div id="goods-quickview-actions" class="goods-quickview-actions" style="display:none;">
                            <a id="goods-quickview-link" href="#" target="_blank" rel="noopener noreferrer" class="goods-quickview-btn-primary">
                                <span>访问商品 / 了解更多</span>
                                <span class="goods-quickview-btn-arrow">&rarr;</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
        $output .= ob_get_clean();
    }

    return $output;
}
