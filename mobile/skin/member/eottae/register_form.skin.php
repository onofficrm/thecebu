<?php
if (!defined('_GNUBOARD_')) exit;

add_stylesheet('<link rel="stylesheet" href="'.$member_skin_url.'/style.css">', 0);
add_javascript('<script src="'.G5_JS_URL.'/jquery.register_form.js"></script>', 0);

if (!isset($is_use_captcha)) {
    $is_use_captcha = ($w === '') ? 1 : 0;
}
if (!isset($captcha_html)) {
    $captcha_html = $is_use_captcha ? captcha_html() : '';
}
if (!isset($captcha_js)) {
    $captcha_js = $is_use_captcha ? chk_captcha_js() : '';
}

$member_audience = function_exists('eottae_member_audience_type')
    ? eottae_member_audience_type($member)
    : (isset($member['mb_2']) ? trim((string) $member['mb_2']) : '');
$member_role = (isset($member['mb_1']) && $member['mb_1'] === 'business') ? 'business' : 'member';
?>

<div class="auth-layout">
    <div class="auth-layout__card register-form">
        <h1 class="auth-layout__title"><?php echo $w === 'u' ? '회원정보 수정' : '회원정보 입력'; ?></h1>
        <p class="auth-layout__sub">세부어때 서비스 이용을 위한 정보를 입력해 주세요</p>

        <form id="fregisterform" name="fregisterform" action="<?php echo $register_action_url ?>" onsubmit="return fregisterform_submit(this);" method="post" enctype="multipart/form-data" autocomplete="off">
        <input type="hidden" name="w" value="<?php echo $w ?>">
        <input type="hidden" name="url" value="<?php echo $urlencode ?>">
        <input type="hidden" name="agree" value="<?php echo $agree ?>">
        <input type="hidden" name="agree2" value="<?php echo $agree2 ?>">

        <?php
        if (function_exists('eottae_render_member_type_fields')) {
            echo eottae_render_member_type_fields(array(
                'audience' => $member_audience,
                'role'     => $member_role,
            ));
        }
        ?>

        <div class="eottae-field">
            <label for="reg_mb_id">아이디 (필수)</label>
            <input type="text" name="mb_id" id="reg_mb_id" <?php echo $required ?> class="frm_input" minlength="3" maxlength="20" placeholder="아이디" value="<?php echo isset($member['mb_id']) ? get_text($member['mb_id']) : ''; ?>"<?php echo $w === 'u' ? ' readonly' : ''; ?>>
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
            <input type="text" name="mb_name" id="reg_mb_name" <?php echo $required ?> class="frm_input" placeholder="이름" value="<?php echo isset($member['mb_name']) ? get_text($member['mb_name']) : ''; ?>">
        </div>
        <div class="eottae-field">
            <label for="reg_mb_nick">닉네임 (필수)</label>
            <input type="text" name="mb_nick" id="reg_mb_nick" <?php echo $required ?> class="frm_input" placeholder="닉네임" value="<?php echo isset($member['mb_nick']) ? get_text($member['mb_nick']) : ''; ?>">
            <span id="msg_mb_nick"></span>
        </div>
        <div class="eottae-field">
            <label for="reg_mb_email">E-mail (필수)</label>
            <input type="email" name="mb_email" id="reg_mb_email" <?php echo $required ?> class="frm_input" placeholder="email@example.com" value="<?php echo isset($member['mb_email']) ? get_text($member['mb_email']) : ''; ?>">
        </div>
        <div class="eottae-field">
            <label for="reg_mb_hp">휴대폰</label>
            <input type="tel" name="mb_hp" id="reg_mb_hp" class="frm_input" placeholder="010-0000-0000" value="<?php echo isset($member['mb_hp']) ? get_text($member['mb_hp']) : ''; ?>">
        </div>

        <?php if ($is_use_captcha) { echo $captcha_html; } ?>

        <button type="submit" class="btn_submit" id="btn_submit"><?php echo $w === 'u' ? '정보 수정' : '가입하기'; ?></button>
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
    var audience = f.mb_2 ? f.mb_2.value : '';
    if (!audience) {
        alert('회원 유형(관광객/교민/둘 다)을 선택해 주세요.');
        return false;
    }
    if ((audience === 'expat' || audience === 'both') && f.mb_1 && !f.mb_1.value) {
        alert('교민 회원은 일반인 또는 사업자를 선택해 주세요.');
        return false;
    }
    return true;
}
</script>
