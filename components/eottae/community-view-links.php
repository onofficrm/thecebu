<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

global $bo_table;

if (!function_exists('eottae_is_community_board') || !eottae_is_community_board($bo_table)) {
    return;
}

include_once G5_SKIN_PATH.'/board/_inc/g5b-youtube.php';

$community_youtube_url = isset($view['wr_link1']) ? trim((string) $view['wr_link1']) : '';
$community_related_url = isset($view['wr_link2']) ? trim((string) $view['wr_link2']) : '';
$community_youtube_id = $community_youtube_url !== '' ? g5b_youtube_id_from_url($community_youtube_url) : '';

if ($community_youtube_id === '' && $community_related_url === '') {
    return;
}
?>

<section class="community-view-page__links" aria-label="외부 링크">
    <?php if ($community_youtube_id !== '') { ?>
    <div class="community-view-page__youtube">
        <?php echo g5b_youtube_embed_html($community_youtube_id, isset($view['wr_subject']) ? $view['wr_subject'] : ''); ?>
    </div>
    <?php } ?>

    <?php if ($community_related_url !== '') {
        $community_related_label = cut_str($community_related_url, 80, '…');
        $community_related_href = function_exists('eottae_community_normalize_url')
            ? eottae_community_normalize_url($community_related_url)
            : $community_related_url;
        ?>
    <div class="community-view-page__related-link">
        <span class="community-view-page__related-link-label">관련 링크</span>
        <a href="<?php echo htmlspecialchars($community_related_href, ENT_QUOTES, 'UTF-8'); ?>" class="community-view-page__related-link-anchor" target="_blank" rel="noopener noreferrer">
            <?php echo get_text($community_related_label); ?>
        </a>
    </div>
    <?php } ?>
</section>
