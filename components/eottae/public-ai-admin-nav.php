<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (function_exists('eottae_talkroom_admin_page_assets')) {
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
        $ai_on = !empty($settings['ai_enabled']);
        $approval = !empty($settings['require_admin_approval']);
        $openai_on = !empty($settings['openai_enabled']);
        $ai_name = trim((string) ($settings['ai_name'] ?? ''));
        if ($ai_name === '') {
            $ai_name = '어때봇';
        }
        ?>
        <section class="my-talk-section my-talk-section--panel my-public-ai-admin" id="sebu-public-ai-admin" aria-labelledby="sebu-public-ai-admin-title">
            <h2 class="my-talk-section__title" id="sebu-public-ai-admin-title">세부공개단톡방 AI</h2>
            <p class="my-talk-section__desc">홈 <strong>세부공개단체톡</strong> 분위기 메이커(<?php echo get_text($ai_name); ?>) 설정·후보 승인·발행 로그를 관리합니다.</p>

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
