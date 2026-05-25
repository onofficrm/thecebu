<?php
include_once(dirname(__FILE__).'/_init.php');
include_once G5_LIB_PATH.'/eottae-talkroom.lib.php';
include_once G5_LIB_PATH.'/eottae-talkroom-dashboard.lib.php';
include_once G5_PATH.'/components/eottae/talk-mypage-dashboard.php';

if (is_file(G5_LIB_PATH.'/eottae-talkroom-ai.lib.php')) {
    include_once G5_LIB_PATH.'/eottae-talkroom-ai.lib.php';
}

$mypage_talk_url = function_exists('eottae_mypage_talk_url')
    ? eottae_mypage_talk_url()
    : G5_URL.'/page/eottae-mypage-talk.php';

if (!$is_member) {
    alert('로그인 후 이용해 주세요.', eottae_login_url($mypage_talk_url));
}

$feed_options = array(
    'room_id' => isset($_GET['feed_room']) ? (int) $_GET['feed_room'] : 0,
    'type'    => isset($_GET['feed_type']) ? trim(strip_tags((string) $_GET['feed_type'])) : '',
    'offset'  => 0,
    'limit'   => function_exists('eottae_talkroom_dashboard_feed_default_limit')
        ? eottae_talkroom_dashboard_feed_default_limit()
        : 20,
);

$ctx = eottae_talkroom_dashboard_build_context($member['mb_id'], $feed_options);

add_stylesheet('<link rel="stylesheet" href="'.G5_CSS_URL.'/eottae-my-talk.css">', 22);

g5_page_start('내 세부톡방');
?>

<main class="mypage-subpage my-talk-page">
    <?php eottae_render_mypage_back(); ?>
    <h1 class="mypage-subpage__title">내 세부톡방</h1>
    <p class="my-talk-page__intro">참여 중인 톡방 활동을 한 화면에서 관리하세요.</p>

    <?php eottae_talkroom_render_mypage_dashboard($ctx); ?>
</main>

<?php
g5_page_end();
