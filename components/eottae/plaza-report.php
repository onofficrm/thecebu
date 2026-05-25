<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('eottae_plaza_render_report_modal')) {
    function eottae_plaza_render_report_modal()
    {
        static $rendered = false;
        if ($rendered) {
            return;
        }
        $rendered = true;

        include_once G5_LIB_PATH.'/eottae-plaza-report.lib.php';
        $reasons = eottae_plaza_report_reasons();
        ?>
        <div class="plaza-report-modal" id="plazaReportModal" hidden>
            <div class="plaza-report-modal__backdrop" data-plaza-report-close></div>
            <div class="plaza-report-modal__panel" role="dialog" aria-labelledby="plazaReportModalTitle">
                <h3 class="plaza-report-modal__title" id="plazaReportModalTitle">신고하기</h3>
                <form id="plazaReportForm" class="plaza-report-form">
                    <input type="hidden" name="target_type" id="plaza_report_target_type" value="">
                    <input type="hidden" name="target_id" id="plaza_report_target_id" value="">
                    <fieldset class="plaza-report-form__reasons">
                        <legend>신고 사유</legend>
                        <?php foreach ($reasons as $code => $label) { ?>
                        <label class="plaza-report-form__radio">
                            <input type="radio" name="reason" value="<?php echo get_text($code); ?>"<?php echo $code === 'ad_spam' ? ' checked' : ''; ?>>
                            <span><?php echo $label; ?></span>
                        </label>
                        <?php } ?>
                    </fieldset>
                    <div class="plaza-report-form__memo" id="plazaReportMemoWrap" hidden>
                        <label for="plaza_report_memo">기타 메모</label>
                        <textarea id="plaza_report_memo" name="memo" class="plaza-comment-form__textarea" rows="3" maxlength="500" placeholder="신고 내용을 입력해 주세요."></textarea>
                    </div>
                    <div class="plaza-report-modal__actions">
                        <button type="button" class="plaza-btn plaza-btn--ghost" data-plaza-report-close>취소</button>
                        <button type="submit" class="plaza-btn plaza-btn--primary">신고 접수</button>
                    </div>
                </form>
            </div>
        </div>
        <?php
    }
}

if (!function_exists('eottae_plaza_report_button_attrs')) {
    function eottae_plaza_report_button_attrs($write, $member, $is_admin, $target_type, $target_id)
    {
        include_once G5_LIB_PATH.'/eottae-plaza-report.lib.php';
        $mb_id = is_array($member) ? ($member['mb_id'] ?? '') : '';
        $check = eottae_plaza_can_submit_report($target_type, $target_id, $mb_id, $is_admin === 'super');
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

if (!function_exists('eottae_plaza_render_post_report_button')) {
    function eottae_plaza_render_post_report_button($write, $member, $is_admin)
    {
        if (!is_array($write) || empty($write['wr_id']) || !empty($write['wr_is_comment'])) {
            return;
        }

        $btn = eottae_plaza_report_button_attrs($write, $member, $is_admin, 'post', (int) $write['wr_id']);
        if (empty($btn)) {
            return;
        }

        eottae_plaza_render_report_modal();
        ?>
        <button type="button" class="plaza-report-btn"<?php echo $btn['attrs']; ?>>신고</button>
        <?php
        eottae_plaza_render_report_script();
    }
}

if (!function_exists('eottae_plaza_render_comment_report_assets')) {
    function eottae_plaza_render_comment_report_assets($list, $parent_write, $member, $is_admin)
    {
        if (!is_array($list) || empty($list)) {
            return;
        }

        $targets = array();
        foreach ($list as $row) {
            if (!is_array($row) || empty($row['wr_id'])) {
                continue;
            }
            if (function_exists('eottae_plaza_is_comment_visible') && !eottae_plaza_is_comment_visible($row, $is_admin === 'super')) {
                continue;
            }
            $btn = eottae_plaza_report_button_attrs($row, $member, $is_admin, 'comment', (int) $row['wr_id']);
            if (!empty($btn)) {
                $targets[] = $btn['data'];
            }
        }

        if (empty($targets)) {
            return;
        }

        eottae_plaza_render_report_modal();
        eottae_plaza_render_report_script($targets);
    }
}

if (!function_exists('eottae_plaza_render_report_script')) {
    function eottae_plaza_render_report_script($comment_targets = array())
    {
        static $rendered = false;
        if ($rendered) {
            return;
        }
        $rendered = true;

        include_once G5_LIB_PATH.'/eottae-plaza-report.lib.php';
        $token = eottae_plaza_report_token();
        ?>
        <script>
        (function () {
          var reportToken = <?php echo json_encode((string) $token, JSON_UNESCAPED_UNICODE); ?>;
          var commentTargets = <?php echo json_encode(array_values($comment_targets), JSON_UNESCAPED_UNICODE); ?>;
          var modal = document.getElementById('plazaReportModal');
          var form = document.getElementById('plazaReportForm');
          var memoWrap = document.getElementById('plazaReportMemoWrap');

          function openReportModal(payload) {
            if (!modal || !form) return;
            document.getElementById('plaza_report_target_type').value = payload.targetType || '';
            document.getElementById('plaza_report_target_id').value = String(payload.targetId || '');
            if (memoWrap) memoWrap.hidden = !(form.querySelector('input[name="reason"][value="etc"]:checked'));
            modal.hidden = false;
          }

          function closeReportModal() {
            if (modal) modal.hidden = true;
          }

          document.querySelectorAll('[data-plaza-report-close]').forEach(function (el) {
            el.addEventListener('click', closeReportModal);
          });

          document.querySelectorAll('.plaza-report-btn[data-target-id]').forEach(function (btn) {
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
            btn.className = 'plaza-report-btn plaza-report-btn--inline';
            btn.textContent = '신고';
            btn.setAttribute('data-target-type', item.target_type);
            btn.setAttribute('data-target-id', String(item.target_id));
            btn.addEventListener('click', function () {
              openReportModal({ targetType: item.target_type, targetId: item.target_id });
            });
            var actions = host.querySelector('.plaza-comment__actions');
            if (actions) {
              actions.appendChild(btn);
            } else {
              var bar = document.createElement('div');
              bar.className = 'plaza-comment__actions';
              bar.appendChild(btn);
              host.appendChild(bar);
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
              fd.append('eottae_plaza_report_token', reportToken);
              fetch('/proc/eottae-plaza-report.php', { method: 'POST', body: fd, credentials: 'same-origin' })
                .then(function (r) { return r.json(); })
                .then(function (data) {
                  alert(data.message || (data.success ? '신고가 접수되었습니다.' : '처리에 실패했습니다.'));
                  if (data.success) {
                    closeReportModal();
                    document.querySelectorAll('.plaza-report-btn').forEach(function (btn) {
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
