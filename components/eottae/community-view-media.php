<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('eottae_community_view_media')) {
    include_once G5_LIB_PATH.'/eottae.lib.php';
}

$community_media = eottae_community_view_media($view);
$community_images = $community_media['images'];
$community_files = $community_media['files'];
$community_image_count = count($community_images);

if ($community_image_count === 0 && empty($community_files)) {
    return;
}
?>

<?php if ($community_image_count > 0) { ?>
<div class="community-view-page__gallery <?php echo eottae_community_view_gallery_class($community_image_count); ?>" id="bo_v_img">
    <?php foreach ($community_images as $community_image_html) { ?>
    <div class="community-view-page__gallery-item">
        <?php echo $community_image_html; ?>
    </div>
    <?php } ?>
</div>
<?php } ?>

<?php if (!empty($community_files)) { ?>
<section class="community-view-page__files" id="bo_v_file">
    <h2 class="community-view-page__files-title">첨부파일</h2>
    <ul class="community-view-page__files-list">
        <?php foreach ($community_files as $community_file) { ?>
        <li class="community-view-page__files-item">
            <a href="<?php echo $community_file['href']; ?>" class="community-view-page__files-link view_file_download">
                <?php echo get_text($community_file['source']); ?>
                <span class="community-view-page__files-meta">(<?php echo $community_file['size']; ?>)</span>
            </a>
        </li>
        <?php } ?>
    </ul>
</section>
<?php } ?>
