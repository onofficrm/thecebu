<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

include_once G5_LIB_PATH.'/eottae-job.lib.php';

if (!function_exists('eottae_job_write_ai_clean')) {
    function eottae_job_write_ai_clean($value, $len = 500)
    {
        $value = trim(strip_tags((string) $value));
        if ($value !== '' && function_exists('cut_str')) {
            $value = cut_str($value, max(1, (int) $len), '');
        }

        return $value;
    }
}

if (!function_exists('eottae_job_write_ai_parse_input')) {
    function eottae_job_write_ai_parse_input(array $post)
    {
        $keys = array(
            'company', 'job_type', 'headcount', 'region', 'job_recruit_status',
            'work_type', 'work_hours', 'salary', 'pay_type',
            'work_desc', 'qualification', 'age', 'gender', 'career', 'language',
            'benefits', 'preferred', 'apply_method', 'contact', 'kakao_id', 'email', 'deadline', 'extra',
        );

        $input = array();
        foreach ($keys as $key) {
            $input[$key] = eottae_job_write_ai_clean($post[$key] ?? '', 1200);
        }
        $input['job_recruit_status'] = eottae_job_normalize_recruit_status($input['job_recruit_status'] ?: 'recruiting');

        return $input;
    }
}

if (!function_exists('eottae_job_write_ai_generate_template')) {
    function eottae_job_write_ai_generate_template(array $input)
    {
        $company = $input['company'] !== '' ? $input['company'] : '세부 업체';
        $job_type = $input['job_type'] !== '' ? $input['job_type'] : '직원';
        $region = $input['region'] !== '' ? $input['region'] : '세부';
        $salary = $input['salary'] !== '' ? $input['salary'] : '협의';
        $contact = $input['contact'] !== '' ? $input['contact'] : '쪽지 또는 댓글 문의';

        return eottae_job_template_normalize_data(array_merge($input, array(
            'company' => $company,
            'job_type' => $job_type,
            'headcount' => $input['headcount'] !== '' ? $input['headcount'] : '1명 이상',
            'region' => $region,
            'salary' => $salary,
            'pay_type' => $input['pay_type'] !== '' ? $input['pay_type'] : 'nego',
            'work_desc' => $input['work_desc'] !== ''
                ? $input['work_desc']
                : $company.'에서 '.$job_type.' 업무를 담당할 분을 찾습니다. 고객 응대와 기본 업무를 성실하게 함께해 주실 분을 환영합니다.',
            'qualification' => $input['qualification'] !== ''
                ? $input['qualification']
                : '성실하고 책임감 있는 분, 기본적인 의사소통이 가능한 분',
            'apply_method' => $input['apply_method'] !== '' ? $input['apply_method'] : '쪽지 또는 연락처로 지원',
            'contact' => $contact,
            'extra' => $input['extra'] !== ''
                ? $input['extra']
                : '상세 근무 조건은 면접 또는 연락 시 협의 가능합니다.',
        )));
    }
}

if (!function_exists('eottae_job_write_ai_generate')) {
    function eottae_job_write_ai_generate(array $input)
    {
        $fallback = eottae_job_write_ai_generate_template($input);

        if (!function_exists('eottae_ai_openai_chat_completion')) {
            return array('data' => $fallback, 'source' => 'template');
        }

        $cfg = eottae_ai_generate_bootstrap_config();
        if (empty($cfg['enabled']) || empty($cfg['api_key'])) {
            return array('data' => $fallback, 'source' => 'template');
        }

        $prompt = "필리핀 세부 지역 사이트 '세부어때'의 구인공고 초안을 작성해 주세요.\n"
            ."아래 기존 입력값을 최대한 보존하고, 비어 있는 구인 템플릿 필드를 자연스러운 한국어로 채우세요.\n"
            ."과장된 복지, 허위 급여, 법률/비자 보장은 쓰지 마세요.\n"
            ."반드시 JSON만 응답하세요. 키: company, job_type, headcount, region, job_recruit_status, work_type, work_hours, salary, pay_type, work_desc, qualification, age, gender, career, language, benefits, preferred, apply_method, contact, kakao_id, email, deadline, extra\n\n"
            ."입력값:\n".json_encode($input, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        $completion = eottae_ai_openai_chat_completion(array(
            'model' => $cfg['model'],
            'messages' => array(
                array('role' => 'system', 'content' => 'You write practical Korean job post drafts for a Cebu local community. Return strict JSON only.'),
                array('role' => 'user', 'content' => $prompt),
            ),
            'temperature' => 0.55,
            'max_tokens' => 1100,
            'response_format' => array('type' => 'json_object'),
        ), array('timeout' => 45, 'connect_timeout' => 10));

        $json = eottae_ai_openai_parse_json_content($completion);
        if (!is_array($json)) {
            return array('data' => $fallback, 'source' => 'template');
        }

        $data = eottae_job_template_normalize_data(array_merge($fallback, $json));

        return array('data' => $data, 'source' => 'api');
    }
}
