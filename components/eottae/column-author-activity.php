<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('eottae_column_author_activity_html')) {
    function eottae_column_author_activity_html(array $sections)
    {
        if (!$sections) {
            return '';
        }

        ob_start();
        ?>
        <section class="sebu-column-section sebu-author-activity" aria-labelledby="sebu-author-activity-title">
            <div class="sebu-column-section__head">
                <h2 class="sebu-column-section__title" id="sebu-author-activity-title">활동 · 등록 정보</h2>
                <p class="sebu-author-activity__desc">칼럼니스트가 공개에 동의한 업체·매물·구인 정보입니다.</p>
            </div>

            <?php foreach ($sections as $section) { ?>
            <div class="sebu-author-activity__group">
                <div class="sebu-author-activity__group-head">
                    <h3 class="sebu-author-activity__group-title"><?php echo get_text($section['label'] ?? ''); ?></h3>
                    <?php if (!empty($section['list_url'])) { ?>
                    <a href="<?php echo get_text($section['list_url']); ?>" class="sebu-author-activity__more">전체 보기</a>
                    <?php } ?>
                </div>
                <ul class="sebu-author-activity__list">
                    <?php foreach (($section['items'] ?? array()) as $item) { ?>
                    <li class="sebu-author-activity__item">
                        <a href="<?php echo get_text($item['url'] ?? '#'); ?>" class="sebu-author-activity__card">
                            <span class="sebu-author-activity__thumb<?php echo empty($item['thumb']) ? ' sebu-author-activity__thumb--empty' : ''; ?>">
                                <?php if (!empty($item['thumb'])) { ?>
                                <img src="<?php echo get_text($item['thumb']); ?>" alt="" loading="lazy" decoding="async">
                                <?php } ?>
                            </span>
                            <span class="sebu-author-activity__body">
                                <?php if (!empty($item['status_label'])) { ?>
                                <span class="sebu-author-activity__status <?php echo get_text($item['status_class'] ?? ''); ?>"><?php echo get_text($item['status_label']); ?></span>
                                <?php } ?>
                                <span class="sebu-author-activity__title"><?php echo get_text($item['title'] ?? ''); ?></span>
                                <?php if (!empty($item['meta'])) { ?>
                                <span class="sebu-author-activity__meta"><?php echo get_text($item['meta']); ?></span>
                                <?php } ?>
                            </span>
                        </a>
                    </li>
                    <?php } ?>
                </ul>
            </div>
            <?php } ?>
        </section>
        <?php

        return (string) ob_get_clean();
    }
}

if (!function_exists('eottae_column_author_exposure_form_fields_html')) {
    function eottae_column_author_exposure_form_fields_html(array $author, array $counts = array())
    {
        if (!function_exists('eottae_column_author_exposure_field_defs')) {
            include_once G5_LIB_PATH.'/eottae-column-author-exposure.lib.php';
        }

        ob_start();
        ?>
        <div class="sebu-column-exposure-fields">
            <?php foreach (eottae_column_author_exposure_field_defs() as $key => $def) {
                $column = $def['column'];
                $checked = !empty($author[$column]);
                $count = (int) ($counts[$key] ?? 0);
                ?>
            <label class="sebu-column-exposure-fields__item">
                <input type="checkbox" name="<?php echo get_text($key); ?>" value="1"<?php echo $checked ? ' checked' : ''; ?>>
                <span class="sebu-column-exposure-fields__copy">
                    <span class="sebu-column-exposure-fields__label"><?php echo get_text($def['label']); ?> 프로필 노출</span>
                    <span class="sebu-column-exposure-fields__desc"><?php echo get_text($def['desc']); ?></span>
                    <?php if ($count > 0) { ?>
                    <span class="sebu-column-exposure-fields__count">현재 노출 가능 <?php echo number_format($count); ?>건</span>
                    <?php } else { ?>
                    <span class="sebu-column-exposure-fields__count sebu-column-exposure-fields__count--empty">등록된 활성 글이 없습니다.</span>
                    <?php } ?>
                </span>
            </label>
            <?php } ?>
        </div>
        <?php

        return (string) ob_get_clean();
    }
}
