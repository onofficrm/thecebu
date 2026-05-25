<?php
include_once(dirname(__FILE__).'/_init.php');

if ($is_admin !== 'super') {
    alert('최고관리자만 이용할 수 있습니다.', G5_URL);
}

include_once G5_LIB_PATH.'/eottae-talkroom.lib.php';
include_once G5_PATH.'/components/eottae/talk-admin-nav.php';

$filter = isset($_GET['status']) ? preg_replace('/[^a-z_]/', '', (string) $_GET['status']) : 'pending';
if (!in_array($filter, array('pending', 'all', 'rejected', 'approved', 'stopped'), true)) {
    $filter = 'pending';
}

$applications = eottae_talkroom_admin_list_applications($filter === 'all' ? 'all' : $filter, 200);
$pending_count = eottae_talkroom_pending_count();
$admin_token = eottae_talkroom_admin_token();

g5_page_start('개설 신청 관리');
?>

<main class="promo-admin-page talk-admin-page">
    <header class="promo-admin-page__header">
        <div class="promo-admin-page__header-top">
            <a href="<?php echo eottae_talkroom_list_url(); ?>" class="promo-admin-page__back">← 세부톡방</a>
            <a href="<?php echo G5_ADMIN_URL; ?>/" class="promo-admin-page__back">그누보드 관리자</a>
        </div>
        <h1 class="promo-admin-page__title">개설 신청 관리</h1>
        <p class="promo-admin-page__desc">
            회원 톡방 개설 신청을 검토하고 승인 또는 반려합니다.
            <?php if ($pending_count > 0) { ?>
            <strong class="talk-admin-page__pending">승인 대기 <?php echo number_format($pending_count); ?>건</strong>
            <?php } ?>
        </p>
        <?php eottae_talkroom_render_admin_nav('applies'); ?>
    </header>

    <nav class="talk-admin-filter" aria-label="신청 상태 필터">
        <a href="<?php echo eottae_talkroom_admin_applies_url(); ?>?status=pending" class="talk-admin-filter__item<?php echo $filter === 'pending' ? ' is-active' : ''; ?>">승인대기</a>
        <a href="<?php echo eottae_talkroom_admin_applies_url(); ?>?status=all" class="talk-admin-filter__item<?php echo $filter === 'all' ? ' is-active' : ''; ?>">전체</a>
        <a href="<?php echo eottae_talkroom_admin_applies_url(); ?>?status=rejected" class="talk-admin-filter__item<?php echo $filter === 'rejected' ? ' is-active' : ''; ?>">반려</a>
    </nav>

    <section class="promo-admin-panel talk-admin-panel">
        <?php if (empty($applications)) { ?>
        <p class="promo-admin-empty">표시할 신청 내역이 없습니다.</p>
        <?php } else { ?>
        <div class="talk-admin-table-wrap">
            <table class="talk-admin-table">
                <thead>
                    <tr>
                        <th scope="col">신청일</th>
                        <th scope="col">신청자</th>
                        <th scope="col">톡방 이름</th>
                        <th scope="col">카테고리</th>
                        <th scope="col">공개</th>
                        <th scope="col">가입</th>
                        <th scope="col">상태</th>
                        <th scope="col">관리</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($applications as $item) { ?>
                    <tr>
                        <td data-label="신청일"><?php echo $item['created_at'] !== '0000-00-00 00:00:00' ? substr($item['created_at'], 0, 16) : '-'; ?></td>
                        <td data-label="신청자">
                            <?php echo $item['owner_nick']; ?><br>
                            <span class="talk-admin-table__sub"><?php echo $item['owner_mb_id']; ?></span>
                        </td>
                        <td data-label="톡방"><?php echo $item['emoji']; ?> <?php echo $item['room_name']; ?></td>
                        <td data-label="카테고리"><?php echo $item['category']; ?></td>
                        <td data-label="공개"><?php echo $item['visibility_label']; ?></td>
                        <td data-label="가입"><?php echo $item['join_type_label']; ?></td>
                        <td data-label="상태"><span class="talk-apply-status <?php echo $item['status_class']; ?>"><?php echo $item['status_label']; ?></span></td>
                        <td data-label="관리" class="talk-admin-table__actions">
                            <a href="<?php echo $item['detail_url']; ?>" class="promo-admin-btn promo-admin-btn--sm">상세</a>
                            <?php if ($item['status'] === 'pending') { ?>
                            <button type="button" class="promo-admin-btn promo-admin-btn--sm promo-admin-btn--primary" data-talk-approve="<?php echo (int) $item['room_id']; ?>">승인</button>
                            <button type="button" class="promo-admin-btn promo-admin-btn--sm" data-talk-reject="<?php echo (int) $item['room_id']; ?>">반려</button>
                            <?php } ?>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
        <?php } ?>
    </section>
</main>

<?php
eottae_talkroom_render_admin_actions_script($admin_token);
g5_page_end();
