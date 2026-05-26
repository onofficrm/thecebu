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
