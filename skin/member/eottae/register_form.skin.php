<?php
if (!defined('_GNUBOARD_')) exit;

add_stylesheet('<link rel="stylesheet" href="'.$member_skin_url.'/style.css">', 0);
add_javascript('<script src="'.G5_JS_URL.'/jquery.register_form.js"></script>', 0);
?>

<div class="auth-layout">
    <div class="auth-layout__card register-form">
        <h1 class="auth-layout__title">회원정보 입력</h1>
        <p class="auth-layout__sub">세부어때 서비스 이용을 위한 정보를 입력해 주세요</p>

        <form id="fregisterform" name="fregisterform" action="<?php echo $register_action_url ?>" onsubmit="return fregisterform_submit(this);" method="post" enctype="multipart/form-data" autocomplete="off">
        <input type="hidden" name="w" value="<?php echo $w ?>">
        <input type="hidden" name="url" value="<?php echo $urlencode ?>">
        <input type="hidden" name="agree" value="<?php echo $agree ?>">
        <input type="hidden" name="agree2" value="<?php echo $agree2 ?>">
        <input type="hidden" name="mb_1" id="reg_mb_1" value="member">

        <div class="eottae-field">
            <label for="reg_mb_id">아이디 (필수)</label>
            <input type="text" name="mb_id" id="reg_mb_id" <?php echo $required ?> class="frm_input" minlength="3" maxlength="20" placeholder="아이디">
            <span id="msg_mb_id"></span>
        </div>
        <div class="eottae-field">
            <label for="reg_mb_password">비밀번호 (필수)</label>
            <input type="password" name="mb_password" id="reg_mb_password" <?php echo $required ?> class="frm_input" minlength="3" maxlength="20" placeholder="비밀번호">
        </div>
        <div class="eottae-field">
            <label for="reg_mb_password_re">비밀번호 확인 (필수)</label>
            <input type="password" name="mb_password_re" id="reg_mb_password_re" <?php echo $required ?> class="frm_input" placeholder="비밀번호 확인">
        </div>
        <div class="eottae-field">
            <label for="reg_mb_name">이름 (필수)</label>
            <input type="text" name="mb_name" id="reg_mb_name" <?php echo $required ?> class="frm_input" placeholder="이름">
        </div>
        <div class="eottae-field">
            <label for="reg_mb_nick">닉네임 (필수)</label>
            <input type="text" name="mb_nick" id="reg_mb_nick" <?php echo $required ?> class="frm_input" placeholder="닉네임">
            <span id="msg_mb_nick"></span>
        </div>
        <div class="eottae-field">
            <label for="reg_mb_email">E-mail (필수)</label>
            <input type="email" name="mb_email" id="reg_mb_email" <?php echo $required ?> class="frm_input" placeholder="email@example.com">
        </div>
        <div class="eottae-field">
            <label for="reg_mb_hp">휴대폰</label>
            <input type="tel" name="mb_hp" id="reg_mb_hp" class="frm_input" placeholder="010-0000-0000">
        </div>

        <div class="auth-member-type" style="margin-top:16px">
            <label>
                <input type="radio" name="eottae_member_type" value="member" checked>
                <span>일반회원</span>
            </label>
            <label>
                <input type="radio" name="eottae_member_type" value="business">
                <span>사업자회원</span>
            </label>
        </div>

        <?php if ($is_use_captcha) { echo $captcha_html; } ?>

        <button type="submit" class="btn_submit" id="btn_submit">가입하기</button>
        </form>
    </div>
</div>

<script>
function fregisterform_submit(f) {
    <?php echo $captcha_js; ?>
    if (typeof f.mb_password !== 'undefined' && f.mb_password.value !== f.mb_password_re.value) {
        alert('비밀번호가 일치하지 않습니다.');
        return false;
    }
    return true;
}
</script>
