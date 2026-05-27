<?php
include_once(dirname(__FILE__).'/_init.php');

if ($is_admin !== 'super') {
    alert('최고관리자만 이용할 수 있습니다.', G5_URL);
}

include_once G5_LIB_PATH.'/eottae-talkroom.lib.php';
include_once G5_PATH.'/components/eottae/talk-admin-nav.php';

if (function_exists('eottae_talkroom_ensure_schema')) {
    eottae_talkroom_ensure_schema();
}

$status_filter = isset($_GET['status']) ? trim((string) $_GET['status']) : 'all';
$allowed_filters = array('all', 'active', 'stopped', 'rejected', 'closed');
if (!in_array($status_filter, $allowed_filters, true)) {
    $status_filter = 'all';
}

$rooms = eottae_talkroom_admin_list_rooms(200, $status_filter);
$admin_token = eottae_talkroom_admin_token();

g5_talk_admin_page_start('톡방 목록 관리');
?>

<main class="promo-admin-page talk-admin-page">
    <header class="promo-admin-page__header">
        <div class="promo-admin-page__header-top">
            <a href="<?php echo eottae_talkroom_list_url(); ?>" class="promo-admin-page__back">← 세부톡방</a>
            <a href="<?php echo G5_ADMIN_URL; ?>/" class="promo-admin-page__back">그누보드 관리자</a>
        </div>
        <h1 class="promo-admin-page__title">톡방 목록</h1>
        <p class="promo-admin-page__desc">개설된 톡방을 상태별로 조회하고, 운영중지·AI 설정을 관리합니다. 톡방은 개설 즉시 이용할 수 있습니다.</p>
        <?php eottae_talkroom_render_admin_nav('rooms'); ?>
    </header>

    <section class="promo-admin-panel talk-admin-panel talk-admin-applies__panel talk-admin-rooms__panel" aria-label="톡방 목록">
        <nav class="talk-admin-filter talk-admin-applies__filter" aria-label="톡방 상태 필터">
            <a href="<?php echo eottae_talkroom_admin_rooms_url('all'); ?>" class="talk-admin-filter__item<?php echo $status_filter === 'all' ? ' is-active' : ''; ?>">전체</a>
            <a href="<?php echo eottae_talkroom_admin_rooms_url('active'); ?>" class="talk-admin-filter__item<?php echo $status_filter === 'active' ? ' is-active' : ''; ?>">운영중</a>
            <a href="<?php echo eottae_talkroom_admin_rooms_url('stopped'); ?>" class="talk-admin-filter__item<?php echo $status_filter === 'stopped' ? ' is-active' : ''; ?>">운영중지</a>
            <a href="<?php echo eottae_talkroom_admin_rooms_url('rejected'); ?>" class="talk-admin-filter__item<?php echo $status_filter === 'rejected' ? ' is-active' : ''; ?>">반려</a>
            <a href="<?php echo eottae_talkroom_admin_rooms_url('closed'); ?>" class="talk-admin-filter__item<?php echo $status_filter === 'closed' ? ' is-active' : ''; ?>">종료</a>
        </nav>

        <?php if (empty($rooms)) { ?>
        <div class="talk-admin-applies__empty">
            <p class="promo-admin-empty">표시할 톡방이 없습니다.</p>
            <p class="talk-admin-applies__empty-hint">톡방이 개설되면 이 목록에 표시됩니다. 상태 필터를 바꿔 보세요.</p>
        </div>
        <?php } else { ?>
        <p class="talk-admin-applies__summary">총 <strong><?php echo number_format(count($rooms)); ?></strong>건</p>
        <div class="talk-admin-table-wrap">
            <table class="talk-admin-table">
                <thead>
                    <tr>
                        <th scope="col">개설일</th>
                        <th scope="col">톡방</th>
                        <th scope="col">방장</th>
                        <th scope="col">카테고리</th>
                        <th scope="col">공개</th>
                        <th scope="col">상태</th>
                        <th scope="col">관리</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rooms as $item) { ?>
                    <tr>
                        <td data-label="개설일">
                            <?php echo $item['created_at'] !== '0000-00-00 00:00:00' ? substr($item['created_at'], 0, 16) : '-'; ?>
                        </td>
                        <td data-label="톡방"><?php echo $item['emoji']; ?> <?php echo $item['room_name']; ?></td>
                        <td data-label="방장"><?php echo $item['owner_nick']; ?> (<?php echo $item['owner_mb_id']; ?>)</td>
                        <td data-label="카테고리"><?php echo $item['category']; ?></td>
                        <td data-label="공개"><?php echo $item['visibility_label']; ?></td>
                        <td data-label="상태"><span class="talk-apply-status <?php echo $item['status_class']; ?>"><?php echo $item['status_label']; ?></span></td>
                        <td data-label="관리" class="talk-admin-table__actions">
                            <a href="<?php echo $item['detail_url']; ?>" class="promo-admin-btn promo-admin-btn--sm">상세</a>
                            <?php if (in_array($item['status'], array('approved', 'active'), true)) { ?>
                            <?php if (function_exists('eottae_talkroom_ai_settings_url')) {
                                include_once G5_LIB_PATH.'/eottae-talkroom-ai.lib.php';
                                ?>
                            <a href="<?php echo eottae_talkroom_ai_settings_url((int) $item['room_id']); ?>" class="promo-admin-btn promo-admin-btn--sm">AI 설정</a>
                            <?php } ?>
                            <button type="button" class="promo-admin-btn promo-admin-btn--sm" data-talk-stop="<?php echo (int) $item['room_id']; ?>">운영중지</button>
                            <?php } ?>
                            <button type="button" class="promo-admin-btn promo-admin-btn--sm promo-admin-btn--danger" data-talk-delete="<?php echo (int) $item['room_id']; ?>" data-room-name="<?php echo htmlspecialchars($item['room_name'], ENT_QUOTES, 'UTF-8'); ?>">삭제</button>
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
g5_talk_admin_page_end();
