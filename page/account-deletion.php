<?php
/**
 * 세부어때 계정·데이터 삭제 안내 (Google Play 계정 삭제 URL)
 * URL: https://thecebu.co.kr/page/account-deletion.php
 */
include_once(dirname(__FILE__).'/_init.php');

if (!function_exists('g5site_cfg')) {
    if (is_file(G5_PATH . '/_site.config.php')) {
        include_once G5_PATH . '/_site.config.php';
    }
}

$site_name = function_exists('g5site_cfg') ? g5site_cfg('site_name', '세부어때') : '세부어때';
$company   = function_exists('g5site_cfg') ? g5site_cfg('company_name', $site_name) : $site_name;
$email     = function_exists('g5site_cfg') ? g5site_cfg('email', 'jong8040@gmail.com') : 'jong8040@gmail.com';
$phone     = function_exists('g5site_cfg') ? g5site_cfg('phone', '') : '';

$leave_url = G5_BBS_URL.'/member_confirm.php?url='.urlencode(G5_BBS_URL.'/member_leave.php');
$login_url = G5_BBS_URL.'/login.php?url='.urlencode($leave_url);
$mypage_url = G5_URL.'/page/eottae-mypage.php';
$privacy_url = G5_URL.'/page/privacy.php';
$delete_cta = !empty($is_member) ? $leave_url : $login_url;

$page_title       = '계정 및 데이터 삭제';
$page_description = $site_name.' 앱·웹사이트 계정과 관련 데이터를 삭제하는 방법을 안내합니다.';
$page_robots      = 'index,follow';

g5_page_start('계정 및 데이터 삭제');
?>
<div class="page-template page-privacy">
    <header class="page-hero reveal">
        <div class="page-inner">
            <p class="page-eyebrow">Account Deletion</p>
            <h1 class="page-title">계정 및 데이터 삭제</h1>
            <p class="page-desc">
                본 페이지는 <strong><?php echo htmlspecialchars($site_name, ENT_QUOTES, 'UTF-8'); ?></strong>
                (Android 앱 · <?php echo htmlspecialchars($company, ENT_QUOTES, 'UTF-8'); ?> ·
                <a href="https://thecebu.co.kr/">thecebu.co.kr</a>)의
                계정과 관련 데이터를 삭제하는 방법을 안내합니다.
            </p>
        </div>
    </header>

    <article class="page-section page-privacy__body reveal">
        <div class="page-inner">
            <section class="page-privacy__block">
                <h2 class="page-section__title">앱·서비스 정보</h2>
                <ul class="page-list">
                    <li>앱 이름: 세부어때</li>
                    <li>패키지명: kr.co.thecebu.app</li>
                    <li>웹사이트: https://thecebu.co.kr</li>
                    <li>문의: <a href="mailto:<?php echo htmlspecialchars($email, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($email, ENT_QUOTES, 'UTF-8'); ?></a><?php if ($phone !== '') { ?> / <?php echo htmlspecialchars($phone, ENT_QUOTES, 'UTF-8'); ?><?php } ?></li>
                </ul>
            </section>

            <section class="page-privacy__block">
                <h2 class="page-section__title">앱에서 직접 삭제하는 방법</h2>
                <ol class="page-list">
                    <li>세부어때 앱 또는 <a href="https://thecebu.co.kr/">thecebu.co.kr</a>에 로그인합니다.</li>
                    <li>하단 또는 메뉴에서 <strong>MY</strong>로 이동합니다.</li>
                    <li><strong>계정 → 회원탈퇴</strong>를 선택합니다.</li>
                    <li>비밀번호를 입력해 본인 확인 후 탈퇴를 완료합니다.</li>
                </ol>
                <p>
                    <a class="btn_submit" style="display:inline-block;padding:12px 18px;border-radius:999px;text-decoration:none;" href="<?php echo htmlspecialchars($delete_cta, ENT_QUOTES, 'UTF-8'); ?>">
                        <?php echo !empty($is_member) ? '지금 회원탈퇴 진행하기' : '로그인 후 회원탈퇴하기'; ?>
                    </a>
                </p>
                <p class="page-section__desc">
                    MY 바로가기:
                    <a href="<?php echo htmlspecialchars($mypage_url, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($mypage_url, ENT_QUOTES, 'UTF-8'); ?></a>
                </p>
            </section>

            <section class="page-privacy__block">
                <h2 class="page-section__title">이메일로 삭제를 요청하는 방법</h2>
                <p>앱에서 로그인이 어렵거나 직접 탈퇴가 어려운 경우, 아래 이메일로 계정 삭제를 요청할 수 있습니다.</p>
                <ul class="page-list">
                    <li>수신: <a href="mailto:<?php echo htmlspecialchars($email, ENT_QUOTES, 'UTF-8'); ?>?subject=<?php echo rawurlencode('세부어때 계정 삭제 요청'); ?>"><?php echo htmlspecialchars($email, ENT_QUOTES, 'UTF-8'); ?></a></li>
                    <li>제목 예: 세부어때 계정 삭제 요청</li>
                    <li>본문에 넣을 내용: 가입 아이디 또는 이메일, 닉네임, 삭제 요청 의사</li>
                </ul>
                <p>본인 확인 후 영업일 기준 7일 이내에 처리합니다.</p>
            </section>

            <section class="page-privacy__block">
                <h2 class="page-section__title">삭제되는 데이터</h2>
                <ul class="page-list">
                    <li>회원 계정 정보: 아이디, 닉네임, 이메일, 연락처, 프로필 정보</li>
                    <li>로그인·구글 계정 연동 정보</li>
                    <li>푸시 알림 구독 정보</li>
                    <li>탈퇴 처리 후 더 이상 로그인할 수 없습니다</li>
                </ul>
            </section>

            <section class="page-privacy__block">
                <h2 class="page-section__title">일부 남을 수 있는 데이터와 기간</h2>
                <ul class="page-list">
                    <li><strong>부정 이용 방지용 아이디:</strong> 재가입 제한을 위해 일정 기간 보관할 수 있습니다.</li>
                    <li><strong>이용자가 공개한 게시글·댓글·거래 글:</strong> 탈퇴 시 계정은 비활성·탈퇴 처리되지만, 이미 공개된 글은 서비스 운영·다른 이용자 거래 기록 유지를 위해 남을 수 있습니다. 삭제를 원하면 탈퇴 전에 본인 글을 삭제하거나 이메일로 요청해 주세요.</li>
                    <li><strong>문의·고객지원 기록:</strong> 관련 법령에 따라 최대 3년</li>
                    <li><strong>접속 로그:</strong> 통신비밀보호법에 따라 최대 3개월</li>
                    <li><strong>법령상 보관이 필요한 기록:</strong> 해당 법령에서 정한 기간</li>
                </ul>
            </section>

            <section class="page-privacy__block">
                <h2 class="page-section__title">관련 안내</h2>
                <ul class="page-list">
                    <li><a href="<?php echo htmlspecialchars($privacy_url, ENT_QUOTES, 'UTF-8'); ?>">개인정보처리방침</a></li>
                </ul>
            </section>
        </div>
    </article>
</div>
<?php
g5_page_end();
