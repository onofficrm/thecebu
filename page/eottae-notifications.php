<?php
include_once(dirname(__FILE__).'/_init.php');

if (!$is_member) {
    alert('로그인 후 이용해 주세요.', eottae_login_url(G5_URL.'/page/eottae-notifications.php'));
}

include_once G5_LIB_PATH.'/eottae-notification.lib.php';

$notification_summary = eottae_mypage_notification_summary($member['mb_id']);
$notification_items = eottae_notification_hub_items($member['mb_id'], $notification_summary);
$notification_total = (int) ($notification_summary['total'] ?? 0);
$comment_summary = isset($notification_summary['comment_summary']) && is_array($notification_summary['comment_summary'])
    ? $notification_summary['comment_summary']
    : array('latest' => null);
$talk_hub = isset($notification_summary['talk_hub']) && is_array($notification_summary['talk_hub'])
    ? $notification_summary['talk_hub']
    : array();

g5_page_start('알림 허브');
?>

<main class="mypage-subpage notification-hub-page">
    <?php eottae_render_mypage_back(); ?>

    <header class="notification-hub-page__hero">
        <p class="notification-hub-page__eyebrow">Notification Hub</p>
        <h1 class="mypage-subpage__title">알림 허브</h1>
        <p class="notification-hub-page__lead">쪽지, 내 글 댓글, 세부톡 활동을 한곳에서 확인하세요.</p>
        <strong class="notification-hub-page__total"><?php echo number_format($notification_total); ?>개</strong>
    </header>

    <section class="notification-hub-page__grid" aria-label="알림 요약">
        <?php foreach ($notification_items as $item) { ?>
        <a href="<?php echo get_text($item['href'] ?? '#'); ?>" class="notification-hub-card<?php echo (int) ($item['count'] ?? 0) > 0 ? ' is-alert' : ''; ?>">
            <span class="notification-hub-card__label"><?php echo get_text($item['label'] ?? '알림'); ?></span>
            <strong class="notification-hub-card__count"><?php echo number_format((int) ($item['count'] ?? 0)); ?></strong>
            <span class="notification-hub-card__desc"><?php echo get_text($item['description'] ?? ''); ?></span>
            <span class="notification-hub-card__cta">확인하기</span>
        </a>
        <?php } ?>
    </section>

    <?php if (!empty($comment_summary['latest'])) {
        $latest_comment = $comment_summary['latest'];
        ?>
    <section class="notification-hub-latest" aria-labelledby="notification-latest-title">
        <h2 id="notification-latest-title">최근 댓글</h2>
        <a href="<?php echo get_text($latest_comment['href'] ?? '#'); ?>" class="notification-hub-latest__link">
            <span><?php echo get_text($latest_comment['board'] ?? '게시판'); ?></span>
            <strong><?php echo get_text($latest_comment['title'] ?? ''); ?></strong>
            <em><?php echo get_text($latest_comment['author'] ?? '회원'); ?>: <?php echo get_text($latest_comment['preview'] ?? ''); ?></em>
        </a>
    </section>
    <?php } ?>

    <?php if (!empty($talk_hub)) { ?>
    <section class="notification-hub-breakdown" aria-labelledby="notification-talk-title">
        <h2 id="notification-talk-title">세부톡 상세</h2>
        <dl>
            <div>
                <dt>새 글</dt>
                <dd><?php echo number_format((int) ($talk_hub['new_posts'] ?? 0)); ?></dd>
            </div>
            <div>
                <dt>새 댓글</dt>
                <dd><?php echo number_format((int) ($talk_hub['new_comments'] ?? 0)); ?></dd>
            </div>
            <div>
                <dt>알림</dt>
                <dd><?php echo number_format((int) ($talk_hub['notifications'] ?? 0)); ?></dd>
            </div>
            <div>
                <dt>관리할 항목</dt>
                <dd><?php echo number_format((int) ($talk_hub['owner_tasks'] ?? 0)); ?></dd>
            </div>
        </dl>
    </section>
    <?php } ?>
</main>
<?php
g5_page_end();
