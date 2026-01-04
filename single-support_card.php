<?php get_header();?>
<div class="support-main-wrapper">
<?php if(have_posts()):while(have_posts()):the_post();$amount=get_post_meta(get_the_ID(),'_card_amount',true);$amount_sub=get_post_meta(get_the_ID(),'_card_amount_sub',true);$target=get_post_meta(get_the_ID(),'_card_target',true);$period=get_post_meta(get_the_ID(),'_card_period',true);$link=get_post_meta(get_the_ID(),'_card_link',true);?>
<article class="single-card-article">
<div class="single-card-header">
<h1 class="single-card-title"><?php the_title();?></h1>
<div class="single-card-meta">
<span class="meta-amount"><?php echo esc_html($amount);?></span>
<span class="meta-divider">|</span>
<span class="meta-target"><?php echo esc_html($target);?></span>
<span class="meta-divider">|</span>
<span class="meta-period"><?php echo esc_html($period);?></span>
</div>
</div>
<div class="single-card-content">
<?php the_content();?>
</div>
<?php if($link):?>
<div class="single-card-cta">
<a href="<?php echo esc_url($link);?>" class="single-cta-button" target="_blank" rel="noopener">🎯 지금 즉시 신청하기</a>
</div>
<?php endif;?>
<div class="single-card-footer">
<a href="<?php echo home_url('/');?>" class="back-button">← 목록으로 돌아가기</a>
</div>
</article>
<?php endwhile;endif;?>
</div>
<?php get_footer();?>
