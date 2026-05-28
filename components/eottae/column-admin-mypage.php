<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('eottae_column_admin_authors_url')) {
    include_once G5_LIB_PATH.'/eottae-column-admin-authors.lib.php';
}

if (!function_exists('eottae_column_render_admin_application_item_html')) {
    function eottae_column_render_admin_application_item_html(array $application)
    {
        if (!function_exists('eottae_column_render_avatar_html')) {
            include_once G5_PATH.'/components/eottae/column-author-profile.php';
        }

        ob_start();
        ?>
        <li class="sebu-column-admin__application-item">
            <div class="sebu-column-admin__application-head">
                <div class="sebu-column-admin__application-profile">
                    <?php echo eottae_column_render_avatar_html($application, 'sm', 'sebu-column-admin__application-avatar'); ?>
                    <div class="sebu-column-admin__application-identity">
                        <div class="sebu-column-admin__application-name-row">
                            <strong class="sebu-column-admin__application-name"><?php echo get_text($application['pen_name'] ?? ''); ?></strong>
                            <span class="sebu-column-admin__status sebu-column-admin__status--<?php echo get_text(preg_replace('/[^a-z]/', '', (string) ($application['status'] ?? 'pending'))); ?>">
                                <?php echo get_text($application['status_label'] ?? ''); ?>
                            </span>
                        </div>
                        <span class="sebu-column-admin__application-sub">
                            <?php echo get_text($application['mb_id'] ?? ''); ?>
                            · <?php echo get_text(substr((string) ($application['created_at'] ?? ''), 0, 10)); ?>
                        </span>
                    </div>
                </div>
            </div>
            <?php echo eottae_column_render_social_links_html($application, 'sebu-column-admin__application-social'); ?>
            <p class="sebu-column-admin__application-title"><?php echo get_text($application['title'] ?? ''); ?> · <?php echo get_text($application['specialty'] ?? ''); ?></p>
            <?php if (!empty($application['area_label'])) { ?>
            <p class="sebu-column-admin__application-meta">활동 지역: <?php echo get_text($application['area_label']); ?></p>
            <?php } ?>
            <?php if (!empty($application['website_url'])) { ?>
            <p class="sebu-column-admin__application-meta">홈페이지: <a href="<?php echo get_text($application['website_url']); ?>" target="_blank" rel="noopener noreferrer"><?php echo get_text($application['website_url']); ?></a></p>
            <?php } ?>
            <p class="sebu-column-admin__application-bio"><?php echo nl2br(get_text($application['bio'] ?? '')); ?></p>
            <?php if (!empty($application['sample_url'])) { ?>
            <p class="sebu-column-admin__application-meta">샘플: <a href="<?php echo get_text($application['sample_url']); ?>" target="_blank" rel="noopener noreferrer"><?php echo get_text($application['sample_url']); ?></a></p>
            <?php } ?>
            <?php if (!empty($application['message'])) { ?>
            <p class="sebu-column-admin__application-meta">메모: <?php echo nl2br(get_text($application['message'])); ?></p>
            <?php } ?>
            <?php if (($application['status'] ?? '') === 'pending') { ?>
            <form class="sebu-column-admin__application-form" data-sebu-column-application-form>
                <input type="hidden" name="application_id" value="<?php echo (int) ($application['application_id'] ?? 0); ?>">
                <textarea name="review_memo" class="sebu-column-form__textarea" rows="2" placeholder="승인/반려 메모 (선택)"></textarea>
                <div class="sebu-column-admin__inline-form">
                    <button type="submit" name="decision" value="approve" class="sebu-column-btn sebu-column-btn--primary sebu-column-btn--sm">승인</button>
                    <button type="submit" name="decision" value="reject" class="sebu-column-btn sebu-column-btn--ghost sebu-column-btn--sm">반려</button>
                </div>
            </form>
            <?php } elseif (!empty($application['review_memo'])) { ?>
            <p class="sebu-column-admin__application-meta">처리 메모: <?php echo nl2br(get_text($application['review_memo'])); ?></p>
            <?php } ?>
        </li>
        <?php

        return (string) ob_get_clean();
    }
}

if (!function_exists('eottae_column_render_mypage_super_admin_section')) {
    function eottae_column_render_mypage_super_admin_section($preview_limit = 5)
    {
        global $is_admin;

        if ($is_admin !== 'super') {
            return;
        }

        include_once G5_LIB_PATH.'/eottae-column.lib.php';
        include_once G5_LIB_PATH.'/eottae-talkroom.lib.php';
        include_once G5_PATH.'/components/eottae/column-author-profile.php';

        eottae_column_ensure_schema();

        $preview_limit = max(1, min(20, (int) $preview_limit));
        $pending_count = eottae_column_pending_application_count();
        $applications = eottae_column_list_applications('pending', $preview_limit);
        $admin_token = function_exists('eottae_talkroom_admin_token') ? eottae_talkroom_admin_token() : '';
        $proc_url = eottae_column_admin_proc_url();
        $admin_url = eottae_column_admin_url(array('tab' => 'applications'));

        static $assets_loaded = false;
        if (!$assets_loaded) {
            $assets_loaded = true;
            add_stylesheet('<link rel="stylesheet" href="'.G5_CSS_URL.'/eottae-column.css">', 24);
            add_javascript('<script src="'.G5_JS_URL.'/eottae-column-admin.js" defer></script>', 24);
        }

        ob_start();
        ?>
        <section class="sebu-column-mypage-admin my-talk-section my-talk-section--panel" id="sebu-column-mypage-admin" aria-labelledby="sebu-column-mypage-admin-title">
            <h2 class="my-talk-section__title" id="sebu-column-mypage-admin-title">칼럼니스트 신청 (최고관리자)</h2>
            <p class="my-talk-section__desc">검토 중인 칼럼니스트 신청을 승인하거나 반려할 수 있습니다.</p>
            <div class="sebu-column-mypage-admin__links">
                <a href="<?php echo get_text($admin_url); ?>" class="my-talk-btn my-talk-btn--primary my-talk-btn--sm">칼럼니스트 신청 관리<?php if ($pending_count > 0) { ?> (<?php echo number_format($pending_count); ?>)<?php } ?></a>
                <a href="<?php echo eottae_column_admin_authors_url(); ?>" class="my-talk-btn my-talk-btn--ghost my-talk-btn--sm">칼럼니스트 목록</a>
                <a href="<?php echo eottae_column_list_url(); ?>" class="my-talk-btn my-talk-btn--ghost my-talk-btn--sm">컬럼 섹션</a>
            </div>

            <div class="sebu-column-admin sebu-column-admin--mypage" data-proc-url="<?php echo get_text($proc_url); ?>" data-admin-token="<?php echo get_text($admin_token); ?>">
                <?php if ($pending_count < 1) { ?>
                <p class="sebu-column-empty">검토 중인 칼럼니스트 신청이 없습니다.</p>
                <?php } elseif (empty($applications)) { ?>
                <p class="sebu-column-empty">신청 <?php echo number_format($pending_count); ?>건이 있으나 목록을 불러오지 못했습니다. <a href="<?php echo get_text($admin_url); ?>">신청 관리</a>에서 확인해 주세요.</p>
                <?php } else { ?>
                <p class="sebu-column-mypage-admin__summary">
                    승인 대기 <strong><?php echo number_format($pending_count); ?></strong>건
                    <?php if ($pending_count > count($applications)) { ?>
                    · 아래 <?php echo number_format(count($applications)); ?>건만 표시
                    <?php } ?>
                </p>
                <ul class="sebu-column-admin__applications">
                    <?php foreach ($applications as $application) {
                        echo eottae_column_render_admin_application_item_html($application);
                    } ?>
                </ul>
                <?php if ($pending_count > count($applications)) { ?>
                <p class="sebu-column-mypage-admin__more">
                    <a href="<?php echo get_text($admin_url); ?>">나머지 신청 <?php echo number_format($pending_count - count($applications)); ?>건 보기 →</a>
                </p>
                <?php } ?>
                <?php } ?>
            </div>
        </section>
        <?php

        echo ob_get_clean();
    }
}
