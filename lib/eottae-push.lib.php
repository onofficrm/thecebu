<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('eottae_push_table')) {
    function eottae_push_table()
    {
        global $g5;
        if (!isset($g5['eottae_push_subscriptions_table'])) {
            $g5['eottae_push_subscriptions_table'] = G5_TABLE_PREFIX.'eottae_push_subscriptions';
        }

        return $g5['eottae_push_subscriptions_table'];
    }
}

if (!function_exists('eottae_push_campaign_table')) {
    function eottae_push_campaign_table()
    {
        global $g5;
        if (!isset($g5['eottae_push_campaigns_table'])) {
            $g5['eottae_push_campaigns_table'] = G5_TABLE_PREFIX.'eottae_push_campaigns';
        }

        return $g5['eottae_push_campaigns_table'];
    }
}

if (!function_exists('eottae_push_enabled')) {
    function eottae_push_enabled()
    {
        if (!function_exists('g5site_cfg')) {
            return false;
        }
        $enabled = g5site_cfg('web_push_enabled', false);

        return $enabled === true || $enabled === 1 || $enabled === '1';
    }
}

if (!function_exists('eottae_push_public_key')) {
    function eottae_push_public_key()
    {
        return function_exists('g5site_cfg') ? trim((string) g5site_cfg('web_push_public_key', '')) : '';
    }
}

if (!function_exists('eottae_push_private_key_pem')) {
    function eottae_push_private_key_pem()
    {
        $pem = function_exists('g5site_cfg') ? trim((string) g5site_cfg('web_push_private_key_pem', '')) : '';

        return str_replace('\n', "\n", $pem);
    }
}

if (!function_exists('eottae_push_is_configured')) {
    function eottae_push_is_configured()
    {
        return eottae_push_enabled()
            && eottae_push_public_key() !== ''
            && eottae_push_private_key_pem() !== ''
            && function_exists('curl_init')
            && function_exists('openssl_sign');
    }
}

if (!function_exists('eottae_push_ensure_schema')) {
    function eottae_push_ensure_schema()
    {
        $table = eottae_push_table();
        sql_query(" CREATE TABLE IF NOT EXISTS `{$table}` (
            `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `mb_id` varchar(20) NOT NULL DEFAULT '',
            `endpoint_hash` char(64) NOT NULL DEFAULT '',
            `endpoint` text NOT NULL,
            `p256dh` varchar(255) NOT NULL DEFAULT '',
            `auth` varchar(255) NOT NULL DEFAULT '',
            `user_agent` varchar(255) NOT NULL DEFAULT '',
            `is_active` tinyint(1) NOT NULL DEFAULT '1',
            `last_error` varchar(500) NOT NULL DEFAULT '',
            `created_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
            `updated_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
            `last_sent_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
            PRIMARY KEY (`id`),
            UNIQUE KEY `endpoint_hash` (`endpoint_hash`),
            KEY `idx_mb_active` (`mb_id`, `is_active`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ", false);

        $campaign_table = eottae_push_campaign_table();
        sql_query(" CREATE TABLE IF NOT EXISTS `{$campaign_table}` (
            `campaign_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `title` varchar(120) NOT NULL DEFAULT '',
            `body` varchar(500) NOT NULL DEFAULT '',
            `url` varchar(500) NOT NULL DEFAULT '',
            `created_by` varchar(20) NOT NULL DEFAULT '',
            `created_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
            `expires_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
            `sent_count` int(11) unsigned NOT NULL DEFAULT '0',
            `is_active` tinyint(1) NOT NULL DEFAULT '1',
            PRIMARY KEY (`campaign_id`),
            KEY `idx_active_expires` (`is_active`, `expires_at`),
            KEY `idx_created_at` (`created_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ", false);
    }
}

if (!function_exists('eottae_push_token')) {
    function eottae_push_token($refresh = false)
    {
        if ($refresh || !get_session('ss_eottae_push_token')) {
            set_session('ss_eottae_push_token', function_exists('get_random_token_string') ? get_random_token_string(16) : bin2hex(random_bytes(16)));
        }

        return (string) get_session('ss_eottae_push_token');
    }
}

if (!function_exists('eottae_push_verify_token')) {
    function eottae_push_verify_token($token)
    {
        $session_token = eottae_push_token(false);

        return $session_token !== '' && is_string($token) && hash_equals($session_token, (string) $token);
    }
}

if (!function_exists('eottae_push_base64url')) {
    function eottae_push_base64url($value)
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }
}

if (!function_exists('eottae_push_register_subscription')) {
    function eottae_push_register_subscription($mb_id, array $subscription)
    {
        eottae_push_ensure_schema();

        $mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) $mb_id);
        $endpoint = trim((string) ($subscription['endpoint'] ?? ''));
        $keys = isset($subscription['keys']) && is_array($subscription['keys']) ? $subscription['keys'] : array();
        $p256dh = trim((string) ($keys['p256dh'] ?? ''));
        $auth = trim((string) ($keys['auth'] ?? ''));
        if ($mb_id === '' || $endpoint === '' || $p256dh === '' || $auth === '') {
            return array('ok' => false, 'message' => '구독 정보가 올바르지 않습니다.');
        }

        $table = eottae_push_table();
        $hash = hash('sha256', $endpoint);
        $ua = isset($_SERVER['HTTP_USER_AGENT']) ? substr((string) $_SERVER['HTTP_USER_AGENT'], 0, 255) : '';
        $now = defined('G5_TIME_YMDHIS') ? G5_TIME_YMDHIS : date('Y-m-d H:i:s');
        sql_query(" INSERT INTO `{$table}` SET
                mb_id = '".sql_escape_string($mb_id)."',
                endpoint_hash = '{$hash}',
                endpoint = '".sql_escape_string($endpoint)."',
                p256dh = '".sql_escape_string($p256dh)."',
                auth = '".sql_escape_string($auth)."',
                user_agent = '".sql_escape_string($ua)."',
                is_active = '1',
                last_error = '',
                created_at = '{$now}',
                updated_at = '{$now}'
            ON DUPLICATE KEY UPDATE
                mb_id = VALUES(mb_id),
                p256dh = VALUES(p256dh),
                auth = VALUES(auth),
                user_agent = VALUES(user_agent),
                is_active = '1',
                last_error = '',
                updated_at = VALUES(updated_at) ", false);

        return array('ok' => true, 'message' => '푸시 알림이 등록되었습니다.');
    }
}

if (!function_exists('eottae_push_unregister_subscription')) {
    function eottae_push_unregister_subscription($mb_id, $endpoint)
    {
        eottae_push_ensure_schema();
        $mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) $mb_id);
        $endpoint = trim((string) $endpoint);
        if ($mb_id === '' || $endpoint === '') {
            return array('ok' => false, 'message' => '구독 정보가 올바르지 않습니다.');
        }

        $table = eottae_push_table();
        $hash = hash('sha256', $endpoint);
        sql_query(" UPDATE `{$table}` SET is_active = '0', updated_at = '".G5_TIME_YMDHIS."'
                    WHERE mb_id = '".sql_escape_string($mb_id)."'
                      AND endpoint_hash = '{$hash}' ", false);

        return array('ok' => true, 'message' => '푸시 알림을 해제했습니다.');
    }
}

if (!function_exists('eottae_push_member_subscription_count')) {
    function eottae_push_member_subscription_count($mb_id)
    {
        eottae_push_ensure_schema();
        $mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) $mb_id);
        if ($mb_id === '') {
            return 0;
        }

        $table = eottae_push_table();
        $row = sql_fetch(" SELECT COUNT(*) AS cnt FROM `{$table}`
                            WHERE mb_id = '".sql_escape_string($mb_id)."'
                              AND is_active = '1' ", false);

        return (int) ($row['cnt'] ?? 0);
    }
}

if (!function_exists('eottae_push_endpoint_audience')) {
    function eottae_push_endpoint_audience($endpoint)
    {
        $parts = parse_url((string) $endpoint);
        if (empty($parts['scheme']) || empty($parts['host'])) {
            return '';
        }

        return $parts['scheme'].'://'.$parts['host'];
    }
}

if (!function_exists('eottae_push_vapid_authorization')) {
    function eottae_push_ecdsa_der_to_raw($der, $part_length = 32)
    {
        $offset = 0;
        if (ord($der[$offset++]) !== 0x30) {
            return $der;
        }
        $seq_len = ord($der[$offset++]);
        if ($seq_len & 0x80) {
            $bytes = $seq_len & 0x7f;
            $seq_len = 0;
            for ($i = 0; $i < $bytes; $i++) {
                $seq_len = ($seq_len << 8) | ord($der[$offset++]);
            }
        }
        $parts = array();
        for ($i = 0; $i < 2; $i++) {
            if (ord($der[$offset++]) !== 0x02) {
                return $der;
            }
            $len = ord($der[$offset++]);
            $part = substr($der, $offset, $len);
            $offset += $len;
            $part = ltrim($part, "\x00");
            $parts[] = str_pad($part, $part_length, "\x00", STR_PAD_LEFT);
        }

        return implode('', $parts);
    }

    function eottae_push_vapid_authorization($endpoint)
    {
        $private = eottae_push_private_key_pem();
        $public = eottae_push_public_key();
        $aud = eottae_push_endpoint_audience($endpoint);
        if ($private === '' || $public === '' || $aud === '') {
            return array();
        }

        $subject = function_exists('g5site_cfg') ? trim((string) g5site_cfg('web_push_subject', 'mailto:help@thecebu.co.kr')) : 'mailto:help@thecebu.co.kr';
        $header = eottae_push_base64url(json_encode(array('typ' => 'JWT', 'alg' => 'ES256')));
        $claims = eottae_push_base64url(json_encode(array(
            'aud' => $aud,
            'exp' => time() + 3600,
            'sub' => $subject !== '' ? $subject : 'mailto:help@thecebu.co.kr',
        )));
        $unsigned = $header.'.'.$claims;
        $signature = '';
        if (!openssl_sign($unsigned, $signature, $private, OPENSSL_ALGO_SHA256)) {
            return array();
        }
        $signature = eottae_push_ecdsa_der_to_raw($signature);

        return array(
            'Authorization: WebPush '.$unsigned.'.'.eottae_push_base64url($signature),
            'Crypto-Key: p256ecdsa='.$public,
        );
    }
}

if (!function_exists('eottae_push_send_to_subscription')) {
    function eottae_push_send_to_subscription(array $row)
    {
        if (!eottae_push_is_configured()) {
            return array('ok' => false, 'message' => '푸시 키가 설정되지 않았습니다.');
        }

        $endpoint = trim((string) ($row['endpoint'] ?? ''));
        if ($endpoint === '') {
            return array('ok' => false, 'message' => '엔드포인트가 없습니다.');
        }

        $headers = array_merge(array(
            'TTL: 86400',
            'Content-Length: 0',
            'Urgency: high',
            'Topic: thecebu-app-notification',
        ), eottae_push_vapid_authorization($endpoint));

        $ch = curl_init($endpoint);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 8);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_exec($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($status >= 200 && $status < 300) {
            return array('ok' => true, 'message' => 'sent');
        }

        return array('ok' => false, 'message' => $error !== '' ? $error : 'HTTP '.$status, 'status' => $status);
    }
}

if (!function_exists('eottae_push_send_to_member')) {
    function eottae_push_send_to_member($mb_id)
    {
        eottae_push_ensure_schema();
        if (!eottae_push_is_configured()) {
            return array('ok' => false, 'sent' => 0, 'message' => '푸시 키가 설정되지 않았습니다.');
        }

        $mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) $mb_id);
        if ($mb_id === '') {
            return array('ok' => false, 'sent' => 0, 'message' => '회원 정보가 없습니다.');
        }

        $table = eottae_push_table();
        $rows = sql_query(" SELECT * FROM `{$table}`
                             WHERE mb_id = '".sql_escape_string($mb_id)."'
                               AND is_active = '1' ", false);
        $sent = 0;
        while ($row = sql_fetch_array($rows)) {
            $result = eottae_push_send_to_subscription($row);
            $now = G5_TIME_YMDHIS;
            if (!empty($result['ok'])) {
                $sent++;
                sql_query(" UPDATE `{$table}` SET last_sent_at = '{$now}', last_error = '', updated_at = '{$now}'
                            WHERE id = '".(int) $row['id']."' ", false);
            } else {
                $status = (int) ($result['status'] ?? 0);
                $active = in_array($status, array(404, 410), true) ? 0 : 1;
                sql_query(" UPDATE `{$table}` SET is_active = '{$active}', last_error = '".sql_escape_string($result['message'] ?? 'failed')."', updated_at = '{$now}'
                            WHERE id = '".(int) $row['id']."' ", false);
            }
        }

        return array('ok' => $sent > 0, 'sent' => $sent);
    }
}

if (!function_exists('eottae_push_broadcast_active')) {
    function eottae_push_broadcast_active($limit = 500)
    {
        eottae_push_ensure_schema();
        if (!eottae_push_is_configured()) {
            return array('ok' => false, 'sent' => 0, 'message' => '푸시 키가 설정되지 않았습니다.');
        }

        $table = eottae_push_table();
        $limit = max(1, min(1000, (int) $limit));
        $rows = sql_query(" SELECT * FROM `{$table}` WHERE is_active = '1' ORDER BY updated_at DESC LIMIT {$limit} ", false);
        $sent = 0;
        while ($row = sql_fetch_array($rows)) {
            $result = eottae_push_send_to_subscription($row);
            if (!empty($result['ok'])) {
                $sent++;
                sql_query(" UPDATE `{$table}` SET last_sent_at = '".G5_TIME_YMDHIS."', last_error = '' WHERE id = '".(int) $row['id']."' ", false);
            }
        }

        return array('ok' => $sent > 0, 'sent' => $sent);
    }
}

if (!function_exists('eottae_push_create_campaign')) {
    function eottae_push_create_campaign($title, $body, $url, $created_by, $ttl_minutes = 20)
    {
        eottae_push_ensure_schema();

        $title = trim((string) $title);
        $body = trim((string) $body);
        $url = trim((string) $url);
        $created_by = preg_replace('/[^a-z0-9_@.-]/i', '', (string) $created_by);
        $ttl_minutes = max(5, min(120, (int) $ttl_minutes));

        if ($title === '' || $body === '') {
            return array('ok' => false, 'message' => '제목과 내용을 입력해 주세요.');
        }
        if (function_exists('mb_substr')) {
            $title = mb_substr($title, 0, 80, 'UTF-8');
            $body = mb_substr($body, 0, 220, 'UTF-8');
        } else {
            $title = substr($title, 0, 80);
            $body = substr($body, 0, 220);
        }
        if ($url === '') {
            $url = G5_URL.'/page/eottae-notifications.php';
        } elseif (strpos($url, 'http://') !== 0 && strpos($url, 'https://') !== 0 && strpos($url, '/') === 0) {
            $url = G5_URL.$url;
        }
        if (strpos($url, G5_URL) !== 0) {
            return array('ok' => false, 'message' => '세부어때 내부 URL만 사용할 수 있습니다.');
        }

        $table = eottae_push_campaign_table();
        $now = G5_TIME_YMDHIS;
        $expires_at = date('Y-m-d H:i:s', G5_SERVER_TIME + $ttl_minutes * 60);
        sql_query(" UPDATE `{$table}` SET is_active = '0' WHERE is_active = '1' ", false);
        sql_query(" INSERT INTO `{$table}` SET
                title = '".sql_escape_string($title)."',
                body = '".sql_escape_string($body)."',
                url = '".sql_escape_string($url)."',
                created_by = '".sql_escape_string($created_by)."',
                created_at = '{$now}',
                expires_at = '".sql_escape_string($expires_at)."',
                is_active = '1' ", false);

        return array('ok' => true, 'campaign_id' => (int) sql_insert_id(), 'message' => '푸시 캠페인을 만들었습니다.');
    }
}

if (!function_exists('eottae_push_latest_campaign_payload')) {
    function eottae_push_latest_campaign_payload()
    {
        eottae_push_ensure_schema();
        $table = eottae_push_campaign_table();
        $row = sql_fetch(" SELECT *
                             FROM `{$table}`
                            WHERE is_active = '1'
                              AND expires_at >= '".sql_escape_string(G5_TIME_YMDHIS)."'
                            ORDER BY campaign_id DESC
                            LIMIT 1 ", false);
        if (empty($row['campaign_id'])) {
            return null;
        }

        return array(
            'title' => get_text($row['title'] ?? '세부어때 알림'),
            'body' => get_text($row['body'] ?? '새 소식을 확인해 주세요.'),
            'url' => (string) ($row['url'] ?? G5_URL.'/page/eottae-notifications.php'),
        );
    }
}

if (!function_exists('eottae_push_send_campaign')) {
    function eottae_push_send_campaign($title, $body, $url, $created_by, $limit = 500)
    {
        $campaign = eottae_push_create_campaign($title, $body, $url, $created_by);
        if (empty($campaign['ok'])) {
            return $campaign;
        }

        $result = eottae_push_broadcast_active($limit);
        $sent = (int) ($result['sent'] ?? 0);
        $table = eottae_push_campaign_table();
        sql_query(" UPDATE `{$table}` SET sent_count = '{$sent}' WHERE campaign_id = '".(int) $campaign['campaign_id']."' ", false);

        return array(
            'ok' => !empty($result['ok']),
            'campaign_id' => (int) $campaign['campaign_id'],
            'sent' => $sent,
            'message' => !empty($result['ok']) ? '푸시 캠페인을 발송했습니다.' : (string) ($result['message'] ?? '발송된 기기가 없습니다.'),
        );
    }
}

if (!function_exists('eottae_push_latest_payload')) {
    function eottae_push_latest_message_payload($mb_id)
    {
        if (!function_exists('eottae_message_thread_list') && is_file(G5_LIB_PATH.'/eottae-message.lib.php')) {
            include_once G5_LIB_PATH.'/eottae-message.lib.php';
        }
        if (!function_exists('eottae_message_thread_list')) {
            return null;
        }

        $threads = eottae_message_thread_list($mb_id, 1, 'unread');
        if (empty($threads[0])) {
            return null;
        }
        $thread = $threads[0];

        return array(
            'title' => '새 쪽지: '.get_text($thread['other_label'] ?? '회원'),
            'body' => get_text($thread['last_body_preview'] ?? '새 쪽지가 도착했습니다.'),
            'url' => $thread['href'] ?? G5_URL.'/page/eottae-messages.php',
        );
    }

    function eottae_push_latest_column_payload()
    {
        global $g5;
        if (empty($g5['write_prefix'])) {
            return null;
        }

        $bo_table = function_exists('eottae_column_board_table') ? eottae_column_board_table() : 'column';
        $bo_table = preg_replace('/[^a-z0-9_]/i', '', $bo_table);
        if ($bo_table === '') {
            return null;
        }

        $write_table = $g5['write_prefix'].$bo_table;
        $exists = sql_fetch(" SHOW TABLES LIKE '".sql_escape_string($write_table)."' ", false);
        if (empty($exists)) {
            return null;
        }

        $row = sql_fetch(" SELECT wr_id, wr_subject, wr_content, wr_datetime
                             FROM `{$write_table}`
                            WHERE wr_is_comment = 0
                            ORDER BY wr_datetime DESC, wr_id DESC
                            LIMIT 1 ", false);
        if (empty($row['wr_id'])) {
            return null;
        }

        $body = trim(strip_tags((string) ($row['wr_content'] ?? '')));
        if (function_exists('cut_str')) {
            $body = cut_str($body, 90);
        } else if (function_exists('mb_substr')) {
            $body = mb_substr($body, 0, 90, 'UTF-8');
        } else {
            $body = substr($body, 0, 90);
        }

        return array(
            'title' => '새 생활정보: '.get_text($row['wr_subject'] ?? ''),
            'body' => $body !== '' ? get_text($body) : '새 생활정보 칼럼이 올라왔습니다.',
            'url' => function_exists('eottae_column_view_url') ? eottae_column_view_url((int) $row['wr_id']) : G5_URL.'/bbs/board.php?bo_table='.$bo_table.'&wr_id='.(int) $row['wr_id'],
        );
    }

    function eottae_push_latest_payload($mb_id)
    {
        if (!function_exists('eottae_mypage_notification_summary') && is_file(G5_LIB_PATH.'/eottae-notification.lib.php')) {
            include_once G5_LIB_PATH.'/eottae-notification.lib.php';
        }
        if (!function_exists('eottae_talkroom_notify_list') && is_file(G5_LIB_PATH.'/eottae-talkroom-notify.lib.php')) {
            include_once G5_LIB_PATH.'/eottae-talkroom-notify.lib.php';
        }

        $campaign = eottae_push_latest_campaign_payload();
        if ($campaign) {
            return array(
                'success' => true,
                'has_notification' => true,
                'total' => 1,
                'title' => $campaign['title'],
                'body' => $campaign['body'],
                'url' => $campaign['url'],
                'icon' => function_exists('g5site_cfg_url') ? g5site_cfg_url('pwa_icon_192_path', '/img/logo/android-chrome-192x192.png') : G5_URL.'/img/logo/android-chrome-192x192.png',
                'badge' => function_exists('g5site_cfg_url') ? g5site_cfg_url('favicon_png_path', '/img/logo/favicon-32x32.png') : G5_URL.'/img/logo/favicon-32x32.png',
            );
        }

        $summary = function_exists('eottae_mypage_notification_summary') ? eottae_mypage_notification_summary($mb_id) : array('total' => 0);
        $total = (int) ($summary['total'] ?? 0);
        $message = eottae_push_latest_message_payload($mb_id);
        $latest = (!$message && function_exists('eottae_talkroom_notify_list')) ? eottae_talkroom_notify_list($mb_id, 1, 0) : array();
        $item = !empty($latest[0]) ? $latest[0] : null;
        $column = (!$message && !$item && $total < 1) ? eottae_push_latest_column_payload() : null;
        $title = $message ? $message['title'] : ($item ? (string) ($item['title'] ?? '세부어때 알림') : ($column ? $column['title'] : '세부어때 알림'));
        $body = $message ? $message['body'] : ($item ? (string) ($item['message'] ?? '') : ($column ? $column['body'] : ''));
        if ($body === '') {
            $body = $total > 0 ? '확인하지 않은 알림이 '.number_format($total).'개 있습니다.' : '새 알림을 확인해 주세요.';
        }

        return array(
            'success' => true,
            'has_notification' => $total > 0 || $message !== null || $item !== null || $column !== null,
            'total' => $total,
            'title' => $title,
            'body' => $body,
            'url' => $message ? $message['url'] : ($item && !empty($item['href']) ? $item['href'] : ($column ? $column['url'] : G5_URL.'/page/eottae-notifications.php')),
            'icon' => function_exists('g5site_cfg_url') ? g5site_cfg_url('pwa_icon_192_path', '/img/logo/android-chrome-192x192.png') : G5_URL.'/img/logo/android-chrome-192x192.png',
            'badge' => function_exists('g5site_cfg_url') ? g5site_cfg_url('favicon_png_path', '/img/logo/favicon-32x32.png') : G5_URL.'/img/logo/favicon-32x32.png',
        );
    }
}
