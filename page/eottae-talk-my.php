<?php
include_once(dirname(__FILE__).'/_init.php');
include_once G5_LIB_PATH.'/eottae-talkroom.lib.php';
include_once G5_PATH.'/components/eottae/talk-my-section.php';

if (!$is_member) {
    alert('로그인 후 이용해 주세요.', eottae_login_url(eottae_talkroom_my_url()));
}

$my = eottae_talkroom_list_my_rooms($member['mb_id']);
$my['created'] = eottae_talkroom_apply_card_viewer_context(
    $my['created'],
    (string) $member['mb_id'],
    ($is_admin === 'super')
);

eottae_talkroom_enqueue_card_delete_assets();

g5_page_start('내 톡방');
?>

<main class="mypage-subpage talk-my-page">
    <p class="mypage-subpage__back"><a href="<?php echo eottae_talkroom_list_url(); ?>">← 세부톡방</a></p>
    <h1 class="mypage-subpage__title">내 톡방</h1>
    <p class="talk-my-page__intro">참여 중인 톡방, 내가 만든 톡방, 승인 대기 중인 톡방을 확인할 수 있습니다.</p>

    <div class="talk-my-page__links">
        <a href="<?php echo eottae_talkroom_create_url(); ?>" class="talk-page__btn talk-page__btn--primary">톡방 만들기</a>
        <a href="<?php echo eottae_talkroom_apply_status_url(); ?>" class="talk-page__btn">개설 신청 현황</a>
    </div>

    <?php
    eottae_talkroom_render_my_section(
        '내가 만든 톡방',
        $my['created'],
        '승인된 내 톡방이 없습니다.',
        count($my['created']) > 0 ? (string) count($my['created']) : ''
    );
    eottae_talkroom_render_my_section(
        '참여 중인 톡방',
        $my['joined'],
        '참여 중인 다른 톡방이 없습니다.',
        count($my['joined']) > 0 ? (string) count($my['joined']) : ''
    );
    eottae_talkroom_render_my_section(
        '참여 승인 대기',
        $my['pending'],
        '승인 대기 중인 톡방이 없습니다.',
        count($my['pending']) > 0 ? (string) count($my['pending']) : ''
    );
    ?>
</main>

<?php
g5_page_end();
