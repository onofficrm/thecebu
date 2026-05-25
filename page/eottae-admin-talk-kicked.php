<?php
include_once(dirname(__FILE__).'/_init.php');

if ($is_admin !== 'super') {
    alert('최고관리자만 이용할 수 있습니다.', G5_URL);
}

include_once G5_LIB_PATH.'/eottae-talkroom.lib.php';
include_once G5_PATH.'/components/eottae/talk-admin-nav.php';

$kicked_members = eottae_talkroom_admin_list_kicked_members(300);
$admin_token = eottae_talkroom_admin_token();

g5_page_start('강퇴 회원 관리');
?>

<main class="promo-admin-page talk-admin-page">
    <header class="promo-admin-page__header">
        <div class="promo-admin-page__header-top">
            <a href="<?php echo eottae_talkroom_list_url(); ?>" class="promo-admin-page__back">← 세부톡방</a>
            <a href="<?php echo G5_ADMIN_URL; ?>/" class="promo-admin-page__back">그누보드 관리자</a>
        </div>
        <h1 class="promo-admin-page__title">강퇴 회원 관리</h1>
        <p class="promo-admin-page__desc">전체 톡방의 강퇴 회원을 확인하고 강퇴 해제할 수 있습니다.</p>
        <?php eottae_talkroom_render_admin_nav('kicked'); ?>
    </header>

    <section class="promo-admin-panel talk-admin-panel">
        <?php if (empty($kicked_members)) { ?>
        <p class="promo-admin-empty">강퇴된 회원이 없습니다.</p>
        <?php } else { ?>
        <div class="talk-admin-table-wrap">
            <table class="talk-admin-table talk-admin-table--kicked">
                <thead>
                    <tr>
                        <th scope="col">강퇴일</th>
                        <th scope="col">톡방</th>
                        <th scope="col">회원</th>
                        <th scope="col">처리자</th>
                        <th scope="col">재참여</th>
                        <th scope="col">사유</th>
                        <th scope="col">관리</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($kicked_members as $item) { ?>
                    <tr>
                        <td data-label="강퇴일"><?php echo $item['kicked_at'] !== '0000-00-00 00:00:00' ? substr($item['kicked_at'], 0, 16) : '-'; ?></td>
                        <td data-label="톡방">
                            <a href="<?php echo $item['enter_href']; ?>"><?php echo $item['emoji']; ?> <?php echo $item['room_name']; ?></a>
                        </td>
                        <td data-label="회원"><?php echo $item['mb_nick']; ?> <span class="talk-manage-member-list__id">(<?php echo $item['mb_id']; ?>)</span></td>
                        <td data-label="처리자"><?php echo $item['kicked_by_nick']; ?></td>
                        <td data-label="재참여"><?php echo $item['can_rejoin_label']; ?></td>
                        <td data-label="사유" class="talk-admin-table__reason"><?php echo nl2br($item['kicked_reason']); ?></td>
                        <td data-label="관리">
                            <button type="button" class="promo-admin-btn promo-admin-btn--sm promo-admin-btn--primary" data-talk-unkick="<?php echo (int) $item['id']; ?>" data-talk-unkick-room="<?php echo (int) $item['room_id']; ?>">강퇴 해제</button>
                            <a href="<?php echo $item['manage_href']; ?>" class="promo-admin-btn promo-admin-btn--sm">톡방 관리</a>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
        <?php } ?>
    </section>
</main>

<?php eottae_talkroom_render_admin_actions_script($admin_token); ?>

<?php
g5_page_end();
