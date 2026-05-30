<?php
$sub_menu = '100950';
include_once './_common.php';
include_once G5_LIB_PATH.'/eottae-translation.lib.php';
include_once G5_LIB_PATH.'/eottae-language-meta.lib.php';

$auth = isset($auth) && is_array($auth) ? $auth : array();

$id = isset($_REQUEST['id']) ? (int) $_REQUEST['id'] : 0;
if ($id < 1) {
    alert('번역 캐시 ID가 올바르지 않습니다.', G5_ADMIN_URL.'/eottae_translation_cache.php');
}

$cache = function_exists('eottae_translation_cache_get_by_id') ? eottae_translation_cache_get_by_id($id) : null;
if (!$cache) {
    alert('번역 캐시를 찾을 수 없습니다.', G5_ADMIN_URL.'/eottae_translation_cache.php');
}

$bo_table = preg_replace('/[^a-z0-9_]/i', '', (string) ($cache['board_type'] ?? ''));
$wr_id = (int) ($cache['post_id'] ?? 0);
$write = function_exists('eottae_translation_post_fetch') ? eottae_translation_post_fetch($bo_table, $wr_id) : null;
if (!$write) {
    alert('원문 게시글을 찾을 수 없습니다.', G5_ADMIN_URL.'/eottae_translation_cache.php');
}

if (!empty($_POST['act']) && $_POST['act'] === 'save_review') {
    auth_check_menu($auth, $sub_menu, 'w');
    check_admin_token();

    $title = isset($_POST['translated_title']) ? (string) $_POST['translated_title'] : '';
    $content = isset($_POST['translated_content']) ? (string) $_POST['translated_content'] : '';
    $reviewed_by = isset($member['mb_id']) ? (string) $member['mb_id'] : '';
    $extras = null;
    if (function_exists('eottae_is_shop_board') && eottae_is_shop_board($bo_table)) {
        $extras = array();
        foreach (eottae_translation_extra_labels($bo_table) as $extra_key => $extra_label) {
            $field_name = 'translated_'.$extra_key;
            $extras[$extra_key] = isset($_POST[$field_name]) ? (string) $_POST[$field_name] : '';
        }
    } elseif (function_exists('eottae_is_event_board') && eottae_is_event_board($bo_table)) {
        $extras = array();
        foreach (eottae_translation_extra_labels($bo_table) as $extra_key => $extra_label) {
            $field_name = 'translated_'.$extra_key;
            $extras[$extra_key] = isset($_POST[$field_name]) ? (string) $_POST[$field_name] : '';
        }
    }
    $result = eottae_translation_cache_save_review($id, $title, $content, $reviewed_by, $extras);

    if (empty($result['success'])) {
        $message = isset($result['message']) ? (string) $result['message'] : 'save_failed';
        alert('번역 저장에 실패했습니다. ('.$message.')');
    }

    goto_url(G5_ADMIN_URL.'/eottae_translation_edit.php?id='.$id);
}

auth_check_menu($auth, $sub_menu, 'r');

$g5['title'] = '번역 검수';
include_once './admin.head.php';

$source_label = function_exists('eottae_lang_label') ? eottae_lang_label($cache['source_language'] ?? 'ko') : get_text($cache['source_language'] ?? 'ko');
$target_label = function_exists('eottae_lang_label') ? eottae_lang_label($cache['target_language'] ?? '') : get_text($cache['target_language'] ?? '');
$review_status = (string) ($cache['review_status'] ?? 'auto');
$review_label = function_exists('eottae_translation_review_status_label')
    ? eottae_translation_review_status_label($review_status)
    : ($review_status === 'reviewed' ? '검수완료' : '자동번역');
$html = function_exists('eottae_translation_post_html_mode') ? eottae_translation_post_html_mode($write) : 0;
$original_title = get_text($write['wr_subject'] ?? '');
$original_content = get_text($write['wr_content'] ?? '');
$translated_title = get_text($cache['translated_title'] ?? '');
$translated_content = get_text($cache['translated_content'] ?? '');
$is_shop_board = function_exists('eottae_is_shop_board') && eottae_is_shop_board($bo_table);
$is_event_board = function_exists('eottae_is_event_board') && eottae_is_event_board($bo_table);
$has_extras_board = $is_shop_board || $is_event_board;
$original_extras = $has_extras_board && function_exists('eottae_translation_extras_from_write')
    ? eottae_translation_extras_from_write($bo_table, $write)
    : array();
$translated_extras = function_exists('eottae_translation_decode_extras')
    ? eottae_translation_decode_extras($cache['translated_extras'] ?? '')
    : array();
$extra_labels = function_exists('eottae_translation_extra_labels')
    ? eottae_translation_extra_labels($bo_table)
    : array();
$post_url = function_exists('get_pretty_url') ? get_pretty_url($bo_table, $wr_id) : G5_BBS_URL.'/board.php?bo_table='.$bo_table.'&amp;wr_id='.$wr_id;
$list_url = G5_ADMIN_URL.'/eottae_translation_cache.php';
?>

<div class="local_desc01 local_desc">
    <p>
        자동 번역 결과를 검수하고 수정할 수 있습니다.
        저장하면 <strong>검수완료</strong> 상태로 표시되며, 사용자 화면에 수정된 번역이 노출됩니다.
        원문 게시글이 수정되면 번역 캐시가 삭제되어 검수 상태도 초기화됩니다.
    </p>
</div>

<div class="local_ov01 local_ov">
    <span class="btn_ov01">
        <span class="ov_txt">상태</span>
        <span class="ov_num"><?php echo get_text($review_label); ?></span>
    </span>
    <span class="btn_ov01">
        <span class="ov_txt">게시판</span>
        <span class="ov_num"><?php echo get_text($bo_table); ?></span>
    </span>
    <span class="btn_ov01">
        <span class="ov_txt">게시글</span>
        <span class="ov_num">#<?php echo $wr_id; ?></span>
    </span>
    <span class="btn_ov01">
        <span class="ov_txt">번역</span>
        <span class="ov_num"><?php echo get_text($source_label); ?> → <?php echo get_text($target_label); ?></span>
    </span>
</div>

<div class="btn_add01 btn_add">
    <a href="<?php echo $post_url; ?>" class="btn btn_02" target="_blank" rel="noopener">게시글 보기</a>
    <a href="<?php echo $list_url; ?>" class="btn btn_02">목록</a>
</div>

<form method="post" action="<?php echo G5_ADMIN_URL; ?>/eottae_translation_edit.php">
    <input type="hidden" name="token" value="<?php echo get_admin_token(); ?>">
    <input type="hidden" name="act" value="save_review">
    <input type="hidden" name="id" value="<?php echo (int) $id; ?>">

    <div class="tbl_frm01 tbl_wrap">
        <table>
        <caption>번역 검수</caption>
        <colgroup>
            <col class="grid_4">
            <col>
        </colgroup>
        <tbody>
        <tr>
            <th scope="row">원문 제목</th>
            <td><div class="eottae-translation-review__readonly"><?php echo $original_title; ?></div></td>
        </tr>
        <tr>
            <th scope="row"><label for="translated_title">번역 제목</label></th>
            <td><input type="text" name="translated_title" id="translated_title" value="<?php echo htmlspecialchars($translated_title, ENT_QUOTES, 'UTF-8'); ?>" class="frm_input required" required size="90"></td>
        </tr>
        <tr>
            <th scope="row">원문 본문</th>
            <td><div class="eottae-translation-review__readonly eottae-translation-review__readonly--content"><?php echo nl2br($original_content); ?></div></td>
        </tr>
        <tr>
            <th scope="row"><label for="translated_content">번역 본문</label></th>
            <td><textarea name="translated_content" id="translated_content" rows="18" class="frm_input required" required><?php echo htmlspecialchars($translated_content, ENT_QUOTES, 'UTF-8'); ?></textarea></td>
        </tr>
        <?php if ($has_extras_board && $original_extras) {
            foreach ($extra_labels as $extra_key => $extra_label) {
                if (empty($original_extras[$extra_key])) {
                    continue;
                }
                $field_name = 'translated_'.$extra_key;
                $field_value = get_text($translated_extras[$extra_key] ?? '');
                $is_multiline = in_array($extra_key, array('events', 'coupons'), true);
                ?>
        <tr>
            <th scope="row">원문 <?php echo get_text($extra_label); ?></th>
            <td><div class="eottae-translation-review__readonly<?php echo $is_multiline ? ' eottae-translation-review__readonly--content' : ''; ?>"><?php echo $is_multiline ? nl2br(get_text($original_extras[$extra_key])) : get_text($original_extras[$extra_key]); ?></div></td>
        </tr>
        <tr>
            <th scope="row"><label for="<?php echo $field_name; ?>">번역 <?php echo get_text($extra_label); ?></label></th>
            <td><?php if ($is_multiline) { ?><textarea name="<?php echo $field_name; ?>" id="<?php echo $field_name; ?>" rows="6" class="frm_input" style="width:100%"><?php echo htmlspecialchars($field_value, ENT_QUOTES, 'UTF-8'); ?></textarea><?php } else { ?><input type="text" name="<?php echo $field_name; ?>" id="<?php echo $field_name; ?>" value="<?php echo htmlspecialchars($field_value, ENT_QUOTES, 'UTF-8'); ?>" class="frm_input" size="90"><?php } ?></td>
        </tr>
                <?php
            }
        } ?>
        <tr>
            <th scope="row">메타</th>
            <td>
                제공자: <?php echo get_text($cache['provider']); ?> /
                원문 수정일: <?php echo get_text($cache['source_updated_at']); ?> /
                캐시 수정일: <?php echo get_text($cache['updated_at']); ?>
                <?php if ($review_status === 'reviewed') { ?>
                <br>검수자: <?php echo get_text($cache['reviewed_by']); ?> /
                검수일: <?php echo get_text($cache['reviewed_at']); ?>
                <?php } ?>
                <?php if ((int) $html > 0) { ?>
                <br><span class="frm_info">HTML 게시글입니다. 태그 구조를 유지하면서 텍스트만 수정해 주세요.</span>
                <?php } ?>
            </td>
        </tr>
        </tbody>
        </table>
    </div>

    <div class="btn_fixed_top">
        <button type="submit" class="btn btn_submit">검수 저장</button>
        <a href="<?php echo $list_url; ?>" class="btn btn_02">목록</a>
    </div>
</form>

<style>
.eottae-translation-review__readonly {
    padding: 10px 12px;
    background: #f8f9fa;
    border: 1px solid #e5e7eb;
    border-radius: 4px;
    line-height: 1.6;
    word-break: break-word;
}
.eottae-translation-review__readonly--content {
    max-height: 320px;
    overflow: auto;
}
#translated_content {
    width: 100%;
    max-width: 100%;
    box-sizing: border-box;
}
</style>

<?php
include_once './admin.tail.php';
