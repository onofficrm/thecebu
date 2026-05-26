<?php
include_once(dirname(__FILE__).'/_init.php');
include_once G5_LIB_PATH.'/eottae-calendar.lib.php';
include_once G5_PATH.'/components/eottae/calendar-event-form.php';

$event_id = isset($_GET['event_id']) ? (int) $_GET['event_id'] : 0;
$event = eottae_calendar_get_event($event_id);

if (!$event) {
    alert('일정을 찾을 수 없습니다.', eottae_calendar_list_url());
}

$is_super = ($is_admin === 'super');
if (!$is_member || !eottae_calendar_can_edit_event($event, $member['mb_id'] ?? '', $is_super)) {
    alert('수정 권한이 없습니다.', eottae_calendar_event_url($event_id));
}

$token = eottae_calendar_member_token();
$form_action = G5_URL.'/proc/eottae-calendar.php';

$old = array(
    'title'           => $event['title'] ?? '',
    'description'     => $event['description'] ?? '',
    'start_date'      => $event['start_date'] ?? '',
    'end_date'        => $event['end_date'] ?? '',
    'start_time'      => $event['start_time'] ?? '',
    'end_time'        => $event['end_time'] ?? '',
    'is_all_day'      => (int) ($event['is_all_day'] ?? 0),
    'location'        => $event['location'] ?? '',
    'area'            => $event['area'] ?? 'etc',
    'category'        => $event['category'] ?? 'etc',
    'badge_style'     => $event['badge_style'] ?? 'default',
    'related_url'     => $event['related_url'] ?? '',
    'related_post_url'=> $event['related_post_url'] ?? '',
    'related_room_id' => (int) ($event['related_room_id'] ?? 0),
);

g5_page_start('일정 수정');
?>

<main class="mypage-subpage sebu-cal-page sebu-cal-page--form">
    <p class="mypage-subpage__back"><a href="<?php echo eottae_calendar_event_url($event_id); ?>">← 일정 상세</a></p>
    <h1 class="mypage-subpage__title">일정 수정</h1>

    <?php echo eottae_calendar_render_event_form($old, $form_action, $token, '수정 저장', array(
        'action'   => 'update',
        'event_id' => $event_id,
    )); ?>
</main>

<?php
g5_page_end();
