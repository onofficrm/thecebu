<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (function_exists('eottae_talkroom_is_admin_shell_request') && eottae_talkroom_is_admin_shell_request()) {
    eottae_talkroom_admin_page_assets();
}

if (!function_exists('eottae_public_ai_render_admin_nav')) {
    function eottae_public_ai_render_admin_nav($active = 'settings')
    {
        include_once G5_LIB_PATH.'/eottae-public-ai.lib.php';
        $pending = eottae_public_ai_pending_count();
        ?>
        <nav class="talk-admin-nav public-ai-admin-nav" aria-label="공개톡 AI 관리">
            <a href="<?php echo eottae_public_ai_admin_settings_url(); ?>" class="talk-admin-nav__item<?php echo $active === 'settings' ? ' is-active' : ''; ?>">AI 기본 설정</a>
            <a href="<?php echo eottae_public_ai_admin_candidates_url('pending'); ?>" class="talk-admin-nav__item<?php echo $active === 'candidates' ? ' is-active' : ''; ?>">
                AI 후보 메시지<?php if ($pending > 0) { ?> (<?php echo number_format($pending); ?>)<?php } ?>
            </a>
            <a href="<?php echo G5_URL; ?>/page/eottae-admin-public-ai-weather.php" class="talk-admin-nav__item<?php echo $active === 'weather' ? ' is-active' : ''; ?>">날씨 데이터</a>
            <a href="<?php echo G5_URL; ?>/page/eottae-admin-public-ai-news.php" class="talk-admin-nav__item<?php echo $active === 'news' ? ' is-active' : ''; ?>">외부뉴스</a>
            <a href="<?php echo eottae_public_ai_admin_logs_url(); ?>" class="talk-admin-nav__item<?php echo $active === 'logs' ? ' is-active' : ''; ?>">AI 발행 로그</a>
        </nav>
        <?php
    }
}

if (!function_exists('eottae_public_ai_render_mypage_admin_section')) {
    /**
     * 마이페이지 — 세부공개단톡방 AI(어때봇) 관리 바로가기
     */
    function eottae_public_ai_render_mypage_admin_section()
    {
        global $is_admin;

        if ($is_admin !== 'super') {
            return;
        }

        if (!function_exists('eottae_public_ai_get_settings')) {
            include_once G5_LIB_PATH.'/eottae-public-ai.lib.php';
        }
        eottae_public_ai_ensure_schema();

        $settings = eottae_public_ai_get_settings();
        $pending = eottae_public_ai_pending_count();
        $stats = function_exists('eottae_public_ai_mypage_dashboard_stats')
            ? eottae_public_ai_mypage_dashboard_stats()
            : array();
        $ai_on = !empty($settings['ai_enabled']);
        $approval = !empty($settings['require_admin_approval']);
        $openai_on = !empty($settings['openai_enabled']);
        $ai_name = trim((string) ($settings['ai_name'] ?? ''));
        if ($ai_name === '') {
            $ai_name = '어때봇';
        }
        $published_today = (int) ($stats['published_today'] ?? 0);
        $max_per_day = (int) ($stats['max_messages_per_day'] ?? 10);
        $openai_success = (int) ($stats['openai_calls_success'] ?? 0);
        $openai_total = (int) ($stats['openai_calls_total'] ?? 0);
        $openai_max = (int) ($stats['openai_max_calls_per_day'] ?? 20);
        $last_gen = '';
        if (!empty($stats['last_candidate_at'])) {
            $last_gen = function_exists('eottae_public_ai_format_dashboard_datetime')
                ? eottae_public_ai_format_dashboard_datetime($stats['last_candidate_at'])
                : substr((string) $stats['last_candidate_at'], 0, 16);
        } elseif (!empty($stats['last_openai_at'])) {
            $last_gen = function_exists('eottae_public_ai_format_dashboard_datetime')
                ? eottae_public_ai_format_dashboard_datetime($stats['last_openai_at'])
                : substr((string) $stats['last_openai_at'], 0, 16);
        }
        ?>
        <section class="my-talk-section my-talk-section--panel my-public-ai-admin" id="sebu-public-ai-admin" aria-labelledby="sebu-public-ai-admin-title">
            <h2 class="my-talk-section__title" id="sebu-public-ai-admin-title">세부공개단톡방 AI</h2>
            <p class="my-talk-section__desc">홈 <strong>세부공개단체톡</strong> 분위기 메이커(<?php echo get_text($ai_name); ?>) 설정·후보 승인·발행 로그를 관리합니다.</p>

            <dl class="my-public-ai-admin__metrics" aria-label="오늘 운영 지표">
                <div class="my-public-ai-admin__metric">
                    <dt>오늘 발행</dt>
                    <dd><strong><?php echo number_format($published_today); ?></strong><span class="my-public-ai-admin__metric-sub">/ <?php echo number_format($max_per_day); ?>건</span></dd>
                </div>
                <div class="my-public-ai-admin__metric">
                    <dt>OpenAI 호출</dt>
                    <dd>
                        <?php if ($openai_on) { ?>
                        <strong><?php echo number_format($openai_success); ?></strong><span class="my-public-ai-admin__metric-sub">성공 · 총 <?php echo number_format($openai_total); ?> / 한도 <?php echo number_format($openai_max); ?></span>
                        <?php } else { ?>
                        <span class="my-public-ai-admin__metric-muted">OFF</span>
                        <?php } ?>
                    </dd>
                </div>
                <div class="my-public-ai-admin__metric">
                    <dt>마지막 생성</dt>
                    <dd><strong><?php echo $last_gen !== '' ? get_text($last_gen) : '—'; ?></strong></dd>
                </div>
            </dl>

            <?php if (function_exists('eottae_public_ai_render_slot_schedule_status')) {
                eottae_public_ai_render_slot_schedule_status('mypage');
            } ?>

            <ul class="my-public-ai-admin__status" aria-label="AI 운영 상태">
                <li class="my-public-ai-admin__status-item<?php echo $ai_on ? ' is-on' : ' is-off'; ?>">
                    AI <?php echo $ai_on ? '사용 중' : '꺼짐'; ?>
                </li>
                <li class="my-public-ai-admin__status-item">
                    발행 <?php echo $approval ? '승인 후' : '자동 가능'; ?>
                </li>
                <li class="my-public-ai-admin__status-item<?php echo $openai_on ? ' is-on' : ''; ?>">
                    OpenAI <?php echo $openai_on ? 'ON' : 'OFF'; ?>
                </li>
            <?php if (!empty($stats['traffic_tick_enabled'])) { ?>
                <li class="my-public-ai-admin__status-item is-on">방문 트리거 ON</li>
            <?php } ?>
            <?php if (!empty($stats['web_cron_urls']['traffic_tick'])) { ?>
            <li class="my-public-ai-admin__status-item">
                <a href="<?php echo htmlspecialchars($stats['web_cron_urls']['traffic_tick'], ENT_QUOTES, 'UTF-8'); ?>" class="my-public-ai-admin__web-cron" target="_blank" rel="noopener noreferrer">웹크론 URL</a>
            </li>
            <?php } ?>
                <?php if ($pending > 0) { ?>
                <li class="my-public-ai-admin__status-item is-pending">승인 대기 <?php echo number_format($pending); ?>건</li>
                <?php } ?>
            </ul>

            <div class="my-talk-super-admin__links my-public-ai-admin__links">
                <a href="<?php echo eottae_public_ai_admin_settings_url(); ?>" class="my-talk-btn my-talk-btn--primary my-talk-btn--sm">AI 세부 설정</a>
                <a href="<?php echo eottae_public_ai_admin_candidates_url('pending'); ?>" class="my-talk-btn my-talk-btn--ghost my-talk-btn--sm">
                    후보 메시지<?php if ($pending > 0) { ?> (<?php echo number_format($pending); ?>)<?php } ?>
                </a>
                <a href="<?php echo G5_URL; ?>/page/eottae-admin-public-ai-weather.php" class="my-talk-btn my-talk-btn--ghost my-talk-btn--sm">날씨 데이터</a>
                <a href="<?php echo G5_URL; ?>/page/eottae-admin-public-ai-news.php" class="my-talk-btn my-talk-btn--ghost my-talk-btn--sm">외부뉴스</a>
                <a href="<?php echo eottae_public_ai_admin_logs_url(); ?>" class="my-talk-btn my-talk-btn--ghost my-talk-btn--sm">발행 로그</a>
            </div>
        </section>
        <?php
    }
}

if (!function_exists('eottae_public_ai_render_admin_dashboard_stats')) {
    /**
     * 공개톡 AI 관리 페이지 상단 운영 지표
     */
    function eottae_public_ai_render_admin_dashboard_stats()
    {
        include_once G5_LIB_PATH.'/eottae-public-ai.lib.php';
        eottae_public_ai_ensure_schema();

        $settings = eottae_public_ai_get_settings();
        $pending = eottae_public_ai_pending_count();
        $stats = function_exists('eottae_public_ai_mypage_dashboard_stats')
            ? eottae_public_ai_mypage_dashboard_stats()
            : array();

        $ai_on = !empty($settings['ai_enabled']);
        $approval = !empty($settings['require_admin_approval']);
        $openai_on = !empty($settings['openai_enabled']);
        $published_today = (int) ($stats['published_today'] ?? 0);
        $max_per_day = (int) ($stats['max_messages_per_day'] ?? 10);
        $openai_success = (int) ($stats['openai_calls_success'] ?? 0);
        $openai_total = (int) ($stats['openai_calls_total'] ?? 0);
        $openai_max = (int) ($stats['openai_max_calls_per_day'] ?? 20);
        $last_gen = '';
        if (!empty($stats['last_candidate_at'])) {
            $last_gen = function_exists('eottae_public_ai_format_dashboard_datetime')
                ? eottae_public_ai_format_dashboard_datetime($stats['last_candidate_at'])
                : substr((string) $stats['last_candidate_at'], 0, 16);
        } elseif (!empty($stats['last_openai_at'])) {
            $last_gen = function_exists('eottae_public_ai_format_dashboard_datetime')
                ? eottae_public_ai_format_dashboard_datetime($stats['last_openai_at'])
                : substr((string) $stats['last_openai_at'], 0, 16);
        }
        ?>
        <section class="public-ai-admin-dashboard" aria-label="오늘 운영 지표">
            <dl class="public-ai-admin-dashboard__metrics">
                <div class="public-ai-admin-dashboard__metric">
                    <dt>오늘 발행</dt>
                    <dd><strong><?php echo number_format($published_today); ?></strong><span class="public-ai-admin-dashboard__sub">/ <?php echo number_format($max_per_day); ?>건</span></dd>
                </div>
                <div class="public-ai-admin-dashboard__metric">
                    <dt>OpenAI 호출</dt>
                    <dd>
                        <?php if ($openai_on) { ?>
                        <strong><?php echo number_format($openai_success); ?></strong><span class="public-ai-admin-dashboard__sub">성공 · 총 <?php echo number_format($openai_total); ?> / 한도 <?php echo number_format($openai_max); ?></span>
                        <?php } else { ?>
                        <span class="public-ai-admin-dashboard__muted">OFF</span>
                        <?php } ?>
                    </dd>
                </div>
                <div class="public-ai-admin-dashboard__metric">
                    <dt>마지막 생성</dt>
                    <dd><strong><?php echo $last_gen !== '' ? get_text($last_gen) : '—'; ?></strong></dd>
                </div>
                <div class="public-ai-admin-dashboard__metric">
                    <dt>승인 대기 후보</dt>
                    <dd><strong><?php echo number_format($pending); ?></strong><span class="public-ai-admin-dashboard__sub">건</span></dd>
                </div>
            </dl>
            <?php if (function_exists('eottae_public_ai_render_slot_schedule_status')) {
                eottae_public_ai_render_slot_schedule_status('admin');
            } ?>
            <ul class="public-ai-admin-dashboard__status" aria-label="AI 운영 상태">
                <li class="public-ai-admin-dashboard__status-item<?php echo $ai_on ? ' is-on' : ' is-off'; ?>">AI <?php echo $ai_on ? '사용 중' : '꺼짐'; ?></li>
                <li class="public-ai-admin-dashboard__status-item">발행 <?php echo $approval ? '승인 후' : '자동 가능'; ?></li>
                <li class="public-ai-admin-dashboard__status-item<?php echo $openai_on ? ' is-on' : ''; ?>">OpenAI <?php echo $openai_on ? 'ON' : 'OFF'; ?></li>
            </ul>
        </section>
        <?php
    }
}

if (!function_exists('eottae_public_ai_render_admin_page_mypage_back')) {
    function eottae_public_ai_render_admin_page_mypage_back()
    {
        if (!function_exists('eottae_public_ai_mypage_admin_url')) {
            include_once G5_LIB_PATH.'/eottae-public-ai.lib.php';
        }
        ?>
        <a href="<?php echo eottae_public_ai_mypage_admin_url(); ?>" class="promo-admin-page__back">← 마이페이지</a>
        <?php
    }
}

if (!function_exists('eottae_public_ai_render_admin_actions_script')) {
    function eottae_public_ai_render_admin_actions_script($admin_token)
    {
        ?>
        <script>
        (function () {
          var adminToken = <?php echo json_encode((string) $admin_token, JSON_UNESCAPED_UNICODE); ?>;

          function postPublicAiAdmin(action, fields) {
            var fd = new FormData();
            fd.append('action', action);
            fd.append('eottae_public_ai_admin_token', adminToken);
            if (fields) {
              Object.keys(fields).forEach(function (key) {
                fd.append(key, fields[key]);
              });
            }
            return fetch('/proc/eottae-public-ai-admin.php', {
              method: 'POST',
              body: fd,
              credentials: 'same-origin'
            }).then(function (r) { return r.json(); });
          }

          window.eottaePublicAiAdminPost = postPublicAiAdmin;
        }());
        </script>
        <?php
    }
}
