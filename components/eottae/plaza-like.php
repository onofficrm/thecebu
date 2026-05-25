<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('eottae_plaza_render_like_button')) {
    function eottae_plaza_render_like_button($wr_id, $count, $liked, $can_toggle, $login_url = '')
    {
        $wr_id = (int) $wr_id;
        $count = (int) $count;
        $liked = !empty($liked);
        $classes = 'plaza-like-btn'.($liked ? ' is-liked' : '').($can_toggle ? '' : ' is-disabled');
        ?>
        <button type="button"
            class="<?php echo $classes; ?>"
            data-plaza-like="<?php echo $wr_id; ?>"
            data-login-url="<?php echo htmlspecialchars((string) $login_url, ENT_QUOTES, 'UTF-8'); ?>"
            aria-pressed="<?php echo $liked ? 'true' : 'false'; ?>"
            <?php if (!$can_toggle && $login_url === '') { ?>disabled<?php } ?>>
            <span class="plaza-like-btn__icon" aria-hidden="true"><?php echo $liked ? '♥' : '♡'; ?></span>
            <span class="plaza-like-btn__count"><?php echo number_format($count); ?></span>
        </button>
        <?php
    }
}

if (!function_exists('eottae_plaza_render_like_script')) {
    function eottae_plaza_render_like_script($member_token, $login_url)
    {
        static $rendered = false;
        if ($rendered) {
            return;
        }
        $rendered = true;
        ?>
        <script>
        (function () {
          var likeToken = <?php echo json_encode((string) $member_token, JSON_UNESCAPED_UNICODE); ?>;
          var loginUrl = <?php echo json_encode((string) $login_url, JSON_UNESCAPED_UNICODE); ?>;

          document.querySelectorAll('[data-plaza-like]').forEach(function (btn) {
            btn.addEventListener('click', function (e) {
              e.preventDefault();
              e.stopPropagation();
              if (btn.classList.contains('is-disabled') && loginUrl) {
                if (confirm('로그인 후 공감할 수 있습니다. 로그인 페이지로 이동할까요?')) {
                  location.href = loginUrl;
                }
                return;
              }
              if (btn.disabled) return;
              btn.disabled = true;
              var fd = new FormData();
              fd.append('wr_id', btn.getAttribute('data-plaza-like'));
              fd.append('eottae_plaza_member_token', likeToken);
              fetch('/proc/eottae-plaza-likes.php', { method: 'POST', body: fd, credentials: 'same-origin' })
                .then(function (r) { return r.json(); })
                .then(function (data) {
                  if (!data.success) {
                    alert(data.message || '처리에 실패했습니다.');
                    btn.disabled = false;
                    return;
                  }
                  var liked = !!data.liked;
                  btn.classList.toggle('is-liked', liked);
                  btn.setAttribute('aria-pressed', liked ? 'true' : 'false');
                  var icon = btn.querySelector('.plaza-like-btn__icon');
                  if (icon) icon.textContent = liked ? '♥' : '♡';
                  var countEl = btn.querySelector('.plaza-like-btn__count');
                  if (countEl && typeof data.count !== 'undefined') {
                    countEl.textContent = String(data.count);
                  }
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
