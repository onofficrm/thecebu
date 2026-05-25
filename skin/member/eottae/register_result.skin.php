<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

add_stylesheet('<link rel="stylesheet" href="'.$member_skin_url.'/style.css">', 0);

$display_name = get_text($mb['mb_nick'] ?: $mb['mb_name']);
?>

<div class="auth-layout">
    <div class="auth-layout__card register-form register-result">
        <h1 class="auth-layout__title">회원가입 완료</h1>
        <p class="register-result__lead">
            <strong><?php echo $display_name; ?></strong>님, 세부어때 가입을 환영합니다.
        </p>

        <?php if (is_use_email_certify()) { ?>
        <div class="register-result__notice">
            <p>입력하신 이메일로 인증 메일이 발송되었습니다. 메일 확인 후 인증을 완료해 주세요.</p>
            <dl class="register-result__info">
                <div>
                    <dt>아이디</dt>
                    <dd><?php echo get_text($mb['mb_id']); ?></dd>
                </div>
                <div>
                    <dt>이메일</dt>
                    <dd><?php echo get_text($mb['mb_email']); ?></dd>
                </div>
            </dl>
            <p class="register-result__hint">이메일 주소가 잘못되었다면 사이트 관리자에게 문의해 주세요.</p>
        </div>
        <?php } else { ?>
        <p class="register-result__notice">
            이제 세부톡, 세부광장, 커뮤니티 등 세부어때의 다양한 서비스를 이용하실 수 있습니다.
        </p>
        <?php } ?>

        <p class="register-result__hint">
            비밀번호는 암호화되어 저장됩니다. 분실 시 가입 이메일로 찾을 수 있습니다.
        </p>

        <a href="<?php echo G5_URL; ?>/" class="btn_submit register-result__cta">메인으로</a>
        <div class="auth-layout__links">
            <a href="<?php echo G5_BBS_URL; ?>/login.php">로그인</a>
        </div>
    </div>
</div>
