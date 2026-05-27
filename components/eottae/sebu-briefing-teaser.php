<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

$sebu_briefing_teaser_title = isset($teaser_title) ? (string) $teaser_title : '오늘의 세부 체크';
$sebu_briefing_teaser_summary = isset($teaser_summary) ? get_text($teaser_summary) : '';
$sebu_briefing_teaser_line = isset($teaser_line) ? get_text($teaser_line) : '';
$sebu_briefing_teaser_stats = isset($teaser_stats) && is_array($teaser_stats) ? $teaser_stats : array();
$sebu_briefing_teaser_url = isset($teaser_url) ? (string) $teaser_url : (function_exists('eottae_briefing_url') ? eottae_briefing_url() : G5_URL.'/briefing/');
$sebu_briefing_teaser_cta = isset($teaser_cta) ? (string) $teaser_cta : '브리핑 보기';
?>

<section class="sebu-briefing-teaser" aria-labelledby="sebu-briefing-teaser-title">
    <div class="sebu-briefing-teaser__inner">
        <div class="sebu-briefing-teaser__content">
            <p class="sebu-briefing-teaser__eyebrow">Daily Briefing</p>
            <h2 class="sebu-briefing-teaser__title" id="sebu-briefing-teaser-title"><?php echo get_text($sebu_briefing_teaser_title); ?></h2>
            <?php if ($sebu_briefing_teaser_summary !== '') { ?>
            <p class="sebu-briefing-teaser__summary"><?php echo $sebu_briefing_teaser_summary; ?></p>
            <?php } ?>
            <?php if ($sebu_briefing_teaser_line !== '') { ?>
            <p class="sebu-briefing-teaser__line"><?php echo $sebu_briefing_teaser_line; ?></p>
            <?php } ?>
            <?php if ($sebu_briefing_teaser_stats) { ?>
            <ul class="sebu-briefing-teaser__stats">
                <?php foreach ($sebu_briefing_teaser_stats as $stat) { ?>
                <li class="sebu-briefing-teaser__stat">
                    <span class="sebu-briefing-teaser__stat-value"><?php echo number_format((int) ($stat['value'] ?? 0)); ?></span>
                    <span class="sebu-briefing-teaser__stat-label"><?php echo get_text($stat['label'] ?? ''); ?></span>
                </li>
                <?php } ?>
            </ul>
            <?php } ?>
        </div>
        <a href="<?php echo htmlspecialchars($sebu_briefing_teaser_url, ENT_QUOTES); ?>" class="sebu-briefing-teaser__cta"><?php echo get_text($sebu_briefing_teaser_cta); ?></a>
    </div>
</section>
