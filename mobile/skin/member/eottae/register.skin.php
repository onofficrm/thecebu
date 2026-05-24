<?php
if (!defined('_GNUBOARD_')) exit;

add_stylesheet('<link rel="stylesheet" href="'.$member_skin_url.'/style.css">', 0);
?>

<div class="auth-layout">
    <div class="auth-layout__card register-form">
        <h1 class="auth-layout__title">회원가입</h1>
        <p class="auth-layout__sub">세부어때에 오신 것을 환영합니다</p>

        <form name="fregister" action="<?php echo $register_action_url ?>" method="post" onsubmit="return fregister_submit(this);">
        <input type="hidden" name="url" value="<?php echo $urlencode ?>">

        <div class="auth-member-type">
            <label>
                <input type="radio" name="eottae_member_type" value="member" checked>
                <span>일반회원</span>
            </label>
            <label>
                <input type="radio" name="eottae_member_type" value="business">
                <span>사업자회원</span>
            </label>
        </div>

        <div id="fregister_term" style="text-align:left;font-size:13px;line-height:1.6;margin-bottom:16px">
            <?php echo conv_content($config['cf_stipulation'], $config['cf_editor']); ?>
            <div class="chk_box" style="margin-top:12px">
                <input type="checkbox" name="agree" value="1" id="agree11" class="selec_chk">
                <label for="agree11"><span></span> 이용약관에 동의합니다 (필수)</label>
            </div>
            <div class="chk_box" style="margin-top:8px">
                <input type="checkbox" name="agree2" value="1" id="agree21" class="selec_chk">
                <label for="agree21"><span></span> 개인정보 처리방침에 동의합니다 (필수)</label>
            </div>
        </div>

        <button type="submit" class="btn_submit">다음</button>
        </form>

        <div class="auth-layout__links">
            <a href="<?php echo G5_BBS_URL ?>/login.php">이미 계정이 있으신가요? 로그인</a>
        </div>
    </div>
</div>

<script>
function fregister_submit(f) {
    if (!f.agree.checked) { alert('이용약관에 동의해 주세요.'); return false; }
    if (!f.agree2.checked) { alert('개인정보 처리방침에 동의해 주세요.'); return false; }
    return true;
}
</script>
