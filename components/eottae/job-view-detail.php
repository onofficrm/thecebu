<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (empty($job_template_data) || !is_array($job_template_data)) {
    return;
}

if (!function_exists('eottae_job_template_view_rows')) {
    include_once G5_LIB_PATH.'/eottae-job.lib.php';
}

$job_sections = eottae_job_template_view_rows($job_template_data);
$job_shop = isset($job_shop) && is_array($job_shop) ? $job_shop : null;
if (!$job_sections) {
    return;
}

if (!function_exists('eottae_job_template_render_value')) {
    function eottae_job_template_render_value($value, $multiline = false)
    {
        $value = trim((string) $value);
        if ($value === '') {
            return '';
        }

        if ($multiline && function_exists('eottae_job_template_format_display_text')) {
            $value = eottae_job_template_format_display_text($value);
        }

        return get_text($value);
    }
}
?>
<section class="job-detail-panel" aria-label="구인 상세정보" data-i18n-aria-label="job.panel.aria">
    <?php foreach ($job_sections as $section) {
        $rows = $section['rows'] ?? array();
        if (!$rows) {
            continue;
        }
        $section_key = (string) ($section['section_key'] ?? '');
        ?>
    <div class="job-detail-panel__block">
        <h2 class="job-detail-panel__title"<?php echo $section_key !== '' ? ' data-i18n="'.get_text($section_key).'"' : ''; ?>><?php echo get_text($section['section'] ?? ''); ?></h2>
        <dl class="job-detail-panel__list">
            <?php foreach ($rows as $row) {
                $label = (string) ($row['label'] ?? '');
                $value = (string) ($row['value'] ?? '');
                $multiline = !empty($row['multiline']);
                $extra_key = (string) ($row['extra_key'] ?? '');
                $label_key = (string) ($row['label_key'] ?? '');
                $is_company_link = $label === '업체명' && is_array($job_shop) && !empty($job_shop['view_url']);
                if ($value === '') {
                    continue;
                }
                $value_display = eottae_job_template_render_value($value, $multiline);
                $item_class = 'job-detail-panel__item';
                if ($multiline) {
                    $item_class .= ' job-detail-panel__item--block';
                }
                if ($label === '') {
                    $item_class .= ' job-detail-panel__item--solo';
                }
                ?>
            <div class="<?php echo $item_class; ?>">
                <?php if ($label !== '') { ?>
                <dt<?php echo $label_key !== '' ? ' data-i18n="'.get_text($label_key).'"' : ''; ?>><?php echo get_text($label); ?></dt>
                <?php } ?>
                <dd class="<?php echo $multiline ? 'job-detail-panel__value--multiline' : 'job-detail-panel__value'; ?>"<?php echo (!$is_company_link && $extra_key !== '') ? ' data-translation-extra="'.get_text($extra_key).'"' : ''; ?>><?php
                    if ($is_company_link) {
                        $job_shop_url = is_array($job_shop) ? (string) ($job_shop['view_url'] ?? '') : '';
                        echo '<a href="'.get_text($job_shop_url).'" class="job-detail-panel__shop-link"><span data-translation-extra="'.get_text($extra_key !== '' ? $extra_key : 'job_company').'">'.$value_display.'</span></a>';
                    } else {
                        echo $value_display;
                    }
                ?></dd>
            </div>
            <?php } ?>
        </dl>
    </div>
    <?php } ?>
</section>
