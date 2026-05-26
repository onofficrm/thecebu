<?php
include_once(dirname(__FILE__).'/_init.php');

include_once G5_LIB_PATH.'/eottae-member-growth.lib.php';
include_once G5_PATH.'/components/eottae/member-growth-display.php';

$mb_id = isset($_GET['mb_id']) ? preg_replace('/[^a-z0-9_@.-]/i', '', (string) $_GET['mb_id']) : '';
if ($mb_id === '') {
    alert('회원 정보가 없습니다.', G5_URL);
}

eottae_member_growth_ensure_schema();

$viewer_mb_id = !empty($member['mb_id']) ? $member['mb_id'] : '';
$public = eottae_member_growth_public_profile($mb_id, $viewer_mb_id);

if (!$public) {
    alert('회원을 찾을 수 없습니다.', G5_URL);
}

add_stylesheet('<link rel="stylesheet" href="'.G5_CSS_URL.'/eottae-member-growth.css">', 23);
add_stylesheet('<link rel="stylesheet" href="'.G5_CSS_URL.'/eottae-member-growth-social.css">', 24);

$title = get_text($public['display_nick'] ?? '').'님 프로필';
g5_page_start($title);
?>

<main class="sebu-member-profile">
    <?php if (!empty($public['is_private'])) { ?>
    <div class="sebu-member-profile__hero">
        <p class="sebu-member-profile__name"><?php echo get_text($public['display_nick']); ?>님</p>
        <p>비공개 프로필입니다.</p>
    </div>
    <?php } else {
        $profile = $public['profile'] ?? array();
        $stats = $public['stats'] ?? array();
        ?>
    <div class="sebu-member-profile__hero">
        <?php echo get_member_profile_img($mb_id, 72, 72); ?>
        <p class="sebu-member-profile__name"><?php echo get_text($public['display_nick']); ?>님</p>
        <div class="sebu-rank-list__meta" style="justify-content:center">
            <?php if (!empty($profile['main_badge'])) {
                echo eottae_member_growth_render_badge($profile['main_badge'], true);
            } ?>
            <?php if (!empty($profile['level'])) {
                echo eottae_member_growth_render_level_chip($profile['level']);
            } ?>
        </div>
        <?php if (!empty($public['public_bio'])) { ?>
        <p class="sebu-member-profile__bio"><?php echo nl2br(get_text($public['public_bio'])); ?></p>
        <?php } ?>

        <div class="sebu-member-profile__stats">
            <span><strong><?php echo number_format((int) ($profile['total_score'] ?? 0)); ?></strong>활동 점수</span>
            <span><strong><?php echo number_format((int) ($public['badge_count'] ?? 0)); ?></strong>뱃지</span>
            <span><strong><?php echo number_format((int) ($stats['post_count'] ?? 0)); ?></strong>글</span>
            <span><strong><?php echo number_format((int) ($stats['comment_count'] ?? 0)); ?></strong>댓글</span>
        </div>
    </div>

    <?php if (!empty($public['recent_badges'])) { ?>
    <section class="member-growth-section">
        <h2 class="member-growth-section__title">최근 획득 뱃지</h2>
        <ul class="member-growth-badge-grid">
            <?php foreach ($public['recent_badges'] as $badge) { ?>
            <li class="member-growth-badge-item is-owned">
                <div class="member-growth-badge-item__info">
                    <?php echo eottae_member_growth_render_badge($badge, !empty($badge['is_main'])); ?>
                </div>
            </li>
            <?php } ?>
        </ul>
    </section>
    <?php } ?>

    <?php if (!empty($public['badges'])) { ?>
    <section class="member-growth-section">
        <h2 class="member-growth-section__title">보유 뱃지</h2>
        <ul class="member-growth-badge-grid">
            <?php foreach ($public['badges'] as $badge) { ?>
            <li class="member-growth-badge-item">
                <div class="member-growth-badge-item__info">
                    <?php echo eottae_member_growth_render_badge($badge, !empty($badge['is_main'])); ?>
                    <?php if (!empty($badge['badge_description'])) { ?>
                    <p class="member-growth-badge-item__desc"><?php echo get_text($badge['badge_description']); ?></p>
                    <?php } ?>
                </div>
            </li>
            <?php } ?>
        </ul>
    </section>
    <?php } ?>

    <p style="text-align:center;margin-top:20px">
        <a href="<?php echo eottae_member_growth_badge_book_url(); ?>">뱃지 도감</a>
        · <a href="<?php echo eottae_member_growth_ranking_url('week'); ?>">활동 랭킹</a>
    </p>
    <?php } ?>
</main>

<?php
g5_page_end();
