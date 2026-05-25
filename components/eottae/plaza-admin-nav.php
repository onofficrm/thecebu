<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('eottae_plaza_render_admin_nav')) {
    function eottae_plaza_render_admin_nav($active = 'posts')
    {
        include_once G5_LIB_PATH.'/eottae-plaza-report.lib.php';
        $report_pending = function_exists('eottae_plaza_admin_pending_report_count')
            ? eottae_plaza_admin_pending_report_count()
            : 0;
        ?>
        <nav class="talk-admin-nav plaza-admin-nav" aria-label="세부광장 관리">
            <a href="<?php echo eottae_plaza_admin_posts_url(); ?>" class="talk-admin-nav__item<?php echo $active === 'posts' ? ' is-active' : ''; ?>">글 관리</a>
            <a href="<?php echo eottae_plaza_admin_reports_url('pending'); ?>" class="talk-admin-nav__item<?php echo $active === 'reports' ? ' is-active' : ''; ?>">
                신고 관리<?php if ($report_pending > 0) { ?> (<?php echo number_format($report_pending); ?>)<?php } ?>
            </a>
            <a href="<?php echo function_exists('eottae_plaza_ai_admin_url') ? eottae_plaza_ai_admin_url() : G5_URL.'/page/eottae-admin-plaza-ai.php'; ?>" class="talk-admin-nav__item<?php echo $active === 'ai' ? ' is-active' : ''; ?>">AI 설정</a>
        </nav>
        <?php
    }
}

if (!function_exists('eottae_plaza_render_admin_actions_script')) {
    function eottae_plaza_render_admin_actions_script($admin_token)
    {
        ?>
        <script>
        (function () {
          var adminToken = <?php echo json_encode((string) $admin_token, JSON_UNESCAPED_UNICODE); ?>;

          function postPlazaAdminAction(action, fields) {
            var fd = new FormData();
            fd.append('action', action);
            fd.append('eottae_plaza_admin_token', adminToken);
            if (fields) {
              Object.keys(fields).forEach(function (key) { fd.append(key, fields[key]); });
            }
            return fetch('/proc/eottae-plaza-admin.php', { method: 'POST', body: fd, credentials: 'same-origin' })
              .then(function (r) { return r.json(); });
          }

          document.querySelectorAll('[data-plaza-hide-post]').forEach(function (btn) {
            btn.addEventListener('click', function () {
              if (!confirm('이 글을 삭제 처리하시겠습니까?')) return;
              btn.disabled = true;
              postPlazaAdminAction('hide_post', { wr_id: btn.getAttribute('data-plaza-hide-post') })
                .then(function (data) {
                  if (data.success) location.reload();
                  else alert(data.message || '처리에 실패했습니다.');
                  btn.disabled = false;
                })
                .catch(function () {
                  alert('네트워크 오류가 발생했습니다.');
                  btn.disabled = false;
                });
            });
          });
        })();
        </script>
        <?php
    }
}

if (!function_exists('eottae_plaza_render_report_handle_script')) {
    function eottae_plaza_render_report_handle_script($admin_token)
    {
        ?>
        <script>
        (function () {
          var adminToken = <?php echo json_encode((string) $admin_token, JSON_UNESCAPED_UNICODE); ?>;

          function postPlazaReportHandle(action, reportId) {
            var fd = new FormData();
            fd.append('action', action);
            fd.append('report_id', String(reportId));
            fd.append('eottae_plaza_admin_token', adminToken);
            return fetch('/proc/eottae-plaza-admin.php', { method: 'POST', body: fd, credentials: 'same-origin' })
              .then(function (r) { return r.json(); });
          }

          document.querySelectorAll('[data-plaza-report-review]').forEach(function (btn) {
            btn.addEventListener('click', function () {
              if (!confirm('이 신고를 확인 처리하시겠습니까?')) return;
              btn.disabled = true;
              postPlazaReportHandle('report_review', btn.getAttribute('data-plaza-report-review'))
                .then(function (data) {
                  if (data.success) location.reload();
                  else alert(data.message || '처리에 실패했습니다.');
                  btn.disabled = false;
                });
            });
          });

          document.querySelectorAll('[data-plaza-report-dismiss]').forEach(function (btn) {
            btn.addEventListener('click', function () {
              if (!confirm('이 신고를 기각하시겠습니까?')) return;
              btn.disabled = true;
              postPlazaReportHandle('report_dismiss', btn.getAttribute('data-plaza-report-dismiss'))
                .then(function (data) {
                  if (data.success) location.reload();
                  else alert(data.message || '처리에 실패했습니다.');
                  btn.disabled = false;
                });
            });
          });

          document.querySelectorAll('[data-plaza-report-delete]').forEach(function (btn) {
            btn.addEventListener('click', function () {
              if (!confirm('신고 대상 글/댓글을 삭제 처리하시겠습니까?')) return;
              btn.disabled = true;
              postPlazaReportHandle('report_delete_content', btn.getAttribute('data-plaza-report-delete'))
                .then(function (data) {
                  if (data.success) location.reload();
                  else alert(data.message || '처리에 실패했습니다.');
                  btn.disabled = false;
                });
            });
          });
        })();
        </script>
        <?php
    }
}
