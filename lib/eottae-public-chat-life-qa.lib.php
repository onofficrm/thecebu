<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

include_once G5_LIB_PATH.'/eottae-talkroom-public-chat.lib.php';
include_once G5_LIB_PATH.'/eottae-public-ai-publish.lib.php';

if (!function_exists('eottae_public_chat_life_qa_clean')) {
    function eottae_public_chat_life_qa_clean($value, $len = 300)
    {
        $value = trim(strip_tags((string) $value));
        if ($value !== '' && function_exists('cut_str')) {
            $value = cut_str($value, max(1, (int) $len), '');
        }

        return $value;
    }
}

if (!function_exists('eottae_public_chat_life_qa_fallback')) {
    function eottae_public_chat_life_qa_fallback($question)
    {
        return "질문 고마워요. 이 내용은 세부 생활 정보라 상황에 따라 달라질 수 있어요.\n\n"
            ."비자·병원·날씨·환율처럼 변동되는 정보는 공식 기관, 병원, 은행/환전소 기준을 함께 확인해 주세요.\n"
            ."질문: ".$question;
    }
}

if (!function_exists('eottae_public_chat_life_qa_answer')) {
    function eottae_public_chat_life_qa_answer($question)
    {
        $question = eottae_public_chat_life_qa_clean($question, 300);
        if ($question === '') {
            return '';
        }

        if (!function_exists('eottae_ai_openai_chat_completion')) {
            return eottae_public_chat_life_qa_fallback($question);
        }

        $cfg = eottae_ai_generate_bootstrap_config();
        if (empty($cfg['enabled']) || empty($cfg['api_key'])) {
            return eottae_public_chat_life_qa_fallback($question);
        }

        $prompt = "세부어때 공개톡에서 사용자가 세부 생활 질문을 했습니다.\n"
            ."한국어로 500자 이내, 친근하지만 단정하지 않는 답변을 작성하세요.\n"
            ."다룰 수 있는 주제: 세부 생활, 비자 준비 방향, 날씨 확인 방법, 환율/환전 팁, 병원/약국 찾는 법, 교통/지역 정보.\n"
            ."의료·법률·비자 승인 여부는 확정적으로 말하지 말고 공식 기관/전문가 확인을 권하세요.\n"
            ."모르면 모른다고 말하고 확인 방법을 제안하세요.\n\n"
            ."질문: ".$question;

        $completion = eottae_ai_openai_chat_completion(array(
            'model' => $cfg['model'],
            'messages' => array(
                array('role' => 'system', 'content' => 'You are a practical Cebu life assistant in a Korean community public chat. Keep answers concise and cautious.'),
                array('role' => 'user', 'content' => $prompt),
            ),
            'temperature' => 0.45,
            'max_tokens' => 700,
        ), array('timeout' => 40, 'connect_timeout' => 10));

        if (empty($completion['ok']) || trim((string) ($completion['content'] ?? '')) === '') {
            return eottae_public_chat_life_qa_fallback($question);
        }

        return eottae_public_chat_life_qa_clean($completion['content'], 500);
    }
}

if (!function_exists('eottae_public_chat_life_qa_send')) {
    function eottae_public_chat_life_qa_send($room_id, $question)
    {
        $question = eottae_public_chat_life_qa_clean($question, 300);
        if ($question === '') {
            return array('ok' => false, 'message' => '질문을 입력해 주세요.');
        }

        $answer = eottae_public_chat_life_qa_answer($question);
        if ($answer === '') {
            return array('ok' => false, 'message' => '답변을 만들지 못했습니다.');
        }

        $content = "세부 생활 질문에 답변드릴게요.\n\n".$answer;

        return eottae_talkroom_public_group_send_ai_message((int) $room_id, $content, array(
            'trigger_type' => 'life_qa',
        ));
    }
}
