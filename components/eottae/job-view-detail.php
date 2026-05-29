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
?>
<section class="job-detail-panel" aria-label="구인 상세정보">
    <?php foreach ($job_sections as $section) {
        $rows = $section['rows'] ?? array();
        if (!$rows) {
            continue;
        }
        ?>
    <div class="job-detail-panel__block">
        <h2 class="job-detail-panel__title"><?php echo get_text($section['section'] ?? ''); ?></h2>
        <dl class="job-detail-panel__list">
            <?php foreach ($rows as $row) {
                $label = (string) ($row['label'] ?? '');
                $value = (string) ($row['value'] ?? '');
                $multiline = !empty($row['multiline']);
                $is_company_link = $label === '업체명' && is_array($job_shop) && !empty($job_shop['view_url']);
                if ($value === '') {
                    continue;
                }
                ?>
            <div class="job-detail-panel__item<?php echo $multiline ? ' job-detail-panel__item--block' : ''; ?>">
                <?php if ($label !== '') { ?>
                <dt><?php echo get_text($label); ?></dt>
                <?php } ?>
                <dd><?php
                    if ($is_company_link) {
                        $job_shop_url = is_array($job_shop) ? (string) ($job_shop['view_url'] ?? '') : '';
                        echo '<a href="'.get_text($job_shop_url).'" class="job-detail-panel__shop-link">'.get_text($value).'</a>';
                    } else {
                        echo $multiline ? nl2br(get_text($value)) : get_text($value);
                    }
                ?></dd>
            </div>
            <?php } ?>
        </dl>
    </div>
    <?php } ?>
</section>
