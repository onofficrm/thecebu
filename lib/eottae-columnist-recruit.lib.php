<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('eottae_columnist_recruit_url')) {
    function eottae_columnist_recruit_url(array $params = array())
    {
        $url = G5_URL.'/columnist/';
        if (!empty($params)) {
            $url .= '?'.http_build_query($params);
        }

        return $url;
    }
}

if (!function_exists('eottae_columnist_recruit_proc_url')) {
    function eottae_columnist_recruit_proc_url()
    {
        return G5_URL.'/proc/eottae-columnist-recruit.php';
    }
}

if (!function_exists('eottae_columnist_recruit_img_base')) {
    function eottae_columnist_recruit_img_base()
    {
        return defined('G5_URL') ? G5_URL.'/img/columnist' : '/img/columnist';
    }
}

if (!function_exists('eottae_columnist_recruit_interest_options')) {
    function eottae_columnist_recruit_interest_options()
    {
        return array(
            'food'       => '맛집/카페',
            'life'       => '생활정보',
            'education'  => '교육/육아',
            'estate'     => '부동산',
            'hobby'      => '취미/모임',
            'business'   => '사업/창업',
            'travel'     => '여행정보',
            'other'      => '기타',
        );
    }
}

if (!function_exists('eottae_columnist_recruit_interest_label')) {
    function eottae_columnist_recruit_interest_label($code)
    {
        $options = eottae_columnist_recruit_interest_options();
        $code = preg_replace('/[^a-z_]/', '', (string) $code);

        return isset($options[$code]) ? $options[$code] : '';
    }
}

if (!function_exists('eottae_columnist_recruit_token')) {
    function eottae_columnist_recruit_token($regenerate = false)
    {
        $token = get_session('eottae_columnist_recruit_token');
        if ($regenerate || $token === '') {
            $token = bin2hex(random_bytes(16));
            set_session('eottae_columnist_recruit_token', $token);
        }

        return $token;
    }
}

if (!function_exists('eottae_columnist_recruit_verify_token')) {
    function eottae_columnist_recruit_verify_token($token)
    {
        $session_token = get_session('eottae_columnist_recruit_token');

        return $session_token !== '' && is_string($token) && hash_equals($session_token, $token);
    }
}

if (!function_exists('eottae_columnist_recruit_comic_panels')) {
    function eottae_columnist_recruit_comic_panels()
    {
        $img = eottae_columnist_recruit_img_base();

        return array(
            array(
                'num'   => '1',
                'lines' => array('세부에 살다 보면 이런 생각 들 때 없나요?', '이 정보, 나만 알기 아까운데?'),
                'image' => $img.'/comic-01.svg',
            ),
            array(
                'num'   => '2',
                'lines' => array('맛집도 알고, 병원도 알고, 집 구하는 팁도 알고…', '근데 이걸 어디에 정리하지?'),
                'image' => $img.'/comic-02.svg',
            ),
            array(
                'num'   => '3',
                'lines' => array('세부어때 컬럼리스트가 되어주세요!', '당신의 경험이 누군가에게는 진짜 필요한 정보가 됩니다.'),
                'image' => $img.'/comic-03.svg',
            ),
            array(
                'num'   => '4',
                'lines' => array('글을 잘 쓰지 않아도 괜찮아요.', '직접 겪은 이야기면 충분합니다.'),
                'image' => $img.'/comic-04.svg',
            ),
            array(
                'num'   => '5',
                'lines' => array('활동하면 내 이름도 알리고, 내 전문 분야도 보여줄 수 있어요.', '업체나 개인 활동 홍보에도 도움이 됩니다.'),
                'image' => $img.'/comic-05.svg',
            ),
            array(
                'num'   => '6',
                'lines' => array('세부의 좋은 정보를 함께 쌓아가는 사람들', '지금 세부어때 컬럼리스트에 참여해보세요!'),
                'image' => $img.'/comic-06.svg',
            ),
        );
    }
}

if (!function_exists('eottae_columnist_recruit_build_message')) {
    function eottae_columnist_recruit_build_message(array $data)
    {
        $lines = array('[컬럼리스트 모집 랜딩 신청]');
        if (!empty($data['contact_phone'])) {
            $lines[] = '연락처: '.$data['contact_phone'];
        }
        if (!empty($data['contact_kakao'])) {
            $lines[] = '카카오톡 ID: '.$data['contact_kakao'];
        }
        if (!empty($data['contact_email'])) {
            $lines[] = '이메일: '.$data['contact_email'];
        }
        if (!empty($data['topic_idea'])) {
            $lines[] = '작성 희망 주제: '.$data['topic_idea'];
        }
        if (!empty($data['referer'])) {
            $lines[] = '접수 페이지: '.$data['referer'];
        }
        if (!empty($data['mb_id'])) {
            $lines[] = '회원 ID: '.$data['mb_id'];
        }

        return implode("\n", $lines);
    }
}

if (!function_exists('eottae_columnist_recruit_submit')) {
    function eottae_columnist_recruit_submit(array $input, array $member = array())
    {
        include_once G5_LIB_PATH.'/eottae-column.lib.php';
        eottae_column_bootstrap_tables();
        global $g5;

        $mb_id = trim((string) ($member['mb_id'] ?? ''));
        if ($mb_id !== '' && eottae_column_is_columnist($mb_id)) {
            return array('ok' => false, 'message' => '이미 컬럼리스트로 등록되어 있습니다.');
        }
        if ($mb_id !== '') {
            $latest = eottae_column_get_latest_application($mb_id);
            if ($latest && ($latest['status'] ?? '') === 'pending') {
                return array('ok' => false, 'message' => '이미 검토 중인 신청이 있습니다. 마이페이지에서 확인해 주세요.');
            }
        }

        $pen_name = trim(strip_tags((string) ($input['pen_name'] ?? '')));
        $contact_phone = trim(strip_tags((string) ($input['contact_phone'] ?? '')));
        $contact_kakao = trim(strip_tags((string) ($input['contact_kakao'] ?? '')));
        $contact_email = trim(strip_tags((string) ($input['contact_email'] ?? '')));
        $interest = preg_replace('/[^a-z_]/', '', (string) ($input['interest'] ?? ''));
        $topic_idea = trim(strip_tags((string) ($input['topic_idea'] ?? '')));
        $channel_url = eottae_column_normalize_url($input['channel_url'] ?? '');
        $bio = trim((string) ($input['bio'] ?? ''));

        if ($pen_name === '') {
            return array('ok' => false, 'message' => '이름 또는 닉네임을 입력해 주세요.');
        }
        if (mb_strlen($pen_name, 'UTF-8') > 80) {
            return array('ok' => false, 'message' => '이름은 80자 이내로 입력해 주세요.');
        }
        if ($contact_phone === '' && $contact_kakao === '') {
            return array('ok' => false, 'message' => '연락처 또는 카카오톡 ID 중 하나는 필수입니다.');
        }
        if ($interest === '' || eottae_columnist_recruit_interest_label($interest) === '') {
            return array('ok' => false, 'message' => '관심 분야를 선택해 주세요.');
        }
        if ($bio === '') {
            return array('ok' => false, 'message' => '간단한 자기소개를 입력해 주세요.');
        }
        if (mb_strlen($bio, 'UTF-8') > 2000) {
            return array('ok' => false, 'message' => '자기소개는 2000자 이내로 입력해 주세요.');
        }
        if ($contact_email !== '' && !filter_var($contact_email, FILTER_VALIDATE_EMAIL)) {
            return array('ok' => false, 'message' => '이메일 형식을 확인해 주세요.');
        }

        $interest_label = eottae_columnist_recruit_interest_label($interest);
        $title = '컬럼리스트 모집 신청';
        $specialty = $interest_label;
        if ($topic_idea !== '') {
            $specialty .= ' · '.$topic_idea;
        }

        $message = eottae_columnist_recruit_build_message(array(
            'contact_phone' => $contact_phone,
            'contact_kakao' => $contact_kakao,
            'contact_email' => $contact_email,
            'topic_idea'    => $topic_idea,
            'referer'       => trim((string) ($input['referer'] ?? '')),
            'mb_id'         => $mb_id,
        ));

        $table = $g5['sebu_column_author_applications_table'];
        $now = G5_TIME_YMDHIS;
        $guest_mb_id = $mb_id !== '' ? $mb_id : 'guest_'.substr(md5($pen_name.$contact_phone.$contact_kakao), 0, 12);

        sql_query(" INSERT INTO `{$table}` SET
            mb_id = '".sql_escape_string($guest_mb_id)."',
            pen_name = '".sql_escape_string($pen_name)."',
            title = '".sql_escape_string($title)."',
            specialty = '".sql_escape_string($specialty)."',
            bio = '".sql_escape_string($bio)."',
            area = '".sql_escape_string($interest)."',
            website_url = '',
            sns_url = '',
            profile_image = '',
            sample_url = '".sql_escape_string($channel_url)."',
            message = '".sql_escape_string($message)."',
            status = 'pending',
            created_at = '{$now}',
            updated_at = '{$now}'
        ", false);

        $application_id = (int) sql_insert_id();
        if ($application_id < 1) {
            return array('ok' => false, 'message' => '신청서 저장에 실패했습니다. 잠시 후 다시 시도해 주세요.');
        }

        return array(
            'ok'              => true,
            'message'         => '신청이 접수되었습니다.',
            'application_id'  => $application_id,
        );
    }
}
