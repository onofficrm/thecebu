<?php
include_once(dirname(__FILE__).'/_init.php');
include_once G5_LIB_PATH.'/eottae-talkroom.lib.php';

if (!$is_member) {
    alert('로그인 후 신청 현황을 확인할 수 있습니다.', eottae_login_url(eottae_talkroom_apply_status_url()));
}

$submitted = !empty($_GET['submitted']);
$applications = eottae_talkroom_list_my_applications($member['mb_id']);

g5_page_start('개설 신청 현황');
?>

<main class="mypage-subpage talk-applies-page">
    <p class="mypage-subpage__back"><a href="<?php echo eottae_talkroom_list_url(); ?>">← 세부톡방</a></p>
    <h1 class="mypage-subpage__title">개설 신청 현황</h1>
    <p class="talk-applies-page__intro">내가 신청한 톡방 개설 내역입니다. 승인 결과는 이 화면에서 확인할 수 있습니다.</p>

    <?php if ($submitted) { ?>
    <div class="talk-applies-page__success" role="status">
        <strong>톡방 개설 신청이 접수되었습니다.</strong>
        <p>최고관리자 승인 후 세부톡방 목록에 노출됩니다.</p>
    </div>
    <?php } ?>

    <div class="talk-applies-page__actions">
        <a href="<?php echo eottae_talkroom_create_url(); ?>" class="talk-page__btn talk-page__btn--primary">톡방 만들기</a>
        <a href="<?php echo eottae_talkroom_list_url(); ?>" class="talk-page__btn">톡방 목록</a>
    </div>

    <?php if (empty($applications)) { ?>
    <div class="empty-state talk-applies-page__empty">
        <p class="empty-state__title">신청한 톡방이 없습니다.</p>
        <p>새 톡방 개설을 신청해 보세요.</p>
    </div>
    <?php } else { ?>
    <div class="talk-applies-list">
        <?php foreach ($applications as $item) { ?>
        <article class="talk-applies-item">
            <div class="talk-applies-item__head">
                <span class="talk-applies-item__emoji" aria-hidden="true"><?php echo $item['emoji']; ?></span>
                <div class="talk-applies-item__title-wrap">
                    <h2 class="talk-applies-item__title"><?php echo $item['room_name']; ?></h2>
                    <p class="talk-applies-item__desc"><?php echo $item['room_description']; ?></p>
                </div>
                <span class="talk-apply-status <?php echo $item['status_class']; ?>"><?php echo $item['status_label']; ?></span>
            </div>
            <dl class="talk-applies-item__meta">
                <div class="talk-applies-item__meta-row">
                    <dt>카테고리</dt>
                    <dd><?php echo $item['category']; ?></dd>
                </div>
                <div class="talk-applies-item__meta-row">
                    <dt>공개</dt>
                    <dd><?php echo $item['visibility_label']; ?></dd>
                </div>
                <div class="talk-applies-item__meta-row">
                    <dt>가입</dt>
                    <dd><?php echo $item['join_type_label']; ?></dd>
                </div>
                <div class="talk-applies-item__meta-row">
                    <dt>신청일</dt>
                    <dd><?php echo $item['created_label'] ?: $item['created_at']; ?></dd>
                </div>
            </dl>
            <?php if ($item['status'] === 'rejected' && $item['reject_reason'] !== '') { ?>
            <div class="talk-applies-item__reject">
                <strong>반려 사유</strong>
                <p><?php echo $item['reject_reason']; ?></p>
            </div>
            <?php } ?>
        </article>
        <?php } ?>
    </div>
    <?php } ?>
</main>

<?php
g5_page_end();
