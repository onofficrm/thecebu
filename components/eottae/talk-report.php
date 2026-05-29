<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('eottae_talkroom_render_report_modal')) {
    function eottae_talkroom_render_report_modal()
    {
        static $rendered = false;
        if ($rendered) {
            return;
        }
        $rendered = true;

        $reasons = eottae_talkroom_report_reasons();
        ?>
        <div class="talk-report-modal" id="talkReportModal" hidden>
            <div class="talk-report-modal__backdrop" data-talk-report-close></div>
            <div class="talk-report-modal__panel" role="dialog" aria-labelledby="talkReportModalTitle">
                <h3 class="talk-report-modal__title" id="talkReportModalTitle">신고하기</h3>
                <form id="talkReportForm" class="talk-report-form">
                    <input type="hidden" name="room_id" id="talk_report_room_id" value="">
                    <input type="hidden" name="target_type" id="talk_report_target_type" value="">
                    <input type="hidden" name="target_id" id="talk_report_target_id" value="">
                    <fieldset class="talk-report-form__reasons">
                        <legend>신고 사유</legend>
                        <?php foreach ($reasons as $code => $label) { ?>
                        <label class="talk-report-form__radio">
                            <input type="radio" name="reason" value="<?php echo get_text($code); ?>"<?php echo $code === 'ad_spam' ? ' checked' : ''; ?>>
                            <span><?php echo $label; ?></span>
                        </label>
                        <?php } ?>
                    </fieldset>
                    <div class="talk-report-form__memo" id="talkReportMemoWrap" hidden>
                        <label for="talk_report_memo">기타 메모</label>
                        <textarea id="talk_report_memo" name="memo" class="talk-apply-form__textarea" rows="3" maxlength="500" placeholder="신고 내용을 입력해 주세요."></textarea>
                    </div>
                    <div class="talk-report-modal__actions">
                        <button type="button" class="talk-page__btn" data-talk-report-close>취소</button>
                        <button type="submit" class="talk-page__btn talk-page__btn--primary">신고 접수</button>
                    </div>
                </form>
            </div>
        </div>
        <?php
    }
}

if (!function_exists('eottae_talkroom_render_post_report_button')) {
    function eottae_talkroom_render_post_report_button($board, $write, $member, $is_admin)
    {
        if (empty($board['bo_table']) || !eottae_talkroom_is_talkroom_board($board['bo_table'])) {
            return;
        }
        if (!is_array($write) || empty($write['wr_id']) || !empty($write['wr_is_comment'])) {
            return;
        }

        $btn = eottae_talkroom_report_button_attrs($board, $write, $member, $is_admin, 'post', (int) $write['wr_id']);
        if (empty($btn)) {
            return;
        }

        eottae_talkroom_render_report_modal();
        ?>
        <div class="talk-report-actions talk-report-actions--post">
            <button type="button" class="talk-report-btn"<?php echo $btn['attrs']; ?>>신고</button>
        </div>
        <?php
        eottae_talkroom_render_report_script();
    }
}

if (!function_exists('eottae_talkroom_render_comment_report_assets')) {
    function eottae_talkroom_render_comment_report_assets($list, $board, $parent_write, $member, $is_admin)
    {
        if (empty($board['bo_table']) || !eottae_talkroom_is_talkroom_board($board['bo_table'])) {
            return;
        }
        if (!is_array($list) || empty($list)) {
            return;
        }

        $targets = array();
        foreach ($list as $row) {
            if (!is_array($row) || empty($row['wr_id'])) {
                continue;
            }
            $btn = eottae_talkroom_report_button_attrs($board, $row, $member, $is_admin, 'comment', (int) $row['wr_id'], $parent_write);
            if (!empty($btn)) {
                $targets[] = $btn['data'];
            }
        }

        if (empty($targets)) {
            return;
        }

        eottae_talkroom_render_report_modal();
        eottae_talkroom_render_report_script($targets);
    }
}

if (!function_exists('eottae_talkroom_report_button_attrs')) {
    function eottae_talkroom_report_button_attrs($board, $write, $member, $is_admin, $target_type, $target_id, $parent_write = null)
    {
        $mb_id = is_array($member) ? ($member['mb_id'] ?? '') : '';
        $check = eottae_talkroom_can_submit_report($board, $write, $mb_id, $target_type, $target_id, $parent_write, $is_admin === 'super');
        if (empty($check['ok'])) {
            return array();
        }

        $room_id = (int) ($check['room_id'] ?? 0);
        $data = array(
            'room_id'     => $room_id,
            'target_type' => $target_type,
            'target_id'   => (int) $target_id,
            'comment_id'  => $target_type === 'comment' ? (int) $target_id : 0,
        );

        $attrs = ' data-room-id="'.(int) $data['room_id'].'"'
            .' data-target-type="'.get_text($data['target_type']).'"'
            .' data-target-id="'.(int) $data['target_id'].'"';

        return array(
            'attrs' => $attrs,
            'data'  => array(
                'room_id'     => $room_id,
                'target_type' => $target_type,
                'target_id'   => (int) $target_id,
                'comment_id'  => $target_type === 'comment' ? (int) $target_id : 0,
            ),
        );
    }
}

if (!function_exists('eottae_talkroom_render_report_script')) {
    function eottae_talkroom_render_report_script($comment_targets = array())
    {
        static $rendered = false;
        if ($rendered) {
            return;
        }
        $rendered = true;

        $token = eottae_talkroom_report_token();
        ?>
        <script>
        (function () {
          var reportToken = <?php echo json_encode((string) $token, JSON_UNESCAPED_UNICODE); ?>;
          var commentTargets = <?php echo json_encode(array_values($comment_targets), JSON_UNESCAPED_UNICODE); ?>;
          var modal = document.getElementById('talkReportModal');
          var form = document.getElementById('talkReportForm');
          var memoWrap = document.getElementById('talkReportMemoWrap');

          function openReportModal(payload) {
            if (!modal || !form) return;
            document.getElementById('talk_report_room_id').value = String(payload.roomId || '');
            document.getElementById('talk_report_target_type').value = payload.targetType || '';
            document.getElementById('talk_report_target_id').value = String(payload.targetId || '');
            var etc = form.querySelector('input[name="reason"][value="etc"]');
            if (etc && etc.checked) memoWrap.hidden = false;
            else if (memoWrap) memoWrap.hidden = true;
            modal.hidden = false;
          }

          function closeReportModal() {
            if (modal) modal.hidden = true;
          }

          document.querySelectorAll('[data-talk-report-close]').forEach(function (el) {
            el.addEventListener('click', closeReportModal);
          });

          document.querySelectorAll('.talk-report-btn[data-room-id]').forEach(function (btn) {
            btn.addEventListener('click', function () {
              openReportModal({
                roomId: btn.getAttribute('data-room-id'),
                targetType: btn.getAttribute('data-target-type'),
                targetId: btn.getAttribute('data-target-id')
              });
            });
          });

          commentTargets.forEach(function (item) {
            var commentId = item.comment_id || item.commentId || item.target_id;
            var host = document.getElementById('c_' + commentId);
            if (!host) return;
            var wrap = host.querySelector('.bo_vl_opt') || host.querySelector('.cm_wrap') || host;
            var btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'talk-report-btn talk-report-btn--inline';
            btn.textContent = '신고';
            btn.setAttribute('data-room-id', String(item.room_id || item.roomId));
            btn.setAttribute('data-target-type', item.target_type || item.targetType);
            btn.setAttribute('data-target-id', String(item.target_id || item.targetId));
            btn.addEventListener('click', function () {
              openReportModal(item);
            });
            var actions = wrap.querySelector('.board-view__comment-actions') || wrap.querySelector('.bo_vc_act');
            if (actions) {
              if (actions.tagName === 'UL') {
                var li = document.createElement('li');
                li.appendChild(btn);
                actions.appendChild(li);
              } else {
                actions.appendChild(btn);
              }
            } else if (wrap.querySelector('.bo_vl_opt')) {
              var ul = wrap.querySelector('.bo_vc_act');
              if (ul) {
                var li = document.createElement('li');
                li.appendChild(btn);
                ul.appendChild(li);
              } else {
                wrap.appendChild(btn);
              }
            } else {
              var bar = document.createElement('div');
              bar.className = 'talk-report-actions talk-report-actions--comment';
              bar.appendChild(btn);
              wrap.appendChild(bar);
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
              fd.append('eottae_talkroom_report_token', reportToken);
              fetch('/proc/eottae-talkroom-report.php', { method: 'POST', body: fd, credentials: 'same-origin' })
                .then(function (r) { return r.json(); })
                .then(function (data) {
                  alert(data.message || (data.success ? '신고가 접수되었습니다.' : '처리에 실패했습니다.'));
                  if (data.success) {
                    closeReportModal();
                    document.querySelectorAll('.talk-report-btn').forEach(function (btn) {
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

if (!function_exists('eottae_talkroom_render_report_handle_script')) {
    function eottae_talkroom_render_report_handle_script($token, $is_admin = false)
    {
        $endpoint = $is_admin ? '/proc/eottae-talkroom-admin.php' : '/proc/eottae-talkroom-owner.php';
        $token_key = $is_admin ? 'eottae_talkroom_admin_token' : 'eottae_talkroom_owner_token';
        ?>
        <script>
        (function () {
          var token = <?php echo json_encode((string) $token, JSON_UNESCAPED_UNICODE); ?>;
          var endpoint = <?php echo json_encode($endpoint, JSON_UNESCAPED_UNICODE); ?>;
          var tokenKey = <?php echo json_encode($token_key, JSON_UNESCAPED_UNICODE); ?>;

          function postReportHandle(action, reportId, roomId, extra) {
            var fd = new FormData();
            fd.append('action', action);
            fd.append('report_id', String(reportId));
            fd.append('room_id', String(roomId));
            fd.append(tokenKey, token);
            if (extra) {
              Object.keys(extra).forEach(function (key) { fd.append(key, extra[key]); });
            }
            return fetch(endpoint, { method: 'POST', body: fd, credentials: 'same-origin' })
              .then(function (r) { return r.json(); });
          }

          document.querySelectorAll('[data-talk-report-review]').forEach(function (btn) {
            btn.addEventListener('click', function () {
              if (!confirm('이 신고를 확인 처리하시겠습니까?')) return;
              btn.disabled = true;
              postReportHandle('report_review', btn.getAttribute('data-talk-report-review'), btn.getAttribute('data-talk-report-room'))
                .then(function (data) {
                  if (data.success) location.reload();
                  else alert(data.message || '처리에 실패했습니다.');
                  btn.disabled = false;
                });
            });
          });

          document.querySelectorAll('[data-talk-report-dismiss]').forEach(function (btn) {
            btn.addEventListener('click', function () {
              if (!confirm('이 신고를 기각하시겠습니까?')) return;
              btn.disabled = true;
              postReportHandle('report_dismiss', btn.getAttribute('data-talk-report-dismiss'), btn.getAttribute('data-talk-report-room'))
                .then(function (data) {
                  if (data.success) location.reload();
                  else alert(data.message || '처리에 실패했습니다.');
                  btn.disabled = false;
                });
            });
          });

          document.querySelectorAll('[data-talk-report-delete]').forEach(function (btn) {
            btn.addEventListener('click', function () {
              if (!confirm('신고 대상 글/댓글을 삭제 처리하시겠습니까?')) return;
              btn.disabled = true;
              postReportHandle('report_delete_content', btn.getAttribute('data-talk-report-delete'), btn.getAttribute('data-talk-report-room'))
                .then(function (data) {
                  if (data.success) location.reload();
                  else alert(data.message || '처리에 실패했습니다.');
                  btn.disabled = false;
                });
            });
          });

          document.querySelectorAll('[data-talk-report-kick]').forEach(function (btn) {
            btn.addEventListener('click', function () {
              var reason = window.prompt('강퇴 사유를 입력해 주세요.', '신고 처리에 따른 강퇴');
              if (reason === null) return;
              reason = reason.trim();
              if (reason === '') { alert('강퇴 사유를 입력해 주세요.'); return; }
              var canRejoin = window.confirm('재참여를 허용하시겠습니까?\n\n확인 = 허용, 취소 = 불허');
              if (!confirm('해당 회원을 강퇴하시겠습니까?')) return;
              btn.disabled = true;
              postReportHandle('report_kick_member', btn.getAttribute('data-talk-report-kick'), btn.getAttribute('data-talk-report-room'), {
                kicked_reason: reason,
                can_rejoin: canRejoin ? '1' : '0'
              }).then(function (data) {
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
