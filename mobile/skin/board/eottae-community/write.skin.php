<?php
if (!defined('_GNUBOARD_')) exit;

include_once(G5_LIB_PATH.'/eottae.lib.php');
include_once(G5_LIB_PATH.'/eottae-job-template.lib.php');
include_once(G5_LIB_PATH.'/eottae-property-template.lib.php');
include_once(G5_LIB_PATH.'/eottae-free-board.lib.php');
include_once(G5_LIB_PATH.'/eottae-community-hub.lib.php');
include_once(G5_LIB_PATH.'/eottae-event-template.lib.php');
include_once(G5_LIB_PATH.'/eottae-report.lib.php');
include_once(G5_LIB_PATH.'/eottae-report-template.lib.php');
include_once(G5_LIB_PATH.'/eottae-board-write-mobile.lib.php');
add_stylesheet('<link rel="stylesheet" href="'.$board_skin_url.'/style.css">', 0);
if (function_exists('eottae_board_write_enqueue_mobile_css')) {
    eottae_board_write_enqueue_mobile_css();
}

$is_job_board_write = function_exists('eottae_is_job_board') && eottae_is_job_board($bo_table);
$is_estate_board_write = function_exists('eottae_is_estate_board') && eottae_is_estate_board($bo_table);
$is_free_board_write = function_exists('eottae_is_free_board') && eottae_is_free_board($bo_table);
$is_event_board_write = function_exists('eottae_is_event_board') && eottae_is_event_board($bo_table);
$is_report_board_write = function_exists('eottae_is_report_board') && eottae_is_report_board($bo_table);
if ($is_report_board_write && function_exists('eottae_report_board_load_assets')) {
    eottae_report_board_load_assets();
}
$is_community_hub_write = function_exists('eottae_is_community_hub_board') && eottae_is_community_hub_board($bo_table);
$hub_board_def = $is_community_hub_write ? eottae_community_hub_board_def($bo_table) : null;
$community_tabs = $is_community_hub_write ? array() : eottae_community_category_tabs($board);
$write_category = $is_community_hub_write ? '' : ($sca !== '' ? $sca : (isset($write['ca_name']) ? get_text($write['ca_name']) : ''));
$job_list_url = function_exists('eottae_board_list_url')
    ? eottae_board_list_url(eottae_job_board_table())
    : G5_BBS_URL.'/board.php?bo_table='.eottae_job_board_table();
$estate_list_url = function_exists('eottae_board_list_url')
    ? eottae_board_list_url(eottae_estate_board_table())
    : G5_BBS_URL.'/board.php?bo_table='.eottae_estate_board_table();
$free_list_url = function_exists('eottae_free_list_url')
    ? eottae_free_list_url()
    : G5_BBS_URL.'/board.php?bo_table='.(function_exists('eottae_free_board_table') ? eottae_free_board_table() : 'free');
if ($is_job_board_write) {
    $write_list_url = $job_list_url;
} elseif ($is_estate_board_write) {
    $write_list_url = $estate_list_url;
} elseif ($is_event_board_write) {
    $write_list_url = eottae_community_hub_list_url($bo_table);
} elseif ($is_report_board_write) {
    $write_list_url = function_exists('eottae_board_list_url')
        ? eottae_board_list_url(eottae_report_board_table())
        : G5_BBS_URL.'/board.php?bo_table='.eottae_report_board_table();
} elseif ($is_community_hub_write) {
    $write_list_url = eottae_community_hub_list_url($bo_table);
} elseif ($is_free_board_write) {
    $write_list_url = $free_list_url;
} else {
    $write_list_url = eottae_community_list_url($sca ? array('sca' => $sca) : array());
}

$snippet_prefill_id = 0;
if ($w !== 'u' && !empty($is_member) && function_exists('eottae_is_business_member') && eottae_is_business_member($member)) {
    include_once G5_LIB_PATH.'/eottae-business-snippet.lib.php';
    $snippet_prefill_id = isset($_GET['snippet_id']) ? (int) $_GET['snippet_id'] : 0;
    if ($snippet_prefill_id > 0 && eottae_business_snippet_write_allowed($bo_table, $write_category)) {
        $snippet_prefill = eottae_business_snippet_get($member['mb_id'], $snippet_prefill_id);
        if (!empty($snippet_prefill)) {
            $subject = $snippet_prefill['wr_subject'];
            $content = $snippet_prefill['wr_content'];
        }
    }
}
?>

<?php
$file_count = eottae_community_write_photo_count($board, $file_count);
$post_language = function_exists('eottae_lang_post_default') ? eottae_lang_post_default($write ?? array()) : 'ko';
?>

<div class="community-write-page board-wrap board-wrap--eottae-community" id="bo_w" style="width:<?php echo $width; ?>">

    <header class="community-write-page__header">
        <a href="<?php echo get_text($write_list_url); ?>" class="community-write-page__back">← 목록으로</a>
        <h1 class="community-write-page__title"><?php
            if ($is_event_board_write) {
                echo $w === 'u' ? '이벤트·프로모션 수정' : '이벤트·프로모션 등록';
            } elseif ($is_report_board_write) {
                echo $w === 'u' ? '제보 수정' : '세부 제보하기';
            } elseif ($is_job_board_write) {
                echo $w === 'u' ? '구인구직 글 수정' : '구인구직 글쓰기';
            } elseif ($is_estate_board_write) {
                echo $w === 'u' ? '부동산 글 수정' : '부동산 글쓰기';
            } elseif ($is_community_hub_write && !empty($hub_board_def['label'])) {
                $hub_label = get_text($hub_board_def['label']);
                echo $w === 'u' ? $hub_label.' 글 수정' : $hub_label.' 글쓰기';
            } elseif ($is_free_board_write) {
                echo $w === 'u' ? '자유게시판 글 수정' : '자유게시판 글쓰기';
            } else {
                echo $w === 'u' ? '글 수정' : '글쓰기';
            }
        ?></h1>
        <p class="community-write-page__desc"><?php
            if ($is_event_board_write) {
                echo '세부 지역 이벤트·할인·프로모션 정보를 간단히 등록해 주세요. 업체 연결은 선택 사항입니다.';
            } elseif ($is_report_board_write) {
                echo '세부에서 본 소식을 간단히 제보해 주세요. 관리자 확인 후 공개될 수 있습니다.';
            } elseif ($is_job_board_write) {
                echo '모집 정보를 정확히 작성해 주세요. 허위·과장 채용 공고는 안내 없이 삭제될 수 있습니다.';
            } elseif ($is_estate_board_write) {
                echo '매물 정보를 정확히 작성해 주세요. 허위·과장 매물 정보는 안내 없이 삭제될 수 있습니다.';
            } elseif ($is_community_hub_write && !empty($hub_board_def['desc'])) {
                echo get_text($hub_board_def['desc']);
            } elseif ($is_free_board_write) {
                echo '세부 교민·여행자와 자유롭게 이야기를 나눠 주세요. 타인을 비방하거나 욕설, 광고성 글은 안내 없이 삭제될 수 있습니다.';
            } else {
                echo '세부 생활 정보를 공유해 주세요. 타인을 비방하거나 욕설, 광고성 글은 안내 없이 삭제될 수 있습니다.';
            }
        ?></p>
    </header>

    <form name="fwrite" id="fwrite" class="community-write-page__form" action="<?php echo $action_url; ?>" onsubmit="return fwrite_submit(this);" method="post" enctype="multipart/form-data">
    <input type="hidden" name="uid" value="<?php echo get_uniqid(); ?>">
    <input type="hidden" name="w" value="<?php echo $w ?>">
    <input type="hidden" name="bo_table" value="<?php echo $bo_table ?>">
    <input type="hidden" name="wr_id" value="<?php echo $wr_id ?>">
    <input type="hidden" name="sca" value="<?php echo $sca ?>">
    <input type="hidden" name="page" value="<?php echo $page ?>">
    <?php include G5_PATH.'/components/eottae/board-write-options.php'; ?>

    <?php if (!$is_community_hub_write && $is_category && !empty($community_tabs)) { ?>
    <div class="community-write-page__field">
        <label for="ca_name">분류</label>
        <select name="ca_name" id="ca_name" class="community-write-page__select" required>
            <option value="">분류 선택</option>
            <?php foreach ($community_tabs as $tab) {
                if ($tab['slug'] === '') {
                    continue;
                } ?>
            <option value="<?php echo get_text($tab['slug']); ?>"<?php echo ($write_category === $tab['slug']) ? ' selected' : ''; ?>><?php echo get_text($tab['label']); ?></option>
            <?php } ?>
        </select>
    </div>
    <?php } ?>

    <?php include G5_PATH.'/components/eottae/business-write-snippets.php'; ?>

    <div class="community-write-page__field">
        <label for="language">작성 언어</label>
        <select name="language" id="language" class="community-write-page__select" data-post-language-select>
            <?php echo function_exists('eottae_lang_options_html') ? eottae_lang_options_html($post_language) : ''; ?>
        </select>
    </div>

    <?php if ($is_event_board_write) {
        include G5_PATH.'/components/eottae/event-write-template.php';
    } elseif ($is_report_board_write) {
        include G5_PATH.'/components/eottae/report-write-template.php';
    } else { ?>
    <div class="community-write-page__field">
        <label for="wr_subject">제목</label>
        <input type="text" name="wr_subject" id="wr_subject" value="<?php echo $subject; ?>" required maxlength="255" placeholder="<?php echo ($is_job_board_write || $is_estate_board_write) ? '템플릿 자동작성 후 수정할 수 있습니다' : '제목을 입력하세요'; ?>" class="community-write-page__input">
    </div>

    <?php if ($is_job_board_write) {
        include G5_PATH.'/components/eottae/job-write-template.php';
    } ?>
    <?php if ($is_estate_board_write) {
        include G5_PATH.'/components/eottae/property-write-template.php';
    } ?>

    <?php if ($is_job_board_write) { ?>
    <input type="hidden" name="wr_content" id="wr_content" value="<?php echo htmlspecialchars($content, ENT_QUOTES, 'UTF-8'); ?>">
    <?php } else {
        if ($is_estate_board_write) {
            $eottae_editor_placeholder = '템플릿 자동작성을 사용하거나 직접 본문을 작성해 주세요';
        } else {
            $eottae_editor_placeholder = '세부 생활 정보, 꿀팁, 질문 등을 자유롭게 작성해 주세요';
        }
        include G5_PATH.'/components/eottae/board-write-editor.php';
    } ?>
    <?php } ?>

    <?php if ($is_community_hub_write
        || (function_exists('eottae_is_community_board') && eottae_is_community_board($bo_table))) {
        include G5_PATH.'/components/eottae/community-write-links.php';
    } ?>

    <?php if ($file_count > 0) { ?>
    <div class="community-write-page__photos">
        <p class="community-write-page__photos-label">사진 첨부 <span>(최대 <?php echo (int) $file_count; ?>장)</span></p>
        <div class="community-write-page__photo-grid">
            <?php for ($i = 0; $i < $file_count; $i++) { ?>
            <label class="community-write-page__photo-slot" for="bf_file_<?php echo $i + 1; ?>">
                <input type="file" name="bf_file[]" id="bf_file_<?php echo $i + 1; ?>" accept="image/*" class="community-write-page__photo-input" data-photo-preview>
                <span class="community-write-page__photo-placeholder">+</span>
                <img src="" alt="" class="community-write-page__photo-preview" hidden>
                <?php if ($w === 'u' && isset($file[$i]['file']) && $file[$i]['file']) { ?>
                <span class="community-write-page__photo-current"><?php echo $file[$i]['source']; ?></span>
                <label class="community-write-page__photo-delete">
                    <input type="checkbox" name="bf_file_del[<?php echo $i; ?>]" value="1"> 삭제
                </label>
                <?php } ?>
            </label>
            <?php } ?>
        </div>
    </div>
    <?php } ?>

    <?php if ($is_use_captcha) { ?>
    <div class="community-write-page__captcha"><?php echo $captcha_html; ?></div>
    <?php } ?>

    <div class="community-write-page__actions">
        <a href="<?php echo get_text($write_list_url); ?>" class="community-write-page__cancel">취소</a>
        <button type="submit" id="btn_submit" class="community-write-page__submit"><?php echo !empty($is_report_board_write) ? '제보하기' : '등록하기'; ?></button>
    </div>
    </form>
</div>

<script>
(function () {
    var select = document.querySelector('[data-post-language-select]');
    if (!select || <?php echo $w === 'u' ? 'true' : 'false'; ?>) return;
    try {
        var lang = localStorage.getItem('cebuatteLanguage');
        if (lang && /^(ko|en|ja|zh)$/.test(lang)) {
            select.value = lang;
        }
    } catch (e) {}
})();

function fwrite_submit(f) {
    <?php echo $editor_js; ?>

    <?php if (!empty($is_job_board_write)) { ?>
    if (typeof window.sebuJobTemplateApplyBeforeSubmit === 'function') {
        window.sebuJobTemplateApplyBeforeSubmit();
    }
    <?php } ?>
    <?php if (!empty($is_estate_board_write)) { ?>
    if (typeof window.sebuPropertyTemplateApplyBeforeSubmit === 'function') {
        window.sebuPropertyTemplateApplyBeforeSubmit();
    }
    <?php } ?>
    <?php if (!empty($is_event_board_write) && function_exists('eottae_is_event_board')) { ?>
    if (typeof window.sebuEventTemplateSyncEditor === 'function') {
        window.sebuEventTemplateSyncEditor();
    }
    <?php } ?>
    <?php if (!empty($is_report_board_write)) { ?>
    if (!f.wr_content.value.trim()) {
        alert('제보 내용을 입력해 주세요.');
        f.wr_content.focus();
        return false;
    }
    <?php } ?>
    <?php if (!empty($is_job_board_write) || !empty($is_estate_board_write) || !empty($is_event_board_write)) { ?>
    if (typeof oEditors !== 'undefined' && oEditors.getById && oEditors.getById.wr_content) {
        try {
            oEditors.getById.wr_content.exec('UPDATE_CONTENTS_FIELD', []);
        } catch (e) {}
    }
    <?php } ?>

    var subject = "";
    var content = "";
    $.ajax({
        url: g5_bbs_url+"/ajax.filter.php",
        type: "POST",
        data: {
            "subject": f.wr_subject.value,
            "content": f.wr_content.value
        },
        dataType: "json",
        async: false,
        cache: false,
        success: function(data) {
            subject = data.subject;
            content = data.content;
        }
    });

    if (subject) {
        alert("제목에 금지단어('"+subject+"')가 포함되어 있습니다.");
        f.wr_subject.focus();
        return false;
    }
    if (content) {
        alert("내용에 금지단어('"+content+"')가 포함되어 있습니다.");
        f.wr_content.focus();
        return false;
    }

    <?php echo $captcha_js; ?>
    document.getElementById('btn_submit').disabled = true;
    return true;
}
</script>
