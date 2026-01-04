/**
 * admin.js - 관리자 화면 JavaScript
 * AI 콘텐츠 자동 생성 (파소나 법칙 적용)
 */

(function($) {
    'use strict';
    
    $(document).ready(function() {
        const generateBtn = $('#generate-content-btn');
        const statusDiv = $('#generation-status');
        
        if (!generateBtn.length) return;
        
        generateBtn.on('click', async function() {
            const keyword = $('#card_keyword').val().trim();
            
            if (!keyword) {
                showStatus('error', '❌ 키워드를 입력해주세요!');
                return;
            }
            
            generateBtn.prop('disabled', true).text('🤖 AI 생성 중...');
            showStatus('loading', '⏳ 파소나 법칙을 적용한 콘텐츠를 생성하고 있습니다...');
            
            try {
                // Anthropic API 직접 호출
                const response = await fetch('https://api.anthropic.com/v1/messages', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        model: 'claude-sonnet-4-20250514',
                        max_tokens: 2000,
                        messages: [{
                            role: 'user',
                            content: `"${keyword}"에 대한 지원금 카드 콘텐츠를 생성해줘.

**파소나(PASONA) 법칙 적용:**
- Problem(문제): 사용자가 놓치고 있는 혜택 강조
- Affinity(친근감): 공감 유도
- Solution(해결책): 이 지원금이 해결책임을 제시
- Offer(제안): 구체적인 혜택과 금액
- Narrowing(한정): 마감임박, 제한된 기회 강조
- Action(행동유도): 지금 바로 신청 유도

**CTR 극대화를 위한 요구사항:**
1. 후킹성 있는 금액/혜택 강조 (예: "최대 500만원", "월 40만원 지원")
2. 감성적이면서도 구체적인 설명
3. 긴박감 조성 (마감임박, 선착순 등)
4. 명확한 지원대상 명시
5. 즉각적인 행동 유도

다음 형식의 JSON으로만 답변:
{
  "title": "카드 제목 (후킹)",
  "amount": "금액/혜택 강조 (큰 글씨)",
  "amount_sub": "부가 설명",
  "description": "파소나 법칙을 적용한 상세 설명 (3-5문장, 감성적이면서 구체적)",
  "target": "지원대상 (간결하게)",
  "period": "신청시기"
}

절대 다른 텍스트 없이 JSON만 출력!`
                        }]
                    })
                });
                
                const data = await response.json();
                let jsonText = data.content?.find(item => item.type === 'text')?.text || '{}';
                jsonText = jsonText.replace(/```json\n?/g, '').replace(/```\n?$/g, '').trim();
                
                const result = JSON.parse(jsonText);
                
                // 폼 자동 입력
                $('#card_amount').val(result.amount);
                $('#card_amount_sub').val(result.amount_sub);
                $('#card_target').val(result.target);
                $('#card_period').val(result.period);
                
                // 제목과 본문은 워드프레스 에디터에 입력
                $('#title').val(result.title);
                if (typeof tinymce !== 'undefined' && tinymce.get('content')) {
                    tinymce.get('content').setContent(result.description);
                } else {
                    $('#content').val(result.description);
                }
                
                showStatus('success', '✅ AI 콘텐츠 생성 완료! 파소나 법칙이 적용되었습니다.');
                
            } catch (error) {
                console.error('생성 오류:', error);
                showStatus('error', '❌ 생성 중 오류가 발생했습니다. 다시 시도해주세요.');
            } finally {
                generateBtn.prop('disabled', false).text('✨ 콘텐츠 자동 생성 (AI)');
            }
        });
        
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
