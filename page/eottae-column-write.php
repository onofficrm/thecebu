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
$statuses = eottae_column_status_options();
$token = eottae_column_member_token();
$proc_url = eottae_column_proc_url();

$column_use_editor = eottae_column_editor_enabled();
$column_editor_js = array('js' => '', 'chk' => '');
$content_raw = $post ? eottae_column_normalize_content_images((string) ($post['wr_content'] ?? '')) : '';
$column_editor_html = '';

if ($column_use_editor) {
    eottae_column_enqueue_editor_assets();
    $column_editor_html = eottae_column_editor_html($content_raw);
    $column_editor_js = eottae_column_editor_form_js();
}

add_stylesheet('<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Noto+Serif+KR:wght@500;600;700&family=Source+Sans+3:wght@400;500;600;700&display=swap">', 20);
add_stylesheet('<link rel="stylesheet" href="'.G5_CSS_URL.'/eottae-column.css">', 24);
add_javascript('<script src="'.G5_JS_URL.'/eottae-column.js" defer></script>', 24);

g5_page_start($wr_id > 0 ? '컬럼 수정' : '컬럼 작성');
?>

<main class="sebu-column-write-page sebu-column-editorial" data-sebu-column-write data-proc-url="<?php echo get_text($proc_url); ?>"<?php echo $column_use_editor ? ' data-column-use-editor="1"' : ''; ?>>
    <header class="sebu-column-studio">
        <p class="sebu-column-studio__eyebrow">Column Studio</p>
        <h1 class="sebu-column-studio__title"><?php echo $wr_id > 0 ? '컬럼 수정' : '새 컬럼 작성'; ?></h1>
        <p class="sebu-column-studio__guide">독자에게 전달할 경험과 통찰을 담아 주세요. 사실 확인된 정보, 읽기 좋은 구성, 적절한 분량이 고급 컬럼의 기준입니다.</p>
        <ul class="sebu-column-studio__tips">
            <li>제목·부제·요약으로 글의 핵심을 먼저 전달하세요.</li>
            <li>본문은 소제목과 짧은 문단으로 나누면 가독성이 높아집니다.</li>
            <li>개인정보·과도한 홍보·확인되지 않은 정보는 제한될 수 있습니다.</li>
        </ul>
    </header>

    <form class="sebu-column-write-form sebu-column-write-form--editorial" method="post" action="<?php echo get_text($proc_url); ?>" enctype="multipart/form-data"<?php echo $column_use_editor ? ' onsubmit="return sebuColumnWriteSubmit(this);"' : ''; ?>>
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

        <div class="sebu-column-form__field sebu-column-form__field--editor">
            <span class="sebu-column-form__label">본문 *</span>
            <?php if ($column_use_editor && $column_editor_html !== '') { ?>
            <p class="sebu-column-form__hint">스마트에디터2 — 상단 <strong>Editor · HTML · TEXT</strong> 탭으로 작성 방식을 바꿀 수 있습니다. 툴바 <strong>사진</strong> 버튼으로 본문에 이미지를 여러 장 넣을 수 있습니다.</p>
            <div class="sebu-column-editor-wrap">
                <?php echo $column_editor_html; ?>
            </div>
            <label class="sebu-column-form__field sebu-column-form__field--body-images">
                <span class="sebu-column-form__label sebu-column-form__label--sub">본문 이미지 추가 (선택, 최대 8장)</span>
                <p class="sebu-column-form__hint">에디터 외에 파일을 선택하면 발행 시 본문 하단에 순서대로 삽입됩니다.</p>
                <input type="file" name="content_images[]" class="sebu-column-form__file" accept="image/jpeg,image/png,image/gif,image/webp" multiple>
            </label>
            <?php } else { ?>
            <textarea name="wr_content" id="wr_content" class="sebu-column-form__textarea sebu-column-form__textarea--body" rows="16" required><?php echo get_text($content_raw); ?></textarea>
            <label class="sebu-column-form__field sebu-column-form__field--body-images">
                <span class="sebu-column-form__label sebu-column-form__label--sub">본문 이미지 (선택)</span>
                <input type="file" name="content_images[]" class="sebu-column-form__file" accept="image/jpeg,image/png,image/gif,image/webp" multiple>
            </label>
            <?php } ?>
        </div>

        <label class="sebu-column-form__field">
            <span class="sebu-column-form__label">관련 링크</span>
            <input type="url" name="related_url" class="sebu-column-form__input" placeholder="https://" value="<?php echo $post ? get_text($post['meta']['related_url'] ?? '') : ''; ?>">
        </label>

        <label class="sebu-column-form__field">
            <span class="sebu-column-form__label">유튜브 링크</span>
            <input type="url" name="youtube_url" class="sebu-column-form__input" placeholder="https://www.youtube.com/watch?v=..." value="<?php echo $post ? get_text($post['meta']['youtube_url'] ?? '') : ''; ?>">
            <p class="sebu-column-form__hint">컬럼 본문 위에 영상이 표시됩니다. watch, youtu.be, shorts, embed URL을 사용할 수 있습니다.</p>
        </label>

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
            <?php if ($wr_id > 0) { ?>
            <button type="button" class="sebu-column-btn sebu-column-btn--danger" data-sebu-column-delete data-wr-id="<?php echo (int) $wr_id; ?>" data-proc-url="<?php echo get_text($proc_url); ?>" data-token="<?php echo get_text($token); ?>" data-list-url="<?php echo get_text(eottae_column_list_url()); ?>">삭제</button>
            <?php } ?>
            <a href="<?php echo $wr_id > 0 ? eottae_column_view_url($wr_id) : eottae_column_mypage_url(); ?>" class="sebu-column-btn sebu-column-btn--ghost">취소</a>
            <button type="submit" class="sebu-column-btn sebu-column-btn--primary"><?php echo $wr_id > 0 ? '저장' : '발행'; ?></button>
        </div>
    </form>
</main>

<?php if ($column_use_editor) { ?>
<script>
function sebuColumnWriteSubmit(f) {
    <?php echo $column_editor_js['js']; ?>
    <?php echo $column_editor_js['chk']; ?>
    var btn = f.querySelector('button[type="submit"]');
    if (btn) {
        btn.disabled = true;
    }
    return true;
}
</script>
<?php } ?>

<?php
g5_page_end();
