<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (empty($hero) || !is_array($hero)) {
    $hero = function_exists('eottae_plaza_hero_data') ? eottae_plaza_hero_data() : array();
}

$plaza_talk_url = function_exists('eottae_plaza_talk_list_url') ? eottae_plaza_talk_list_url() : G5_URL.'/talk';
$plaza_write_url = !empty($write_href) ? $write_href : (function_exists('eottae_plaza_write_url') ? eottae_plaza_write_url() : '#');
$plaza_login_url = function_exists('eottae_plaza_login_url') ? eottae_plaza_login_url() : G5_BBS_URL.'/login.php';
?>

<section class="plaza-hero">
    <div class="plaza-hero__inner">
        <?php if (!empty($hero['kicker'])) { ?>
        <p class="plaza-hero__kicker"><?php echo get_text($hero['kicker']); ?></p>
        <?php } ?>
        <h1 class="plaza-hero__title"><?php echo get_text($hero['title'] ?? '세부광장'); ?></h1>
        <?php if (!empty($hero['desc'])) { ?>
        <p class="plaza-hero__desc"><?php echo get_text($hero['desc']); ?></p>
        <?php } ?>
        <p class="plaza-hero__stats">
            <span>전체글 <strong><?php echo number_format((int) $total_count); ?></strong></span>
            <span class="plaza-hero__stats-divider" aria-hidden="true">·</span>
            <span>오늘 <strong><?php echo number_format((int) $today_count); ?></strong></span>
        </p>
        <div class="plaza-hero__actions">
            <?php if (!empty($write_href)) { ?>
            <a href="<?php echo $plaza_write_url; ?>" class="plaza-btn plaza-btn--primary">글쓰기</a>
            <?php } elseif (empty($is_member)) { ?>
            <a href="<?php echo $plaza_login_url; ?>" class="plaza-btn plaza-btn--primary">로그인 후 글쓰기</a>
            <?php } ?>
            <a href="<?php echo $plaza_talk_url; ?>" class="plaza-btn plaza-btn--ghost">세부톡방 둘러보기</a>
            <a href="<?php echo function_exists('eottae_plaza_talk_create_url') ? eottae_plaza_talk_create_url() : G5_URL.'/page/eottae-talk-create.php'; ?>" class="plaza-btn plaza-btn--ghost">톡방 만들기</a>
        </div>
    </div>
</section>
