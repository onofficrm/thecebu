<?php
include_once(dirname(__FILE__).'/_init.php');

if ($is_admin !== 'super') {
    alert('최고관리자만 이용할 수 있습니다.', G5_URL);
}

include_once G5_LIB_PATH.'/eottae-golf-join.lib.php';
include_once G5_LIB_PATH.'/eottae-talkroom.lib.php';
include_once G5_PATH.'/components/eottae/golf-join-admin-nav.php';

eottae_golf_join_ensure_schema();

$tab = isset($_GET['tab']) ? preg_replace('/[^a-z]/', '', (string) $_GET['tab']) : 'posts';
if (!in_array($tab, array('posts', 'reports', 'courses'), true)) {
    $tab = 'posts';
}

$admin_token = eottae_talkroom_admin_token();
$proc_url = eottae_golf_join_admin_proc_url();
$status_filter = isset($_GET['status']) ? preg_replace('/[^a-z]/', '', (string) $_GET['status']) : 'all';

g5_talk_admin_page_start('골프조인 관리');
?>

<main class="promo-admin-page talk-admin-page">
    <header class="promo-admin-page__header">
        <div class="promo-admin-page__header-top">
            <a href="<?php echo eottae_golf_join_list_url(); ?>" class="promo-admin-page__back">← 골프조인</a>
            <a href="<?php echo G5_ADMIN_URL; ?>/" class="promo-admin-page__back">그누보드 관리자</a>
        </div>
        <h1 class="promo-admin-page__title">골프조인 관리</h1>
        <p class="promo-admin-page__desc">모집글·신고·골프장 데이터를 관리합니다.</p>
        <?php eottae_golf_join_render_admin_nav($tab); ?>
    </header>

    <?php if ($tab === 'posts') { ?>
    <nav class="talk-admin-filter">
        <a href="<?php echo eottae_golf_join_admin_url('posts'); ?>?status=all" class="talk-admin-filter__item<?php echo $status_filter === 'all' ? ' is-active' : ''; ?>">전체</a>
        <a href="<?php echo eottae_golf_join_admin_url('posts'); ?>?status=recruiting" class="talk-admin-filter__item<?php echo $status_filter === 'recruiting' ? ' is-active' : ''; ?>">모집중</a>
        <a href="<?php echo eottae_golf_join_admin_url('posts'); ?>?status=full" class="talk-admin-filter__item<?php echo $status_filter === 'full' ? ' is-active' : ''; ?>">정원마감</a>
        <a href="<?php echo eottae_golf_join_admin_url('posts'); ?>?status=closed" class="talk-admin-filter__item<?php echo $status_filter === 'closed' ? ' is-active' : ''; ?>">종료</a>
        <a href="<?php echo eottae_golf_join_admin_url('posts'); ?>?status=hidden" class="talk-admin-filter__item<?php echo $status_filter === 'hidden' ? ' is-active' : ''; ?>">숨김</a>
    </nav>
    <?php
    $posts = eottae_golf_join_admin_list_posts($status_filter);
    ?>
    <section class="promo-admin-panel talk-admin-panel">
        <?php if (empty($posts)) { ?>
        <p class="promo-admin-empty">표시할 모집글이 없습니다.</p>
        <?php } else { ?>
        <div class="talk-admin-table-wrap">
            <table class="talk-admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>골프장</th>
                        <th>지역</th>
                        <th>방장</th>
                        <th>인원</th>
                        <th>상태</th>
                        <th>등록일</th>
                        <th>관리</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($posts as $item) { ?>
                    <tr>
                        <td data-label="ID"><?php echo (int) $item['id']; ?></td>
                        <td data-label="골프장">
                            <a href="<?php echo $item['detail_url']; ?>" target="_blank" rel="noopener"><?php echo get_text($item['golf_course_name']); ?></a>
                            <?php if ($item['title'] !== '') { ?><div class="talk-report-list__meta"><?php echo get_text($item['title']); ?></div><?php } ?>
                        </td>
                        <td data-label="지역"><?php echo get_text($item['region_label']); ?></td>
                        <td data-label="방장"><?php echo get_text($item['host_nickname']); ?></td>
                        <td data-label="인원"><?php echo (int) $item['current_count']; ?>/<?php echo (int) $item['recruit_count']; ?></td>
                        <td data-label="상태"><?php echo get_text($item['status_label']); ?><?php if (!empty($item['is_hidden'])) { ?> <span class="talk-report-list__meta">숨김</span><?php } ?></td>
                        <td data-label="등록일"><?php echo substr($item['created_at'], 0, 16); ?></td>
                        <td data-label="관리" class="talk-admin-table__actions">
                            <select class="golf-admin-status-select" data-join-id="<?php echo (int) $item['id']; ?>">
                                <?php foreach (eottae_golf_join_post_status_options() as $code => $label) { ?>
                                <option value="<?php echo get_text($code); ?>"<?php echo $item['status'] === $code ? ' selected' : ''; ?>><?php echo get_text($label); ?></option>
                                <?php } ?>
                            </select>
                            <?php if (empty($item['is_hidden'])) { ?>
                            <button type="button" class="promo-admin-btn promo-admin-btn--sm" data-golf-hide="<?php echo (int) $item['id']; ?>">숨김</button>
                            <?php } else { ?>
                            <button type="button" class="promo-admin-btn promo-admin-btn--sm" data-golf-restore="<?php echo (int) $item['id']; ?>">복구</button>
                            <?php } ?>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
        <?php } ?>
    </section>

    <?php } elseif ($tab === 'reports') { ?>
    <?php
    $report_status = isset($_GET['status']) ? preg_replace('/[^a-z]/', '', (string) $_GET['status']) : 'pending';
    $reports = eottae_golf_join_admin_list_reports($report_status === 'all' ? 'all' : $report_status);
    ?>
    <nav class="talk-admin-filter">
        <a href="<?php echo eottae_golf_join_admin_url('reports'); ?>?status=pending" class="talk-admin-filter__item<?php echo $report_status === 'pending' ? ' is-active' : ''; ?>">접수</a>
        <a href="<?php echo eottae_golf_join_admin_url('reports'); ?>?status=resolved" class="talk-admin-filter__item<?php echo $report_status === 'resolved' ? ' is-active' : ''; ?>">처리완료</a>
        <a href="<?php echo eottae_golf_join_admin_url('reports'); ?>?status=all" class="talk-admin-filter__item<?php echo $report_status === 'all' ? ' is-active' : ''; ?>">전체</a>
    </nav>
    <section class="promo-admin-panel talk-admin-panel">
        <?php if (empty($reports)) { ?>
        <p class="promo-admin-empty">표시할 신고가 없습니다.</p>
        <?php } else { ?>
        <div class="talk-admin-table-wrap">
            <table class="talk-admin-table">
                <thead>
                    <tr>
                        <th>신고일</th>
                        <th>조인</th>
                        <th>사유</th>
                        <th>신고자</th>
                        <th>상태</th>
                        <th>관리</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($reports as $item) { ?>
                    <tr>
                        <td data-label="신고일"><?php echo substr($item['created_at'], 0, 16); ?></td>
                        <td data-label="조인"><a href="<?php echo $item['detail_url']; ?>" target="_blank" rel="noopener"><?php echo get_text($item['post_title']); ?></a></td>
                        <td data-label="사유"><?php echo get_text($item['reason']); ?><?php if ($item['memo'] !== '') { ?><div class="talk-report-list__meta"><?php echo get_text($item['memo']); ?></div><?php } ?></td>
                        <td data-label="신고자"><?php echo get_text($item['reporter_nick']); ?></td>
                        <td data-label="상태"><?php echo get_text($item['status']); ?></td>
                        <td data-label="관리">
                            <?php if ($item['status'] === 'pending') { ?>
                            <button type="button" class="promo-admin-btn promo-admin-btn--sm promo-admin-btn--primary" data-golf-report-resolve="<?php echo (int) $item['id']; ?>">처리완료</button>
                            <?php } ?>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
        <?php } ?>
    </section>

    <?php } else { ?>
    <?php $courses = eottae_golf_join_admin_list_courses_all(); ?>
    <section class="promo-admin-panel talk-admin-panel">
        <h2 class="promo-admin-panel__title">골프장 추가</h2>
        <form class="golf-admin-course-form" id="golf-admin-course-form">
            <input type="hidden" name="course_id" id="golf-admin-course-id" value="0">
            <p><label>지역
                <select name="region" required>
                    <?php foreach (eottae_golf_join_region_options() as $code => $label) { ?>
                    <option value="<?php echo get_text($code); ?>"><?php echo get_text($label); ?></option>
                    <?php } ?>
                </select>
            </label></p>
            <p><label>골프장명 <input type="text" name="name" required maxlength="120"></label></p>
            <p><label>주소 <input type="text" name="address" maxlength="255"></label></p>
            <p><label><input type="checkbox" name="is_active" value="1" checked> 노출</label></p>
            <button type="submit" class="promo-admin-btn promo-admin-btn--primary">저장</button>
        </form>

        <h2 class="promo-admin-panel__title">골프장 목록</h2>
        <div class="talk-admin-table-wrap">
            <table class="talk-admin-table">
                <thead>
                    <tr><th>지역</th><th>골프장</th><th>노출</th><th>관리</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($courses as $c) { ?>
                    <tr>
                        <td><?php echo get_text($c['region_label']); ?></td>
                        <td><?php echo get_text($c['name']); ?></td>
                        <td><?php echo get_text($c['is_active_label']); ?></td>
                        <td><button type="button" class="promo-admin-btn promo-admin-btn--sm" data-golf-course-edit="<?php echo (int) $c['id']; ?>"
                            data-region="<?php echo get_text($c['region']); ?>"
                            data-name="<?php echo get_text($c['name']); ?>"
                            data-address="<?php echo get_text($c['address']); ?>"
                            data-active="<?php echo (int) ($c['is_active'] ?? 0); ?>">수정</button></td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </section>
    <?php } ?>
</main>

<script>
window.EOTTaeGolfJoinAdmin = {
    procUrl: <?php echo json_encode($proc_url, JSON_UNESCAPED_UNICODE); ?>,
    adminToken: <?php echo json_encode($admin_token, JSON_UNESCAPED_UNICODE); ?>
};
</script>
<?php
add_javascript('<script src="'.G5_JS_URL.'/eottae-golf-join-admin.js" defer></script>', 25);
g5_talk_admin_page_end();
