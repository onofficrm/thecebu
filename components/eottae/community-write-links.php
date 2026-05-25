<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

global $bo_table;

if (!function_exists('eottae_is_community_board') || !eottae_is_community_board($bo_table)) {
    return;
}

if (!function_exists('eottae_community_write_link_values')) {
    include_once G5_LIB_PATH.'/eottae.lib.php';
}

$community_link_values = eottae_community_write_link_values(isset($write) ? $write : null);
?>

<div class="community-write-page__links">
    <div class="community-write-page__field">
        <label for="wr_link1">유튜브 링크 <span class="community-write-page__optional">(선택)</span></label>
        <input type="url" name="wr_link1" id="wr_link1" value="<?php echo htmlspecialchars($community_link_values['youtube'], ENT_QUOTES, 'UTF-8'); ?>" maxlength="1000" placeholder="https://www.youtube.com/watch?v=..." class="community-write-page__input" inputmode="url" autocomplete="off">
        <p class="community-write-page__hint">YouTube · youtu.be · Shorts URL을 붙여넣으면 글 보기에서 영상이 재생됩니다.</p>
    </div>

    <div class="community-write-page__field">
        <label for="wr_link2">관련 URL <span class="community-write-page__optional">(선택)</span></label>
        <input type="url" name="wr_link2" id="wr_link2" value="<?php echo htmlspecialchars($community_link_values['url'], ENT_QUOTES, 'UTF-8'); ?>" maxlength="1000" placeholder="https://example.com/article" class="community-write-page__input" inputmode="url" autocomplete="off">
        <p class="community-write-page__hint">출처, 참고 기사, SNS 게시글 등 관련 링크를 입력하세요.</p>
    </div>
</div>
