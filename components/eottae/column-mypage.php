<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('eottae_column_mypage_section_html')) {
    function eottae_column_mypage_section_html(array $member, $is_columnist = false)
    {
        include_once G5_LIB_PATH.'/eottae-column.lib.php';
        include_once G5_LIB_PATH.'/eottae-column-likes.lib.php';
        include_once G5_LIB_PATH.'/eottae-column-bookmarks.lib.php';
        include_once G5_PATH.'/components/eottae/column-card.php';
        include_once G5_PATH.'/components/eottae/column-author-profile.php';

        $mb_id = $member['mb_id'] ?? '';
        $author = eottae_column_get_author($mb_id);
        $is_columnist = $is_columnist || eottae_column_is_columnist($mb_id);
        $latest_application = !$is_columnist ? eottae_column_get_latest_application($mb_id) : null;

        ob_start();
        ?>
        <section class="sebu-column-mypage" aria-labelledby="sebu-column-mypage-title">
            <h2 class="sebu-column-mypage__title" id="sebu-column-mypage-title"><?php echo function_exists('eottae_column_menu_label') ? eottae_column_menu_label() : '컬럼'; ?></h2>

            <?php if ($is_columnist && $author) {
                $stats = $author['stats'] ?? array();
                ?>
            <div class="sebu-column-mypage__profile">
                <?php echo eottae_column_render_avatar_html($author, 'sm', 'sebu-column-mypage__avatar'); ?>
                <div>
                    <p class="sebu-column-mypage__name"><?php echo get_text($author['display_name'] ?? ''); ?></p>
                    <p class="sebu-column-mypage__grade"><?php echo get_text($author['grade_label'] ?? ''); ?> · <?php echo get_text($author['title'] ?? ''); ?></p>
                    <p class="sebu-column-mypage__stats">
                        컬럼 <?php echo number_format((int) ($stats['column_count'] ?? 0)); ?>개
                        · 조회 <?php echo number_format((int) ($stats['total_views'] ?? 0)); ?>
                        · 공감 <?php echo number_format((int) ($stats['total_likes'] ?? 0)); ?>
                    </p>
                </div>
            </div>
            <p class="sebu-column-mypage__actions">
                <a href="<?php echo eottae_column_write_url(); ?>" class="sebu-column-btn sebu-column-btn--primary">새 컬럼 작성</a>
                <a href="<?php echo eottae_column_mypage_url(); ?>" class="sebu-column-btn sebu-column-btn--ghost">내 컬럼 관리</a>
                <a href="<?php echo eottae_column_author_url($mb_id); ?>" class="sebu-column-btn sebu-column-btn--ghost">프로필 보기</a>
            </p>
            <?php
                $drafts = eottae_column_list(array('mb_id' => $mb_id, 'status' => 'draft', 'include_hidden' => true, 'limit' => 3));
                $recent = eottae_column_list(array('mb_id' => $mb_id, 'limit' => 3));
                if (!empty($drafts)) {
                    echo '<h3 class="sebu-column-mypage__subtitle">임시저장</h3><ul class="sebu-column-mypage__list">';
                    foreach ($drafts as $post) {
                        echo '<li>'.eottae_column_card_html($post, 'compact').'</li>';
                    }
                    echo '</ul>';
                }
                if (!empty($recent)) {
                    echo '<h3 class="sebu-column-mypage__subtitle">최근 작성</h3><ul class="sebu-column-mypage__list">';
                    foreach ($recent as $post) {
                        echo '<li>'.eottae_column_card_html($post, 'compact').'</li>';
                    }
                    echo '</ul>';
                }
            } else { ?>
            <p class="sebu-column-mypage__guest">세부 생활정보를 전문적으로 전달하는 칼럼니스트가 되어보세요.</p>
            <p class="sebu-column-mypage__actions">
                <a href="<?php echo eottae_column_list_url(); ?>" class="sebu-column-btn sebu-column-btn--ghost">컬럼 읽기</a>
                <?php if ($latest_application && ($latest_application['status'] ?? '') === 'pending') { ?>
                <span class="sebu-column-btn sebu-column-btn--disabled">칼럼니스트 신청 검토중</span>
                <?php } else { ?>
                <a href="<?php echo eottae_column_apply_url(); ?>" class="sebu-column-btn sebu-column-btn--primary">칼럼니스트 신청하기</a>
                <?php } ?>
            </p>
            <?php } ?>
        </section>
        <?php

        return (string) ob_get_clean();
    }
}
