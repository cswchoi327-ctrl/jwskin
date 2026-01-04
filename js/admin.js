/**
 * admin.js - 관리자 화면 JavaScript
 * 템플릿 기반 자동 생성
 */

jQuery(document).ready(function($) {
    'use strict';
    
    console.log('Admin JS 로드됨');
    
    // 파소나 법칙 적용 템플릿 데이터베이스
    const templates = {
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
            description: '중소기업에서 일하는 청년들의 장기 근속을 돕기 위한 정부 지원금입니다. 본인이 400만원을 납입하면 정부와 기업이 1,200만원을 추가 지원하여 2년 후 총 1,600만원을 받을 수 있습니다. 청년 여러분의 안정적인 미래를 위한 기회, 놓치지 마세요. 신청 기간이 한정되어 있으니 서둘러 신청하세요!',
            target: '중소기업 재직 청년',
            period: '기업 참여 시 상시'
        },
        '근로장려금': {
            title: '근로장려금 - 최대 330만원 현금 지급',
            amount: '최대 330만원',
            amount_sub: '연 1회 현금 지급',
            description: '일은 하는데 소득이 적어 생활이 힘드셨나요? 근로장려금은 열심히 일하는 저소득 근로자를 위한 정부의 직접 현금 지원입니다. 신청만 하면 가구 유형에 따라 최대 330만원까지 계좌로 바로 입금됩니다. 작년에 신청하지 못한 분들도 5월 정기 신청 기간에 꼭 신청하세요. 내 돈인데 안 받으면 손해입니다!',
            target: '저소득 근로자 가구',
            period: '5월 정기신청, 9월 반기신청'
        },
        '자녀장려금': {
            title: '자녀장려금 - 자녀 1명당 최대 100만원',
            amount: '자녀당 최대 100만원',
            amount_sub: '현금 지급',
            description: '아이를 키우는 게 경제적으로 부담되시죠? 자녀장려금은 저소득 가구의 자녀 양육을 지원하기 위한 정부 현금 지원입니다. 18세 미만 자녀 1명당 최대 100만원씩 지급되며, 자녀가 2명이면 200만원, 3명이면 300만원을 받을 수 있습니다. 근로장려금과 함께 신청 가능하니 5월에 꼭 신청하세요!',
            target: '저소득 가구 (부양자녀 有)',
            period: '5월 정기신청'
        },
        '청년월세지원': {
            title: '청년월세 한시 특별지원 - 월 20만원 x 12개월',
            amount: '최대 240만원',
            amount_sub: '월 20만원 x 12개월',
            description: '월세 때문에 저축은커녕 생활비도 빠듯한 청년 여러분, 정부가 도와드립니다. 만 19~34세 청년이라면 누구나 월 20만원씩 12개월간 최대 240만원의 월세를 지원받을 수 있습니다. 원가구 소득과 무관하게 청년 본인 소득 기준으로 심사하므로 부모님 소득이 높아도 신청 가능합니다. 선착순 마감이니 지금 바로 신청하세요!',
            target: '만 19~34세 무주택 청년',
            period: '예산 소진 시 조기 마감'
        },
        '기초연금': {
            title: '기초연금 - 매월 최대 334,810원 지급',
            amount: '월 최대 334,810원',
            amount_sub: '평생 매월 지급',
            description: '나이 들어 경제적으로 힘드신 어르신들을 위한 정부의 평생 지원입니다. 만 65세 이상이시고 소득 하위 70%에 해당하시면 매월 최대 334,810원을 평생 받을 수 있습니다. 이미 받고 계신 분들도 올해 금액이 인상되었으니 확인해보세요. 주변 어르신들께도 꼭 알려드려 모두가 혜택을 받을 수 있도록 도와주세요!',
            target: '만 65세 이상 어르신',
            period: '생일 도래 1개월 전부터'
        },
        '출산지원금': {
            title: '출산지원금 - 첫째 200만원, 둘째 300만원',
            amount: '첫째 200만원',
            amount_sub: '둘째 300만원, 셋째 이상 더 많이',
            description: '아이를 낳으면 경제적 부담이 크시죠? 정부와 지자체가 출산 가정을 적극 지원합니다. 첫째 출산 시 200만원, 둘째는 300만원을 현금으로 지급하며, 지역에 따라 더 많은 금액을 받을 수 있습니다. 출산 후 60일 이내에 신청하면 바로 계좌로 입금됩니다. 출산 축하와 함께 육아 경비로 활용하세요!',
            target: '출산 가정',
            period: '출산 후 60일 이내'
        },
        '실업급여': {
            title: '실업급여 - 최대 1일 66,000원 x 270일',
            amount: '최대 1,782만원',
            amount_sub: '최대 270일간 지급',
            description: '갑자기 일자리를 잃어 막막하신가요? 실업급여는 구직활동 중인 실업자를 위한 정부의 생활 지원금입니다. 퇴직 전 평균임금의 60%를 매월 받을 수 있으며, 최대 270일간 지원됩니다. 자발적 퇴사여도 정당한 사유가 있다면 수급 가능합니다. 퇴직 후 12개월 이내에 신청해야 하니 지금 바로 신청하세요!',
            target: '실직자 (고용보험 가입자)',
            period: '퇴직 후 12개월 이내'
        }
    };
    
    // 생성 버튼 클릭 이벤트
    const generateBtn = $('#generate-content-btn');
    const statusDiv = $('#generation-status');
    
    console.log('버튼 찾기:', generateBtn.length);
    
    if (!generateBtn.length) {
        console.error('생성 버튼을 찾을 수 없습니다!');
        return;
    }
    
    generateBtn.on('click', function(e) {
        e.preventDefault();
        console.log('버튼 클릭됨');
        
        const keyword = $('#card_keyword').val().trim();
        console.log('입력된 키워드:', keyword);
        
        if (!keyword) {
            showStatus('error', '❌ 키워드를 입력해주세요!');
            return;
        }
        
        generateBtn.prop('disabled', true).text('🤖 생성 중...');
        showStatus('loading', '⏳ 콘텐츠를 생성하고 있습니다...');
        
        // 짧은 딜레이로 로딩 효과
        setTimeout(function() {
            let result;
            
            // 템플릿에서 찾기
            if (templates[keyword]) {
                console.log('템플릿 발견:', keyword);
                result = templates[keyword];
            } else {
                console.log('키워드 기반 생성:', keyword);
                result = generateFromKeyword(keyword);
            }
            
            console.log('생성 결과:', result);
            
            // 폼 자동 입력
            $('#card_amount').val(result.amount);
            $('#card_amount_sub').val(result.amount_sub);
            $('#card_target').val(result.target);
            $('#card_period').val(result.period);
            
            // 제목 입력
            $('#title').val(result.title);
            
            // 본문 입력 (비주얼/텍스트 에디터 모두 지원)
            const contentField = $('#content');
            if (contentField.length) {
                contentField.val(result.description);
            }
            
            // TinyMCE 에디터가 있다면
            if (typeof tinymce !== 'undefined') {
                const editor = tinymce.get('content');
                if (editor) {
                    editor.setContent(result.description);
                }
            }
            
            // 클래식 에디터 textarea
            if (typeof wp !== 'undefined' && wp.editor) {
                wp.editor.remove('content');
                wp.editor.initialize('content', {
                    tinymce: true,
                    quicktags: true
                });
                setTimeout(function() {
                    const ed = tinymce.get('content');
                    if (ed) {
                        ed.setContent(result.description);
                    }
                }, 100);
            }
            
            showStatus('success', '✅ 콘텐츠 생성 완료! 필요시 수정 후 발행하세요.');
            generateBtn.prop('disabled', false).text('✨ 콘텐츠 자동 생성 (AI)');
            
            console.log('폼 입력 완료');
            
        }, 800);
    });
        
        // 키워드 기반 자동 생성
        function generateFromKeyword(keyword) {
            const amounts = ['최대 300만원', '최대 500만원', '최대 1,000만원', '월 20만원 지원'];
            const subs = ['정부 직접 지원', '현금 지급', '매월 지급', '조건 충족 시'];
            const targets = ['대한민국 국민', '청년 (만 19~39세)', '저소득 가구', '모든 국민'];
            const periods = ['상시 접수', '연중 신청 가능', '기간 내 신청', '예산 소진 시 마감'];
            
            const randomAmount = amounts[Math.floor(Math.random() * amounts.length)];
            const randomSub = subs[Math.floor(Math.random() * subs.length)];
            const randomTarget = targets[Math.floor(Math.random() * targets.length)];
            const randomPeriod = periods[Math.floor(Math.random() * periods.length)];
            
            return {
                title: keyword + ' - 지금 바로 신청하세요',
                amount: randomAmount,
                amount_sub: randomSub,
                description: `"${keyword}"는(은) 많은 분들이 놓치고 있는 정부 지원 혜택입니다. 조건만 충족하면 누구나 신청할 수 있으며, 신청 절차도 간단합니다. 하지만 신청하지 않으면 절대 받을 수 없습니다. 지금 이 기회를 놓치면 큰 손해입니다. 아래 신청 방법을 확인하시고 지금 바로 신청하세요. 선착순 또는 예산 소진 시 조기 마감될 수 있으니 서두르시기 바랍니다!`,
                target: randomTarget,
                period: randomPeriod
            };
        }
        
        function showStatus(type, message) {
            statusDiv.show().removeClass('error success loading');
            
            if (type === 'error') {
                statusDiv.addClass('error').css({
                    background: '#fee',
                    border: '2px solid #f00',
                    color: '#c00'
                });
            } else if (type === 'success') {
                statusDiv.addClass('success').css({
                    background: '#efe',
                    border: '2px solid #0a0',
                    color: '#070'
                });
            } else {
                statusDiv.addClass('loading').css({
                    background: '#fef3cd',
                    border: '2px solid #ff9800',
                    color: '#856404'
                });
            }
            
            statusDiv.html(message);
        }
    });
    
})(jQuery);
