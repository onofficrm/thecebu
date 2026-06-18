<?php
if (!defined('_GNUBOARD_')) exit;

add_stylesheet('<link rel="stylesheet" href="'.$member_skin_url.'/style.css">', 0);

$eottae_auth_logo_url = function_exists('eottae_site_logo_url')
    ? eottae_site_logo_url('logo_path')
    : (function_exists('g5site_cfg_url') ? g5site_cfg_url('logo_path', '') : '');
$eottae_auth_site_title = isset($g5_site_title) ? get_text($g5_site_title) : '세부어때';
?>

<div class="auth-layout">
    <div class="auth-layout__card login-form">
        <h1 class="auth-layout__brand">
            <a href="<?php echo G5_URL; ?>/" class="auth-layout__logo">
                <?php if ($eottae_auth_logo_url !== '') { ?>
                <img src="<?php echo $eottae_auth_logo_url; ?>" alt="<?php echo $eottae_auth_site_title; ?>" class="auth-layout__logo-img">
                <?php } else { ?>
                <span class="auth-layout__title"><?php echo $eottae_auth_site_title; ?></span>
                <?php } ?>
            </a>
        </h1>
        <p class="auth-layout__sub">로그인하고 세부 생활 정보를 만나보세요</p>

        <form name="flogin" action="<?php echo $login_action_url ?>" onsubmit="return flogin_submit(this);" method="post">
        <input type="hidden" name="url" value="<?php echo $login_url ?>">

        <label for="login_id" class="sound_only">아이디</label>
        <input type="text" name="mb_id" id="login_id" required class="frm_input" placeholder="아이디">

        <label for="login_pw" class="sound_only">비밀번호</label>
        <input type="password" name="mb_password" id="login_pw" required class="frm_input" placeholder="비밀번호">

        <div class="login_if_auto chk_box" style="margin:12px 0">
            <input type="checkbox" name="auto_login" id="login_auto_login" class="selec_chk" data-app-auto-login>
            <label for="login_auto_login"><span></span> 자동로그인</label>
            <p class="auth-layout__hint" data-app-auto-login-hint style="display:none;margin:6px 0 0;font-size:12px;color:#64748b">앱에서는 로그인 상태를 유지하기 위해 자동로그인이 기본 적용됩니다.</p>
        </div>

        <button type="submit" class="btn_submit">로그인</button>
        </form>

        <?php if (function_exists('get_social_skin_path') && !empty($config['cf_social_login_use'])) { ?>
        <div class="auth-layout__divider" aria-hidden="true"><span>또는</span></div>
        <?php
        eottae_load_component('social-auth');
        echo eottae_render_social_auth('login', isset($urlencode) ? $urlencode : '');
        ?>
        <?php } ?>

        <div class="auth-layout__links">
            <a href="<?php echo G5_BBS_URL ?>/register.php">회원가입</a>
            <a href="<?php echo G5_BBS_URL ?>/password_lost.php">비밀번호 찾기</a>
            <a href="<?php echo G5_BBS_URL ?>/password_lost.php">아이디 찾기</a>
        </div>
    </div>
</div>

<script>
function eottae_is_app_context() {
    return !!(
        (window.matchMedia && window.matchMedia('(display-mode: standalone)').matches) ||
        window.navigator.standalone ||
        (document.referrer && document.referrer.indexOf('android-app://') === 0)
    );
}

function eottae_apply_app_auto_login() {
    var checkbox = document.querySelector('[data-app-auto-login]');
    if (!checkbox || !eottae_is_app_context()) {
        return;
    }

    checkbox.checked = true;
    checkbox.setAttribute('aria-describedby', 'app-auto-login-hint');

    var hint = document.querySelector('[data-app-auto-login-hint]');
    if (hint) {
        hint.id = 'app-auto-login-hint';
        hint.style.display = 'block';
    }
}

eottae_apply_app_auto_login();

function flogin_submit(f) {
    eottae_apply_app_auto_login();
    return true;
}
</script>
