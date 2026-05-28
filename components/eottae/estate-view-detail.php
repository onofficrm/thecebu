<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (empty($estate_template_data) || !is_array($estate_template_data)) {
    return;
}

if (!function_exists('eottae_estate_template_view_rows')) {
    include_once G5_LIB_PATH.'/eottae-estate-template.lib.php';
}

$estate_sections = eottae_estate_template_view_rows($estate_template_data);
if (!$estate_sections) {
    return;
}
?>
<section class="estate-detail-panel" aria-label="부동산 매물 상세정보">
    <?php foreach ($estate_sections as $section) {
        $rows = $section['rows'] ?? array();
        if (!$rows) {
            continue;
        }
        ?>
    <div class="estate-detail-panel__block">
        <h2 class="estate-detail-panel__title"><?php echo get_text($section['section'] ?? ''); ?></h2>
        <dl class="estate-detail-panel__list">
            <?php foreach ($rows as $row) {
                $label = (string) ($row['label'] ?? '');
                $value = (string) ($row['value'] ?? '');
                $multiline = !empty($row['multiline']);
                if ($value === '') {
                    continue;
                }
                ?>
            <div class="estate-detail-panel__item<?php echo $multiline ? ' estate-detail-panel__item--block' : ''; ?>">
                <?php if ($label !== '') { ?>
                <dt><?php echo get_text($label); ?></dt>
                <?php } ?>
                <dd><?php echo $multiline ? nl2br(get_text($value)) : get_text($value); ?></dd>
            </div>
            <?php } ?>
        </dl>
    </div>
    <?php } ?>
</section>
