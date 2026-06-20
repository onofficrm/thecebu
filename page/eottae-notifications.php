<?php
include_once(dirname(__FILE__).'/_init.php');

global $is_member, $member;

if (!$is_member) {
    alert('로그인 후 이용해 주세요.', eottae_login_url(G5_URL.'/page/eottae-notifications.php'));
}

include_once G5_LIB_PATH.'/eottae-notification.lib.php';
include_once G5_LIB_PATH.'/eottae-push.lib.php';
include_once G5_LIB_PATH.'/eottae-talkroom-notify.lib.php';

$notification_summary = eottae_mypage_notification_summary($member['mb_id']);
$notification_items = eottae_notification_hub_items($member['mb_id'], $notification_summary);
$notification_total = (int) ($notification_summary['total'] ?? 0);
$comment_summary = isset($notification_summary['comment_summary']) && is_array($notification_summary['comment_summary'])
    ? $notification_summary['comment_summary']
    : array('latest' => null);
$talk_hub = isset($notification_summary['talk_hub']) && is_array($notification_summary['talk_hub'])
    ? $notification_summary['talk_hub']
    : array();
$push_enabled = function_exists('eottae_push_enabled') && eottae_push_enabled();
$push_configured = function_exists('eottae_push_is_configured') && eottae_push_is_configured();
$push_subscriptions = function_exists('eottae_push_member_subscription_count') ? eottae_push_member_subscription_count($member['mb_id']) : 0;
$talk_notifications = function_exists('eottae_talkroom_notify_list') ? eottae_talkroom_notify_list($member['mb_id'], 8, 0) : array();
$talk_notify_token = function_exists('eottae_talkroom_member_token') ? eottae_talkroom_member_token() : '';
$notification_groups = array(
    array(
        'id' => 'personal',
        'label' => '개인 알림',
        'desc' => '쪽지, 댓글, 세부톡 활동',
        'count' => $notification_total,
    ),
    array(
        'id' => 'app',
        'label' => '앱 상태',
        'desc' => $push_subscriptions > 0 ? '이 기기 알림 등록됨' : '앱 알림 권한 확인',
        'count' => $push_subscriptions,
    ),
    array(
        'id' => 'talk',
        'label' => '세부톡',
        'desc' => '톡방 알림과 읽음 처리',
        'count' => count($talk_notifications),
    ),
);

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

    <nav class="notification-hub-category" aria-label="알림 분류">
        <?php foreach ($notification_groups as $group) { ?>
        <a href="#notification-<?php echo get_text($group['id']); ?>" class="<?php echo (int) ($group['count'] ?? 0) > 0 ? 'is-alert' : ''; ?>">
            <span><?php echo get_text($group['label']); ?></span>
            <strong><?php echo number_format((int) ($group['count'] ?? 0)); ?></strong>
            <em><?php echo get_text($group['desc']); ?></em>
        </a>
        <?php } ?>
    </nav>

    <section class="notification-hub-page__grid" id="notification-personal" aria-label="알림 요약">
        <?php foreach ($notification_items as $item) { ?>
        <a href="<?php echo get_text($item['href'] ?? '#'); ?>" class="notification-hub-card<?php echo (int) ($item['count'] ?? 0) > 0 ? ' is-alert' : ''; ?>">
            <span class="notification-hub-card__label"><?php echo get_text($item['label'] ?? '알림'); ?></span>
            <strong class="notification-hub-card__count"><?php echo number_format((int) ($item['count'] ?? 0)); ?></strong>
            <span class="notification-hub-card__desc"><?php echo get_text($item['description'] ?? ''); ?></span>
            <span class="notification-hub-card__cta">확인하기</span>
        </a>
        <?php } ?>
    </section>

    <section class="notification-hub-breakdown" id="notification-app" aria-labelledby="notification-app-title">
        <h2 id="notification-app-title">앱 알림 상태</h2>
        <dl>
            <div>
                <dt>푸시 기능</dt>
                <dd><?php echo $push_enabled ? 'ON' : 'OFF'; ?></dd>
            </div>
            <div>
                <dt>운영 키</dt>
                <dd><?php echo $push_configured ? '설정됨' : '설정 필요'; ?></dd>
            </div>
            <div>
                <dt>등록 기기</dt>
                <dd><?php echo number_format($push_subscriptions); ?>대</dd>
            </div>
            <div>
                <dt>로그인 유지</dt>
                <dd>자동로그인 기반</dd>
            </div>
        </dl>
        <p class="notification-hub-page__lead" style="margin-top:10px">
            앱에서 로그인하면 자동로그인이 기본 적용됩니다. 푸시 알림은 앱 실행 후 알림 권한을 허용하면 이 기기에 등록됩니다.
            <?php if (!$push_configured) { ?>운영 서버의 <code>_site.config.local.php</code>에 VAPID 키를 넣으면 실제 푸시 발송이 활성화됩니다.<?php } ?>
        </p>
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

    <section class="notification-hub-latest" id="notification-talk" aria-labelledby="notification-talk-latest-title">
        <div style="display:flex;align-items:center;justify-content:space-between;gap:10px">
            <h2 id="notification-talk-latest-title">최근 세부톡 알림</h2>
            <?php if (!empty($talk_notifications)) { ?>
            <form method="post" action="<?php echo G5_URL; ?>/proc/eottae-talkroom-notifications.php" data-notification-mark-all-form>
                <input type="hidden" name="action" value="mark_all">
                <input type="hidden" name="eottae_talkroom_member_token" value="<?php echo get_text($talk_notify_token); ?>">
                <button type="submit" class="sebu-column-btn sebu-column-btn--outline sebu-column-btn--sm">전체 읽음</button>
            </form>
            <?php } ?>
        </div>
        <?php if (empty($talk_notifications)) { ?>
        <p class="notification-hub-page__lead">아직 세부톡 알림이 없습니다.</p>
        <?php } else { ?>
        <div class="notification-hub-talk-list">
            <?php foreach ($talk_notifications as $notice) { ?>
            <a href="<?php echo get_text($notice['href'] ?: '#'); ?>" class="notification-hub-latest__link<?php echo empty($notice['is_read']) ? ' is-alert' : ''; ?>">
                <span><?php echo get_text($notice['type_label'] ?? '세부톡'); ?><?php echo !empty($notice['room_name']) ? ' · '.get_text($notice['room_name']) : ''; ?></span>
                <strong><?php echo get_text($notice['title'] ?? '알림'); ?></strong>
                <em><?php echo get_text($notice['message'] ?? ''); ?> · <?php echo get_text($notice['time_label'] ?? ''); ?></em>
            </a>
            <?php } ?>
        </div>
        <?php } ?>
    </section>

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
<script>
(function () {
    var form = document.querySelector('[data-notification-mark-all-form]');
    if (!form) {
        return;
    }
    form.addEventListener('submit', function (event) {
        event.preventDefault();
        var data = new FormData(form);
        fetch(form.action, {
            method: 'POST',
            credentials: 'same-origin',
            body: data
        }).then(function (response) {
            return response.json();
        }).then(function () {
            window.location.reload();
        }).catch(function () {
            form.submit();
        });
    });
})();
</script>
<?php
g5_page_end();
