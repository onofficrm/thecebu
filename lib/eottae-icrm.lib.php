<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('eottae_icrm_load_uri_lib')) {
    function eottae_icrm_load_uri_lib()
    {
        if (!function_exists('generate_seo_title') || !function_exists('exist_seo_title_recursive')) {
            include_once G5_LIB_PATH.'/uri.lib.php';
        }
    }
}

if (!function_exists('eottae_icrm_normalize_bo_table')) {
    function eottae_icrm_normalize_bo_table($bo_table)
    {
        return preg_replace('/[^a-z0-9_]/', '', (string) $bo_table);
    }
}

if (!function_exists('eottae_icrm_normalize_wr_id')) {
    function eottae_icrm_normalize_wr_id($wr_id)
    {
        return max(0, (int) $wr_id);
    }
}

if (!function_exists('eottae_icrm_is_authorized')) {
    /**
     * data/eottae-secrets.local.php — icrm_api_token 및/또는 icrm_allowed_ips
     */
    function eottae_icrm_is_authorized()
    {
        if (!function_exists('eottae_secrets_load')) {
            include_once G5_LIB_PATH.'/eottae-secrets.lib.php';
        }
        eottae_secrets_load();

        $token = function_exists('eottae_secrets_get')
            ? trim((string) eottae_secrets_get('icrm_api_token', ''))
            : '';

        if ($token !== '') {
            $provided = '';
            if (!empty($_SERVER['HTTP_X_ICRM_TOKEN'])) {
                $provided = trim((string) $_SERVER['HTTP_X_ICRM_TOKEN']);
            } elseif (isset($_GET['token'])) {
                $provided = trim((string) $_GET['token']);
            } elseif (isset($_POST['token'])) {
                $provided = trim((string) $_POST['token']);
            }

            if ($provided !== '' && hash_equals($token, $provided)) {
                return true;
            }
        }

        $allowed_ips = function_exists('eottae_secrets_get')
            ? trim((string) eottae_secrets_get('icrm_allowed_ips', ''))
            : '';

        if ($allowed_ips !== '') {
            $remote = isset($_SERVER['REMOTE_ADDR']) ? trim((string) $_SERVER['REMOTE_ADDR']) : '';
            if ($remote !== '') {
                $list = preg_split('/\s*,\s*/', $allowed_ips);
                foreach ($list as $ip) {
                    $ip = trim((string) $ip);
                    if ($ip !== '' && hash_equals($ip, $remote)) {
                        return true;
                    }
                }
            }
        }

        return false;
    }
}

if (!function_exists('eottae_icrm_auth_configured')) {
    function eottae_icrm_auth_configured()
    {
        if (!function_exists('eottae_secrets_load')) {
            include_once G5_LIB_PATH.'/eottae-secrets.lib.php';
        }
        eottae_secrets_load();

        $token = trim((string) eottae_secrets_get('icrm_api_token', ''));
        $ips = trim((string) eottae_secrets_get('icrm_allowed_ips', ''));

        return $token !== '' || $ips !== '';
    }
}

if (!function_exists('eottae_icrm_json')) {
    function eottae_icrm_json(array $payload, $http_code = 200)
    {
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        if (!headers_sent()) {
            header('Content-Type: application/json; charset=utf-8');
        }

        $http_code = (int) $http_code;
        if ($http_code >= 400) {
            http_response_code($http_code);
        }

        echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }
}

if (!function_exists('eottae_icrm_build_final_url')) {
    /**
     * 짧은주소(글이름) — 끝에 반드시 /
     */
    function eottae_icrm_build_final_url($bo_table, $wr_seo_title, $wr_id)
    {
        $bo_table = eottae_icrm_normalize_bo_table($bo_table);
        $wr_id = eottae_icrm_normalize_wr_id($wr_id);
        $wr_seo_title = trim((string) $wr_seo_title);
        $base = rtrim(G5_URL, '/');

        if ($bo_table === '') {
            return '';
        }

        if ($wr_seo_title !== '') {
            return $base.'/'.$bo_table.'/'.urlencode($wr_seo_title).'/';
        }

        return $base.'/bbs/board.php?bo_table='.rawurlencode($bo_table).'&wr_id='.$wr_id;
    }
}

if (!function_exists('eottae_icrm_seo_title_is_duplicate')) {
    /**
     * 동일 게시판 내 다른 글과 wr_seo_title 충돌 여부
     */
    function eottae_icrm_seo_title_is_duplicate($write_table, $seo_title, $wr_id)
    {
        eottae_icrm_load_uri_lib();

        $seo_title = trim((string) $seo_title);
        if ($seo_title === '' || !function_exists('exist_seo_url')) {
            return false;
        }

        return exist_seo_url('bbs', $seo_title, $write_table, $wr_id) === 'is_exists';
    }
}

if (!function_exists('eottae_icrm_save_wr_seo_title')) {
    function eottae_icrm_save_wr_seo_title($write_table, $wr_id, $seo_title)
    {
        $seo_title = trim((string) $seo_title);
        $wr_id = eottae_icrm_normalize_wr_id($wr_id);

        sql_query("
            UPDATE `{$write_table}` SET
                wr_seo_title = '".sql_real_escape_string($seo_title)."'
            WHERE wr_id = '{$wr_id}'
        ", false);

        if (function_exists('get_write')) {
            get_write($write_table, $wr_id, false);
        }

        return $seo_title;
    }
}

if (!function_exists('eottae_icrm_generate_wr_seo_title_from_subject')) {
    /**
     * wr_subject → generate_seo_title + exist_seo_title_recursive (write_update.php 동일)
     */
    function eottae_icrm_generate_wr_seo_title_from_subject($write_table, $wr_id, $subject)
    {
        eottae_icrm_load_uri_lib();

        $subject = trim(strip_tags((string) $subject));
        if ($subject === '' || !function_exists('generate_seo_title') || !function_exists('exist_seo_title_recursive')) {
            return '';
        }

        return exist_seo_title_recursive(
            'bbs',
            generate_seo_title($subject),
            $write_table,
            $wr_id
        );
    }
}

if (!function_exists('eottae_icrm_ensure_wr_seo_title')) {
    /**
     * wr_seo_title 확정 — 비어 있거나 중복이면 그누보드 기본 함수로 재생성 후 DB 저장
     * iCRM이 넣은 slug는 충돌 시 홈페이지가 최종값을 결정한다.
     *
     * @return array{ok:bool,message?:string,wr_seo_title?:string,created?:bool,fixed?:bool,not_found?:bool}
     */
    function eottae_icrm_ensure_wr_seo_title($bo_table, $wr_id)
    {
        global $g5;

        eottae_icrm_load_uri_lib();

        $bo_table = eottae_icrm_normalize_bo_table($bo_table);
        $wr_id = eottae_icrm_normalize_wr_id($wr_id);

        if ($bo_table === '' || $wr_id < 1) {
            return array('ok' => false, 'message' => 'bo_table 또는 wr_id가 올바르지 않습니다.');
        }

        $board = get_board_db($bo_table, true);
        if (empty($board['bo_table'])) {
            return array('ok' => false, 'message' => '게시판을 찾을 수 없습니다.', 'not_found' => true);
        }

        $write_table = $g5['write_prefix'].$bo_table;
        $write = sql_fetch("
            SELECT wr_id, wr_subject, wr_seo_title, wr_is_comment
            FROM `{$write_table}`
            WHERE wr_id = '{$wr_id}'
            LIMIT 1
        ", false);

        if (empty($write['wr_id']) || !empty($write['wr_is_comment'])) {
            return array(
                'ok'         => false,
                'message'    => '게시글을 찾을 수 없습니다.',
                'not_found'  => true,
            );
        }

        $subject = trim(strip_tags((string) ($write['wr_subject'] ?? '')));
        $seo_title = trim((string) ($write['wr_seo_title'] ?? ''));
        $created = false;
        $fixed = false;

        if ($seo_title === '') {
            if ($subject === '') {
                return array('ok' => true, 'wr_seo_title' => '', 'created' => false);
            }

            if (function_exists('seo_title_update')) {
                seo_title_update($write_table, $wr_id, 'bbs');
                $refreshed = get_write($write_table, $wr_id, true);
                $seo_title = trim((string) ($refreshed['wr_seo_title'] ?? ''));
            }

            if ($seo_title === '') {
                $seo_title = eottae_icrm_generate_wr_seo_title_from_subject($write_table, $wr_id, $subject);
                if ($seo_title !== '') {
                    eottae_icrm_save_wr_seo_title($write_table, $wr_id, $seo_title);
                }
            }

            $created = $seo_title !== '';
        } elseif (eottae_icrm_seo_title_is_duplicate($write_table, $seo_title, $wr_id)) {
            $base_subject = $subject !== '' ? $subject : $seo_title;
            $seo_title = eottae_icrm_generate_wr_seo_title_from_subject($write_table, $wr_id, $base_subject);
            if ($seo_title !== '') {
                eottae_icrm_save_wr_seo_title($write_table, $wr_id, $seo_title);
                $fixed = true;
            }
        }

        if ($created || $fixed) {
            if (function_exists('eottae_board_seo_sync_write')) {
                include_once G5_LIB_PATH.'/eottae-board-seo.lib.php';
                eottae_board_seo_sync_write($bo_table, $wr_id);
            }
        }

        return array(
            'ok'            => true,
            'wr_seo_title'  => $seo_title,
            'created'       => $created,
            'fixed'         => $fixed,
        );
    }
}

if (!function_exists('eottae_icrm_resolve_post')) {
    /**
     * iCRM 연동 — 저장된 wr_seo_title + final_url 반환 (제목으로 URL 예측 금지)
     *
     * @return array<string, mixed>
     */
    function eottae_icrm_resolve_post($bo_table, $wr_id)
    {
        $bo_table = eottae_icrm_normalize_bo_table($bo_table);
        $wr_id = eottae_icrm_normalize_wr_id($wr_id);

        if ($bo_table === '' || $wr_id < 1) {
            return array(
                'ok'      => false,
                'message' => 'bo_table(영문/숫자/_)과 wr_id(정수)가 필요합니다.',
            );
        }

        $ensure = eottae_icrm_ensure_wr_seo_title($bo_table, $wr_id);
        if (empty($ensure['ok'])) {
            $payload = array(
                'ok'      => false,
                'message' => $ensure['message'] ?? 'wr_seo_title 확정에 실패했습니다.',
            );
            if (!empty($ensure['not_found'])) {
                $payload['not_found'] = true;
            }

            return $payload;
        }

        $wr_seo_title = trim((string) ($ensure['wr_seo_title'] ?? ''));

        return array(
            'ok'            => true,
            'bo_table'      => $bo_table,
            'wr_id'         => $wr_id,
            'wr_seo_title'  => $wr_seo_title,
            'final_url'     => eottae_icrm_build_final_url($bo_table, $wr_seo_title, $wr_id),
            'seo_created'   => !empty($ensure['created']),
        );
    }
}
