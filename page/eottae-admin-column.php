<?php
include_once(dirname(__FILE__).'/_init.php');

if ($is_admin !== 'super') {
    alert('최고관리자만 이용할 수 있습니다.', G5_URL);
}

include_once G5_LIB_PATH.'/eottae-column.lib.php';
include_once G5_PATH.'/components/eottae/column-author-profile.php';
include_once G5_LIB_PATH.'/eottae-column-likes.lib.php';
include_once G5_LIB_PATH.'/eottae-column-report.lib.php';
include_once G5_LIB_PATH.'/eottae-talkroom.lib.php';

eottae_column_ensure_schema();
eottae_column_ensure_badges();

$tab = isset($_GET['tab']) ? preg_replace('/[^a-z_]/', '', (string) $_GET['tab']) : 'columns';
if (!in_array($tab, array('columns', 'authors', 'applications', 'monthly', 'reports', 'categories'), true)) {
    $tab = 'columns';
}

$admin_token = eottae_talkroom_admin_token();
$proc_url = eottae_column_admin_proc_url();
$columns = eottae_column_list(array('limit' => 100, 'include_hidden' => true));
$authors = eottae_column_admin_list_authors(isset($_GET['q']) ? (string) $_GET['q'] : '');
$applications = eottae_column_list_applications(isset($_GET['application_status']) ? (string) $_GET['application_status'] : 'pending', 100);
$reports = eottae_column_list_pending_reports(50);
$monthly = eottae_column_get_monthly_columnist();
$categories = eottae_column_category_options();
$statuses = eottae_column_status_options();

add_stylesheet('<link rel="stylesheet" href="'.G5_CSS_URL.'/eottae-column.css">', 24);
add_javascript('<script src="'.G5_JS_URL.'/eottae-column-admin.js" defer></script>', 24);

g5_page_start('생활정보 컬럼 관리');
?>

<main class="sebu-column-admin sebu-column-editorial" data-proc-url="<?php echo get_text($proc_url); ?>" data-admin-token="<?php echo get_text($admin_token); ?>">
    <header class="sebu-column-admin__header">
        <h1 class="sebu-column-admin__title">생활정보 컬럼 관리</h1>
        <p class="sebu-column-admin__desc"><a href="<?php echo eottae_column_list_url(); ?>">컬럼 섹션 보기 →</a></p>
    </header>

    <nav class="sebu-column-admin__tabs" aria-label="관리 메뉴">
        <a href="<?php echo eottae_column_admin_url(array('tab' => 'columns')); ?>" class="sebu-column-admin__tab<?php echo $tab === 'columns' ? ' is-active' : ''; ?>">컬럼 목록</a>
        <a href="<?php echo eottae_column_admin_url(array('tab' => 'authors')); ?>" class="sebu-column-admin__tab<?php echo $tab === 'authors' ? ' is-active' : ''; ?>">칼럼니스트</a>
        <a href="<?php echo eottae_column_admin_url(array('tab' => 'applications')); ?>" class="sebu-column-admin__tab<?php echo $tab === 'applications' ? ' is-active' : ''; ?>">신청 관리<?php
            $pending_tab_count = eottae_column_pending_application_count();
            if ($pending_tab_count > 0) {
                echo ' ('.number_format($pending_tab_count).')';
            }
        ?></a>
        <a href="<?php echo eottae_column_admin_url(array('tab' => 'monthly')); ?>" class="sebu-column-admin__tab<?php echo $tab === 'monthly' ? ' is-active' : ''; ?>">이달의 칼럼니스트</a>
        <a href="<?php echo eottae_column_admin_url(array('tab' => 'reports')); ?>" class="sebu-column-admin__tab<?php echo $tab === 'reports' ? ' is-active' : ''; ?>">신고 관리<?php if (count($reports) > 0) { ?> (<?php echo count($reports); ?>)<?php } ?></a>
        <a href="<?php echo eottae_column_admin_url(array('tab' => 'categories')); ?>" class="sebu-column-admin__tab<?php echo $tab === 'categories' ? ' is-active' : ''; ?>">카테고리</a>
    </nav>

    <?php if ($tab === 'columns') { ?>
    <section class="sebu-column-admin__panel">
        <h2 class="sebu-column-admin__panel-title">컬럼 목록</h2>
        <div class="sebu-column-admin__table-wrap">
            <table class="sebu-column-admin__table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>제목</th>
                        <th>작성자</th>
                        <th>카테고리</th>
                        <th>상태</th>
                        <th>조회</th>
                        <th>관리</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($columns as $col) { ?>
                    <tr>
                        <td><?php echo (int) ($col['wr_id'] ?? 0); ?></td>
                        <td><a href="<?php echo get_text($col['view_url'] ?? '#'); ?>"><?php echo get_text($col['wr_subject'] ?? ''); ?></a></td>
                        <td><?php echo get_text($col['author_name'] ?? ''); ?></td>
                        <td><?php echo get_text($col['category_label'] ?? ''); ?></td>
                        <td><?php echo get_text(eottae_column_status_label($col['status'] ?? '')); ?></td>
                        <td><?php echo number_format((int) ($col['wr_hit'] ?? 0)); ?></td>
                        <td>
                            <form class="sebu-column-admin__inline-form" data-sebu-column-flags-form>
                                <input type="hidden" name="action" value="set_flags">
                                <input type="hidden" name="wr_id" value="<?php echo (int) ($col['wr_id'] ?? 0); ?>">
                                <label><input type="checkbox" name="is_featured" value="1"<?php echo !empty($col['is_featured']) ? ' checked' : ''; ?>> 추천</label>
                                <label><input type="checkbox" name="is_recommended" value="1"<?php echo !empty($col['is_recommended']) ? ' checked' : ''; ?>> 인기</label>
                                <select name="status">
                                    <?php foreach ($statuses as $code => $label) { ?>
                                    <option value="<?php echo get_text($code); ?>"<?php echo ($col['status'] ?? '') === $code ? ' selected' : ''; ?>><?php echo get_text($label); ?></option>
                                    <?php } ?>
                                </select>
                                <button type="submit" class="sebu-column-btn sebu-column-btn--sm">저장</button>
                            </form>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </section>
    <?php } ?>

    <?php if ($tab === 'authors') { ?>
    <section class="sebu-column-admin__panel">
        <h2 class="sebu-column-admin__panel-title">칼럼니스트 관리</h2>
        <form class="sebu-column-admin-form" method="post" action="<?php echo get_text($proc_url); ?>" enctype="multipart/form-data" data-sebu-column-author-form>
            <input type="hidden" name="action" value="save_author">
            <input type="hidden" name="admin_token" value="<?php echo get_text($admin_token); ?>">

            <label class="sebu-column-form__field">
                <span class="sebu-column-form__label">회원 ID (mb_id) *</span>
                <input type="text" name="mb_id" class="sebu-column-form__input" required>
            </label>
            <label class="sebu-column-form__field">
                <span class="sebu-column-form__label">필명</span>
                <input type="text" name="pen_name" class="sebu-column-form__input">
            </label>
            <label class="sebu-column-form__field">
                <span class="sebu-column-form__label">타이틀</span>
                <input type="text" name="title" class="sebu-column-form__input" placeholder="예: 교육·가족 컬럼니스트">
            </label>
            <label class="sebu-column-form__field">
                <span class="sebu-column-form__label">전문 분야</span>
                <input type="text" name="specialty" class="sebu-column-form__input">
            </label>
            <label class="sebu-column-form__field">
                <span class="sebu-column-form__label">소개글</span>
                <textarea name="bio" class="sebu-column-form__textarea" rows="4"></textarea>
            </label>
            <label class="sebu-column-form__field">
                <span class="sebu-column-form__label">프로필 이미지</span>
                <input type="file" name="profile_image" accept="image/*">
            </label>
            <label class="sebu-column-form__field">
                <span class="sebu-column-form__label">활동 지역</span>
                <select name="area" class="sebu-column-form__select">
                    <option value="">선택</option>
                    <?php foreach (eottae_column_area_options() as $code => $label) { ?>
                    <option value="<?php echo get_text($code); ?>"><?php echo get_text($label); ?></option>
                    <?php } ?>
                </select>
            </label>
            <label class="sebu-column-form__field"><input type="checkbox" name="is_active" value="1" checked> 칼럼니스트 활성</label>
            <label class="sebu-column-form__field"><input type="checkbox" name="is_visible" value="1" checked> 프로필 노출</label>
            <label class="sebu-column-form__field"><input type="checkbox" name="is_official" value="1"> 공식 칼럼니스트</label>
            <button type="submit" class="sebu-column-btn sebu-column-btn--primary">칼럼니스트 저장</button>
        </form>

        <h3 class="sebu-column-admin__subtitle">등록된 칼럼니스트</h3>
        <ul class="sebu-column-admin__author-list">
            <?php foreach ($authors as $author) { ?>
            <li>
                <strong><?php echo get_text($author['display_name'] ?? ''); ?></strong>
                (<?php echo get_text($author['mb_id'] ?? ''); ?>)
                · <?php echo get_text($author['title'] ?? ''); ?>
                · 컬럼 <?php echo number_format((int) ($author['stats']['column_count'] ?? 0)); ?>개
                <a href="<?php echo get_text($author['profile_url'] ?? '#'); ?>">프로필</a>
            </li>
            <?php } ?>
        </ul>
    </section>
    <?php } ?>

    <?php if ($tab === 'applications') { ?>
    <section class="sebu-column-admin__panel">
        <div class="sebu-column-section__head">
            <h2 class="sebu-column-admin__panel-title">칼럼니스트 신청 관리</h2>
            <div class="sebu-column-section__sort">
                <a href="<?php echo eottae_column_admin_url(array('tab' => 'applications', 'application_status' => 'pending')); ?>" class="sebu-column-sort-btn<?php echo (($_GET['application_status'] ?? 'pending') === 'pending') ? ' is-active' : ''; ?>">검토중</a>
                <a href="<?php echo eottae_column_admin_url(array('tab' => 'applications', 'application_status' => 'approved')); ?>" class="sebu-column-sort-btn<?php echo (($_GET['application_status'] ?? '') === 'approved') ? ' is-active' : ''; ?>">승인</a>
                <a href="<?php echo eottae_column_admin_url(array('tab' => 'applications', 'application_status' => 'rejected')); ?>" class="sebu-column-sort-btn<?php echo (($_GET['application_status'] ?? '') === 'rejected') ? ' is-active' : ''; ?>">반려</a>
            </div>
        </div>
        <?php if (empty($applications)) { ?>
        <p class="sebu-column-empty">표시할 신청서가 없습니다.</p>
        <?php } else { ?>
        <ul class="sebu-column-admin__applications">
            <?php
            include_once G5_PATH.'/components/eottae/column-admin-mypage.php';
            foreach ($applications as $application) {
                echo eottae_column_render_admin_application_item_html($application);
            }
            ?>
        </ul>
        <?php } ?>
    </section>
    <?php } ?>

    <?php if ($tab === 'monthly') { ?>
    <section class="sebu-column-admin__panel">
        <h2 class="sebu-column-admin__panel-title">이달의 칼럼니스트</h2>
        <?php if ($monthly) { ?>
        <p class="sebu-column-admin__current-monthly">
            현재: <strong><?php echo get_text($monthly['author']['display_name'] ?? ''); ?></strong>
            — <?php echo get_text($monthly['award']['reason'] ?? ''); ?>
        </p>
        <?php } ?>
        <form class="sebu-column-admin-form" data-sebu-column-monthly-form>
            <input type="hidden" name="action" value="save_monthly">
            <label class="sebu-column-form__field">
                <span class="sebu-column-form__label">회원 ID *</span>
                <input type="text" name="mb_id" class="sebu-column-form__input" required>
            </label>
            <label class="sebu-column-form__field">
                <span class="sebu-column-form__label">선정 이유</span>
                <textarea name="reason" class="sebu-column-form__textarea" rows="3"></textarea>
            </label>
            <div class="sebu-column-form__row">
                <label class="sebu-column-form__field">
                    <span class="sebu-column-form__label">노출 시작일</span>
                    <input type="date" name="start_date" class="sebu-column-form__input" value="<?php echo date('Y-m-01'); ?>">
                </label>
                <label class="sebu-column-form__field">
                    <span class="sebu-column-form__label">노출 종료일</span>
                    <input type="date" name="end_date" class="sebu-column-form__input" value="<?php echo date('Y-m-t'); ?>">
                </label>
            </div>
            <label class="sebu-column-form__field"><input type="checkbox" name="show_on_main" value="1" checked> 메인 노출</label>
            <button type="submit" class="sebu-column-btn sebu-column-btn--primary">이달의 칼럼니스트 설정</button>
        </form>
    </section>
    <?php } ?>

    <?php if ($tab === 'reports') { ?>
    <section class="sebu-column-admin__panel">
        <h2 class="sebu-column-admin__panel-title">신고 관리</h2>
        <?php if (empty($reports)) { ?>
        <p>대기 중인 신고가 없습니다.</p>
        <?php } else { ?>
        <ul class="sebu-column-admin__reports">
            <?php foreach ($reports as $report) { ?>
            <li class="sebu-column-admin__report-item">
                <p><a href="<?php echo get_text($report['view_url'] ?? '#'); ?>"><?php echo get_text($report['wr_subject'] ?? ''); ?></a></p>
                <p>사유: <?php echo get_text($report['reason_label'] ?? ''); ?> · 신고자: <?php echo get_text($report['reporter_mb_id'] ?? ''); ?></p>
                <form class="sebu-column-admin__inline-form" data-sebu-column-report-form>
                    <input type="hidden" name="action" value="handle_report">
                    <input type="hidden" name="report_id" value="<?php echo (int) ($report['report_id'] ?? 0); ?>">
                    <button type="submit" name="report_action" value="dismiss" class="sebu-column-btn sebu-column-btn--ghost sebu-column-btn--sm">기각</button>
                    <button type="submit" name="report_action" value="hide" class="sebu-column-btn sebu-column-btn--ghost sebu-column-btn--sm">숨김</button>
                    <button type="submit" name="report_action" value="delete" class="sebu-column-btn sebu-column-btn--sm">삭제 처리</button>
                </form>
            </li>
            <?php } ?>
        </ul>
        <?php } ?>
    </section>
    <?php } ?>

    <?php if ($tab === 'categories') { ?>
    <section class="sebu-column-admin__panel">
        <h2 class="sebu-column-admin__panel-title">카테고리</h2>
        <p>컬럼 카테고리는 코드에서 관리됩니다. 게시판 분류와 동기화된 목록:</p>
        <ul class="sebu-column-admin__categories">
            <?php foreach ($categories as $code => $label) { ?>
            <li>
                <code><?php echo get_text($code); ?></code> — <?php echo get_text($label); ?>
                <a href="<?php echo eottae_column_category_url($code); ?>" target="_blank" rel="noopener noreferrer">랜딩 보기</a>
            </li>
            <?php } ?>
        </ul>
    </section>
    <?php } ?>
</main>

<?php
g5_page_end();
