<?php
include_once(dirname(__FILE__).'/_init.php');

if ($is_admin !== 'super') {
    alert('최고관리자만 이용할 수 있습니다.', G5_URL);
}

include_once G5_LIB_PATH.'/eottae-talkroom.lib.php';
include_once G5_LIB_PATH.'/eottae-talkroom-ai.lib.php';
include_once G5_PATH.'/components/eottae/talk-admin-nav.php';

$rooms = eottae_talkroom_admin_list_rooms(200);
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
        <p class="promo-admin-page__desc">승인된 톡방을 관리합니다. 운영중지 시 일반 세부톡방 목록에서 숨겨집니다.</p>
        <?php eottae_talkroom_render_admin_nav('rooms'); ?>
    </header>

    <section class="promo-admin-panel talk-admin-panel">
        <?php if (empty($rooms)) { ?>
        <p class="promo-admin-empty">운영 중이거나 중지된 톡방이 없습니다.</p>
        <?php } else { ?>
        <div class="talk-admin-table-wrap">
            <table class="talk-admin-table">
                <thead>
                    <tr>
                        <th scope="col">승인일</th>
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
                        <td data-label="승인일"><?php echo $item['approved_at'] !== '0000-00-00 00:00:00' ? substr($item['approved_at'], 0, 16) : '-'; ?></td>
                        <td data-label="톡방"><?php echo $item['emoji']; ?> <?php echo $item['room_name']; ?></td>
                        <td data-label="방장"><?php echo $item['owner_nick']; ?> (<?php echo $item['owner_mb_id']; ?>)</td>
                        <td data-label="카테고리"><?php echo $item['category']; ?></td>
                        <td data-label="공개"><?php echo $item['visibility_label']; ?></td>
                        <td data-label="상태"><span class="talk-apply-status <?php echo $item['status_class']; ?>"><?php echo $item['status_label']; ?></span></td>
                        <td data-label="관리" class="talk-admin-table__actions">
                            <a href="<?php echo $item['detail_url']; ?>" class="promo-admin-btn promo-admin-btn--sm">상세</a>
                            <a href="<?php echo eottae_talkroom_ai_settings_url((int) $item['room_id']); ?>" class="promo-admin-btn promo-admin-btn--sm">AI 설정</a>
                            <?php if (in_array($item['status'], array('approved', 'active'), true)) { ?>
                            <button type="button" class="promo-admin-btn promo-admin-btn--sm" data-talk-stop="<?php echo (int) $item['room_id']; ?>">운영중지</button>
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
g5_talk_admin_page_end();
