<?php
include_once(dirname(__FILE__).'/_init.php');
include_once G5_LIB_PATH.'/eottae-calendar.lib.php';
include_once G5_PATH.'/components/eottae/calendar-event-form.php';

if (!$is_member) {
    alert('로그인 후 일정을 등록할 수 있습니다.', function_exists('eottae_login_url') ? eottae_login_url(eottae_calendar_create_url()) : G5_BBS_URL.'/login.php');
}

$from = isset($_GET['from']) ? preg_replace('/[^a-z_]/', '', (string) $_GET['from']) : '';
$room_id = isset($_GET['room_id']) ? (int) $_GET['room_id'] : 0;
$is_super = ($is_admin === 'super');

if ($room_id > 0 && in_array($from, array('talk', 'talk_post'), true)) {
    if (!eottae_calendar_can_create_from_talk($room_id, $member['mb_id'] ?? '', $is_super)) {
        alert('해당 톡방 참여자만 일정을 등록할 수 있습니다.', eottae_calendar_list_url());
    }
}

$token = eottae_calendar_member_token();
$form_action = G5_URL.'/proc/eottae-calendar.php';
$old = array(
    'title'            => isset($_GET['title']) ? get_text((string) $_GET['title']) : '',
    'description'      => isset($_GET['description']) ? get_text((string) $_GET['description']) : '',
    'start_date'       => date('Y-m-d'),
    'end_date'         => date('Y-m-d'),
    'start_time'       => '',
    'end_time'         => '',
    'is_all_day'       => 0,
    'location'         => '',
    'area'             => 'cebu_city',
    'category'         => 'event',
    'badge_style'      => 'default',
    'related_url'      => '',
    'related_post_url' => isset($_GET['related_post_url']) ? get_text((string) $_GET['related_post_url']) : '',
    'related_room_id'  => $room_id,
);

if (in_array($from, array('talk', 'talk_post'), true)) {
    $old['category'] = isset($_GET['category']) ? preg_replace('/[^a-z_]/', '', (string) $_GET['category']) : 'talk';
    if ($old['category'] === '') {
        $old['category'] = 'talk';
    }
    $old['badge_style'] = isset($_GET['badge_style']) ? preg_replace('/[^a-z_]/', '', (string) $_GET['badge_style']) : 'recommend';
    if ($old['badge_style'] === '') {
        $old['badge_style'] = 'recommend';
    }
}

$intro = '세부 지역 일정을 등록하면 캘린더에 공개됩니다.';
if ($room_id > 0 && function_exists('eottae_talkroom_get_room')) {
    $room = eottae_talkroom_get_room($room_id);
    if (is_array($room) && !empty($room['room_name'])) {
        $intro = '「'.get_text($room['room_name']).'」 톡방과 연결된 일정을 등록합니다.';
    }
}

g5_page_start('일정 등록');
?>

<main class="mypage-subpage sebu-cal-page sebu-cal-page--form">
    <p class="mypage-subpage__back"><a href="<?php echo eottae_calendar_list_url(); ?>">← 세부어때 캘린더</a></p>
    <h1 class="mypage-subpage__title">일정 등록</h1>
    <p class="sebu-cal-page__intro"><?php echo $intro; ?></p>

    <?php echo eottae_calendar_render_event_form($old, $form_action, $token, '일정 등록', array(
        'from'    => $from,
        'room_id' => $room_id,
    )); ?>
</main>

<?php
g5_page_end();
