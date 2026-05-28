<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('eottae_columnist_recruit_comic_html')) {
    function eottae_columnist_recruit_comic_html()
    {
        if (!function_exists('eottae_columnist_recruit_comic_panels')) {
            include_once G5_LIB_PATH.'/eottae-columnist-recruit.lib.php';
        }

        $panels = eottae_columnist_recruit_comic_panels();

        ob_start();
        ?>
        <section class="sebu-columnist-comic" aria-label="컬럼리스트 모집 스토리 만화">
            <ol class="sebu-columnist-comic__grid">
                <?php foreach ($panels as $panel) {
                    $alt = get_text('컷 '.$panel['num'].': '.implode(' ', $panel['lines']));
                    ?>
                <li class="sebu-columnist-comic__panel">
                    <article class="sebu-columnist-comic__card">
                        <div class="sebu-columnist-comic__visual" data-comic-num="<?php echo get_text($panel['num']); ?>">
                            <img
                                src="<?php echo get_text($panel['image']); ?>"
                                alt="<?php echo $alt; ?>"
                                class="sebu-columnist-comic__image"
                                loading="lazy"
                                width="1024"
                                height="1024"
                                onerror="this.classList.add('is-hidden');this.parentElement.classList.add('is-placeholder');"
                            >
                            <div class="sebu-columnist-comic__placeholder" aria-hidden="true">
                                <span class="sebu-columnist-comic__placeholder-icon">✏️</span>
                                <span class="sebu-columnist-comic__placeholder-num">컷 <?php echo get_text($panel['num']); ?></span>
                            </div>
                        </div>
                        <div class="sebu-columnist-comic__bubble">
                            <?php foreach ($panel['lines'] as $line) { ?>
                            <p class="sebu-columnist-comic__line"><?php echo get_text($line); ?></p>
                            <?php } ?>
                        </div>
                    </article>
                </li>
                <?php } ?>
            </ol>
        </section>
        <?php

        return (string) ob_get_clean();
    }
}
