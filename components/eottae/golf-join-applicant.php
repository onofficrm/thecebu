<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('eottae_golf_join_applicant_html')) {
    /**
     * @param array<string, mixed> $applicant
     */
    function eottae_golf_join_applicant_html(array $applicant)
    {
        $id = (int) ($applicant['id'] ?? 0);
        $nick = get_text($applicant['nickname'] ?? '회원');
        $message = trim((string) ($applicant['message'] ?? ''));

        ob_start();
        ?>
        <li class="golf-join-applicant" data-member-id="<?php echo $id; ?>">
            <div class="golf-join-applicant__head">
                <strong class="golf-join-applicant__name"><?php echo $nick; ?></strong>
                <time class="golf-join-applicant__date" datetime="<?php echo get_text($applicant['created_at'] ?? ''); ?>">
                    <?php echo get_text($applicant['created_at_label'] ?? ''); ?>
                </time>
            </div>
            <dl class="golf-join-applicant__meta">
                <div><dt>성별</dt><dd><?php echo get_text($applicant['gender_label'] ?? ''); ?></dd></div>
                <div><dt>나이대</dt><dd><?php echo get_text($applicant['age_group_label'] ?? ''); ?></dd></div>
                <div><dt>타수</dt><dd><?php echo get_text($applicant['score_range_label'] ?? ''); ?></dd></div>
            </dl>
            <?php if ($message !== '') { ?>
            <p class="golf-join-applicant__message"><?php echo nl2br(get_text($message)); ?></p>
            <?php } else { ?>
            <p class="golf-join-applicant__message golf-join-applicant__message--empty">신청 메시지 없음</p>
            <?php } ?>
            <div class="golf-join-applicant__actions">
                <button type="button" class="golf-join-applicant__btn golf-join-applicant__btn--approve" data-action="approve" data-member-id="<?php echo $id; ?>">승인</button>
                <button type="button" class="golf-join-applicant__btn golf-join-applicant__btn--reject" data-action="reject" data-member-id="<?php echo $id; ?>">거절</button>
            </div>
        </li>
        <?php

        return (string) ob_get_clean();
    }
}
