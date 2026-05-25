<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('eottae_social_auth_return_url')) {
    function eottae_social_auth_return_url($urlencode = '')
    {
        if ($urlencode !== '') {
            return (string) $urlencode;
        }

        global $login_url, $url;

        if (isset($login_url) && $login_url !== '') {
            return (string) $login_url;
        }
        if (isset($url) && $url !== '') {
            return (string) $url;
        }

        return '';
    }
}

if (!function_exists('eottae_social_auth_self_url')) {
    function eottae_social_auth_self_url()
    {
        $self_url = G5_BBS_URL.'/login.php';
        if (defined('G5_SOCIAL_USE_POPUP') && G5_SOCIAL_USE_POPUP && defined('G5_SOCIAL_LOGIN_URL')) {
            $self_url = G5_SOCIAL_LOGIN_URL.'/popup.php';
        }

        return $self_url;
    }
}

if (!function_exists('eottae_google_auth_icon_svg')) {
    function eottae_google_auth_icon_svg()
    {
        return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48" width="20" height="20" aria-hidden="true" focusable="false">'
            .'<path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"/>'
            .'<path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.56 2.95-2.24 5.44-4.78 7.11l7.73 6c4.51-4.16 7.09-10.27 7.09-17.65z"/>'
            .'<path fill="#FBBC05" d="M10.53 28.35c-.48-1.45-.76-2.99-.76-4.58s.27-3.13.76-4.58l-7.98-6.19C1.98 16.08 0 19.92 0 24c0 4.08 1.98 7.92 5.55 10.79l7.98-6.44z"/>'
            .'<path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.15 1.45-4.92 2.28-8.16 2.28-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z"/>'
            .'</svg>';
    }
}

if (!function_exists('eottae_render_social_auth')) {
    /**
     * @param string $mode login|register
     */
    function eottae_render_social_auth($mode = 'login', $urlencode = '')
    {
        global $config;

        if (empty($config['cf_social_login_use']) || !function_exists('social_service_check')) {
            return '';
        }

        $return_url = eottae_social_auth_return_url($urlencode);
        $url_param = $urlencode !== '' ? (string) $urlencode : urlencode($return_url);
        $self_url = eottae_social_auth_self_url();
        $providers = array(
            'google' => array(
                'login' => 'Google 계정으로 로그인',
                'register' => 'Google 계정으로 가입',
                'class' => 'eottae-google-auth',
            ),
            'naver' => array(
                'login' => '네이버로 로그인',
                'register' => '네이버로 회원가입',
                'class' => 'eottae-social-auth__btn eottae-social-auth__btn--naver',
            ),
            'kakao' => array(
                'login' => '카카오로 로그인',
                'register' => '카카오로 회원가입',
                'class' => 'eottae-social-auth__btn eottae-social-auth__btn--kakao',
            ),
        );

        $items = array();
        foreach ($providers as $provider => $meta) {
            if (!social_service_check($provider)) {
                continue;
            }
            $label = $mode === 'register' ? $meta['register'] : $meta['login'];
            $href = $self_url.'?provider='.$provider.'&amp;url='.$url_param;
            $items[] = array(
                'provider' => $provider,
                'href' => $href,
                'label' => $label,
                'class' => $meta['class'],
            );
        }

        if (empty($items)) {
            return '';
        }

        static $popup_script_added = false;

        ob_start();
        ?>
        <div class="eottae-social-auth" data-eottae-social-auth>
            <?php foreach ($items as $item) { ?>
            <a href="<?php echo $item['href']; ?>"
               class="<?php echo $item['class']; ?> social_link"
               title="<?php echo get_text($item['label']); ?>">
                <?php if ($item['provider'] === 'google') { ?>
                <span class="eottae-google-auth__icon"><?php echo eottae_google_auth_icon_svg(); ?></span>
                <?php } ?>
                <span class="eottae-google-auth__label"><?php echo get_text($item['label']); ?></span>
            </a>
            <?php } ?>
        </div>
        <?php
        if (!$popup_script_added && defined('G5_SOCIAL_USE_POPUP') && G5_SOCIAL_USE_POPUP) {
            $popup_script_added = true;
            ?>
        <script>
        (function () {
          var root = document.querySelector('[data-eottae-social-auth]');
          if (!root) return;
          root.addEventListener('click', function (e) {
            var link = e.target.closest('a.social_link');
            if (!link) return;
            e.preventDefault();
            var popUrl = link.getAttribute('href');
            var newWin = window.open(popUrl, 'social_sing_on', 'location=0,status=0,scrollbars=1,width=600,height=500');
            if (!newWin || newWin.closed || typeof newWin.closed === 'undefined') {
              alert('브라우저에서 팝업이 차단되어 있습니다. 팝업 활성화 후 다시 시도해 주세요.');
            }
          });
        })();
        </script>
            <?php
        }

        return ob_get_clean();
    }
}
