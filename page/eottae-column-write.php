<?php
include_once(dirname(__FILE__).'/_init.php');
include_once G5_LIB_PATH.'/eottae-column.lib.php';

if (!$is_member) {
    alert('로그인 후 이용해 주세요.', function_exists('eottae_login_url') ? eottae_login_url(eottae_column_write_url()) : G5_BBS_URL.'/login.php');
}

$is_super = ($is_admin === 'super');
if (!eottae_column_can_write($member['mb_id'], $is_super)) {
    alert('승인된 칼럼니스트만 컬럼을 작성할 수 있습니다.', eottae_column_list_url());
}

eottae_column_ensure_schema();

$wr_id = isset($_GET['wr_id']) ? (int) $_GET['wr_id'] : 0;
$post = null;
$meta = null;
if ($wr_id > 0) {
    if (!eottae_column_can_edit($member['mb_id'], $wr_id, $is_super)) {
        alert('수정 권한이 없습니다.', eottae_column_list_url());
    }
    $post = eottae_column_get_post($wr_id, array(
        'skip_hit'       => true,
        'include_hidden' => true,
        'is_super'       => $is_super,
        'member_mb_id'   => $member['mb_id'],
    ));
    if (!$post) {
        alert('글을 찾을 수 없습니다.', eottae_column_mypage_url());
    }
    $meta = $post['meta'] ?? array();
}

$categories = eottae_column_category_options();
$areas = eottae_column_area_options();
$statuses = eottae_column_status_options();
$token = eottae_column_member_token();
$proc_url = eottae_column_proc_url();

add_stylesheet('<link rel="stylesheet" href="'.G5_CSS_URL.'/eottae-column.css">', 24);
add_javascript('<script src="'.G5_JS_URL.'/eottae-column.js" defer></script>', 24);

g5_page_start($wr_id > 0 ? '컬럼 수정' : '컬럼 작성');
?>

<main class="sebu-column-write-page">
    <header class="sebu-column-write-page__head">
        <h1 class="sebu-column-write-page__title"><?php echo $wr_id > 0 ? '컬럼 수정' : '새 컬럼 작성'; ?></h1>
        <p class="sebu-column-write-page__guide">세부 교민에게 실제로 도움이 되는 경험과 정보를 중심으로 작성해주세요.<br>개인정보, 과도한 홍보, 확인되지 않은 정보는 제한될 수 있습니다.</p>
    </header>

    <form class="sebu-column-write-form" method="post" action="<?php echo get_text($proc_url); ?>" enctype="multipart/form-data">
        <input type="hidden" name="action" value="save">
        <input type="hidden" name="eottae_column_token" value="<?php echo get_text($token); ?>">
        <input type="hidden" name="wr_id" value="<?php echo (int) $wr_id; ?>">
        <?php if ($wr_id > 0 && !empty($meta['thumbnail'])) { ?>
        <input type="hidden" name="thumbnail_keep" value="1">
        <?php } ?>

        <label class="sebu-column-form__field">
            <span class="sebu-column-form__label">제목 *</span>
            <input type="text" name="title" class="sebu-column-form__input" required maxlength="200" value="<?php echo $post ? get_text($post['wr_subject'] ?? '') : ''; ?>">
        </label>

        <label class="sebu-column-form__field">
            <span class="sebu-column-form__label">부제목</span>
            <input type="text" name="subtitle" class="sebu-column-form__input" maxlength="255" value="<?php echo $post ? get_text($post['subtitle'] ?? '') : ''; ?>">
        </label>

        <label class="sebu-column-form__field">
            <span class="sebu-column-form__label">요약 설명</span>
            <textarea name="summary" class="sebu-column-form__textarea" rows="2" maxlength="500"><?php echo $post ? get_text($post['summary'] ?? '') : ''; ?></textarea>
        </label>

        <label class="sebu-column-form__field">
            <span class="sebu-column-form__label">카테고리 *</span>
            <select name="category" class="sebu-column-form__select" required>
                <option value="">선택</option>
                <?php foreach ($categories as $code => $label) { ?>
                <option value="<?php echo get_text($code); ?>"<?php echo $post && ($post['category'] ?? '') === $code ? ' selected' : ''; ?>><?php echo get_text($label); ?></option>
                <?php } ?>
            </select>
        </label>

        <label class="sebu-column-form__field">
            <span class="sebu-column-form__label">썸네일 이미지</span>
            <?php if ($post && !empty($post['thumbnail_url'])) { ?>
            <img src="<?php echo get_text($post['thumbnail_url']); ?>" alt="" class="sebu-column-write-form__thumb-preview">
            <?php } ?>
            <input type="file" name="thumbnail" class="sebu-column-form__file" accept="image/*">
        </label>

        <label class="sebu-column-form__field">
            <span class="sebu-column-form__label">본문 *</span>
            <textarea name="content" class="sebu-column-form__textarea sebu-column-form__textarea--body" rows="16" required><?php echo $post ? get_text($post['wr_content'] ?? '') : ''; ?></textarea>
        </label>

        <label class="sebu-column-form__field">
            <span class="sebu-column-form__label">태그 (쉼표 구분)</span>
            <input type="text" name="tags" class="sebu-column-form__input" value="<?php echo $post ? get_text($post['tags'] ?? '') : ''; ?>">
        </label>

        <label class="sebu-column-form__field">
            <span class="sebu-column-form__label">지역</span>
            <select name="area" class="sebu-column-form__select">
                <option value="">선택</option>
                <?php foreach ($areas as $code => $label) { ?>
                <option value="<?php echo get_text($code); ?>"<?php echo $post && ($post['area'] ?? '') === $code ? ' selected' : ''; ?>><?php echo get_text($label); ?></option>
                <?php } ?>
            </select>
        </label>

        <label class="sebu-column-form__field">
            <span class="sebu-column-form__label">관련 링크</span>
            <input type="url" name="related_url" class="sebu-column-form__input" value="<?php echo $post ? get_text($post['meta']['related_url'] ?? '') : ''; ?>">
        </label>

        <div class="sebu-column-form__row">
            <label class="sebu-column-form__field">
                <span class="sebu-column-form__label">관련 세부톡방 ID</span>
                <input type="number" name="related_room_id" class="sebu-column-form__input" min="0" value="<?php echo $post ? (int) ($post['meta']['related_room_id'] ?? 0) : 0; ?>">
            </label>
            <label class="sebu-column-form__field">
                <span class="sebu-column-form__label">관련 캘린더 일정 ID</span>
                <input type="number" name="related_event_id" class="sebu-column-form__input" min="0" value="<?php echo $post ? (int) ($post['meta']['related_event_id'] ?? 0) : 0; ?>">
            </label>
        </div>

        <label class="sebu-column-form__field">
            <span class="sebu-column-form__label">발행 상태</span>
            <select name="status" class="sebu-column-form__select">
                <?php foreach ($statuses as $code => $label) { ?>
                <option value="<?php echo get_text($code); ?>"<?php echo $post && ($post['status'] ?? '') === $code ? ' selected' : ($code === 'published' && !$post ? ' selected' : ''); ?>><?php echo get_text($label); ?></option>
                <?php } ?>
            </select>
        </label>

        <?php if ($is_super) { ?>
        <fieldset class="sebu-column-form__admin-flags">
            <legend>관리자 옵션</legend>
            <label><input type="checkbox" name="is_featured" value="1"<?php echo $post && !empty($post['is_featured']) ? ' checked' : ''; ?>> 추천 컬럼</label>
            <label><input type="checkbox" name="is_recommended" value="1"<?php echo $post && !empty($post['is_recommended']) ? ' checked' : ''; ?>> 인기 컬럼</label>
            <label><input type="checkbox" name="is_representative" value="1"<?php echo $post && !empty($post['is_representative']) ? ' checked' : ''; ?>> 대표 컬럼</label>
        </fieldset>
        <?php } ?>

        <div class="sebu-column-write-form__actions">
            <a href="<?php echo eottae_column_mypage_url(); ?>" class="sebu-column-btn sebu-column-btn--ghost">취소</a>
            <button type="submit" class="sebu-column-btn sebu-column-btn--primary"><?php echo $wr_id > 0 ? '저장' : '발행'; ?></button>
        </div>
    </form>
</main>

<?php
g5_page_end();
