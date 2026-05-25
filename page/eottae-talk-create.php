<?php
include_once(dirname(__FILE__).'/_init.php');
include_once G5_LIB_PATH.'/eottae-talkroom.lib.php';

if (!$is_member) {
    alert('로그인 후 톡방 개설을 신청할 수 있습니다.', eottae_login_url(eottae_talkroom_create_url()));
}

eottae_talkroom_upgrade_schema();

$apply_token = eottae_talkroom_apply_token();
$categories = eottae_talkroom_category_options();
$form_action = G5_URL.'/proc/eottae-talkroom-apply.php';

$old = array(
    'room_name'        => '',
    'room_description' => '',
    'room_detail'      => '',
    'category'         => '',
    'emoji'            => '💬',
    'rules'            => '',
    'contact'          => '',
    'apply_reason'     => '',
    'visibility'       => 'public',
    'join_type'        => 'open',
);

g5_page_start('톡방 만들기');
?>

<main class="mypage-subpage talk-apply-page">
    <p class="mypage-subpage__back"><a href="<?php echo eottae_talkroom_list_url(); ?>">← 세부톡방</a></p>
    <h1 class="mypage-subpage__title">톡방 만들기</h1>
    <p class="talk-apply-page__intro">톡방 개설 신청서를 작성해 주세요. 최고관리자 승인 후 목록에 노출됩니다.</p>

    <form class="talk-apply-form" method="post" action="<?php echo $form_action; ?>" novalidate>
        <input type="hidden" name="eottae_talkroom_token" value="<?php echo get_text($apply_token); ?>">

        <div class="talk-apply-form__field">
            <label for="talk_room_name">톡방 이름 <span class="talk-apply-form__required">*</span></label>
            <input type="text" id="talk_room_name" name="room_name" class="talk-apply-form__input" maxlength="40" required placeholder="예: 세부 맘카페, 주말 골프 모임" value="<?php echo get_text($old['room_name']); ?>">
        </div>

        <div class="talk-apply-form__field">
            <label for="talk_room_description">톡방 한 줄 소개 <span class="talk-apply-form__required">*</span></label>
            <input type="text" id="talk_room_description" name="room_description" class="talk-apply-form__input" maxlength="500" required placeholder="목록에 보이는 짧은 소개" value="<?php echo get_text($old['room_description']); ?>">
        </div>

        <div class="talk-apply-form__field">
            <label for="talk_room_detail">톡방 상세 설명 <span class="talk-apply-form__required">*</span></label>
            <textarea id="talk_room_detail" name="room_detail" class="talk-apply-form__textarea" rows="5" maxlength="5000" required placeholder="어떤 주제로, 누구와 소통하는 톡방인지 자세히 적어 주세요."><?php echo get_text($old['room_detail']); ?></textarea>
        </div>

        <div class="talk-apply-form__field">
            <label for="talk_category">카테고리 <span class="talk-apply-form__required">*</span></label>
            <select id="talk_category" name="category" class="talk-apply-form__select" required>
                <option value="">선택해 주세요</option>
                <?php foreach ($categories as $code => $label) { ?>
                <option value="<?php echo get_text($code); ?>"<?php echo $old['category'] === $code ? ' selected' : ''; ?>><?php echo get_text($label); ?></option>
                <?php } ?>
            </select>
        </div>

        <div class="talk-apply-form__field talk-apply-form__field--emoji">
            <label for="talk_emoji">대표 이모지</label>
            <input type="text" id="talk_emoji" name="emoji" class="talk-apply-form__input talk-apply-form__input--emoji" maxlength="8" placeholder="💬" value="<?php echo get_text($old['emoji']); ?>">
            <p class="talk-apply-form__hint">목록 카드에 표시됩니다. 비워두면 💬 가 사용됩니다.</p>
        </div>

        <div class="talk-apply-form__field">
            <label for="talk_rules">운영 규칙 <span class="talk-apply-form__required">*</span></label>
            <textarea id="talk_rules" name="rules" class="talk-apply-form__textarea" rows="4" maxlength="5000" required placeholder="예: 광고·욕설 금지, 세부 지역 관련 정보만 공유"><?php echo get_text($old['rules']); ?></textarea>
        </div>

        <div class="talk-apply-form__field">
            <label for="talk_contact">방장 연락처 또는 카카오톡 ID <span class="talk-apply-form__required">*</span></label>
            <input type="text" id="talk_contact" name="contact" class="talk-apply-form__input" maxlength="255" required placeholder="관리자·승인 문의용 (목록에 노출되지 않음)" value="<?php echo get_text($old['contact']); ?>">
        </div>

        <fieldset class="talk-apply-form__fieldset">
            <legend>공개 여부 <span class="talk-apply-form__required">*</span></legend>
            <label class="talk-apply-form__radio">
                <input type="radio" name="visibility" value="public"<?php echo $old['visibility'] === 'public' ? ' checked' : ''; ?>>
                <span>공개 톡방</span>
            </label>
            <label class="talk-apply-form__radio">
                <input type="radio" name="visibility" value="private"<?php echo $old['visibility'] === 'private' ? ' checked' : ''; ?>>
                <span>비공개 톡방</span>
            </label>
        </fieldset>

        <fieldset class="talk-apply-form__fieldset">
            <legend>가입 방식 <span class="talk-apply-form__required">*</span></legend>
            <label class="talk-apply-form__radio">
                <input type="radio" name="join_type" value="open"<?php echo $old['join_type'] === 'open' ? ' checked' : ''; ?>>
                <span>누구나 참여 가능</span>
            </label>
            <label class="talk-apply-form__radio">
                <input type="radio" name="join_type" value="approval"<?php echo $old['join_type'] === 'approval' ? ' checked' : ''; ?>>
                <span>방장 승인 후 참여 가능</span>
            </label>
        </fieldset>

        <div class="talk-apply-form__field">
            <label for="talk_apply_reason">신청 사유 <span class="talk-apply-form__required">*</span></label>
            <textarea id="talk_apply_reason" name="apply_reason" class="talk-apply-form__textarea" rows="4" maxlength="2000" required placeholder="이 톡방이 필요한 이유, 운영 계획 등을 적어 주세요."><?php echo get_text($old['apply_reason']); ?></textarea>
        </div>

        <div class="talk-apply-form__actions">
            <button type="submit" class="talk-page__btn talk-page__btn--primary talk-apply-form__submit">개설 신청하기</button>
            <a href="<?php echo eottae_talkroom_apply_status_url(); ?>" class="talk-page__btn">내 신청 현황</a>
        </div>
    </form>
</main>

<?php
g5_page_end();
