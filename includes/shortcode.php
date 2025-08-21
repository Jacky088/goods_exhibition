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

function goods_exhibition_shortcode_callback($atts) {
    // 默认不限制数量，获取所有产品
    $atts = shortcode_atts(
        array('limit' => -1),
        $atts,
        'goods_exhibition'
    );

    global $wpdb;
    $table_name = $wpdb->prefix . 'goods_exhibition';

    $limit = intval($atts['limit']);
    $limit_clause = $limit > 0 ? "LIMIT $limit" : "";

    // 按类别升序，创建时间降序取数据
    $products = $wpdb->get_results("SELECT * FROM {$table_name} ORDER BY category ASC, created_at DESC $limit_clause", ARRAY_A);

    if (empty($products)) {
        return '<div class="goods-exhibition-empty">暂无产品</div>';
    }

    // 按产品类别分组
    $grouped_products = [];
    foreach ($products as $product) {
        $category = trim($product['category']);
        if ($category === '') {
            $category = __('未分类', 'goods-exhibition'); // 默认分类
        }
        if (!isset($grouped_products[$category])) {
            $grouped_products[$category] = [];
        }
        $grouped_products[$category][] = $product;
    }

    ob_start();

    // 遍历每个分类输出对应产品
    foreach ($grouped_products as $category_name => $items) {
        ?>
        <h2 class="goods-exhibition-category-title"><?php echo esc_html($category_name); ?></h2>
        <div class="goods-exhibition-wrapper">
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
                            <img src="<?php echo esc_url($product['image_url']); ?>" alt="<?php echo esc_attr($product['name']); ?>" class="goods-exhibition-image no-lightbox">
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
                            <img src="<?php echo esc_url($product['image_url']); ?>" alt="<?php echo esc_attr($product['name']); ?>" class="goods-exhibition-image no-lightbox">
                        </div>
                    </div>
                <?php endif; ?>
                <?php endforeach; ?>
            </div>
            <button class="goods-exhibition-arrow goods-exhibition-arrow-right" aria-label="下一个"><span>&#10095;</span></button>
        </div>
        <?php
    }

    return ob_get_clean();
}
