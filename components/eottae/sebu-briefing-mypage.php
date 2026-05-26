<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

$sebu_briefing_scope = 'my';
$sebu_briefing_title = '내 세부어때 브리핑';
$sebu_briefing_subtitle = '내가 참여 중인 톡방과 커뮤니티 활동을 요약했습니다.';
$sebu_briefing_lines = isset($lines) && is_array($lines) ? $lines : array();
$sebu_briefing_cards = isset($cards) && is_array($cards) ? $cards : array();
$sebu_briefing_talk_url = isset($data['mypage_talk_url']) ? $data['mypage_talk_url'] : (function_exists('eottae_mypage_talk_url') ? eottae_mypage_talk_url() : G5_URL.'/mypage/talk.php');
$sebu_briefing_is_empty = !empty($data['is_empty']);
?>

<section class="sebu-briefing sebu-briefing--my" aria-labelledby="sebu-briefing-my-title">
    <div class="sebu-briefing__inner">
        <header class="sebu-briefing__head sebu-briefing__head--my">
            <div>
                <p class="sebu-briefing__eyebrow">My Briefing</p>
                <h2 class="sebu-briefing__title" id="sebu-briefing-my-title"><?php echo get_text($sebu_briefing_title); ?></h2>
                <p class="sebu-briefing__subtitle"><?php echo get_text($sebu_briefing_subtitle); ?></p>
            </div>
            <a href="<?php echo htmlspecialchars($sebu_briefing_talk_url, ENT_QUOTES); ?>" class="sebu-briefing__more">톡방 대시보드</a>
        </header>

        <?php if ($sebu_briefing_cards) { ?>
        <div class="sebu-briefing__cards sebu-briefing__cards--my">
            <?php foreach ($sebu_briefing_cards as $card) { ?>
            <a href="<?php echo htmlspecialchars($card['url'] ?? '#', ENT_QUOTES); ?>" class="sebu-briefing-card sebu-briefing-card--<?php echo htmlspecialchars($card['icon'] ?? 'default', ENT_QUOTES); ?>">
                <span class="sebu-briefing-card__icon" aria-hidden="true"></span>
                <span class="sebu-briefing-card__value"><?php echo number_format((int) ($card['value'] ?? 0)); ?></span>
                <span class="sebu-briefing-card__label"><?php echo get_text($card['label'] ?? ''); ?></span>
            </a>
            <?php } ?>
        </div>
        <?php } ?>

        <div class="sebu-briefing__body">
            <?php if ($sebu_briefing_is_empty) { ?>
            <p class="sebu-briefing__line sebu-briefing__line--muted">참여 중인 톡방 활동이 아직 많지 않습니다. 관심 톡방에 참여해보세요.</p>
            <?php } elseif ($sebu_briefing_lines) {
                foreach ($sebu_briefing_lines as $line) {
                    if (trim((string) $line) === '') {
                        continue;
                    }
            ?>
            <p class="sebu-briefing__line"><?php echo get_text($line); ?></p>
            <?php }
            } ?>
        </div>
    </div>
</section>
