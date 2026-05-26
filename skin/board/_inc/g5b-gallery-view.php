<?php
if (!defined('_GNUBOARD_')) exit;

/**
 * 갤러리형 글보기 — 대표 이미지 + 첨부 이미지 그리드
 */
$g5b_gallery_images = array();
$g5b_gallery_hero = '';

if (!empty($view['file']['count'])) {
    for ($i = 0; $i < count($view['file']); $i++) {
        if (empty($view['file'][$i]['source'])) {
            continue;
        }

        $img_html = '';
        if (!empty($view['file'][$i]['view'])) {
            $img_html = $view['file'][$i]['view'];
        } elseif (function_exists('eottae_gallery_file_view_html')) {
            $img_html = eottae_gallery_file_view_html($view['file'][$i], $bo_table);
        }

        if ($img_html !== '') {
            $g5b_gallery_images[] = array_merge($view['file'][$i], array('view' => $img_html));
        }
    }
}

if (count($g5b_gallery_images)) {
    $g5b_gallery_hero = !empty($g5b_gallery_images[0]['view'])
        ? $g5b_gallery_images[0]['view']
        : get_file_thumbnail($g5b_gallery_images[0]);
} else {
    $hero_thumb = get_list_thumbnail($bo_table, $view['wr_id'], 1200, 800, false, false);
    if (!empty($hero_thumb['src'])) {
        $alt = !empty($hero_thumb['alt']) ? get_text($hero_thumb['alt']) : get_text(strip_tags($view['wr_subject']));
        $g5b_gallery_hero = '<img src="'.htmlspecialchars($hero_thumb['src'], ENT_QUOTES).'" alt="'.htmlspecialchars($alt, ENT_QUOTES).'" class="board-view__hero-img">';
    }
}
?>

<?php if ($g5b_gallery_hero) { ?>
<div class="board-view__hero" id="bo_v_img">
    <?php echo $g5b_gallery_hero; ?>
</div>
<?php } ?>

<?php if (count($g5b_gallery_images) > 1) { ?>
<div class="board-view__gallery" aria-label="첨부 이미지">
    <h3 class="board-view__section-title sound_only">이미지 갤러리</h3>
    <ul class="board-view__gallery-grid">
    <?php for ($i = 0; $i < count($g5b_gallery_images); $i++) { ?>
        <li class="board-view__gallery-item"><?php echo $g5b_gallery_images[$i]['view']; ?></li>
    <?php } ?>
    </ul>
</div>
<?php } elseif (count($g5b_gallery_images) === 1 && !$g5b_gallery_hero) { ?>
<div class="board-view__hero" id="bo_v_img">
    <?php echo $g5b_gallery_images[0]['view']; ?>
</div>
<?php } ?>

<div class="board-view__content-wrap">
    <div id="bo_v_con" class="board-view__content"><?php echo get_view_thumbnail($view['content']); ?></div>
</div>
