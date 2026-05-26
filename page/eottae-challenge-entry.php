<?php
include_once(dirname(__FILE__).'/_init.php');
include_once G5_LIB_PATH.'/eottae-challenge.lib.php';
include_once G5_LIB_PATH.'/eottae-challenge-likes.lib.php';
include_once G5_LIB_PATH.'/eottae-challenge-report.lib.php';

$entry_id = isset($_GET['entry_id']) ? (int) $_GET['entry_id'] : 0;
$entry = eottae_challenge_get_entry($entry_id, true);

if (!$entry) {
    alert('참여글을 찾을 수 없습니다.', eottae_challenge_list_url());
}

$is_owner = $is_member && ($member['mb_id'] ?? '') === ($entry['mb_id'] ?? '');
$is_super = ($is_admin === 'super');
$liked = $is_member ? eottae_challenge_member_liked($entry_id, $member['mb_id']) : false;
$comments = eottae_challenge_list_comments($entry_id);
$token = eottae_challenge_member_token();
$report_token = eottae_challenge_report_token();
$report_reasons = eottae_challenge_report_reasons();
$joined_msg = isset($_GET['msg']) ? get_text((string) $_GET['msg']) : '';

add_stylesheet('<link rel="stylesheet" href="'.G5_CSS_URL.'/eottae-challenge.css">', 24);
add_javascript('<script src="'.G5_JS_URL.'/eottae-challenge.js" defer></script>', 24);

g5_page_start(get_text($entry['title'] ?? '인증글'));
?>

<main class="sebu-challenge-page sebu-challenge-page--entry" data-sebu-challenge-entry data-entry-id="<?php echo (int) $entry_id; ?>">
    <p class="sebu-challenge-page__back">
        <a href="<?php echo eottae_challenge_view_url((int) ($entry['challenge_id'] ?? 0)); ?>">← <?php echo get_text($entry['challenge_title'] ?? '챌린지'); ?></a>
    </p>

    <?php if ($joined_msg !== '') { ?>
    <div class="sebu-challenge-alert sebu-challenge-alert--success" role="status"><?php echo $joined_msg; ?></div>
    <?php } ?>

    <article class="sebu-challenge-entry-view">
        <?php if (!empty($entry['is_best'])) { ?>
        <span class="sebu-challenge-badge sebu-challenge-badge--best">우수 인증글</span>
        <?php } ?>

        <h1 class="sebu-challenge-entry-view__title"><?php echo get_text($entry['title'] ?? ''); ?></h1>

        <p class="sebu-challenge-entry-view__meta">
            <?php echo get_text($entry['writer_name'] ?? ''); ?>
            <?php if (!empty($entry['area_label'])) { ?> · <?php echo get_text($entry['area_label']); ?><?php } ?>
            <?php if (!empty($entry['time_label'])) { ?> · <?php echo get_text($entry['time_label']); ?><?php } ?>
        </p>

        <?php if (!empty($entry['image_url'])) { ?>
        <figure class="sebu-challenge-entry-view__figure">
            <img src="<?php echo get_text($entry['image_url']); ?>" alt="" class="sebu-challenge-entry-view__image">
        </figure>
        <?php } ?>

        <div class="sebu-challenge-entry-view__content"><?php echo nl2br(get_text($entry['content'] ?? '')); ?></div>

        <?php if (!empty($entry['place_name']) || !empty($entry['related_url']) || !empty($entry['room_name'])) { ?>
        <dl class="sebu-challenge-entry-view__extra">
            <?php if (!empty($entry['place_name'])) { ?>
            <div><dt>장소</dt><dd><?php echo get_text($entry['place_name']); ?></dd></div>
            <?php } ?>
            <?php if (!empty($entry['room_name'])) { ?>
            <div><dt>세부톡방</dt><dd><?php echo get_text($entry['room_name']); ?></dd></div>
            <?php } ?>
            <?php if (!empty($entry['related_url'])) { ?>
            <div><dt>링크</dt><dd><a href="<?php echo get_text($entry['related_url']); ?>" target="_blank" rel="noopener noreferrer"><?php echo get_text($entry['related_url']); ?></a></dd></div>
            <?php } ?>
        </dl>
        <?php } ?>

        <div class="sebu-challenge-entry-view__actions">
            <?php if ($is_member) { ?>
            <button type="button" class="sebu-challenge-like-btn<?php echo $liked ? ' is-liked' : ''; ?>" data-sebu-challenge-like data-entry-id="<?php echo (int) $entry_id; ?>" data-token="<?php echo get_text($token); ?>">
                공감 <span data-sebu-challenge-like-count><?php echo number_format((int) ($entry['like_count'] ?? 0)); ?></span>
            </button>
            <?php } else { ?>
            <span class="sebu-challenge-like-btn is-disabled">공감 <?php echo number_format((int) ($entry['like_count'] ?? 0)); ?></span>
            <?php } ?>

            <?php if ($is_member && !$is_owner) { ?>
            <button type="button" class="sebu-challenge-btn sebu-challenge-btn--ghost sebu-challenge-btn--sm" data-sebu-challenge-report-open>신고</button>
            <?php } ?>

            <?php if ($is_owner || $is_super) { ?>
            <form method="post" action="<?php echo eottae_challenge_proc_url(); ?>" class="sebu-challenge-entry-delete-form" onsubmit="return confirm('삭제하시겠습니까?');">
                <input type="hidden" name="action" value="delete_entry">
                <input type="hidden" name="response" value="json">
                <input type="hidden" name="entry_id" value="<?php echo (int) $entry_id; ?>">
                <input type="hidden" name="eottae_challenge_token" value="<?php echo get_text($token); ?>">
                <button type="submit" class="sebu-challenge-btn sebu-challenge-btn--ghost sebu-challenge-btn--sm">삭제</button>
            </form>
            <?php } ?>
        </div>
    </article>

    <section class="sebu-challenge-comments" aria-labelledby="sebu-challenge-comments-title">
        <h2 class="sebu-challenge-comments__title" id="sebu-challenge-comments-title">댓글 <?php echo number_format(count($comments)); ?></h2>

        <?php if ($is_member) { ?>
        <form class="sebu-challenge-comment-form" data-sebu-challenge-comment-form>
            <textarea name="content" rows="3" class="sebu-challenge-form__textarea" placeholder="댓글을 남겨보세요." required maxlength="1000"></textarea>
            <button type="submit" class="sebu-challenge-btn sebu-challenge-btn--primary sebu-challenge-btn--sm">댓글 등록</button>
            <input type="hidden" name="action" value="add_comment">
            <input type="hidden" name="entry_id" value="<?php echo (int) $entry_id; ?>">
            <input type="hidden" name="eottae_challenge_token" value="<?php echo get_text($token); ?>">
        </form>
        <?php } else { ?>
        <p class="sebu-challenge-comments__login">댓글을 작성하려면 <a href="<?php echo function_exists('eottae_login_url') ? eottae_login_url(eottae_challenge_entry_url($entry_id)) : G5_BBS_URL.'/login.php'; ?>">로그인</a>해 주세요.</p>
        <?php } ?>

        <ul class="sebu-challenge-comments__list" data-sebu-challenge-comment-list>
            <?php foreach ($comments as $comment) { ?>
            <li class="sebu-challenge-comments__item">
                <strong><?php echo get_text($comment['writer_name'] ?? ''); ?></strong>
                <span class="sebu-challenge-comments__time"><?php echo get_text($comment['time_label'] ?? ''); ?></span>
                <p><?php echo get_text($comment['content'] ?? ''); ?></p>
            </li>
            <?php } ?>
        </ul>
    </section>

    <?php if ($is_member && !$is_owner) { ?>
    <dialog class="sebu-challenge-report-modal" data-sebu-challenge-report-modal>
        <form method="post" action="<?php echo eottae_challenge_proc_url(); ?>" class="sebu-challenge-report-form" data-sebu-challenge-report-form>
            <h3>신고하기</h3>
            <input type="hidden" name="action" value="report">
            <input type="hidden" name="response" value="json">
            <input type="hidden" name="entry_id" value="<?php echo (int) $entry_id; ?>">
            <input type="hidden" name="eottae_challenge_token" value="<?php echo get_text($token); ?>">
            <input type="hidden" name="eottae_challenge_report_token" value="<?php echo get_text($report_token); ?>">
            <label>
                <span>사유</span>
                <select name="reason" required>
                    <option value="">선택</option>
                    <?php foreach ($report_reasons as $code => $label) { ?>
                    <option value="<?php echo get_text($code); ?>"><?php echo get_text($label); ?></option>
                    <?php } ?>
                </select>
            </label>
            <label>
                <span>상세 (선택)</span>
                <textarea name="memo" rows="3" maxlength="1000"></textarea>
            </label>
            <div class="sebu-challenge-report-form__actions">
                <button type="button" class="sebu-challenge-btn sebu-challenge-btn--ghost" data-sebu-challenge-report-close>취소</button>
                <button type="submit" class="sebu-challenge-btn sebu-challenge-btn--primary">신고 접수</button>
            </div>
        </form>
    </dialog>
    <?php } ?>
</main>

<script>
window.eottaeChallengeProcUrl = <?php echo json_encode(eottae_challenge_proc_url(), JSON_UNESCAPED_UNICODE); ?>;
</script>

<?php
g5_page_end();
