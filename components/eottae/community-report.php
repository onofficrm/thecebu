<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('eottae_community_render_report_modal')) {
    function eottae_community_render_report_modal()
    {
        static $rendered = false;
        if ($rendered) {
            return;
        }
        $rendered = true;

        include_once G5_LIB_PATH.'/eottae-community-report.lib.php';
        $reasons = eottae_community_report_reasons();
        ?>
        <div class="community-report-modal" id="communityReportModal" hidden>
            <div class="community-report-modal__backdrop" data-community-report-close></div>
            <div class="community-report-modal__panel" role="dialog" aria-labelledby="communityReportModalTitle">
                <h3 class="community-report-modal__title" id="communityReportModalTitle">신고하기</h3>
                <form id="communityReportForm" class="community-report-form">
                    <input type="hidden" name="target_type" id="community_report_target_type" value="">
                    <input type="hidden" name="target_id" id="community_report_target_id" value="">
                    <fieldset class="community-report-form__reasons">
                        <legend>신고 사유</legend>
                        <?php foreach ($reasons as $code => $label) { ?>
                        <label class="community-report-form__radio">
                            <input type="radio" name="reason" value="<?php echo get_text($code); ?>"<?php echo $code === 'ad_spam' ? ' checked' : ''; ?>>
                            <span><?php echo $label; ?></span>
                        </label>
                        <?php } ?>
                    </fieldset>
                    <div class="community-report-form__memo" id="communityReportMemoWrap" hidden>
                        <label for="community_report_memo">기타 메모</label>
                        <textarea id="community_report_memo" name="memo" class="community-report-form__textarea" rows="3" maxlength="500" placeholder="신고 내용을 입력해 주세요."></textarea>
                    </div>
                    <div class="community-report-modal__actions">
                        <button type="button" class="btn_b01 btn" data-community-report-close>취소</button>
                        <button type="submit" class="btn_b01 btn">신고 접수</button>
                    </div>
                </form>
            </div>
        </div>
        <?php
    }
}

if (!function_exists('eottae_community_report_button_attrs')) {
    function eottae_community_report_button_attrs($write, $member, $is_admin, $target_type, $target_id)
    {
        include_once G5_LIB_PATH.'/eottae-community-report.lib.php';
        $mb_id = is_array($member) ? ($member['mb_id'] ?? '') : '';
        $check = eottae_community_can_submit_report($target_type, $target_id, $mb_id, $is_admin === 'super');
        if (empty($check['ok'])) {
            return array();
        }

        return array(
            'attrs' => ' data-target-type="'.get_text($target_type).'"'
                .' data-target-id="'.(int) $target_id.'"',
            'data'  => array(
                'target_type' => $target_type,
                'target_id'   => (int) $target_id,
            ),
        );
    }
}

if (!function_exists('eottae_community_render_post_report_button')) {
    function eottae_community_render_post_report_button($write, $member, $is_admin)
    {
        if (!is_array($write) || empty($write['wr_id']) || !empty($write['wr_is_comment'])) {
            return;
        }

        $btn = eottae_community_report_button_attrs($write, $member, $is_admin, 'post', (int) $write['wr_id']);
        if (empty($btn)) {
            return;
        }

        eottae_community_render_report_modal();
        ?>
        <button type="button" class="community-report-btn"<?php echo $btn['attrs']; ?>>신고</button>
        <?php
        eottae_community_render_report_script();
    }
}

if (!function_exists('eottae_community_render_comment_report_assets')) {
    function eottae_community_render_comment_report_assets($list, $parent_write, $member, $is_admin)
    {
        if (!is_array($list) || empty($list)) {
            return;
        }

        $targets = array();
        foreach ($list as $row) {
            if (!is_array($row) || empty($row['wr_id'])) {
                continue;
            }
            if (function_exists('eottae_community_is_write_visible') && !eottae_community_is_write_visible($row, $is_admin === 'super')) {
                continue;
            }
            $btn = eottae_community_report_button_attrs($row, $member, $is_admin, 'comment', (int) $row['wr_id']);
            if (!empty($btn)) {
                $targets[] = $btn['data'];
            }
        }

        if (empty($targets)) {
            return;
        }

        eottae_community_render_report_modal();
        eottae_community_render_report_script($targets);
    }
}

if (!function_exists('eottae_community_render_report_script')) {
    function eottae_community_render_report_script($comment_targets = array())
    {
        static $rendered = false;
        if ($rendered) {
            return;
        }
        $rendered = true;

        include_once G5_LIB_PATH.'/eottae-community-report.lib.php';
        $token = eottae_community_report_token();
        ?>
        <script>
        (function () {
          var reportToken = <?php echo json_encode((string) $token, JSON_UNESCAPED_UNICODE); ?>;
          var commentTargets = <?php echo json_encode(array_values($comment_targets), JSON_UNESCAPED_UNICODE); ?>;
          var modal = document.getElementById('communityReportModal');
          var form = document.getElementById('communityReportForm');
          var memoWrap = document.getElementById('communityReportMemoWrap');

          function openReportModal(payload) {
            if (!modal || !form) return;
            document.getElementById('community_report_target_type').value = payload.targetType || '';
            document.getElementById('community_report_target_id').value = String(payload.targetId || '');
            if (memoWrap) memoWrap.hidden = !(form.querySelector('input[name="reason"][value="etc"]:checked'));
            modal.hidden = false;
          }

          function closeReportModal() {
            if (modal) modal.hidden = true;
          }

          document.querySelectorAll('[data-community-report-close]').forEach(function (el) {
            el.addEventListener('click', closeReportModal);
          });

          document.querySelectorAll('.community-report-btn[data-target-id]').forEach(function (btn) {
            btn.addEventListener('click', function () {
              openReportModal({
                targetType: btn.getAttribute('data-target-type'),
                targetId: btn.getAttribute('data-target-id')
              });
            });
          });

          commentTargets.forEach(function (item) {
            var commentId = item.target_id;
            var host = document.getElementById('c_' + commentId);
            if (!host) return;
            var btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'community-report-btn community-report-btn--inline';
            btn.textContent = '신고';
            btn.setAttribute('data-target-type', item.target_type);
            btn.setAttribute('data-target-id', String(item.target_id));
            btn.addEventListener('click', function () {
              openReportModal({ targetType: item.target_type, targetId: item.target_id });
            });
            var actions = host.querySelector('.bo_vc_act');
            if (actions) {
              var li = document.createElement('li');
              li.appendChild(btn);
              actions.appendChild(li);
            }
          });

          if (form) {
            form.querySelectorAll('input[name="reason"]').forEach(function (radio) {
              radio.addEventListener('change', function () {
                if (!memoWrap) return;
                memoWrap.hidden = radio.value !== 'etc';
              });
            });
            form.addEventListener('submit', function (e) {
              e.preventDefault();
              var fd = new FormData(form);
              fd.append('eottae_community_report_token', reportToken);
              fetch('/proc/eottae-community-report.php', { method: 'POST', body: fd, credentials: 'same-origin' })
                .then(function (r) { return r.json(); })
                .then(function (data) {
                  alert(data.message || (data.success ? '신고가 접수되었습니다.' : '처리에 실패했습니다.'));
                  if (data.success) {
                    closeReportModal();
                    document.querySelectorAll('.community-report-btn').forEach(function (btn) {
                      if (btn.getAttribute('data-target-type') === fd.get('target_type')
                        && btn.getAttribute('data-target-id') === fd.get('target_id')) {
                        btn.disabled = true;
                        btn.textContent = '신고됨';
                      }
                    });
                  }
                })
                .catch(function () { alert('네트워크 오류가 발생했습니다.'); });
            });
          }
        })();
        </script>
        <?php
    }
}

if (!function_exists('eottae_community_render_report_handle_script')) {
    function eottae_community_render_report_handle_script($admin_token)
    {
        ?>
        <script>
        (function () {
          var adminToken = <?php echo json_encode((string) $admin_token, JSON_UNESCAPED_UNICODE); ?>;

          function postCommunityReportHandle(action, reportId) {
            var fd = new FormData();
            fd.append('action', action);
            fd.append('report_id', String(reportId));
            fd.append('eottae_community_admin_token', adminToken);
            return fetch('/proc/eottae-community-admin.php', { method: 'POST', body: fd, credentials: 'same-origin' })
              .then(function (r) { return r.json(); });
          }

          document.querySelectorAll('[data-community-report-review]').forEach(function (btn) {
            btn.addEventListener('click', function () {
              if (!confirm('이 신고를 확인 처리하시겠습니까?')) return;
              btn.disabled = true;
              postCommunityReportHandle('report_review', btn.getAttribute('data-community-report-review'))
                .then(function (data) {
                  if (data.success) location.reload();
                  else alert(data.message || '처리에 실패했습니다.');
                  btn.disabled = false;
                });
            });
          });

          document.querySelectorAll('[data-community-report-dismiss]').forEach(function (btn) {
            btn.addEventListener('click', function () {
              if (!confirm('이 신고를 기각하시겠습니까?')) return;
              btn.disabled = true;
              postCommunityReportHandle('report_dismiss', btn.getAttribute('data-community-report-dismiss'))
                .then(function (data) {
                  if (data.success) location.reload();
                  else alert(data.message || '처리에 실패했습니다.');
                  btn.disabled = false;
                });
            });
          });

          document.querySelectorAll('[data-community-report-delete]').forEach(function (btn) {
            btn.addEventListener('click', function () {
              if (!confirm('신고 대상 글/댓글을 삭제 처리하시겠습니까?')) return;
              btn.disabled = true;
              postCommunityReportHandle('report_delete_content', btn.getAttribute('data-community-report-delete'))
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
