<?php
/**
 * Template Name: 지원금 페이지
 */
get_header();

$main_url = get_option('support_main_url', home_url());
$ad_top = get_option('support_ad_code_top', '');
$ad_content = get_option('support_ad_code_content', '');
$ad_bottom = get_option('support_ad_code_bottom', '');

$cards_query = new WP_Query([
    'post_type' => 'support_card',
    'posts_per_page' => -1,
    'orderby' => 'menu_order',
    'order' => 'ASC'
]);
?>

<div class="support-main-wrapper">
    <div class="intro-section">
        <span class="intro-badge">🔥 신청마감 D-3</span>
        <h1 class="intro-title">숨은 지원금 찾기</h1>
        <p class="intro-subtitle">1인 평균 127만원 환급! 지금 바로 확인하세요</p>
    </div>

    <?php if ($ad_top): ?>
    <div class="ad-container top-banner">
        <?php echo $ad_top; ?>
    </div>
    <?php endif; ?>

    <?php if ($cards_query->have_posts()): ?>
        <div class="info-card-grid">
            <?php 
            $count = 0;
            while ($cards_query->have_posts()): 
                $cards_query->the_post();
                
                // 3개마다 중간 광고 삽입
                if ($ad_content && $count > 0 && $count % 3 === 0):
            ?>
                    <div class="ad-container in-content">
                        <?php echo $ad_content; ?>
                    </div>
            <?php 
                endif;
                $count++;
                
                $amount = get_post_meta(get_the_ID(), '_card_amount', true);
                $amount_sub = get_post_meta(get_the_ID(), '_card_amount_sub', true);
                $target = get_post_meta(get_the_ID(), '_card_target', true);
                $period = get_post_meta(get_the_ID(), '_card_period', true);
                $link = get_post_meta(get_the_ID(), '_card_link', true) ?: $main_url;
                $featured = get_post_meta(get_the_ID(), '_card_featured', true);
            ?>
            
            <a href="<?php echo esc_url($link); ?>" class="info-card <?php echo $featured ? 'featured' : ''; ?>" target="_blank">
                <div class="card-header">
                    <div class="card-amount"><?php echo esc_html($amount); ?></div>
                    <div class="card-amount-sub"><?php echo esc_html($amount_sub); ?></div>
                </div>
                <div class="card-body">
                    <h3 class="card-title"><?php the_title(); ?></h3>
                    <div class="card-description">
                        <?php echo wp_trim_words(get_the_content(), 30); ?>
                    </div>
                    <div class="card-details">
                        <div class="detail-row">
                            <span class="detail-label">지원대상</span>
                            <span class="detail-value"><?php echo esc_html($target); ?></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">신청시기</span>
                            <span class="detail-value"><?php echo esc_html($period); ?></span>
                        </div>
                    </div>
                    <div class="card-cta">
                        지금 바로 신청하기
                    </div>
                </div>
            </a>
            
            <?php endwhile; wp_reset_postdata(); ?>
        </div>
    <?php else: ?>
        <p style="text-align: center; padding: 60px 20px; color: #999;">
            등록된 지원금 카드가 없습니다.
        </p>
    <?php endif; ?>

    <?php if ($ad_bottom): ?>
    <div class="ad-container bottom-banner">
        <?php echo $ad_bottom; ?>
    </div>
    <?php endif; ?>
</div>

<?php get_footer(); ?>
