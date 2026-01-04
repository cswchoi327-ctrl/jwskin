<?php get_header();?>
<div class="support-main-wrapper">
<div class="intro-section">
<span class="intro-badge">🔥 신청마감 임박</span>
<h1 class="intro-title">숨은 지원금 찾기</h1>
<p class="intro-subtitle">대한민국 국민 누구나 받을 수 있는 혜택</p>
</div>
<?php $cards=new WP_Query(['post_type'=>'support_card','posts_per_page'=>-1]);if($cards->have_posts()):?>
<div class="info-card-grid">
<?php while($cards->have_posts()):$cards->the_post();$amount=get_post_meta(get_the_ID(),'_card_amount',true);$amount_sub=get_post_meta(get_the_ID(),'_card_amount_sub',true);$target=get_post_meta(get_the_ID(),'_card_target',true);$period=get_post_meta(get_the_ID(),'_card_period',true);$link=get_post_meta(get_the_ID(),'_card_link',true)?:home_url();$featured=get_post_meta(get_the_ID(),'_card_featured',true);?>
<a href="<?php echo esc_url($link);?>" class="info-card <?php echo $featured?'featured':'';?>" target="_blank">
<div class="card-header">
<div class="card-amount"><?php echo esc_html($amount);?></div>
<div class="card-amount-sub"><?php echo esc_html($amount_sub);?></div>
</div>
<div class="card-body">
<h3 class="card-title"><?php the_title();?></h3>
<div class="card-description"><?php echo wp_trim_words(get_the_content(),20);?></div>
<div class="card-details">
<div class="detail-row"><span class="detail-label">대상</span><span class="detail-value"><?php echo esc_html($target);?></span></div>
<div class="detail-row"><span class="detail-label">기간</span><span class="detail-value"><?php echo esc_html($period);?></span></div>
</div>
<div class="card-cta">지금 바로 신청하기</div>
</div>
</a>
<?php endwhile;wp_reset_postdata();?>
</div>
<?php endif;?>
</div>
<?php get_footer();?>.
