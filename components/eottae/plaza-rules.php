<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('eottae_plaza_render_rules')) {
    function eottae_plaza_render_rules($compact = false)
    {
        include_once G5_LIB_PATH.'/eottae-plaza.lib.php';
        $items = eottae_plaza_rules_items();
        $class = $compact ? 'plaza-rules plaza-rules--compact' : 'plaza-rules';
        ?>
        <aside class="<?php echo $class; ?>" aria-label="세부광장 운영 규칙">
            <h2 class="plaza-rules__title">운영 안내</h2>
            <p class="plaza-rules__lead">세부광장은 모두가 보는 공개 공간입니다.<br>서로 존중하는 분위기를 위해 아래 내용을 지켜주세요.</p>
            <ol class="plaza-rules__list">
                <?php foreach ($items as $idx => $rule) { ?>
                <li><?php echo ($idx + 1).'. '.get_text($rule); ?></li>
                <?php } ?>
            </ol>
        </aside>
        <?php
    }
}
