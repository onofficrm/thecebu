<?php
if (!defined('_GNUBOARD_')) exit;

add_stylesheet('<link rel="stylesheet" href="'.$member_skin_url.'/style.css">', 0);

$eottae_is_leave = (strpos($url, 'member_leave.php') !== false);
$eottae_confirm_title = $eottae_is_leave ? '회원 탈퇴' : '정보 수정';
$eottae_confirm_sub = $eottae_is_leave
    ? '비밀번호를 입력하시면 회원탈퇴가 완료됩니다.'
    : '회원님의 정보를 안전하게 보호하기 위해 비밀번호를 한번 더 확인합니다.';
?>

<div class="auth-layout">
    <div class="auth-layout__card member-confirm-form">
        <h1 class="auth-layout__title"><?php echo $eottae_confirm_title; ?></h1>
        <p class="auth-layout__sub"><?php echo $eottae_confirm_sub; ?></p>

        <form name="fmemberconfirm" action="<?php echo $url ?>" onsubmit="return fmemberconfirm_submit(this);" method="post">
        <input type="hidden" name="mb_id" value="<?php echo $member['mb_id'] ?>">
        <input type="hidden" name="w" value="u">

        <div class="eottae-field member-confirm-form__id">
            <label for="mb_confirm_id">회원아이디</label>
            <p id="mb_confirm_id" class="member-confirm-form__id-value"><?php echo get_text($member['mb_id']); ?></p>
        </div>

        <div class="eottae-field">
            <label for="confirm_mb_password">비밀번호</label>
            <input type="password" name="mb_password" id="confirm_mb_password" required class="frm_input" maxlength="20" placeholder="비밀번호">
        </div>

        <button type="submit" id="btn_submit" class="btn_submit"><?php echo $eottae_is_leave ? '탈퇴하기' : '확인'; ?></button>
        </form>

        <div class="auth-layout__links">
            <?php if ($eottae_is_leave) { ?>
            <a href="<?php echo function_exists('eottae_mypage_url') ? eottae_mypage_url() : G5_URL.'/page/eottae-mypage.php'; ?>">마이페이지로 돌아가기</a>
            <?php } else { ?>
            <a href="<?php echo function_exists('eottae_mypage_url') ? eottae_mypage_url() : G5_URL.'/page/eottae-mypage.php'; ?>">마이페이지</a>
            <a href="<?php echo G5_BBS_URL ?>/login.php">로그인</a>
            <?php } ?>
        </div>
    </div>
</div>

<script>
function fmemberconfirm_submit(f) {
    document.getElementById('btn_submit').disabled = true;
    return true;
}
</script>
