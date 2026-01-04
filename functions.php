<?php
/**
 * Functions.php - 핵심 기능
 * - 관리자 화면에서 카드 관리
 * - 키워드만 입력하면 템플릿 기반으로 콘텐츠 자동 생성
 * - 모든 광고 플랫폼 지원
 */

// ==================== 테마 설정 ====================
function support_theme_setup() {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('custom-logo');
    
    register_nav_menus([
        'primary' => '메인 메뉴',
        'footer' => '푸터 메뉴'
    ]);
}
add_action('after_setup_theme', 'support_theme_setup');

// ==================== 스타일/스크립트 로드 ====================
function support_enqueue_scripts() {
    wp_enqueue_style('support-style', get_stylesheet_uri());
    wp_enqueue_script('support-front-js', get_template_directory_uri() . '/js/front.js', [], '1.0', true);
}
add_action('wp_enqueue_scripts', 'support_enqueue_scripts');

function support_admin_enqueue_scripts($hook) {
    // 지원금 카드 편집 페이지에서만 로드
    if ($hook !== 'post.php' && $hook !== 'post-new.php') {
        return;
    }
    
    global $post_type;
    if ($post_type !== 'support_card') {
        return;
    }
    
    wp_enqueue_script('support-admin-js', get_template_directory_uri() . '/js/admin.js', ['jquery'], '1.0.1', true);
    wp_localize_script('support-admin-js', 'supportAdmin', [
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('support_admin_nonce')
    ]);
}
add_action('admin_enqueue_scripts', 'support_admin_enqueue_scripts');

// ==================== 커스텀 포스트 타입 ====================
function register_support_card_cpt() {
    register_post_type('support_card', [
        'labels' => [
            'name' => '지원금 카드',
            'singular_name' => '지원금 카드',
            'add_new' => '새 카드 추가',
            'add_new_item' => '새 지원금 카드',
            'edit_item' => '카드 편집',
            'all_items' => '모든 카드'
        ],
        'public' => true,
        'has_archive' => false,
        'menu_icon' => 'dashicons-money-alt',
        'supports' => ['title', 'editor', 'page-attributes'],
        'show_in_rest' => true,
        'menu_position' => 20
    ]);
}
add_action('init', 'register_support_card_cpt');

// ==================== 메타 박스 ====================
function add_support_card_meta_boxes() {
    add_meta_box('support_card_details', '카드 상세 정보', 'render_support_card_meta_box', 'support_card', 'normal', 'high');
}
add_action('add_meta_boxes', 'add_support_card_meta_boxes');

function render_support_card_meta_box($post) {
    wp_nonce_field('support_card_save', 'support_card_nonce');
    
    $amount = get_post_meta($post->ID, '_card_amount', true);
    $amount_sub = get_post_meta($post->ID, '_card_amount_sub', true);
    $target = get_post_meta($post->ID, '_card_target', true);
    $period = get_post_meta($post->ID, '_card_period', true);
    $link = get_post_meta($post->ID, '_card_link', true);
    $featured = get_post_meta($post->ID, '_card_featured', true);
    $keyword = get_post_meta($post->ID, '_card_keyword', true);
    ?>
    <style>
        .support-meta-field { margin-bottom: 20px; }
        .support-meta-field label { display: block; font-weight: 600; margin-bottom: 8px; color: #1e40af; }
        .support-meta-field input[type="text"],
        .support-meta-field input[type="url"] { width: 100%; padding: 10px; border: 2px solid #e5e7eb; border-radius: 8px; }
        .support-meta-field input[type="text"]:focus,
        .support-meta-field input[type="url"]:focus { border-color: #2563EB; outline: none; }
        .generate-content-btn { background: linear-gradient(135deg, #2563EB 0%, #7C3AED 100%); color: white; border: none; padding: 15px 30px; border-radius: 8px; font-weight: 700; cursor: pointer; font-size: 16px; margin-bottom: 10px; }
        .generate-content-btn:hover { opacity: 0.9; }
        .generate-content-btn:disabled { opacity: 0.5; cursor: not-allowed; }
        .ai-notice { background: #FEF3C7; border-left: 4px solid #F59E0B; padding: 15px; margin-bottom: 20px; border-radius: 8px; }
        .ai-notice strong { color: #D97706; }
    </style>
    
    <div class="ai-notice">
        <strong>🤖 자동 생성:</strong> 키워드만 입력하고 버튼을 누르면 제목, 본문, 상세정보가 자동으로 입력됩니다!
    </div>
    
    <div class="support-meta-field">
        <label>📌 키워드 (필수)</label>
        <input type="text" name="card_keyword" id="support_card_keyword" value="<?php echo esc_attr($keyword); ?>" placeholder="예: 청년도약계좌" />
        <p style="color: #666; font-size: 13px; margin-top: 5px;">
            지원 키워드: 청년도약계좌, 청년내일채움공제, 근로장려금, 자녀장려금, 청년월세지원, 기초연금, 출산지원금, 실업급여
        </p>
    </div>
    
    <button type="button" class="generate-content-btn" id="support_generate_btn">
        ✨ 콘텐츠 자동 생성
    </button>
    
    <div id="support_generation_status" style="padding: 10px; border-radius: 8px; display: none; margin-bottom: 20px;"></div>
    
    <script>
    (function() {
        var templates = {
            '청년도약계좌': {
                title: '청년도약계좌 - 5년 만기 시 최대 5,000만원',
                amount: '최대 5,000만원',
                amount_sub: '정부 기여금 + 이자 포함',
                description: '<h2>청년의 미래를 위한 특별한 기회</h2><p>월급은 받지만 저축은 항상 부족했던 청년들을 위한 <strong>정부 직접 지원 저축 상품</strong>입니다.</p><h3>✅ 주요 혜택</h3><ul><li>매월 최대 70만원 납입 가능</li><li>정부가 최대 6% 기여금 추가 지원</li><li>5년 만기 시 <strong>최대 5,000만원</strong> 목돈 마련</li><li>비과세 혜택으로 이자 100% 수령</li></ul><h3>🎯 신청 자격</h3><p>만 19~34세 청년으로 개인소득 7,500만원 이하면 누구나 신청 가능합니다.</p><h3>⚠️ 서두르세요!</h3><p>선착순 마감이므로 늦으면 신청 기회를 놓칠 수 있습니다. 지금 바로 신청하세요!</p>',
                target: '만 19~34세 청년',
                period: '상시 모집 (선착순 마감)'
            },
            '청년내일채움공제': {
                title: '청년내일채움공제 - 2년 근속 시 최대 1,600만원',
                amount: '최대 1,600만원',
                amount_sub: '정부 + 기업 공동 지원',
                description: '<h2>중소기업 청년을 위한 목돈 마련 제도</h2><p>중소기업에서 2년만 근속하면 <strong>1,600만원</strong>을 받을 수 있는 정부 지원 프로그램입니다.</p><h3>💰 지원 내역</h3><ul><li>청년 본인: 400만원 납입</li><li>정부 지원: 900만원</li><li>기업 지원: 300만원</li><li><strong>총 1,600만원 수령!</strong></li></ul><h3>📋 신청 조건</h3><p>만 15~34세 청년으로 중소·중견기업에 정규직으로 취업하면 신청 가능합니다.</p><p>본인 부담은 월 16.7만원 수준으로 부담 없이 목돈을 만들 수 있습니다.</p>',
                target: '중소기업 재직 청년',
                period: '기업 참여 시 상시'
            },
            '근로장려금': {
                title: '근로장려금 - 최대 330만원 현금 지급',
                amount: '최대 330만원',
                amount_sub: '연 1회 현금 지급',
                description: '<h2>일하는 저소득 가구를 위한 현금 지원</h2><p>열심히 일하지만 소득이 적은 가구에 정부가 <strong>현금을 직접 지급</strong>하는 제도입니다.</p><h3>💵 지급 금액</h3><ul><li>단독 가구: 최대 165만원</li><li>홑벌이 가구: 최대 285만원</li><li>맞벌이 가구: 최대 330만원</li></ul><h3>✅ 신청 자격</h3><p>부부 합산 연소득이 일정 금액 미만이고 재산 2억 4천만원 미만이면 신청 가능합니다.</p><h3>📅 신청 기간</h3><ul><li>정기 신청: 매년 5월</li><li>반기 신청: 매년 9월</li></ul><p>신청만 하면 계좌로 바로 입금됩니다!</p>',
                target: '저소득 근로자 가구',
                period: '5월 정기신청, 9월 반기신청'
            },
            '자녀장려금': {
                title: '자녀장려금 - 자녀 1명당 최대 100만원',
                amount: '자녀당 최대 100만원',
                amount_sub: '현금 지급',
                description: '<h2>아이 키우는 가정을 위한 현금 지원</h2><p>18세 미만 자녀를 양육하는 저소득 가구에 <strong>자녀 1명당 최대 100만원</strong>을 지원합니다.</p><h3>💰 지급 금액</h3><ul><li>자녀 1명: 최대 100만원</li><li>자녀 2명: 최대 200만원</li><li>자녀 3명: 최대 300만원</li></ul><h3>✅ 신청 자격</h3><p>부부 합산 연소득 4,000만원 미만이고 18세 미만 자녀가 있으면 신청 가능합니다.</p><h3>🎁 근로장려금과 중복 가능</h3><p>근로장려금과 자녀장려금을 함께 받을 수 있어 최대 600만원 이상 수령 가능합니다.</p>',
                target: '저소득 가구 (부양자녀 有)',
                period: '5월 정기신청'
            },
            '청년월세지원': {
                title: '청년월세 한시 특별지원 - 월 20만원 x 12개월',
                amount: '최대 240만원',
                amount_sub: '월 20만원 x 12개월',
                description: '<h2>월세 부담 덜어주는 청년 지원금</h2><p>월세 때문에 힘든 청년들에게 정부가 <strong>매월 20만원씩 12개월</strong>을 지원합니다.</p><h3>💵 지원 내용</h3><ul><li>월 20만원 x 12개월</li><li>총 <strong>240만원</strong> 지원</li><li>본인 계좌로 직접 입금</li></ul><h3>✅ 신청 자격</h3><ul><li>만 19~34세 무주택 청년</li><li>독립 거주 중 (부모와 별도 거주)</li><li>본인 소득 기준 충족</li></ul><h3>⚠️ 주의사항</h3><p>원가구 소득과 무관하게 청년 본인 소득만으로 심사하므로 부모님 소득이 높아도 신청 가능합니다!</p>',
                target: '만 19~34세 무주택 청년',
                period: '예산 소진 시 조기 마감'
            },
            '기초연금': {
                title: '기초연금 - 매월 최대 334,810원 지급',
                amount: '월 최대 334,810원',
                amount_sub: '평생 매월 지급',
                description: '<h2>어르신을 위한 평생 연금</h2><p>만 65세 이상 어르신께 정부가 <strong>매월 최대 334,810원</strong>을 평생 지급합니다.</p><h3>💰 지급 금액 (2024년 기준)</h3><ul><li>단독 가구: 최대 334,810원</li><li>부부 가구: 최대 535,680원</li></ul><h3>✅ 신청 자격</h3><ul><li>만 65세 이상</li><li>소득 하위 70% 이하</li></ul><h3>📅 신청 방법</h3><p>생일이 속한 달의 1개월 전부터 신청 가능하며, 국민연금공단 지사나 주민센터에서 신청할 수 있습니다.</p><p>이미 받고 계신 분들도 매년 금액이 인상되니 확인해보세요!</p>',
                target: '만 65세 이상 어르신',
                period: '생일 도래 1개월 전부터'
            },
            '출산지원금': {
                title: '출산지원금 - 첫째 200만원, 둘째 300만원',
                amount: '첫째 200만원',
                amount_sub: '둘째 300만원, 셋째 이상 더 많이',
                description: '<h2>출산 가정을 위한 현금 지원</h2><p>아이를 낳으면 정부와 지자체가 <strong>출산 축하금</strong>을 지급합니다.</p><h3>💰 지급 금액</h3><ul><li>첫째 아이: 200만원</li><li>둘째 아이: 300만원</li><li>셋째 이상: 500만원 이상</li></ul><h3>🎁 추가 혜택</h3><ul><li>지역별 추가 지원금</li><li>출산용품 지원</li><li>산후조리비 지원</li></ul><h3>📋 신청 방법</h3><p>출산 후 60일 이내에 주민센터나 온라인으로 신청하면 계좌로 입금됩니다.</p><p>지자체별로 추가 지원이 있으니 거주 지역의 혜택도 꼭 확인하세요!</p>',
                target: '출산 가정',
                period: '출산 후 60일 이내'
            },
            '실업급여': {
                title: '실업급여 - 최대 1일 66,000원 x 270일',
                amount: '최대 1,782만원',
                amount_sub: '최대 270일간 지급',
                description: '<h2>실직자를 위한 생활 안정 지원</h2><p>일자리를 잃은 분들께 <strong>구직활동 기간 동안 생활비</strong>를 지원하는 제도입니다.</p><h3>💵 지급 금액</h3><ul><li>퇴직 전 평균임금의 60%</li><li>하한액: 1일 63,104원</li><li>상한액: 1일 66,000원</li></ul><h3>📅 지급 기간</h3><ul><li>50세 미만: 120~240일</li><li>50세 이상/장애인: 최대 270일</li></ul><h3>✅ 신청 자격</h3><p>고용보험 가입 기간이 180일 이상이고 비자발적 이직인 경우 신청 가능합니다. 자발적 퇴사도 정당한 사유가 있으면 수급 가능합니다.</p><h3>⚠️ 신청 기한</h3><p>퇴직 후 12개월 이내에 신청해야 하니 서둘러 신청하세요!</p>',
                target: '실직자 (고용보험 가입자)',
                period: '퇴직 후 12개월 이내'
            }
        };
        
        document.getElementById('support_generate_btn').addEventListener('click', function() {
            var keyword = document.getElementById('support_card_keyword').value.trim();
            var statusDiv = document.getElementById('support_generation_status');
            var btn = this;
            
            if (!keyword) {
                statusDiv.style.display = 'block';
                statusDiv.style.background = '#fee2e2';
                statusDiv.style.border = '2px solid #ef4444';
                statusDiv.style.color = '#991b1b';
                statusDiv.innerHTML = '❌ 키워드를 입력해주세요!';
                return;
            }
            
            btn.disabled = true;
            btn.textContent = '🤖 생성 중...';
            
            statusDiv.style.display = 'block';
            statusDiv.style.background = '#fef3c7';
            statusDiv.style.border = '2px solid #f59e0b';
            statusDiv.style.color = '#92400e';
            statusDiv.innerHTML = '⏳ 콘텐츠를 생성하고 있습니다...';
            
            setTimeout(function() {
                var result = templates[keyword] || {
                    title: keyword + ' - 지금 바로 신청하세요',
                    amount: '최대 300만원',
                    amount_sub: '정부 직접 지원',
                    description: '<h2>' + keyword + ' 안내</h2><p>' + keyword + '은(는) 많은 분들이 놓치고 있는 정부 지원 혜택입니다.</p><h3>✅ 주요 혜택</h3><p>조건만 충족하면 누구나 신청할 수 있으며, 신청 절차도 간단합니다.</p><h3>⚠️ 놓치지 마세요</h3><p>신청하지 않으면 절대 받을 수 없습니다. 지금 바로 신청하세요!</p>',
                    target: '대한민국 국민',
                    period: '상시 접수'
                };
                
                // 메타 필드 채우기
                document.getElementById('card_amount').value = result.amount;
                document.getElementById('card_amount_sub').value = result.amount_sub;
                document.getElementById('card_target').value = result.target;
                document.getElementById('card_period').value = result.period;
                
                // 제목 채우기
                var titleInput = document.getElementById('title');
                if (titleInput) {
                    titleInput.value = result.title;
                }
                
                // 본문 채우기
                if (typeof tinymce !== 'undefined' && tinymce.editors.length > 0) {
                    tinymce.editors[0].setContent(result.description);
                } else if (typeof wp !== 'undefined' && wp.editor) {
                    wp.editor.remove('content');
                    document.getElementById('content').value = result
    
    <hr style="margin: 30px 0; border: none; border-top: 2px solid #e5e7eb;" />
    
    <div class="support-meta-field">
        <label>💰 금액/혜택 강조</label>
        <input type="text" name="card_amount" id="card_amount" value="<?php echo esc_attr($amount); ?>" placeholder="AI가 자동 생성" />
    </div>
    
    <div class="support-meta-field">
        <label>💬 부가 설명</label>
        <input type="text" name="card_amount_sub" id="card_amount_sub" value="<?php echo esc_attr($amount_sub); ?>" placeholder="AI가 자동 생성" />
    </div>
    
    <div class="support-meta-field">
        <label>👥 지원대상</label>
        <input type="text" name="card_target" id="card_target" value="<?php echo esc_attr($target); ?>" placeholder="AI가 자동 생성" />
    </div>
    
    <div class="support-meta-field">
        <label>📅 신청시기</label>
        <input type="text" name="card_period" id="card_period" value="<?php echo esc_attr($period); ?>" placeholder="AI가 자동 생성" />
    </div>
    
    <div class="support-meta-field">
        <label>🔗 연결 URL</label>
        <input type="url" name="card_link" id="card_link" value="<?php echo esc_attr($link); ?>" placeholder="https://example.com" />
    </div>
    
    <div class="support-meta-field">
        <label>
            <input type="checkbox" name="card_featured" value="1" <?php checked($featured, '1'); ?> />
            🔥 인기 카드로 표시
        </label>
    </div>
    <?php
}

// ==================== 메타 데이터 저장 ====================
function save_support_card_meta($post_id) {
    if (!isset($_POST['support_card_nonce']) || !wp_verify_nonce($_POST['support_card_nonce'], 'support_card_save')) {
        return;
    }
    
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    
    $fields = ['card_keyword', 'card_amount', 'card_amount_sub', 'card_target', 'card_period', 'card_link'];
    foreach ($fields as $field) {
        if (isset($_POST[$field])) {
            update_post_meta($post_id, '_' . $field, sanitize_text_field($_POST[$field]));
        }
    }
    
    $featured = isset($_POST['card_featured']) ? '1' : '0';
    update_post_meta($post_id, '_card_featured', $featured);
}
add_action('save_post_support_card', 'save_support_card_meta');

// ==================== 설정 페이지 ====================
function add_support_settings_menu() {
    add_options_page('지원금 스킨 설정', '지원금 스킨', 'manage_options', 'support-settings', 'render_support_settings_page');
}
add_action('admin_menu', 'add_support_settings_menu');

function render_support_settings_page() {
    if (isset($_POST['support_settings_save'])) {
        check_admin_referer('support_settings_action');
        
        update_option('support_main_url', esc_url_raw($_POST['support_main_url']));
        update_option('support_ad_platform', sanitize_text_field($_POST['support_ad_platform']));
        update_option('support_ad_code_top', wp_kses_post($_POST['support_ad_code_top']));
        update_option('support_ad_code_content', wp_kses_post($_POST['support_ad_code_content']));
        update_option('support_ad_code_bottom', wp_kses_post($_POST['support_ad_code_bottom']));
        
        echo '<div class="notice notice-success"><p>✅ 설정이 저장되었습니다!</p></div>';
    }
    
    $main_url = get_option('support_main_url', '');
    $ad_platform = get_option('support_ad_platform', 'taboola');
    $ad_top = get_option('support_ad_code_top', '');
    $ad_content = get_option('support_ad_code_content', '');
    $ad_bottom = get_option('support_ad_code_bottom', '');
    ?>
    <div class="wrap">
        <h1>🎨 지원금 스킨 설정</h1>
        <div style="max-width: 800px;">
            <div style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                <table class="form-table">
                    <tr>
                        <th>🔗 메인 URL</th>
                        <td>
                            <input type="url" name="support_main_url" value="<?php echo esc_attr($main_url); ?>" class="regular-text" />
                            <p class="description">카드 클릭 시 연결될 기본 URL</p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th>📢 광고 플랫폼</th>
                        <td>
                            <select name="support_ad_platform">
                                <option value="taboola" <?php selected($ad_platform, 'taboola'); ?>>타뷸라 (Taboola)</option>
                                <option value="dable" <?php selected($ad_platform, 'dable'); ?>>데이블 (Dable)</option>
                                <option value="mgid" <?php selected($ad_platform, 'mgid'); ?>>MGID</option>
                                <option value="outbrain" <?php selected($ad_platform, 'outbrain'); ?>>아웃브레인 (Outbrain)</option>
                                <option value="adsense" <?php selected($ad_platform, 'adsense'); ?>>구글 애드센스</option>
                                <option value="custom" <?php selected($ad_platform, 'custom'); ?>>기타</option>
                            </select>
                        </td>
                    </tr>
                    
                    <tr>
                        <th>📢 상단 광고 코드</th>
                        <td>
                            <textarea name="support_ad_code_top" rows="6" class="large-text code"><?php echo esc_textarea($ad_top); ?></textarea>
                            <p class="description">페이지 상단에 표시될 광고</p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th>📢 본문 중간 광고 코드</th>
                        <td>
                            <textarea name="support_ad_code_content" rows="6" class="large-text code"><?php echo esc_textarea($ad_content); ?></textarea>
                            <p class="description">카드 사이에 삽입될 광고 (3개마다)</p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th>📢 하단 광고 코드</th>
                        <td>
                            <textarea name="support_ad_code_bottom" rows="6" class="large-text code"><?php echo esc_textarea($ad_bottom); ?></textarea>
                            <p class="description">페이지 하단에 표시될 광고</p>
                        </td>
                    </tr>
                </table>
                
                <p class="submit">
                    <button type="button" onclick="saveSupportSettings()" class="button button-primary button-large">
                        💾 설정 저장
                    </button>
                </p>
            </div>
        </div>
    </div>
    
    <script>
    function saveSupportSettings() {
        const formData = new FormData();
        formData.append('action', 'save_support_settings');
        formData.append('nonce', '<?php echo wp_create_nonce('support_settings_action'); ?>');
        formData.append('support_main_url', document.querySelector('[name="support_main_url"]').value);
        formData.append('support_ad_platform', document.querySelector('[name="support_ad_platform"]').value);
        formData.append('support_ad_code_top', document.querySelector('[name="support_ad_code_top"]').value);
        formData.append('support_ad_code_content', document.querySelector('[name="support_ad_code_content"]').value);
        formData.append('support_ad_code_bottom', document.querySelector('[name="support_ad_code_bottom"]').value);
        
        fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
            method: 'POST',
            body: formData
        }).then(() => location.reload());
    }
    </script>
    <?php
}

add_action('wp_ajax_save_support_settings', function() {
    check_ajax_referer('support_settings_action', 'nonce');
    update_option('support_main_url', esc_url_raw($_POST['support_main_url']));
    update_option('support_ad_platform', sanitize_text_field($_POST['support_ad_platform']));
    update_option('support_ad_code_top', wp_kses_post($_POST['support_ad_code_top']));
    update_option('support_ad_code_content', wp_kses_post($_POST['support_ad_code_content']));
    update_option('support_ad_code_bottom', wp_kses_post($_POST['support_ad_code_bottom']));
    wp_send_json_success();
});
