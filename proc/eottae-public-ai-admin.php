<?php
/**
 * 홈 공개톡 AI 관리 API (최고관리자)
 * POST /proc/eottae-public-ai-admin.php
 */
chdir(dirname(__FILE__).'/..');
include_once dirname(__FILE__).'/../_common.php';
include_once G5_LIB_PATH.'/eottae-public-ai.lib.php';
include_once G5_LIB_PATH.'/eottae-public-ai-generator.lib.php';
include_once G5_LIB_PATH.'/eottae-public-ai-publish.lib.php';
include_once G5_LIB_PATH.'/eottae-public-ai-openai.lib.php';

header('Content-Type: application/json; charset=utf-8');

function eottae_public_ai_admin_json($success, $message, $extra = array())
{
    $payload = array_merge(array(
        'success' => (bool) $success,
        'message' => (string) $message,
    ), is_array($extra) ? $extra : array());

    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    eottae_public_ai_admin_json(false, '잘못된 요청입니다.');
}

if ($is_admin !== 'super' || empty($member['mb_id'])) {
    eottae_public_ai_admin_json(false, '최고관리자만 이용할 수 있습니다.');
}

$token = isset($_POST['eottae_public_ai_admin_token']) ? trim((string) $_POST['eottae_public_ai_admin_token']) : '';
if (!eottae_public_ai_verify_admin_token($token)) {
    eottae_public_ai_admin_json(false, '보안 토큰이 만료되었습니다. 페이지를 새로고침한 뒤 다시 시도해 주세요.');
}

$action = isset($_POST['action']) ? trim((string) $_POST['action']) : '';
$mb_id = (string) $member['mb_id'];

if ($action === 'save_settings') {
    $result = eottae_public_ai_save_settings(array(
        'ai_enabled'             => !empty($_POST['ai_enabled']),
        'ai_name'                => isset($_POST['ai_name']) ? (string) $_POST['ai_name'] : '',
        'ai_persona'             => isset($_POST['ai_persona']) ? (string) $_POST['ai_persona'] : '',
        'auto_publish'           => !empty($_POST['auto_publish']),
        'require_admin_approval' => !empty($_POST['require_admin_approval']),
        'max_messages_per_day'   => isset($_POST['max_messages_per_day']) ? (int) $_POST['max_messages_per_day'] : 3,
        'min_silence_minutes'    => isset($_POST['min_silence_minutes']) ? (int) $_POST['min_silence_minutes'] : 180,
        'active_start_time'      => isset($_POST['active_start_time']) ? (string) $_POST['active_start_time'] : '',
        'active_end_time'        => isset($_POST['active_end_time']) ? (string) $_POST['active_end_time'] : '',
        'use_calendar'           => !empty($_POST['use_calendar']),
        'use_weather'            => !empty($_POST['use_weather']),
        'use_holidays'           => !empty($_POST['use_holidays']),
        'use_talk_rooms'         => !empty($_POST['use_talk_rooms']),
        'use_popular_posts'      => !empty($_POST['use_popular_posts']),
        'use_business_events'    => !empty($_POST['use_business_events']),
        'use_external_news'      => !empty($_POST['use_external_news']),
        'openai_enabled'            => !empty($_POST['openai_enabled']),
        'openai_model'              => isset($_POST['openai_model']) ? (string) $_POST['openai_model'] : '',
        'openai_api_key'            => isset($_POST['openai_api_key']) ? (string) $_POST['openai_api_key'] : '',
        'openai_max_calls_per_day'  => isset($_POST['openai_max_calls_per_day']) ? (int) $_POST['openai_max_calls_per_day'] : 20,
        'openai_max_message_length' => isset($_POST['openai_max_message_length']) ? (int) $_POST['openai_max_message_length'] : 400,
        'openai_fallback_template'  => !empty($_POST['openai_fallback_template']),
    ), $mb_id);

    if (!empty($result['ok'])) {
        eottae_public_ai_admin_token(true);
    }

    eottae_public_ai_admin_json(!empty($result['ok']), $result['message']);
}

if ($action === 'save_candidate') {
    $result = eottae_public_ai_save_candidate(array(
        'candidate_id' => isset($_POST['candidate_id']) ? (int) $_POST['candidate_id'] : 0,
        'trigger_type' => isset($_POST['trigger_type']) ? (string) $_POST['trigger_type'] : '',
        'source_type'  => isset($_POST['source_type']) ? (string) $_POST['source_type'] : '',
        'source_id'    => isset($_POST['source_id']) ? (int) $_POST['source_id'] : 0,
        'title'        => isset($_POST['title']) ? (string) $_POST['title'] : '',
        'message'      => isset($_POST['message']) ? (string) $_POST['message'] : '',
        'action_label' => isset($_POST['action_label']) ? (string) $_POST['action_label'] : '',
        'action_url'   => isset($_POST['action_url']) ? (string) $_POST['action_url'] : '',
        'admin_memo'   => isset($_POST['admin_memo']) ? (string) $_POST['admin_memo'] : '',
    ), $mb_id);

    if (!empty($result['ok'])) {
        eottae_public_ai_admin_token(true);
    }

    eottae_public_ai_admin_json(!empty($result['ok']), $result['message'], array(
        'candidate_id' => (int) ($result['candidate_id'] ?? 0),
    ));
}

if ($action === 'approve_candidate') {
    $candidate_id = isset($_POST['candidate_id']) ? (int) $_POST['candidate_id'] : 0;
    $result = eottae_public_ai_set_candidate_status($candidate_id, 'approved', $mb_id);
    if (!empty($result['ok'])) {
        eottae_public_ai_admin_token(true);
    }
    eottae_public_ai_admin_json(!empty($result['ok']), $result['message']);
}

if ($action === 'reject_candidate') {
    $candidate_id = isset($_POST['candidate_id']) ? (int) $_POST['candidate_id'] : 0;
    $result = eottae_public_ai_set_candidate_status($candidate_id, 'rejected', $mb_id);
    if (!empty($result['ok'])) {
        eottae_public_ai_admin_token(true);
    }
    eottae_public_ai_admin_json(!empty($result['ok']), $result['message']);
}

if ($action === 'delete_candidate') {
    $candidate_id = isset($_POST['candidate_id']) ? (int) $_POST['candidate_id'] : 0;
    $result = eottae_public_ai_set_candidate_status($candidate_id, 'deleted', $mb_id);
    if (!empty($result['ok'])) {
        eottae_public_ai_admin_token(true);
    }
    eottae_public_ai_admin_json(!empty($result['ok']), $result['message']);
}

if ($action === 'generate_candidates') {
    $result = eottae_public_ai_run_candidate_generator(array(
        'is_test' => true,
        'force'   => true,
    ));
    if (!empty($result['candidate_ids'])) {
        eottae_public_ai_admin_token(true);
    }
    $message = '후보 메시지 생성을 완료했습니다.';
    if ((int) ($result['saved'] ?? 0) < 1) {
        $reason = trim((string) ($result['reason'] ?? ''));
        if ($reason === 'ai_disabled') {
            $message = 'AI가 비활성화되어 있습니다. 테스트 실행은 강제로 진행했으나 저장된 후보가 없습니다.';
        } elseif ($reason === 'no_data') {
            $message = '참고할 내부 데이터가 없어 후보 메시지를 만들지 않았습니다.';
        } elseif ($reason === 'all_skipped') {
            $message = '생성 가능한 후보가 있었으나 중복·제한 규칙으로 모두 건너뛰었습니다.';
        } elseif ($reason === 'daily_candidate_cap') {
            $message = '오늘 후보 생성 한도에 도달해 추가 생성하지 않았습니다.';
        } else {
            $message = '저장된 후보가 없습니다. (생성 '.(int) ($result['generated_count'] ?? 0).'건, 건너뜀 '.(int) ($result['skipped'] ?? 0).'건)';
        }
    } else {
        $message = '후보 메시지 '.(int) $result['saved'].'건을 승인 대기로 저장했습니다.';
    }

    eottae_public_ai_admin_json((int) ($result['saved'] ?? 0) > 0 || (int) ($result['generated_count'] ?? 0) > 0, $message, array(
        'saved'           => (int) ($result['saved'] ?? 0),
        'skipped'         => (int) ($result['skipped'] ?? 0),
        'generated_count' => (int) ($result['generated_count'] ?? 0),
        'candidate_ids'   => isset($result['candidate_ids']) ? $result['candidate_ids'] : array(),
        'reason'          => (string) ($result['reason'] ?? ''),
        'redirect'        => eottae_public_ai_admin_candidates_url('pending'),
    ));
}

if ($action === 'save_weather') {
    if (!function_exists('eottae_public_ai_weather_save')) {
        include_once G5_LIB_PATH.'/eottae-public-ai-weather.lib.php';
    }
    $result = eottae_public_ai_weather_save(array(
        'forecast_date'   => isset($_POST['forecast_date']) ? (string) $_POST['forecast_date'] : '',
        'weather_summary' => isset($_POST['weather_summary']) ? (string) $_POST['weather_summary'] : '',
        'rain_chance'     => isset($_POST['rain_chance']) ? (int) $_POST['rain_chance'] : 0,
        'temperature_min' => isset($_POST['temperature_min']) ? $_POST['temperature_min'] : '',
        'temperature_max' => isset($_POST['temperature_max']) ? $_POST['temperature_max'] : '',
        'source'          => isset($_POST['source']) ? (string) $_POST['source'] : 'manual',
        'source_note'     => isset($_POST['source_note']) ? (string) $_POST['source_note'] : '',
    ));
    if (!empty($result['ok'])) {
        eottae_public_ai_admin_token(true);
    }
    eottae_public_ai_admin_json(!empty($result['ok']), $result['message']);
}

if ($action === 'save_external_news') {
    if (!function_exists('eottae_public_ai_external_news_save')) {
        include_once G5_LIB_PATH.'/eottae-public-ai-news.lib.php';
    }
    $result = eottae_public_ai_external_news_save(array(
        'title'       => isset($_POST['title']) ? (string) $_POST['title'] : '',
        'summary'     => isset($_POST['summary']) ? (string) $_POST['summary'] : '',
        'category'    => isset($_POST['category']) ? (string) $_POST['category'] : '',
        'source_name' => isset($_POST['source_name']) ? (string) $_POST['source_name'] : '',
        'source_url'  => isset($_POST['source_url']) ? (string) $_POST['source_url'] : '',
        'status'      => 'active',
    ));
    if (!empty($result['ok'])) {
        eottae_public_ai_admin_token(true);
    }
    eottae_public_ai_admin_json(!empty($result['ok']), $result['message']);
}

if ($action === 'test_openai') {
    $test_type = isset($_POST['test_type']) ? trim((string) $_POST['test_type']) : '';
    $custom_text = isset($_POST['custom_text']) ? (string) $_POST['custom_text'] : '';
    $fixture = eottae_public_ai_openai_admin_test_fixture($test_type, $custom_text);
    if (empty($fixture['ok'])) {
        eottae_public_ai_admin_json(false, (string) ($fixture['message'] ?? '테스트 데이터를 준비하지 못했습니다.'));
    }

    $settings = eottae_public_ai_get_settings();
    $gen = eottae_public_ai_generate_message(
        $fixture['source_data'],
        $fixture['trigger_type'],
        $fixture['template'],
        $settings,
        array('force_test' => true)
    );

    $candidate = isset($gen['candidate']) && is_array($gen['candidate']) ? $gen['candidate'] : null;
    if (!$candidate) {
        eottae_public_ai_admin_json(false, '메시지를 생성하지 못했습니다. API 키·OpenAI 사용 설정·일일 한도를 확인해 주세요.', array(
            'source' => (string) ($gen['source'] ?? ''),
            'error'  => (string) ($gen['error'] ?? ''),
        ));
    }

    eottae_public_ai_admin_json(true, '테스트 메시지를 생성했습니다.', array(
        'source'        => (string) ($gen['source'] ?? ''),
        'error'         => (string) ($gen['error'] ?? ''),
        'trigger_type'  => (string) ($fixture['trigger_type'] ?? ''),
        'title'         => (string) ($candidate['title'] ?? ''),
        'message'       => (string) ($candidate['message'] ?? ''),
        'action_label'  => (string) ($candidate['action_label'] ?? ''),
        'action_url'    => (string) ($candidate['action_url'] ?? ''),
        'is_sensitive'  => !empty($candidate['is_sensitive']),
        'force_admin_approval' => !empty($candidate['force_admin_approval']),
        'template_message' => (string) ($fixture['template']['message'] ?? ''),
    ));
}

if ($action === 'publish_candidate') {
    $candidate_id = isset($_POST['candidate_id']) ? (int) $_POST['candidate_id'] : 0;
    $force = !empty($_POST['force']);
    $result = eottae_public_ai_publish_candidate($candidate_id, $mb_id, array(
        'force' => $force,
    ));
    if (!empty($result['ok'])) {
        eottae_public_ai_admin_token(true);
    }
    eottae_public_ai_admin_json(!empty($result['ok']), $result['message'], array(
        'wr_id' => (int) ($result['wr_id'] ?? 0),
        'reason' => (string) ($result['reason'] ?? ''),
    ));
}

eottae_public_ai_admin_json(false, '지원하지 않는 요청입니다.');
