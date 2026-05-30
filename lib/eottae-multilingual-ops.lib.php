<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('eottae_multilingual_ops_status')) {
    /**
     * 다국어·번역 운영 점검 항목 (관리자 대시보드용)
     *
     * @return array<int, array<string, mixed>>
     */
    function eottae_multilingual_ops_status()
    {
        $items = array();

        $translation_ready = function_exists('eottae_translation_provider') && eottae_translation_provider() !== '';
        $provider = $translation_ready ? eottae_translation_provider() : '';
        $translation_key = ($provider !== '' && function_exists('eottae_translation_api_key'))
            ? trim((string) eottae_translation_api_key($provider))
            : '';
        $items[] = array(
            'id' => 'translation_provider',
            'label' => '번역 API',
            'ok' => $translation_ready && $translation_key !== '',
            'detail' => $translation_ready
                ? ($translation_key !== '' ? '제공자: '.eottae_translation_provider() : 'API 키가 비어 있습니다.')
                : 'translation_provider 미설정',
        );

        $prewarm = function_exists('eottae_translation_auto_prewarm_enabled') && eottae_translation_auto_prewarm_enabled();
        $items[] = array(
            'id' => 'translation_prewarm',
            'label' => '자동 선번역',
            'ok' => $prewarm,
            'detail' => $prewarm ? 'translation_auto_prewarm 활성' : '비활성 — secrets 또는 환경변수 확인',
        );

        $cache_table = function_exists('eottae_translation_table') ? eottae_translation_table() : '';
        $cache_exists = $cache_table !== '' && sql_fetch(" show tables like '".sql_escape_string($cache_table)."' ", false);
        $items[] = array(
            'id' => 'translation_cache_table',
            'label' => '번역 캐시 테이블',
            'ok' => !empty($cache_exists),
            'detail' => !empty($cache_exists) ? $cache_table : 'post_translations 테이블 없음 — 글 조회·관리자 접속 시 생성',
        );

        $job_table = function_exists('eottae_translation_job_table') ? eottae_translation_job_table() : 'post_translation_jobs';
        $job_exists = sql_fetch(" show tables like '".sql_escape_string($job_table)."' ", false);
        $items[] = array(
            'id' => 'translation_job_table',
            'label' => '번역 작업 큐',
            'ok' => !empty($job_exists),
            'detail' => !empty($job_exists) ? $job_table : 'post_translation_jobs 테이블 없음',
        );

        $cron_path = defined('G5_PATH') ? G5_PATH.'/cron/sebu_translation_queue.php' : '';
        $items[] = array(
            'id' => 'translation_cron',
            'label' => '번역 cron 스크립트',
            'ok' => $cron_path !== '' && is_file($cron_path),
            'detail' => $cron_path !== '' && is_file($cron_path)
                ? 'cron/sebu_translation_queue.php'
                : 'cron 스크립트 없음',
        );

        $lang_seo = function_exists('eottae_lang_seo_enabled') && eottae_lang_seo_enabled();
        $items[] = array(
            'id' => 'lang_seo',
            'label' => '다국어 SEO URL',
            'ok' => $lang_seo,
            'detail' => $lang_seo
                ? '/en/, /ja/, /zh/ prefix 활성'
                : 'lang_seo_enabled 비활성 — secrets 또는 LANG_SEO_ENABLED=1',
        );

        $auto_route = function_exists('eottae_lang_seo_auto_route_enabled') && eottae_lang_seo_auto_route_enabled();
        $items[] = array(
            'id' => 'lang_seo_auto_route',
            'label' => '첫 방문 언어 자동 이동',
            'ok' => !$lang_seo || $auto_route,
            'detail' => !$lang_seo
                ? 'SEO 비활성 — 해당 없음'
                : ($auto_route ? 'lang_seo_auto_route 활성' : '비활성'),
        );

        $rewrite_hint = '관리자 > 환경설정 > DB업그레이드 실행 후 rewrite 규칙 반영';
        $items[] = array(
            'id' => 'lang_seo_rewrite',
            'label' => 'rewrite 규칙',
            'ok' => !$lang_seo,
            'detail' => $lang_seo ? $rewrite_hint : 'SEO 비활성 — 해당 없음',
            'warn' => $lang_seo,
        );

        $cron_key = function_exists('eottae_translation_cron_key') ? eottae_translation_cron_key() : '';
        $items[] = array(
            'id' => 'translation_cron_key',
            'label' => '번역 웹크론 키',
            'ok' => $cron_key !== '',
            'detail' => $cron_key !== ''
                ? 'translation_cron_key 설정됨'
                : 'translation_cron_key 또는 talkroom_ai_cron_key 필요',
        );

        $tick_cfg = function_exists('eottae_translation_traffic_tick_config')
            ? eottae_translation_traffic_tick_config()
            : array('enabled' => false);
        $items[] = array(
            'id' => 'translation_traffic_tick',
            'label' => '방문 트리거 큐 처리',
            'ok' => !empty($tick_cfg['enabled']),
            'detail' => !empty($tick_cfg['enabled'])
                ? '활성 — 약 '.(int) ($tick_cfg['interval'] ?? 90).'초 간격, '.(int) ($tick_cfg['limit'] ?? 2).'건/회'
                : '비활성 — translation_traffic_tick_enabled 또는 prewarm 확인',
        );

        return $items;
    }
}

if (!function_exists('eottae_multilingual_ops_render_panel')) {
    function eottae_multilingual_ops_render_panel()
    {
        $items = eottae_multilingual_ops_status();
        $all_ok = true;
        foreach ($items as $item) {
            if (empty($item['ok']) && empty($item['warn'])) {
                $all_ok = false;
                break;
            }
        }

        ob_start();
        ?>
        <div class="local_desc01 local_desc eottae-multilingual-ops">
            <p>
                <strong>다국어 운영 점검</strong>
                <?php if ($all_ok) { ?>
                — 필수 항목이 준비되었습니다.
                <?php } else { ?>
                — 아래 항목을 확인한 뒤 운영을 시작하세요.
                <?php } ?>
            </p>
            <ul style="margin:10px 0 0;padding-left:18px;line-height:1.7">
                <?php foreach ($items as $item) {
                    $is_warn = !empty($item['warn']) && empty($item['ok']);
                    $mark = !empty($item['ok']) ? '✓' : ($is_warn ? '!' : '✗');
                    $color = !empty($item['ok']) ? '#198754' : ($is_warn ? '#cc8800' : '#c0392b');
                    ?>
                <li style="color:<?php echo $color; ?>">
                    <strong><?php echo get_text($item['label']); ?></strong>
                    (<?php echo $mark; ?>)
                    — <?php echo get_text($item['detail']); ?>
                </li>
                <?php } ?>
            </ul>
            <?php
            $web_cron = function_exists('eottae_translation_web_cron_urls') ? eottae_translation_web_cron_urls() : array();
            if (!empty($web_cron)) { ?>
            <div style="margin-top:12px">
                <p><strong>일반 호스팅 — 외부 웹크론 URL</strong> (cron-job.org 등에서 5~10분 간격 GET)</p>
                <?php if (!empty($web_cron['queue'])) { ?>
                <p style="margin:6px 0 0">큐 일괄 처리:<br><code style="word-break:break-all"><?php echo get_text($web_cron['queue']); ?></code></p>
                <?php } ?>
                <?php if (!empty($web_cron['traffic_tick'])) { ?>
                <p style="margin:6px 0 0">방문 트리거(경량):<br><code style="word-break:break-all"><?php echo get_text($web_cron['traffic_tick']); ?></code></p>
                <?php } ?>
                <p style="margin:8px 0 0;color:#666">키는 <code>translation_cron_key</code> 또는 <code>talkroom_ai_cron_key</code> 입니다.</p>
            </div>
            <?php } ?>
            <p style="margin-top:10px;color:#666">
                방문 트리거: 사이트 GET 요청 시 shutdown 훅에서 대기 큐를 소량 처리합니다(관리자·AJAX·proc 제외).
            </p>
        </div>
        <?php

        return (string) ob_get_clean();
    }
}
