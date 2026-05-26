<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

$sebu_briefing_scope = 'today';
$sebu_briefing_title = '오늘의 세부 브리핑';
$sebu_briefing_subtitle = '세부 교민들이 오늘 확인하면 좋은 일정과 커뮤니티 소식을 모았습니다.';
$sebu_briefing_lines = isset($lines) && is_array($lines) ? $lines : array();
$sebu_briefing_summary = isset($summary) ? (string) $summary : '';
$sebu_briefing_cards = isset($cards) && is_array($cards) ? $cards : array();
$sebu_briefing_popular = isset($popular) && is_array($popular) ? $popular : array();
$sebu_briefing_admin_notice = isset($data['admin_notice']) ? get_text($data['admin_notice']) : '';
?>

<section class="sebu-briefing sebu-briefing--today" aria-labelledby="sebu-briefing-today-title">
    <div class="sebu-briefing__inner">
        <header class="sebu-briefing__head">
            <div>
                <p class="sebu-briefing__eyebrow">Daily Briefing</p>
                <h2 class="sebu-briefing__title" id="sebu-briefing-today-title"><?php echo get_text($sebu_briefing_title); ?></h2>
                <p class="sebu-briefing__subtitle"><?php echo get_text($sebu_briefing_subtitle); ?></p>
            </div>
        </header>

        <?php if ($sebu_briefing_admin_notice !== '') { ?>
        <div class="sebu-briefing__notice" role="note">
            <strong class="sebu-briefing__notice-label">오늘의 안내</strong>
            <p><?php echo $sebu_briefing_admin_notice; ?></p>
        </div>
        <?php } ?>

        <?php if ($sebu_briefing_cards) { ?>
        <div class="sebu-briefing__cards">
            <?php foreach ($sebu_briefing_cards as $card) { ?>
            <a href="<?php echo htmlspecialchars($card['url'] ?? '#', ENT_QUOTES); ?>" class="sebu-briefing-card sebu-briefing-card--<?php echo htmlspecialchars($card['icon'] ?? 'default', ENT_QUOTES); ?>">
                <span class="sebu-briefing-card__icon" aria-hidden="true"></span>
                <span class="sebu-briefing-card__value"><?php echo number_format((int) ($card['value'] ?? 0)); ?></span>
                <span class="sebu-briefing-card__label"><?php echo get_text($card['label'] ?? ''); ?></span>
            </a>
            <?php } ?>
        </div>
        <?php } ?>

        <?php if ($sebu_briefing_lines) { ?>
        <div class="sebu-briefing__body">
            <?php foreach ($sebu_briefing_lines as $line) {
                if (trim((string) $line) === '') {
                    continue;
                }
            ?>
            <p class="sebu-briefing__line"><?php echo get_text($line); ?></p>
            <?php } ?>
        </div>
        <?php } ?>

        <?php if ($sebu_briefing_summary !== '') { ?>
        <p class="sebu-briefing__summary"><?php echo get_text($sebu_briefing_summary); ?></p>
        <?php } ?>

        <?php if ($sebu_briefing_popular) { ?>
        <div class="sebu-briefing__popular">
            <h3 class="sebu-briefing__popular-title">인기글</h3>
            <ul class="sebu-briefing-popular-list">
                <?php foreach ($sebu_briefing_popular as $post) { ?>
                <li class="sebu-briefing-popular-list__item">
                    <a href="<?php echo htmlspecialchars($post['url'] ?? '#', ENT_QUOTES); ?>" class="sebu-briefing-popular-list__link">
                        <?php if (!empty($post['board'])) { ?>
                        <span class="sebu-briefing-popular-list__board"><?php echo get_text($post['board']); ?></span>
                        <?php } ?>
                        <span class="sebu-briefing-popular-list__title"><?php echo get_text($post['title'] ?? ''); ?></span>
                        <span class="sebu-briefing-popular-list__meta">조회 <?php echo number_format((int) ($post['views'] ?? 0)); ?></span>
                    </a>
                </li>
                <?php } ?>
            </ul>
        </div>
        <?php } ?>
    </div>
</section>
