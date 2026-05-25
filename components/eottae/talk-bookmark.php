<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('eottae_talkroom_render_post_bookmark_button')) {
    function eottae_talkroom_render_post_bookmark_button($board, $write, $member, $is_admin)
    {
        if (empty($board['bo_table']) || !function_exists('eottae_talkroom_is_talkroom_board')
            || !eottae_talkroom_is_talkroom_board($board['bo_table'])) {
            return;
        }
        if (!is_array($write) || empty($write['wr_id']) || !empty($write['wr_is_comment'])) {
            return;
        }
        if (empty($member['mb_id'])) {
            return;
        }

        if (!function_exists('eottae_talkroom_bookmark_can_access_post')) {
            include_once G5_LIB_PATH.'/eottae-talkroom-bookmarks.lib.php';
        }

        $room_id = function_exists('eottae_talkroom_get_write_room_id')
            ? eottae_talkroom_get_write_room_id($write)
            : (int) ($write['wr_1'] ?? 0);
        $post_id = (int) $write['wr_id'];
        if ($room_id < 1 || $post_id < 1) {
            return;
        }

        $is_super = ($is_admin === 'super');
        $access = eottae_talkroom_bookmark_can_access_post($member['mb_id'], $room_id, $post_id, $is_super);
        if (empty($access['ok'])) {
            return;
        }

        $saved = eottae_talkroom_bookmark_is_saved($member['mb_id'], $room_id, $post_id);
        $token = eottae_talkroom_member_token();
        $proc_url = eottae_talkroom_bookmarks_proc_url();
        ?>
        <div class="talk-bookmark-actions talk-bookmark-actions--post">
            <button type="button"
                class="talk-bookmark-btn<?php echo $saved ? ' talk-bookmark-btn--saved' : ''; ?>"
                data-talk-bookmark
                data-room-id="<?php echo (int) $room_id; ?>"
                data-post-id="<?php echo (int) $post_id; ?>"
                data-saved="<?php echo $saved ? '1' : '0'; ?>"
                aria-pressed="<?php echo $saved ? 'true' : 'false'; ?>">
                <?php echo $saved ? '저장됨' : '저장하기'; ?>
            </button>
        </div>
        <script>
        (function () {
            var btn = document.querySelector('.talk-bookmark-actions--post [data-talk-bookmark]');
            if (!btn || btn.dataset.talkBookmarkBound === '1') {
                return;
            }
            btn.dataset.talkBookmarkBound = '1';
            var token = <?php echo json_encode((string) $token, JSON_UNESCAPED_UNICODE); ?>;
            var procUrl = <?php echo json_encode((string) $proc_url, JSON_UNESCAPED_UNICODE); ?>;

            btn.addEventListener('click', function () {
                var saved = btn.getAttribute('data-saved') === '1';
                var fd = new FormData();
                fd.append('action', saved ? 'remove' : 'add');
                fd.append('room_id', btn.getAttribute('data-room-id') || '');
                fd.append('post_id', btn.getAttribute('data-post-id') || '');
                fd.append('eottae_talkroom_member_token', token);

                btn.disabled = true;
                fetch(procUrl, { method: 'POST', body: fd, credentials: 'same-origin' })
                    .then(function (r) { return r.json(); })
                    .then(function (data) {
                        if (!data.success) {
                            alert(data.message || '처리에 실패했습니다.');
                            btn.disabled = false;
                            return;
                        }
                        var isSaved = !!data.saved;
                        btn.setAttribute('data-saved', isSaved ? '1' : '0');
                        btn.setAttribute('aria-pressed', isSaved ? 'true' : 'false');
                        btn.textContent = isSaved ? '저장됨' : '저장하기';
                        btn.classList.toggle('talk-bookmark-btn--saved', isSaved);
                        btn.disabled = false;
                    })
                    .catch(function () {
                        alert('네트워크 오류가 발생했습니다.');
                        btn.disabled = false;
                    });
            });
        })();
        </script>
        <?php
    }
}
