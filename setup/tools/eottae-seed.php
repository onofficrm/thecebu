<?php
/**
 * 세부어때 — 샘플 업체·커뮤니티 시드 (관리자 1회 실행)
 * URL: /setup/tools/eottae-seed.php?run=1
 */
$g5_path = realpath(__DIR__.'/../..');
chdir($g5_path);
include_once $g5_path.'/common.php';

if (!defined('_GNUBOARD_')) {
    exit;
}

include_once __DIR__.'/eottae-seed.lib.php';

if (!$is_admin || $is_admin !== 'super') {
    alert('최고관리자만 실행할 수 있습니다.', G5_BBS_URL.'/login.php?url='.urlencode(G5_URL.'/setup/tools/eottae-seed.php'));
}

$run = isset($_GET['run']) && $_GET['run'] === '1';
$shops_only = isset($_GET['shops']) && $_GET['shops'] === '1';

define('EOTTAE_SETUP_MINIMAL', true);
$g5_css_brand = '';
$g5['title'] = '세부어때 샘플 데이터';
include_once G5_PATH.'/head.php';
?>

<div class="shop-list-page" style="max-width:640px;margin:24px auto">
    <h1 class="shop-list-page__title">샘플 데이터 시드</h1>
    <p style="color:#64748b;margin-bottom:24px">좌표가 포함된 업체 샘플·커뮤니티 글·메뉴를 등록합니다. 동일 제목은 건너뜁니다.</p>

    <?php if (!$run) { ?>
    <p style="margin-bottom:16px">
        <a href="?run=1" class="eottae-btn-write" onclick="return confirm('샘플 데이터를 등록합니다. 계속할까요?');">전체 시드 실행</a>
        &nbsp;
        <a href="?run=1&amp;shops=1" class="inquiry-button__btn inquiry-button__btn--map" style="display:inline-flex;text-decoration:none" onclick="return confirm('업체 샘플만 등록합니다.');">업체 샘플만</a>
    </p>
    <?php } else { ?>
    <div class="board-list-page" style="padding:0">
        <?php
        if ($shops_only) {
            $logs = array();
            foreach (eottae_seed_get_sample_shops() as $shop) {
                $logs[] = eottae_seed_insert_shop($shop);
            }
        } else {
            $logs = eottae_seed_run();
            $logs = array_merge($logs, eottae_seed_community_samples_run());
        }

        foreach ($logs as $entry) {
            $icon = $entry['ok'] ? '✅' : '❌';
            echo '<p class="board-post-item" style="margin-bottom:8px"><strong>'.$icon.' '.htmlspecialchars($entry['message'], ENT_QUOTES, 'UTF-8').'</strong></p>';
        }
        ?>
    </div>
    <p style="margin-top:24px"><a href="<?php echo G5_BBS_URL; ?>/board.php?bo_table=shop">업체 목록 확인</a></p>
    <?php } ?>
</div>

<?php
include_once G5_PATH.'/tail.php';
