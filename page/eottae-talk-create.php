<?php
include_once(dirname(__FILE__).'/_init.php');
include_once G5_LIB_PATH.'/eottae-talkroom.lib.php';
include_once G5_LIB_PATH.'/eottae-talkroom-apply-ai.lib.php';

if (!$is_member) {
    alert('로그인 후 톡방 개설을 신청할 수 있습니다.', eottae_login_url(eottae_talkroom_create_url()));
}

eottae_talkroom_upgrade_schema();

$apply_token = eottae_talkroom_apply_token();
$categories = eottae_talkroom_category_options();
$form_action = G5_URL.'/proc/eottae-talkroom-apply.php';
$ai_enabled = function_exists('g5site_cfg_bool') && g5site_cfg_bool('ai_generate_enabled', false)
    && function_exists('g5site_cfg') && trim((string) g5site_cfg('ai_generate_api_key', '')) !== '';

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

$eottae_ai_btn_icon = '<svg class="eottae-ai-btn__icon" width="16" height="16" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 2l1.8 5.5L19 9l-5.2 1.5L12 16l-1.8-5.5L5 9l5.2-1.5L12 2z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/><path d="M5 19h3M16 19h3M19 16v3" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>';

g5_page_start('톡방 만들기');
?>

<main class="mypage-subpage talk-apply-page">
    <p class="mypage-subpage__back"><a href="<?php echo eottae_talkroom_list_url(); ?>">← 세부톡방</a></p>
    <h1 class="mypage-subpage__title">톡방 만들기</h1>
    <p class="talk-apply-page__intro">톡방 정보를 입력하면 바로 공개되어 세부톡방 목록에 노출됩니다.</p>

    <form class="talk-apply-form" method="post" action="<?php echo $form_action; ?>" novalidate data-talk-apply-form>
        <input type="hidden" name="eottae_talkroom_token" value="<?php echo get_text($apply_token); ?>">

        <section class="talk-apply-form__ai-panel">
            <div class="talk-apply-form__ai-head">
                <div>
                    <h2 class="talk-apply-form__ai-title">AI 자동 작성</h2>
                    <p class="talk-apply-form__ai-desc">주제만 입력하면 이름·소개·규칙·신청 사유를 자동으로 채워 드립니다.<?php if (!$ai_enabled) { ?> (API 미설정 시 기본 템플릿으로 작성됩니다)<?php } ?></p>
                </div>
                <button type="button" class="eottae-ai-btn talk-apply-form__ai-btn" data-talk-apply-ai="all"><?php echo $eottae_ai_btn_icon; ?><span class="eottae-ai-btn__label">AI로 전체 자동 작성</span></button>
            </div>
            <div class="talk-apply-form__field">
                <label for="talk_topic_hint">만들고 싶은 톡방 주제 (AI 참고용)</label>
                <input type="text" id="talk_topic_hint" name="topic_hint" class="talk-apply-form__input" maxlength="200" placeholder="예: 주말 축구 모임, IT Park 맘카페, 세부 사업자 정보교류" value="">
            </div>
            <p class="talk-apply-form__ai-status" data-talk-apply-ai-status hidden></p>
        </section>

        <div class="talk-apply-form__field">
            <div class="talk-apply-form__label-row">
                <label for="talk_room_name">톡방 이름 <span class="talk-apply-form__required">*</span></label>
                <button type="button" class="talk-apply-form__field-ai" data-talk-apply-ai="room_name">AI 작성</button>
            </div>
            <input type="text" id="talk_room_name" name="room_name" class="talk-apply-form__input" maxlength="40" required placeholder="예: 세부 맘카페, 주말 골프 모임" value="<?php echo get_text($old['room_name']); ?>">
        </div>

        <div class="talk-apply-form__field">
            <div class="talk-apply-form__label-row">
                <label for="talk_room_description">톡방 한 줄 소개 <span class="talk-apply-form__required">*</span></label>
                <button type="button" class="talk-apply-form__field-ai" data-talk-apply-ai="room_description">AI 작성</button>
            </div>
            <input type="text" id="talk_room_description" name="room_description" class="talk-apply-form__input" maxlength="500" required placeholder="목록에 보이는 짧은 소개" value="<?php echo get_text($old['room_description']); ?>">
        </div>

        <div class="talk-apply-form__field">
            <div class="talk-apply-form__label-row">
                <label for="talk_room_detail">톡방 상세 설명 <span class="talk-apply-form__required">*</span></label>
                <button type="button" class="talk-apply-form__field-ai" data-talk-apply-ai="room_detail">AI 작성</button>
            </div>
            <textarea id="talk_room_detail" name="room_detail" class="talk-apply-form__textarea" rows="5" maxlength="5000" required placeholder="어떤 주제로, 누구와 소통하는 톡방인지 자세히 적어 주세요."><?php echo get_text($old['room_detail']); ?></textarea>
        </div>

        <div class="talk-apply-form__field">
            <div class="talk-apply-form__label-row">
                <label for="talk_category">카테고리 <span class="talk-apply-form__required">*</span></label>
                <button type="button" class="talk-apply-form__field-ai" data-talk-apply-ai="emoji">AI 추천</button>
            </div>
            <select id="talk_category" name="category" class="talk-apply-form__select" required>
                <option value="">선택해 주세요</option>
                <?php foreach ($categories as $code => $label) { ?>
                <option value="<?php echo get_text($code); ?>"<?php echo $old['category'] === $code ? ' selected' : ''; ?>><?php echo get_text($label); ?></option>
                <?php } ?>
            </select>
        </div>

        <div class="talk-apply-form__field talk-apply-form__field--emoji">
            <label for="talk_emoji">대표 이모지</label>
            <?php echo eottae_talkroom_render_emoji_picker($old['emoji'], 'talk_emoji'); ?>
        </div>

        <div class="talk-apply-form__field">
            <div class="talk-apply-form__label-row">
                <label for="talk_rules">운영 규칙 <span class="talk-apply-form__required">*</span></label>
                <button type="button" class="talk-apply-form__field-ai" data-talk-apply-ai="rules">AI 작성</button>
            </div>
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
            <div class="talk-apply-form__label-row">
                <label for="talk_apply_reason">신청 사유 <span class="talk-apply-form__required">*</span></label>
                <button type="button" class="talk-apply-form__field-ai" data-talk-apply-ai="apply_reason">AI 작성</button>
            </div>
            <textarea id="talk_apply_reason" name="apply_reason" class="talk-apply-form__textarea" rows="4" maxlength="2000" required placeholder="이 톡방이 필요한 이유, 운영 계획 등을 적어 주세요."><?php echo get_text($old['apply_reason']); ?></textarea>
        </div>

        <div class="talk-apply-form__actions">
            <button type="submit" class="talk-page__btn talk-page__btn--primary talk-apply-form__submit">톡방 만들기</button>
            <a href="<?php echo eottae_talkroom_apply_status_url(); ?>" class="talk-page__btn">내 신청 현황</a>
        </div>
    </form>
</main>

<?php
g5_page_end();
