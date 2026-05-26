<?php
if (!defined('_GNUBOARD_')) exit;

add_stylesheet('<link rel="stylesheet" href="'.$member_skin_url.'/style.css">', 0);
?>

<div class="auth-layout">
    <div class="auth-layout__card password-find-form">
        <h1 class="auth-layout__title">비밀번호 찾기</h1>
        <p class="auth-layout__sub">가입 시 등록한 정보로 비밀번호를 재설정할 수 있습니다</p>

        <form name="fpasswordlost" action="<?php echo $action_url ?>" onsubmit="return fpasswordlost_submit(this);" method="post" autocomplete="off">
        <input type="hidden" name="token" value="<?php echo $token ?>">

        <div class="eottae-field">
            <label for="mb_email">E-mail</label>
            <input type="email" name="mb_email" id="mb_email" required class="frm_input" placeholder="가입 이메일">
        </div>
        <div class="eottae-field">
            <label for="mb_id">아이디</label>
            <input type="text" name="mb_id" id="mb_id" required class="frm_input" placeholder="아이디">
        </div>

        <?php echo captcha_html(); ?>

        <button type="submit" class="btn_submit">인증메일 보내기</button>
        </form>

        <div class="auth-layout__links">
            <a href="<?php echo G5_BBS_URL ?>/login.php">로그인으로 돌아가기</a>
            <a href="<?php echo G5_BBS_URL ?>/register.php">회원가입</a>
        </div>
    </div>
</div>

<script>
function fpasswordlost_submit(f) {
    <?php echo chk_captcha_js(); ?>
    return true;
}
</script>
