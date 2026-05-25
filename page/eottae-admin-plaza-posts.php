<?php
include_once(dirname(__FILE__).'/_init.php');

if ($is_admin !== 'super') {
    alert('최고관리자만 이용할 수 있습니다.', G5_URL);
}

include_once G5_LIB_PATH.'/eottae-plaza.lib.php';
include_once G5_PATH.'/components/eottae/plaza-admin-nav.php';

$filters = array(
    'ca_name'       => isset($_GET['ca_name']) ? trim((string) $_GET['ca_name']) : '',
    'region'        => isset($_GET['region']) ? trim((string) $_GET['region']) : '',
    'mb_id'         => isset($_GET['mb_id']) ? trim((string) $_GET['mb_id']) : '',
    'reported_only' => !empty($_GET['reported_only']),
);

$posts = eottae_plaza_admin_list_posts($filters, 200);
$admin_token = eottae_plaza_admin_token();
$type_options = eottae_plaza_type_options();
$region_options = eottae_plaza_region_options();

g5_page_start('세부광장 글 관리');
?>

<main class="promo-admin-page talk-admin-page plaza-admin-page">
    <header class="promo-admin-page__header">
        <div class="promo-admin-page__header-top">
            <a href="<?php echo eottae_plaza_list_url(); ?>" class="promo-admin-page__back">← 세부광장</a>
            <a href="<?php echo G5_ADMIN_URL; ?>/" class="promo-admin-page__back">그누보드 관리자</a>
        </div>
        <h1 class="promo-admin-page__title">세부광장 글 관리</h1>
        <p class="promo-admin-page__desc">세부광장 글을 검색·필터하고 삭제 처리할 수 있습니다.</p>
        <?php eottae_plaza_render_admin_nav('posts'); ?>
    </header>

    <form class="talk-admin-filter plaza-admin-filter" method="get" action="">
        <select name="ca_name" class="talk-admin-filter__select">
            <option value="">글 유형 전체</option>
            <?php foreach ($type_options as $opt) { ?>
            <option value="<?php echo get_text($opt['slug']); ?>"<?php echo $filters['ca_name'] === $opt['slug'] ? ' selected' : ''; ?>><?php echo get_text($opt['label']); ?></option>
            <?php } ?>
        </select>
        <select name="region" class="talk-admin-filter__select">
            <option value="">지역 전체</option>
            <?php foreach ($region_options as $region) { ?>
            <option value="<?php echo get_text($region); ?>"<?php echo $filters['region'] === $region ? ' selected' : ''; ?>><?php echo get_text($region); ?></option>
            <?php } ?>
        </select>
        <input type="text" name="mb_id" value="<?php echo get_text($filters['mb_id']); ?>" placeholder="작성자 ID" class="talk-admin-filter__input">
        <label class="talk-admin-filter__check">
            <input type="checkbox" name="reported_only" value="1"<?php echo $filters['reported_only'] ? ' checked' : ''; ?>> 신고된 글만
        </label>
        <button type="submit" class="promo-admin-btn promo-admin-btn--sm">검색</button>
    </form>

    <section class="promo-admin-panel talk-admin-panel">
        <?php if (empty($posts)) { ?>
        <p class="promo-admin-empty">표시할 글이 없습니다.</p>
        <?php } else { ?>
        <div class="talk-admin-table-wrap">
            <table class="talk-admin-table plaza-admin-table">
                <thead>
                    <tr>
                        <th scope="col">작성일</th>
                        <th scope="col">유형</th>
                        <th scope="col">지역</th>
                        <th scope="col">제목</th>
                        <th scope="col">작성자</th>
                        <th scope="col">댓글</th>
                        <th scope="col">공감</th>
                        <th scope="col">신고</th>
                        <th scope="col">조회</th>
                        <th scope="col">상태</th>
                        <th scope="col">관리</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($posts as $item) { ?>
                    <tr>
                        <td data-label="작성일"><?php echo $item['datetime'] !== '' ? substr($item['datetime'], 0, 16) : '-'; ?></td>
                        <td data-label="유형"><?php echo $item['ca_name']; ?></td>
                        <td data-label="지역"><?php echo $item['region']; ?></td>
                        <td data-label="제목">
                            <a href="<?php echo $item['href']; ?>" target="_blank" rel="noopener noreferrer"><?php echo $item['subject']; ?></a>
                        </td>
                        <td data-label="작성자"><?php echo $item['author']; ?><?php if ($item['mb_id'] !== '') { ?> <span class="talk-report-list__meta">(<?php echo $item['mb_id']; ?>)</span><?php } ?></td>
                        <td data-label="댓글"><?php echo number_format($item['comment_count']); ?></td>
                        <td data-label="공감"><?php echo number_format($item['like_count']); ?></td>
                        <td data-label="신고"><?php echo number_format($item['report_count']); ?></td>
                        <td data-label="조회"><?php echo number_format($item['hit']); ?></td>
                        <td data-label="상태"><?php echo !empty($item['is_hidden']) ? '삭제됨' : '노출'; ?></td>
                        <td data-label="관리" class="talk-admin-table__actions">
                            <a href="<?php echo $item['href']; ?>" class="promo-admin-btn promo-admin-btn--sm" target="_blank" rel="noopener noreferrer">보기</a>
                            <?php if (empty($item['is_hidden'])) { ?>
                            <button type="button" class="promo-admin-btn promo-admin-btn--sm" data-plaza-hide-post="<?php echo (int) $item['wr_id']; ?>">삭제 처리</button>
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
eottae_plaza_render_admin_actions_script($admin_token);
g5_page_end();
