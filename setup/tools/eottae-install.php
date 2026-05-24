<?php
/**
 * 세부어때 — 게시판·환경설정 1회 설치
 *
 * 사용: 최고관리자 로그인 후 브라우저에서 접속
 * URL: /setup/tools/eottae-install.php
 *
 * 설치 후 보안을 위해 이 파일을 삭제하거나 rename 하세요.
 */
$g5_path = realpath(__DIR__.'/../..');
chdir($g5_path);
include_once($g5_path.'/common.php');

if (!defined('_GNUBOARD_')) {
    exit;
}

include_once(__DIR__.'/eottae-install.lib.php');

if (!$is_admin || $is_admin !== 'super') {
    alert('최고관리자만 실행할 수 있습니다.', G5_BBS_URL.'/login.php?url='.urlencode(G5_URL.'/setup/tools/eottae-install.php'));
}

$run = isset($_GET['run']) && $_GET['run'] === '1';
$update_only = isset($_GET['update']) && $_GET['update'] === '1';

$g5['title'] = '세부어때 설치';
include_once(G5_PATH.'/head.php');
?>

<div class="shop-list-page" style="max-width:640px;margin:24px auto">
    <h1 class="shop-list-page__title">세부어때 DB 설치</h1>
    <p style="color:#64748b;margin-bottom:24px">shop · community 게시판 생성 및 환경설정(테마 해제, 회원스킨 eottae)을 적용합니다.</p>

    <?php if (!$run) { ?>
    <div class="shop-detail-page__info" style="margin-bottom:20px">
        <h2 style="margin:0 0 12px;font-size:1rem">실행 내용</h2>
        <ul style="margin:0;padding-left:1.2rem;line-height:1.8">
            <li><strong>shop</strong> 게시판 — 스킨 <code>eottae-shop</code>, 여분필드 10개 라벨</li>
            <li><strong>community</strong> 게시판 — 스킨 <code>eottae-community</code></li>
            <li>환경설정: <code>cf_theme</code> 비우기, 회원스킨 <code>eottae</code></li>
        </ul>
    </div>
    <p style="margin-bottom:16px">
        <a href="?run=1" class="eottae-btn-write" onclick="return confirm('게시판·설정을 적용합니다. 계속할까요?');">설치 실행</a>
        &nbsp;
        <a href="?update=1&amp;run=1" class="inquiry-button__btn inquiry-button__btn--map" style="display:inline-flex;text-decoration:none" onclick="return confirm('기존 shop/community 스킨만 갱신합니다.');">기존 게시판 스킨만 갱신</a>
    </p>
    <?php } else { ?>
    <div class="board-list-page" style="padding:0">
        <?php
        $logs = $update_only ? eottae_install_update_existing_boards() : eottae_install_run();
        foreach ($logs as $entry) {
            $icon = $entry['action'] === 'skip' ? '⏭' : ($entry['ok'] ? '✅' : '❌');
            echo '<p class="board-post-item" style="margin-bottom:8px"><strong>'.$icon.' '.htmlspecialchars($entry['message'], ENT_QUOTES, 'UTF-8').'</strong></p>';
        }
        ?>
    </div>
    <hr style="margin:24px 0;border:none;border-top:1px solid #e2e8f0">
    <h2 style="font-size:1rem;margin-bottom:12px">확인 URL</h2>
    <ul style="line-height:2">
        <li><a href="<?php echo G5_BBS_URL; ?>/board.php?bo_table=shop">업소 목록 (/bbs/board.php?bo_table=shop)</a></li>
        <li><a href="<?php echo G5_BBS_URL; ?>/board.php?bo_table=community">커뮤니티</a></li>
        <li><a href="<?php echo G5_BBS_URL; ?>/login.php">로그인 (eottae 스킨)</a></li>
        <li><a href="<?php echo G5_URL; ?>/page/eottae-mypage.php">마이페이지</a></li>
        <li><a href="<?php echo G5_URL; ?>/">홈</a></li>
    </ul>
    <p style="margin-top:24px;color:#64748b;font-size:13px">⚠️ 설치 완료 후 <code>setup/tools/eottae-install.php</code> 파일 삭제를 권장합니다.</p>
    <?php } ?>
</div>

<?php
include_once(G5_PATH.'/tail.php');
