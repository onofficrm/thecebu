<?php
include_once(dirname(__FILE__).'/_init.php');
include_once G5_LIB_PATH.'/eottae-challenge.lib.php';
include_once G5_LIB_PATH.'/eottae-challenge-likes.lib.php';
include_once G5_PATH.'/components/eottae/challenge-card.php';

eottae_challenge_ensure_schema();

$filter = isset($_GET['status']) ? preg_replace('/[^a-z_]/', '', (string) $_GET['status']) : '';
$challenges = eottae_challenge_list(array('limit' => 50));

if ($filter !== '' && in_array($filter, array('active', 'scheduled', 'ended'), true)) {
    $challenges = array_values(array_filter($challenges, function ($item) use ($filter) {
        return ($item['display_status'] ?? '') === $filter;
    }));
}

add_stylesheet('<link rel="stylesheet" href="'.G5_CSS_URL.'/eottae-challenge.css">', 24);

g5_page_start('챌린지');
?>

<main class="sebu-challenge-page">
    <header class="sebu-challenge-page__hero">
        <h1 class="sebu-challenge-page__title">챌린지</h1>
        <p class="sebu-challenge-page__intro">세부 생활을 함께 기록하고 공유해보세요.<br>참여만 해도 세부어때가 더 살아납니다.</p>
        <p class="sebu-challenge-page__guide-link"><a href="<?php echo G5_URL; ?>/page/eottae-challenge-guide.php">챌린지 참여 방법 안내 →</a></p>
    </header>

    <nav class="sebu-challenge-filter" aria-label="챌린지 상태 필터">
        <a href="<?php echo eottae_challenge_list_url(); ?>" class="sebu-challenge-filter__btn<?php echo $filter === '' ? ' is-active' : ''; ?>">전체</a>
        <a href="<?php echo eottae_challenge_list_url(array('status' => 'active')); ?>" class="sebu-challenge-filter__btn<?php echo $filter === 'active' ? ' is-active' : ''; ?>">진행중</a>
        <a href="<?php echo eottae_challenge_list_url(array('status' => 'scheduled')); ?>" class="sebu-challenge-filter__btn<?php echo $filter === 'scheduled' ? ' is-active' : ''; ?>">예정</a>
        <a href="<?php echo eottae_challenge_list_url(array('status' => 'ended')); ?>" class="sebu-challenge-filter__btn<?php echo $filter === 'ended' ? ' is-active' : ''; ?>">종료</a>
    </nav>

    <?php if (empty($challenges)) { ?>
    <div class="sebu-challenge-empty">
        <p class="sebu-challenge-empty__title">표시할 챌린지가 없습니다</p>
        <p class="sebu-challenge-empty__desc">곧 새로운 챌린지가 열릴 예정입니다.</p>
    </div>
    <?php } else { ?>
    <ul class="sebu-challenge-list">
        <?php foreach ($challenges as $challenge) {
            echo eottae_challenge_card_html($challenge);
        } ?>
    </ul>
    <?php } ?>

    <?php if ($is_member) { ?>
    <p class="sebu-challenge-page__mypage-link">
        <a href="<?php echo eottae_challenge_mypage_url(); ?>" class="sebu-challenge-btn sebu-challenge-btn--ghost">내 챌린지 참여 내역</a>
    </p>
    <?php } ?>
</main>

<?php
g5_page_end();
