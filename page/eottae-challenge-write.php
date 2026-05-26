<?php
include_once(dirname(__FILE__).'/_init.php');
include_once G5_LIB_PATH.'/eottae-challenge.lib.php';
include_once G5_LIB_PATH.'/eottae-talkroom.lib.php';

$challenge_id = isset($_GET['challenge_id']) ? (int) $_GET['challenge_id'] : 0;
$write_url = eottae_challenge_write_url($challenge_id);

if (!$is_member) {
    alert('로그인 후 참여할 수 있습니다.', function_exists('eottae_login_url') ? eottae_login_url($write_url) : G5_BBS_URL.'/login.php');
}

$check = eottae_challenge_can_participate($challenge_id, $member['mb_id']);
if (empty($check['ok'])) {
    alert($check['message'] ?? '참여할 수 없습니다.', eottae_challenge_view_url($challenge_id));
}

$challenge = $check['challenge'];
$token = eottae_challenge_member_token();
$room_options = eottae_challenge_member_room_options($member['mb_id']);
$areas = eottae_challenge_area_options();

add_stylesheet('<link rel="stylesheet" href="'.G5_CSS_URL.'/eottae-challenge.css">', 24);

g5_page_start('챌린지 참여');
?>

<main class="sebu-challenge-page sebu-challenge-page--form">
    <p class="sebu-challenge-page__back"><a href="<?php echo eottae_challenge_view_url($challenge_id); ?>">← <?php echo get_text($challenge['title'] ?? '챌린지'); ?></a></p>
    <h1 class="sebu-challenge-page__title">인증글 작성</h1>
    <p class="sebu-challenge-page__intro">사진과 함께 챌린지에 참여해 주세요.</p>

    <form class="sebu-challenge-form" method="post" action="<?php echo eottae_challenge_proc_url(); ?>" enctype="multipart/form-data">
        <input type="hidden" name="action" value="create_entry">
        <input type="hidden" name="challenge_id" value="<?php echo (int) $challenge_id; ?>">
        <input type="hidden" name="eottae_challenge_token" value="<?php echo get_text($token); ?>">

        <label class="sebu-challenge-form__field">
            <span class="sebu-challenge-form__label">제목 <span class="required">*</span></span>
            <input type="text" name="title" class="sebu-challenge-form__input" maxlength="200" required placeholder="한 줄 제목">
        </label>

        <label class="sebu-challenge-form__field">
            <span class="sebu-challenge-form__label">내용 <span class="required">*</span></span>
            <textarea name="content" class="sebu-challenge-form__textarea" rows="6" required placeholder="후기, 팁, 경험을 자유롭게 작성해 주세요."></textarea>
        </label>

        <label class="sebu-challenge-form__field">
            <span class="sebu-challenge-form__label">사진 첨부</span>
            <input type="file" name="entry_image" class="sebu-challenge-form__file" accept="image/jpeg,image/png,image/gif,image/webp">
            <span class="sebu-challenge-form__hint">JPG, PNG, GIF, WEBP · 최대 5MB</span>
        </label>

        <label class="sebu-challenge-form__field">
            <span class="sebu-challenge-form__label">지역</span>
            <select name="area" class="sebu-challenge-form__select">
                <?php foreach ($areas as $code => $label) { ?>
                <option value="<?php echo get_text($code); ?>"><?php echo get_text($label); ?></option>
                <?php } ?>
            </select>
        </label>

        <label class="sebu-challenge-form__field">
            <span class="sebu-challenge-form__label">관련 장소명</span>
            <input type="text" name="place_name" class="sebu-challenge-form__input" maxlength="200" placeholder="예: SM Seaside City 맛집">
        </label>

        <label class="sebu-challenge-form__field">
            <span class="sebu-challenge-form__label">관련 링크</span>
            <input type="url" name="related_url" class="sebu-challenge-form__input" maxlength="500" placeholder="https://">
        </label>

        <?php if (!empty($room_options)) { ?>
        <label class="sebu-challenge-form__field">
            <span class="sebu-challenge-form__label">관련 세부톡방</span>
            <select name="related_room_id" class="sebu-challenge-form__select">
                <option value="0">선택 안 함</option>
                <?php foreach ($room_options as $room_id => $room_name) { ?>
                <option value="<?php echo (int) $room_id; ?>"><?php echo get_text($room_name); ?></option>
                <?php } ?>
            </select>
        </label>
        <?php } ?>

        <label class="sebu-challenge-form__field sebu-challenge-form__field--checkbox">
            <input type="checkbox" name="is_public" value="1" checked>
            <span>다른 회원에게 공개</span>
        </label>

        <div class="sebu-challenge-form__actions">
            <button type="submit" class="sebu-challenge-btn sebu-challenge-btn--primary sebu-challenge-btn--lg">참여 완료</button>
            <a href="<?php echo eottae_challenge_view_url($challenge_id); ?>" class="sebu-challenge-btn sebu-challenge-btn--ghost">취소</a>
        </div>
    </form>
</main>

<?php
g5_page_end();
