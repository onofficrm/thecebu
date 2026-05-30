<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (empty($is_job_board_view)) {
    return;
}

if (!function_exists('eottae_job_location_from_row') && is_file(G5_LIB_PATH.'/eottae-job.lib.php')) {
    include_once G5_LIB_PATH.'/eottae-job.lib.php';
}

$job_aside_location = isset($job_location) && is_array($job_location)
    ? $job_location
    : (function_exists('eottae_job_location_from_row') ? eottae_job_location_from_row($view) : null);
$job_aside_template = isset($job_template_data) && is_array($job_template_data)
    ? $job_template_data
    : (function_exists('eottae_job_template_from_row') ? eottae_job_template_from_row($view) : null);
$job_aside_status = isset($job_recruit_status)
    ? $job_recruit_status
    : (function_exists('eottae_job_recruit_status_from_row') ? eottae_job_recruit_status_from_row($view) : 'recruiting');
$job_aside_has_map = function_exists('eottae_job_view_has_map') && eottae_job_view_has_map($job_aside_location);
$job_aside_inquiry_opts = function_exists('eottae_job_view_inquiry_opts')
    ? eottae_job_view_inquiry_opts($view, $bo_table, $job_aside_location, $job_aside_template)
    : array();
$job_aside_apply_label = function_exists('eottae_job_view_apply_method_label')
    ? eottae_job_view_apply_method_label($job_aside_template)
    : '';

if ($job_aside_has_map && function_exists('eottae_enqueue_google_maps')) {
    eottae_enqueue_google_maps();
}

$job_aside_shop_map = array(
    'address' => is_array($job_aside_location) ? (string) ($job_aside_location['location_text'] ?? '') : '',
    'lat'     => is_array($job_aside_location) ? (string) ($job_aside_location['latitude'] ?? '') : '',
    'lng'     => is_array($job_aside_location) ? (string) ($job_aside_location['longitude'] ?? '') : '',
    'name'    => get_text($view['wr_subject'] ?? ''),
    'region'  => is_array($job_aside_location) ? (string) ($job_aside_location['area_label'] ?? '') : '',
);
$shop_map = $job_aside_shop_map;
?>

<aside class="job-view-aside" aria-label="구인 요약">
    <section class="job-view-aside__summary">
        <div class="job-view-aside__badges">
            <?php echo eottae_job_render_recruit_badge($job_aside_status, 'job-recruit-badge--view'); ?>
        </div>
        <h2 class="job-view-aside__title"><?php echo get_text($view['wr_subject'] ?? ''); ?></h2>
        <dl class="job-view-aside__meta">
            <?php if (is_array($job_aside_location) && ($job_aside_location['area_label'] ?? '') !== '') { ?>
            <div>
                <dt data-i18n="job.field.location_area">지역</dt>
                <dd data-translation-extra="job_location_area"><?php echo get_text($job_aside_location['area_label']); ?></dd>
            </div>
            <?php } ?>
            <?php if (is_array($job_aside_location) && ($job_aside_location['location_text'] ?? '') !== '') { ?>
            <div>
                <dt data-i18n="job.field.location_detail">상세위치</dt>
                <dd data-translation-extra="job_location_text"><?php echo get_text($job_aside_location['location_text']); ?></dd>
            </div>
            <?php } ?>
            <?php if ($job_aside_apply_label !== '') { ?>
            <div>
                <dt data-i18n="job.field.apply_method">지원방법</dt>
                <dd><?php echo get_text($job_aside_apply_label); ?></dd>
            </div>
            <?php } ?>
            <div>
                <dt>등록일</dt>
                <dd><?php echo date('Y.m.d H:i', strtotime($view['wr_datetime'] ?? 'now')); ?></dd>
            </div>
        </dl>
    </section>

    <?php if ($job_aside_has_map) {
        include G5_PATH.'/components/eottae/shop-detail-map.php';
    } ?>

    <?php if ($job_aside_inquiry_opts && function_exists('eottae_render_inquiry_buttons')) {
        eottae_render_inquiry_buttons('detail', $job_aside_inquiry_opts);
    } ?>
</aside>

<?php
$job_view_mobile_inquiry_opts = $job_aside_inquiry_opts;
