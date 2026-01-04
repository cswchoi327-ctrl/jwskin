<?php
function support_theme_setup(){add_theme_support('title-tag');add_theme_support('post-thumbnails');add_theme_support('html5',['search-form','comment-form','comment-list','gallery','caption']);register_nav_menus(['primary'=>'메인 메뉴']);}
add_action('after_setup_theme','support_theme_setup');
function support_enqueue_scripts(){wp_enqueue_style('support-style',get_stylesheet_uri(),array(),filemtime(get_template_directory().'/style.css'));}
add_action('wp_enqueue_scripts','support_enqueue_scripts');
function register_support_card_cpt(){register_post_type('support_card',['labels'=>['name'=>'지원금 카드','singular_name'=>'카드','add_new'=>'새 카드 추가','add_new_item'=>'새 카드 추가','edit_item'=>'카드 수정','view_item'=>'카드 보기','all_items'=>'모든 카드'],'public'=>true,'publicly_queryable'=>true,'show_ui'=>true,'show_in_menu'=>true,'has_archive'=>false,'menu_icon'=>'dashicons-money-alt','supports'=>['title','editor','custom-fields'],'show_in_rest'=>true,'rewrite'=>['slug'=>'support']]);flush_rewrite_rules();}
add_action('init','register_support_card_cpt');

/* 탭메뉴 설정 페이지 */
function support_tabs_menu(){add_theme_page('탭메뉴 설정','탭메뉴 설정','manage_options','support-tabs','support_tabs_page');}
add_action('admin_menu','support_tabs_menu');
function support_tabs_page(){if(isset($_POST['support_tabs_save'])){check_admin_referer('support_tabs_action','support_tabs_nonce');$tabs=[];for($i=1;$i<=10;$i++){if(!empty($_POST["tab_name_$i"])){$tabs[]=array('name'=>sanitize_text_field($_POST["tab_name_$i"]),'link'=>esc_url_raw($_POST["tab_link_$i"]),'target'=>sanitize_text_field($_POST["tab_target_$i"]));}}update_option('support_tabs',$tabs);echo '<div class="updated"><p>✅ 탭메뉴가 저장되었습니다!</p></div>';}$tabs=get_option('support_tabs',[]);?>
<div class="wrap">
<h1>🔖 탭메뉴 설정</h1>
<p>홈페이지 상단에 표시될 탭메뉴를 설정하세요. 최대 10개까지 추가 가능합니다.</p>
<form method="post">
<?php wp_nonce_field('support_tabs_action','support_tabs_nonce');?>
<style>
.tabs-table{width:100%;background:#fff;border:1px solid #ddd;border-radius:8px;margin:20px 0}
.tabs-table th{background:#f5f5f5;padding:12px;text-align:left;font-weight:600;border-bottom:2px solid #ddd}
.tabs-table td{padding:12px;border-bottom:1px solid #eee}
.tabs-table input[type="text"]{width:100%;padding:8px;border:1px solid #ddd;border-radius:4px}
.tabs-table select{padding:8px;border:1px solid #ddd;border-radius:4px}
.save-btn{background:#2563EB;color:#fff;padding:12px 30px;border:none;border-radius:6px;font-size:16px;font-weight:700;cursor:pointer}
.save-btn:hover{background:#1E40AF}
.notice-info{background:#E0F2FE;border-left:4px solid #0EA5E9;padding:15px;margin:20px 0;border-radius:4px}
</style>
<div class="notice-info">
<strong>💡 사용 팁:</strong>
<ul style="margin:10px 0 0 20px">
<li>탭 이름: 메뉴에 표시될 텍스트 (예: 홈, 소개, 문의)</li>
<li>링크: 클릭 시 이동할 URL (예: https://example.com 또는 /about)</li>
<li>새창: 링크를 새 창에서 열지 선택</li>
<li>빈 칸은 자동으로 무시됩니다</li>
</ul>
</div>
<table class="tabs-table">
<thead>
<tr>
<th style="width:50px">순서</th>
<th style="width:25%">탭 이름</th>
<th style="width:45%">링크</th>
<th style="width:15%">새창 열기</th>
<th style="width:15%">미리보기</th>
</tr>
</thead>
<tbody>
<?php for($i=1;$i<=10;$i++){$tab=$tabs[$i-1]??null;?>
<tr>
<td style="text-align:center;font-weight:700;color:#666"><?php echo $i;?></td>
<td><input type="text" name="tab_name_<?php echo $i;?>" value="<?php echo $tab?esc_attr($tab['name']):'';?>" placeholder="예: 홈"/></td>
<td><input type="text" name="tab_link_<?php echo $i;?>" value="<?php echo $tab?esc_attr($tab['link']):'';?>" placeholder="예: <?php echo home_url('/');?>"/></td>
<td>
<select name="tab_target_<?php echo $i;?>">
<option value="_self" <?php if($tab&&$tab['target']=='_self')echo 'selected';?>>현재 창</option>
<option value="_blank" <?php if($tab&&$tab['target']=='_blank')echo 'selected';?>>새 창</option>
</select>
</td>
<td style="text-align:center">
<?php if($tab):?>
<a href="<?php echo esc_url($tab['link']);?>" target="<?php echo esc_attr($tab['target']);?>" style="color:#2563EB;text-decoration:none">🔗 보기</a>
<?php else:?>
<span style="color:#ccc">-</span>
<?php endif;?>
</td>
</tr>
<?php }?>
</tbody>
</table>
<p style="text-align:center;margin:30px 0">
<button type="submit" name="support_tabs_save" class="save-btn">💾 탭메뉴 저장</button>
</p>
</form>
<div class="notice-info" style="margin-top:30px">
<strong>📌 기본 탭 예시:</strong>
<ul style="margin:10px 0 0 20px">
<li><strong>홈:</strong> <?php echo home_url('/');?></li>
<li><strong>전체 지원금:</strong> <?php echo home_url('/');?></li>
<li><strong>청년 지원:</strong> <?php echo home_url('/?filter=youth');?></li>
<li><strong>노인 지원:</strong> <?php echo home_url('/?filter=senior');?></li>
<li><strong>문의하기:</strong> <?php echo home_url('/contact');?></li>
</ul>
</div>
</div>
<?php }

function add_support_card_meta_boxes(){add_meta_box('support_card_details','💰 카드 정보 자동 생성','render_support_card_meta_box','support_card','normal','high');}
add_action('add_meta_boxes','add_support_card_meta_boxes');
function render_support_card_meta_box($post){wp_nonce_field('support_card_save','support_card_nonce');$amount=get_post_meta($post->ID,'_card_amount',true);$amount_sub=get_post_meta($post->ID,'_card_amount_sub',true);$target=get_post_meta($post->ID,'_card_target',true);$period=get_post_meta($post->ID,'_card_period',true);$link=get_post_meta($post->ID,'_card_link',true);$featured=get_post_meta($post->ID,'_card_featured',true);?>
<style>.sp-field{margin:15px 0}.sp-field label{display:block;font-weight:600;margin-bottom:5px}.sp-field input{width:100%;padding:8px;border:1px solid #ddd;border-radius:4px}.sp-btn{background:linear-gradient(135deg,#2563EB 0%,#7C3AED 100%);color:#fff;border:none;padding:15px 30px;border-radius:8px;font-weight:700;cursor:pointer;margin:10px 0;font-size:16px}.sp-btn:hover{background:linear-gradient(135deg,#1E40AF 0%,#6D28D9 100%)}.sp-notice{background:#FEF3C7;padding:15px;margin:15px 0;border-left:4px solid #F59E0B;border-radius:4px}.sp-alert{padding:15px;margin:15px 0;border-radius:8px;font-weight:600}</style>
<div class="sp-notice"><strong>🤖 자동 생성 시스템</strong><br>키워드 입력 → 생성 버튼 → 제목과 내용이 자동 작성됨<br><small style="color:#92400e">청년도약계좌, 청년내일채움공제, 근로장려금, 자녀장려금, 청년월세지원, 기초연금, 출산지원금, 실업급여</small></div>
<div class="sp-field"><label style="font-size:16px">📌 키워드 입력</label><input type="text" id="sp_kw" placeholder="예: 청년도약계좌" style="font-size:15px"/></div>
<button type="button" class="sp-btn" onclick="spAutoFill()">✨ 자동 생성하기</button>
<div id="sp_status"></div>
<script>
var AD_CODE='YOUR_ADSENSE_CODE_HERE';
var DATA={'청년도약계좌':{t:'청년도약계좌 - 5년 만기 시 최대 5,000만원',a:'최대 5,000만원',s:'정부 기여금+이자',tg:'만 19~34세 청년',p:'상시(선착순)',l:'https://www.youthaccount.kr',problem:'월세, 생활비 부담으로 저축이 불가능',solution:'월 70만원만 넣으면 정부가 6% 더 얹어줌',proof:'선착순 100만명 가입 완료'},'청년내일채움공제':{t:'청년내일채움공제 - 2년 근속 시 1,600만원',a:'최대 1,600만원',s:'정부+기업',tg:'중소기업 청년',p:'기업 참여 시',l:'https://www.work.go.kr/jobgong',problem:'취업했는데 월급 적어서 저축 못함',solution:'회사 다니면서 자동으로 1,600만원 모임',proof:'참여 기업 50만곳 돌파'},'근로장려금':{t:'근로장려금 - 최대 330만원 현금',a:'최대 330만원',s:'연 1회 현금',tg:'저소득 근로자',p:'5월,9월',l:'https://www.nts.go.kr/eitc',problem:'일해도 생활비 빠듯해서 저축 불가',solution:'신청만 하면 최대 330만원 현금 지급',proof:'작년 124만명이 수령'},'자녀장려금':{t:'자녀장려금 - 자녀당 100만원',a:'자녀당 100만원',s:'현금',tg:'저소득 가구(자녀有)',p:'5월',l:'https://www.nts.go.kr/ctc',problem:'아이 키우는데 돈이 너무 많이 듦',solution:'자녀 1명당 100만원씩 현금 지급',proof:'근로장려금과 중복 가능'},'청년월세지원':{t:'청년월세 지원 - 월 20만원x12개월',a:'최대 240만원',s:'월 20만원x12',tg:'만 19~34세',p:'예산 소진 시 마감',l:'https://www.bokjiro.go.kr',problem:'월세가 월급의 절반, 독립이 불가능',solution:'매월 20만원씩 12개월간 지원',proof:'신청자 전년비 340% 급증'},'기초연금':{t:'기초연금 - 매월 334,810원',a:'월 334,810원',s:'평생 매월',tg:'만 65세 이상',p:'생일 1개월 전',l:'https://basicpension.mohw.go.kr',problem:'노후 준비 없이 은퇴, 생활비 걱정',solution:'만 65세부터 평생 매월 33만원',proof:'소득 하위 70% 모두 수령 가능'},'출산지원금':{t:'출산지원금 - 첫째 200만원',a:'첫째 200만원',s:'둘째 300만원',tg:'출산 가정',p:'출산 후 60일',l:'https://www.bokjiro.go.kr',problem:'출산비용 부담으로 아이 포기',solution:'첫째 200, 둘째 300, 셋째 500만원',proof:'지자체 추가 지원 최대 1억'},'실업급여':{t:'실업급여 - 최대 1,782만원',a:'최대 1,782만원',s:'최대 270일',tg:'실직자(고용보험)',p:'퇴직 후 12개월',l:'https://www.ei.go.kr',problem:'갑자기 실직, 당장 생활비 막막',solution:'평균임금 60% 최대 270일 지급',proof:'퇴직 후 12개월 내 신청 필수'}};
function spAutoFill(){var k=document.getElementById('sp_kw').value.trim(),st=document.getElementById('sp_status');if(!k){st.innerHTML='<div class="sp-alert" style="background:#fee;color:#c00;border:2px solid #f00">❌ 키워드를 입력하세요</div>';return}st.innerHTML='<div class="sp-alert" style="background:#fef3c7;color:#92400e;border:2px solid #f59e0b">⏳ 생성 중...</div>';setTimeout(function(){var d=DATA[k]||{t:k+' - 지금 신청',a:'최대 300만원',s:'정부 지원',tg:'대한민국 국민',p:'상시',l:'https://www.bokjiro.go.kr',problem:'정부 지원금 몰라서 못 받음',solution:'지금 신청하면 혜택 가능',proof:'많은 사람들이 수령 중'};document.getElementById('card_amount').value=d.a;document.getElementById('card_amount_sub').value=d.s;document.getElementById('card_target').value=d.tg;document.getElementById('card_period').value=d.p;document.getElementById('card_link').value=d.l;var html='<div style="max-width:800px;margin:0 auto;font-family:-apple-system,sans-serif;line-height:1.8;color:#333">';html+='<div style="background:linear-gradient(135deg,#FF6B6B,#FF8E53);color:#fff;padding:30px;border-radius:20px;text-align:center;margin-bottom:30px"><h1 style="font-size:32px;font-weight:800;margin:0 0 10px 0">🔥 '+d.t+'</h1><p style="font-size:18px;margin:0">5분만 투자하면 평생 후회 없습니다</p></div>';html+='<div style="background:#fff;border-radius:20px;padding:30px;margin-bottom:25px;box-shadow:0 4px 20px rgba(0,0,0,.08)"><span style="display:inline-block;background:linear-gradient(135deg,#EF4444,#DC2626);color:#fff;padding:8px 20px;border-radius:50px;font-size:13px;font-weight:700;margin-bottom:15px">😰 이런 고민 있으신가요?</span><h2 style="font-size:24px;font-weight:800;color:#1a1a1a;margin-bottom:15px">'+d.problem+'?</h2><div style="background:#FEF2F2;padding:20px;border-radius:12px;border-left:4px solid #EF4444;margin:20px 0"><p style="font-size:16px;color:#991B1B;margin:0"><strong>이대로 가면 수백만원을 그냥 포기하는 겁니다</strong></p></div></div>';html+='<div style="background:#fff;border-radius:20px;padding:30px;margin-bottom:25px;box-shadow:0 4px 20px rgba(0,0,0,.08)"><span style="display:inline-block;background:linear-gradient(135deg,#DC2626,#991B1B);color:#fff;padding:8px 20px;border-radius:50px;font-size:13px;font-weight:700;margin-bottom:15px">⚠️ 더 큰 문제</span><h2 style="font-size:24px;font-weight:800;margin-bottom:15px">모르면 손해, 알면 인생 역전</h2><div style="background:linear-gradient(135deg,#FEE2E2,#FECACA);padding:20px;border-radius:15px;text-align:center;border:3px dashed #EF4444;margin:20px 0"><div style="font-size:18px;font-weight:800;color:#991B1B;margin-bottom:10px">⏰ 지금 이 순간에도 마감되고 있습니다</div><div style="font-size:32px;font-weight:800;color:#DC2626;margin:10px 0">23:45:12</div><p style="margin:10px 0 0 0;font-size:14px;color:#991B1B">선착순 마감 임박! 오늘 신청 안 하면 다음 기회는 없습니다</p></div><p style="font-size:16px;color:#666">당신이 모르는 사이, <strong style="color:#DC2626">옆집은 이미 받아갑니다</strong></p></div>';html+='<div style="background:#fff;border-radius:20px;padding:30px;margin-bottom:25px;box-shadow:0 4px 20px rgba(0,0,0,.08)"><span style="display:inline-block;background:linear-gradient(135deg,#2563EB,#7C3AED);color:#fff;padding:8px 20px;border-radius:50px;font-size:13px;font-weight:700;margin-bottom:15px">✨ 해결책</span><h2 style="font-size:24px;font-weight:800;margin-bottom:15px">딱 5분이면 찾을 수 있습니다</h2><p style="font-size:17px;margin-bottom:20px">'+d.solution+'</p><div style="background:#F0FDF4;padding:25px;border-radius:15px;border-left:5px solid #10B981"><div style="font-size:18px;font-weight:800;color:#065F46;margin-bottom:15px">💯 이미 받아간 사람들</div><p style="font-size:15px;color:#064E3B;margin:10px 0">✓ '+d.proof+'</p></div></div>';html+='<div style="background:linear-gradient(135deg,#10B981,#059669);color:#fff;padding:30px;border-radius:20px;text-align:center;margin:30px 0;box-shadow:0 8px 30px rgba(16,185,129,.3)"><div style="font-size:24px;font-weight:800;margin-bottom:10px">🎁 지금 클릭하면 무료 확인</div><div style="font-size:16px;margin-bottom:20px">내가 받을 수 있는 금액 1분 만에 확인</div><a href="'+d.l+'" style="display:inline-block;background:#fff;color:#059669;padding:18px 50px;border-radius:50px;font-size:18px;font-weight:800;text-decoration:none;box-shadow:0 4px 15px rgba(0,0,0,.2)" target="_blank">👉 '+d.a+' 받으러 가기</a><p style="margin-top:15px;font-size:13px">※ 개인정보 입력 없음 | 100% 무료 확인</p></div>';html+='<div style="background:#fff;border-radius:20px;padding:30px;box-shadow:0 4px 20px rgba(0,0,0,.08)"><span style="display:inline-block;background:linear-gradient(135deg,#2563EB,#1E40AF);color:#fff;padding:8px 20px;border-radius:50px;font-size:13px;font-weight:700;margin-bottom:15px">🎯 해당되면 즉시 신청</span><h2 style="font-size:24px;font-weight:800;margin-bottom:15px">신청 자격</h2><div style="background:#FEF3C7;padding:20px;border-radius:15px"><p style="font-size:16px;line-height:2;margin:0"><strong>대상:</strong> '+d.tg+'<br><strong>기간:</strong> '+d.p+'<br><strong>금액:</strong> '+d.a+'</p></div></div>';html+='<div style="background:linear-gradient(135deg,#DC2626,#991B1B);color:#fff;padding:30px;border-radius:20px;text-align:center;margin:40px 0;box-shadow:0 8px 30px rgba(220,38,38,.3)"><div style="font-size:24px;font-weight:800;margin-bottom:10px">⚡ 마지막 기회입니다</div><div style="font-size:16px;margin-bottom:20px">오늘 신청 안 하면 내일도 미룹니다</div><a href="'+d.l+'" style="display:inline-block;background:#fff;color:#DC2626;padding:18px 50px;border-radius:50px;font-size:18px;font-weight:800;text-decoration:none;box-shadow:0 4px 15px rgba(0,0,0,.2)" target="_blank">🎯 지금 즉시 신청하기</a><p style="margin-top:20px;font-size:14px">✓ 딱 5분이면 끝<br>✓ 복잡한 절차 없음<br>✓ 100% 무료</p></div>';html+='</div>';var blocks=[wp.blocks.createBlock('core/html',{content:html})];wp.data.dispatch('core/editor').resetEditorBlocks([]);wp.data.dispatch('core/editor').editPost({title:d.t});wp.data.dispatch('core/block-editor').resetBlocks(blocks);st.innerHTML='<div class="sp-alert" style="background:#d1fae5;color:#065f46;border:2px solid #10b981">✅ 파소나 법칙 적용 완료! 위에서 확인하고 발행하세요!</div>'},500)}
</script>
<hr style="margin:20px 0;border:none;border-top:1px solid #ddd"/>
<div class="sp-field"><label>💰 금액</label><input type="text" name="card_amount" id="card_amount" value="<?php echo esc_attr($amount);?>"/></div>
<div class="sp-field"><label>💬 부가</label><input type="text" name="card_amount_sub" id="card_amount_sub" value="<?php echo esc_attr($amount_sub);?>"/></div>
<div class="sp-field"><label>👥 대상</label><input type="text" name="card_target" id="card_target" value="<?php echo esc_attr($target);?>"/></div>
<div class="sp-field"><label>📅 기간</label><input type="text" name="card_period" id="card_period" value="<?php echo esc_attr($period);?>"/></div>
<div class="sp-field"><label>🔗 URL</label><input type="url" name="card_link" id="card_link" value="<?php echo esc_attr($link);?>"/></div>
<div class="sp-field"><label><input type="checkbox" name="card_featured" value="1" <?php checked($featured,'1');?>/> 🔥 인기</label></div>
<?php }
function save_support_card_meta($post_id){if(!isset($_POST['support_card_nonce'])||!wp_verify_nonce($_POST['support_card_nonce'],'support_card_save'))return;if(defined('DOING_AUTOSAVE')&&DOING_AUTOSAVE)return;$fields=['card_amount','card_amount_sub','card_target','card_period','card_link'];foreach($fields as $f)if(isset($_POST[$f]))update_post_meta($post_id,'_'.$f,sanitize_text_field($_POST[$f]));update_post_meta($post_id,'_card_featured',isset($_POST['card_featured'])?'1':'0');}
add_action('save_post_support_card','save_support_card_meta');
