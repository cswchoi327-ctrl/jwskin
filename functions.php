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
        .generate-content-btn { background: linear-gradient(135deg, #2563EB 0%, #7C3AED 100%); color: white; border: none; padding: 15px 30px; border-radius: 8px; font-weight: 700; cursor: pointer; font-size: 16px; }
        .generate-content-btn:hover { opacity: 0.9; }
        .generate-content-btn:disabled { opacity: 0.5; cursor: not-allowed; }
        .ai-notice { background: #FEF3C7; border-left: 4px solid #F59E0B; padding: 15px; margin-bottom: 20px; border-radius: 8px; }
        .ai-notice strong { color: #D97706; }
    </style>
    
    <div class="ai-notice">
        <strong>🤖 AI 자동 생성:</strong> 키워드만 입력하고 "콘텐츠 자동 생성" 버튼을 누르면 파소나 법칙을 적용한 고CTR 콘텐츠가 자동으로 생성됩니다!
    </div>
    
    <div class="support-meta-field">
        <label>📌 키워드 (필수)</label>
        <input type="text" name="card_keyword" id="card_keyword" value="<?php echo esc_attr($keyword); ?>" placeholder="예: 청년도약계좌" required />
        <p style="color: #666; font-size: 13px; margin-top: 5px;">이 키워드로 AI가 모든 콘텐츠를 자동 생성합니다</p>
    </div>
    
    <button type="button" class="generate-content-btn" onclick="generateSupportContent()">
        ✨ 콘텐츠 자동 생성 (AI)
    </button>
    
    <div id="generation-status" style="margin-top: 15px; padding: 10px; border-radius: 8px; display: none;"></div>
    
    <script>
    function generateSupportContent() {
        console.log('함수 호출됨');
        
        var keyword = document.getElementById('card_keyword').value.trim();
        var statusDiv = document.getElementById('generation-status');
        var btn = event.target;
        
        console.log('키워드:', keyword);
        
        if (!keyword) {
            statusDiv.style.display = 'block';
            statusDiv.style.background = '#fee';
            statusDiv.style.border = '2px solid #f00';
            statusDiv.style.color = '#c00';
            statusDiv.innerHTML = '❌ 키워드를 입력해주세요!';
            return;
        }
        
        btn.disabled = true;
        btn.textContent = '🤖 생성 중...';
        
        statusDiv.style.display = 'block';
        statusDiv.style.background = '#fef3cd';
        statusDiv.style.border = '2px solid #ff9800';
        statusDiv.style.color = '#856404';
        statusDiv.innerHTML = '⏳ 콘텐츠를 생성하고 있습니다...';
        
        // 템플릿 데이터
        var templates = {
            '청년도약계좌': {
                title: '청년도약계좌 - 5년 만기 시 최대 5,000만원',
                amount: '최대 5,000만원',
                amount_sub: '정부 기여금 + 이자 포함',
                description: '월급은 받지만 저축은 항상 부족했던 청년들을 위한 특별한 기회입니다. 청년도약계좌는 정부가 직접 지원하는 장기 저축 상품으로, 매월 70만원까지 납입하면 정부가 최대 6%의 기여금을 추가로 지원합니다. 5년 만기 시 원금 + 이자 + 정부 기여금을 합쳐 최대 5,000만원을 만들 수 있습니다. 선착순 마감이니 지금 바로 신청하세요!',
                target: '만 19~34세 청년',
                period: '상시 모집 (선착순 마감)'
            },
            '청년내일채움공제': {
                title: '청년내일채움공제 - 2년 근속 시 최대 1,600만원',
                amount: '최대 1,600만원',
                amount_sub: '정부 + 기업 공동 지원',
                description: '중소기업에서 일하는 청년들의 장기 근속을 돕기 위한 정부 지원금입니다. 본인이 400만원을 납입하면 정부와 기업이 1,200만원을 추가 지원하여 2년 후 총 1,600만원을 받을 수 있습니다. 청년 여러분의 안정적인 미래를 위한 기회, 놓치지 마세요.',
                target: '중소기업 재직 청년',
                period: '기업 참여 시 상시'
            },
            '근로장려금': {
                title: '근로장려금 - 최대 330만원 현금 지급',
                amount: '최대 330만원',
                amount_sub: '연 1회 현금 지급',
                description: '일은 하는데 소득이 적어 생활이 힘드셨나요? 근로장려금은 열심히 일하는 저소득 근로자를 위한 정부의 직접 현금 지원입니다. 신청만 하면 가구 유형에 따라 최대 330만원까지 계좌로 바로 입금됩니다.',
                target: '저소득 근로자 가구',
                period: '5월 정기신청, 9월 반기신청'
            }
        };
        
        setTimeout(function() {
            var result;
            
            if (templates[keyword]) {
                console.log('템플릿 발견');
                result = templates[keyword];
            } else {
                console.log('기본 생성');
                result = {
                    title: keyword + ' - 지금 바로 신청하세요',
                    amount: '최대 300만원',
                    amount_sub: '정부 직접 지원',
                    description: keyword + '은(는) 많은 분들이 놓치고 있는 정부 지원 혜택입니다. 조건만 충족하면 누구나 신청할 수 있으며, 신청 절차도 간단합니다. 하지만 신청하지 않으면 절대 받을 수 없습니다. 지금 이 기회를 놓치면 큰 손해입니다. 아래 신청 방법을 확인하시고 지금 바로 신청하세요!',
                    target: '대한민국 국민',
                    period: '상시 접수'
                };
            }
            
            console.log('결과:', result);
            
            // 메타 필드 채우기
            document.getElementById('card_amount').value = result.amount;
            document.getElementById('card_amount_sub').value = result.amount_sub;
            document.getElementById('card_target').value = result.target;
            document.getElementById('card_period').value = result.period;
            
            // 제목 채우기
            document.getElementById('title').value = result.title;
            
            // 본문 채우기 (여러 방법 시도)
            var contentSet = false;
            
            // 방법 1: TinyMCE
            if (typeof tinymce !== 'undefined') {
                var editor = tinymce.get('content');
                if (editor) {
                    editor.setContent(result.description);
                    contentSet = true;
                    console.log('TinyMCE로 설정');
                }
            }
            
            // 방법 2: textarea 직접
            if (!contentSet) {
                var contentField = document.getElementById('content');
                if (contentField) {
                    contentField.value = result.description;
                    contentSet = true;
                    console.log('textarea로 설정');
                }
            }
            
            // 방법 3: wp.editor
            if (!contentSet && typeof wp !== 'undefined' && wp.editor) {
                wp.editor.getContent = function() {
                    return result.description;
                };
                console.log('wp.editor로 설정');
            }
            
            statusDiv.style.background = '#efe';
            statusDiv.style.border = '2px solid #0a0';
            statusDiv.style.color = '#070';
            statusDiv.innerHTML = '✅ 콘텐츠 생성 완료! 필요시 수정 후 발행하세요.';
            
            btn.disabled = false;
            btn.textContent = '✨ 콘텐츠 자동 생성 (AI)';
            
            console.log('완료');
        }, 500);
    }
    </script>
    
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
