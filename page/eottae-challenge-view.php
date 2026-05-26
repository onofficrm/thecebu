<?php
include_once(dirname(__FILE__).'/_init.php');
include_once G5_LIB_PATH.'/eottae-challenge.lib.php';
include_once G5_LIB_PATH.'/eottae-challenge-likes.lib.php';
include_once G5_PATH.'/components/eottae/challenge-card.php';

$challenge_id = isset($_GET['challenge_id']) ? (int) $_GET['challenge_id'] : 0;
$challenge = eottae_challenge_get($challenge_id);

if (!$challenge) {
    alert('챌린지를 찾을 수 없습니다.', eottae_challenge_list_url());
}

$entries = eottae_challenge_list_entries($challenge_id, array('limit' => 30));
$can_join = ($challenge['display_status'] ?? '') === 'active';
$write_href = $can_join
    ? eottae_challenge_write_url($challenge_id)
    : ($is_member ? '#' : (function_exists('eottae_login_url') ? eottae_login_url(eottae_challenge_write_url($challenge_id)) : G5_BBS_URL.'/login.php'));

add_stylesheet('<link rel="stylesheet" href="'.G5_CSS_URL.'/eottae-challenge.css">', 24);

g5_page_start(get_text($challenge['title'] ?? '챌린지'));
?>

<main class="sebu-challenge-page sebu-challenge-page--view">
    <p class="sebu-challenge-page__back"><a href="<?php echo eottae_challenge_list_url(); ?>">← 챌린지 목록</a></p>

    <header class="sebu-challenge-detail">
        <div class="sebu-challenge-detail__head">
            <?php if (!empty($challenge['image_url'])) { ?>
            <img src="<?php echo get_text($challenge['image_url']); ?>" alt="" class="sebu-challenge-detail__image">
            <?php } else { ?>
            <span class="sebu-challenge-detail__icon"><?php echo get_text($challenge['icon_display'] ?? '🏆'); ?></span>
            <?php } ?>
            <div class="sebu-challenge-detail__titles">
                <span class="<?php echo get_text($challenge['display_status_class'] ?? ''); ?>"><?php echo get_text($challenge['display_status_label'] ?? ''); ?></span>
                <h1 class="sebu-challenge-detail__title"><?php echo get_text($challenge['title'] ?? ''); ?></h1>
                <p class="sebu-challenge-detail__period">참여 기간: <?php echo get_text($challenge['period_label'] ?? ''); ?></p>
            </div>
        </div>

        <?php if (!empty($challenge['description'])) { ?>
        <p class="sebu-challenge-detail__desc"><?php echo nl2br(get_text($challenge['description'])); ?></p>
        <?php } ?>

        <dl class="sebu-challenge-detail__info">
            <div class="sebu-challenge-detail__info-row">
                <dt>참여자</dt>
                <dd><?php echo number_format((int) ($challenge['participant_count'] ?? 0)); ?>명</dd>
            </div>
            <div class="sebu-challenge-detail__info-row">
                <dt>인증글</dt>
                <dd><?php echo number_format((int) ($challenge['entry_count'] ?? 0)); ?>개</dd>
            </div>
        </dl>

        <?php if (!empty($challenge['how_to_join'])) { ?>
        <section class="sebu-challenge-detail__section">
            <h2 class="sebu-challenge-detail__section-title">참여 방법</h2>
            <div class="sebu-challenge-detail__text"><?php echo nl2br(get_text($challenge['how_to_join'])); ?></div>
        </section>
        <?php } ?>

        <?php if (!empty($challenge['conditions_text'])) { ?>
        <section class="sebu-challenge-detail__section">
            <h2 class="sebu-challenge-detail__section-title">참여 조건</h2>
            <div class="sebu-challenge-detail__text"><?php echo nl2br(get_text($challenge['conditions_text'])); ?></div>
        </section>
        <?php } ?>

        <?php if (!empty($challenge['reward_text'])) { ?>
        <section class="sebu-challenge-detail__section sebu-challenge-detail__section--reward">
            <h2 class="sebu-challenge-detail__section-title">보상</h2>
            <div class="sebu-challenge-detail__text"><?php echo nl2br(get_text($challenge['reward_text'])); ?></div>
            <?php if (!empty($challenge['reward_badge_label'])) { ?>
            <span class="sebu-challenge-badge sebu-challenge-badge--reward"><?php echo get_text($challenge['reward_badge_label']); ?> 뱃지</span>
            <?php } ?>
            <?php if ((int) ($challenge['reward_point'] ?? 0) > 0) { ?>
            <span class="sebu-challenge-badge sebu-challenge-badge--point"><?php echo number_format((int) $challenge['reward_point']); ?>P</span>
            <?php } ?>
        </section>
        <?php } ?>

        <?php if (!empty($challenge['notice_text'])) { ?>
        <section class="sebu-challenge-detail__section sebu-challenge-detail__section--notice">
            <h2 class="sebu-challenge-detail__section-title">주의사항</h2>
            <div class="sebu-challenge-detail__text"><?php echo nl2br(get_text($challenge['notice_text'])); ?></div>
        </section>
        <?php } ?>

        <div class="sebu-challenge-detail__actions">
            <?php if ($can_join) { ?>
            <a href="<?php echo $write_href; ?>" class="sebu-challenge-btn sebu-challenge-btn--primary sebu-challenge-btn--lg">참여하기</a>
            <?php } else { ?>
            <p class="sebu-challenge-detail__closed">현재 참여할 수 없는 챌린지입니다.</p>
            <?php } ?>
        </div>
    </header>

    <section class="sebu-challenge-entries" aria-labelledby="sebu-challenge-entries-title">
        <h2 class="sebu-challenge-entries__title" id="sebu-challenge-entries-title">인증글</h2>
        <?php if (empty($entries)) { ?>
        <p class="sebu-challenge-empty__desc">아직 인증글이 없습니다. 첫 참여자가 되어 보세요!</p>
        <?php } else { ?>
        <ul class="sebu-challenge-entry-grid">
            <?php foreach ($entries as $entry) {
                echo eottae_challenge_entry_card_html($entry);
            } ?>
        </ul>
        <?php } ?>
    </section>
</main>

<?php
g5_page_end();
