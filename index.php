<?php get_header(); ?>

<div class="support-main-wrapper">
    <div class="intro-section">
        <span class="intro-badge">🔥 신청마감 임박</span>
        <h1 class="intro-title">숨은 지원금 찾기</h1>
        <p class="intro-subtitle">대한민국 국민이라면 누구나 받을 수 있는 혜택을 확인하세요</p>
    </div>

    <?php 
    $ad_top = get_option('support_ad_code_top', '');
    if ($ad_top): 
    ?>
    <div class="ad-container top-banner">
        <?php echo $ad_top; ?>
    </div>
    <?php endif; ?>

    <?php if (have_posts()): ?>
        <div class="info-card-grid">
            <?php while (have_posts()): the_post(); ?>
                <article class="info-card">
                    <div class="card-body">
                        <h2 class="card-title"><?php the_title(); ?></h2>
                        <div class="card-description">
                            <?php the_excerpt(); ?>
                        </div>
                        <a href="<?php the_permalink(); ?>" class="card-cta">
                            자세히 보기
                        </a>
                    </div>
                </article>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <p style="text-align: center; padding: 60px 20px; color: #999;">
            아직 등록된 지원금 카드가 없습니다.<br>
            관리자 화면에서 새 카드를 추가해주세요.
        </p>
    <?php endif; ?>

    <?php 
    $ad_bottom = get_option('support_ad_code_bottom', '');
    if ($ad_bottom): 
    ?>
    <div class="ad-container bottom-banner">
        <?php echo $ad_bottom; ?>
    </div>
    <?php endif; ?>
</div>

<?php get_footer(); ?>
