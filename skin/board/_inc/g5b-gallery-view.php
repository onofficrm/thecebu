<?php
if (!defined('_GNUBOARD_')) exit;

/**
 * 갤러리형 글보기 — 대표 이미지 슬라이더 + 썸네일 그리드
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

$g5b_gallery_slide_count = count($g5b_gallery_images);

if ($g5b_gallery_slide_count < 1) {
    $hero_thumb = get_list_thumbnail($bo_table, $view['wr_id'], 1200, 800, false, false);
    if (!empty($hero_thumb['src'])) {
        $alt = !empty($hero_thumb['alt']) ? get_text($hero_thumb['alt']) : get_text(strip_tags($view['wr_subject']));
        $g5b_gallery_images[] = array(
            'view' => '<img src="'.htmlspecialchars($hero_thumb['src'], ENT_QUOTES).'" alt="'.htmlspecialchars($alt, ENT_QUOTES).'" class="board-view__hero-img">',
        );
        $g5b_gallery_slide_count = 1;
    }
}

if ($g5b_gallery_slide_count < 1) {
    return;
}

$g5b_gallery_slider_js = G5_PATH.'/js/g5b-gallery-view-slider.js';
if (is_file($g5b_gallery_slider_js)) {
    $g5b_gallery_slider_ver = (int) filemtime($g5b_gallery_slider_js);
    add_javascript('<script src="'.G5_JS_URL.'/g5b-gallery-view-slider.js?ver='.$g5b_gallery_slider_ver.'" defer></script>', 25);
}
?>

<div class="board-gal-slider<?php echo $g5b_gallery_slide_count > 1 ? ' board-gal-slider--multi' : ''; ?>" data-gal-slider data-autoplay="3000">
    <div class="board-gal-slider__stage board-view__hero" id="bo_v_img">
        <div class="board-gal-slider__track" aria-live="polite">
            <?php for ($i = 0; $i < $g5b_gallery_slide_count; $i++) { ?>
            <div class="board-gal-slider__slide<?php echo $i === 0 ? ' is-active' : ''; ?>" data-gal-slide aria-hidden="<?php echo $i === 0 ? 'false' : 'true'; ?>">
                <?php echo $g5b_gallery_images[$i]['view']; ?>
            </div>
            <?php } ?>
        </div>

        <?php if ($g5b_gallery_slide_count > 1) { ?>
        <button type="button" class="board-gal-slider__nav board-gal-slider__nav--prev" data-gal-slider-prev aria-label="이전 이미지">
            <i class="fa fa-chevron-left" aria-hidden="true"></i>
        </button>
        <button type="button" class="board-gal-slider__nav board-gal-slider__nav--next" data-gal-slider-next aria-label="다음 이미지">
            <i class="fa fa-chevron-right" aria-hidden="true"></i>
        </button>
        <span class="board-gal-slider__counter" data-gal-slider-counter>1 / <?php echo (int) $g5b_gallery_slide_count; ?></span>
        <?php } ?>
    </div>

    <?php if ($g5b_gallery_slide_count > 1) { ?>
    <div class="board-view__gallery" aria-label="첨부 이미지">
        <h3 class="board-view__section-title sound_only">이미지 갤러리</h3>
        <ul class="board-view__gallery-grid board-gal-slider__thumbs">
            <?php for ($i = 0; $i < $g5b_gallery_slide_count; $i++) {
                $g5b_gallery_thumb_html = $g5b_gallery_images[$i]['view'];
                if (preg_match('/<img\b[^>]*>/i', $g5b_gallery_thumb_html, $g5b_gallery_thumb_match)) {
                    $g5b_gallery_thumb_html = $g5b_gallery_thumb_match[0];
                }
            ?>
            <li class="board-view__gallery-item">
                <button type="button" class="board-gal-slider__thumb<?php echo $i === 0 ? ' is-active' : ''; ?>" data-gal-thumb="<?php echo (int) $i; ?>" aria-label="이미지 <?php echo (int) ($i + 1); ?> 보기">
                    <?php echo $g5b_gallery_thumb_html; ?>
                </button>
            </li>
            <?php } ?>
        </ul>
    </div>
    <?php } ?>
</div>

<div class="board-view__content-wrap">
    <div id="bo_v_con" class="board-view__content"><?php echo get_view_thumbnail($view['content']); ?></div>
</div>
