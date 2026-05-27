<?php
include_once(dirname(__FILE__).'/_init.php');
include_once G5_LIB_PATH.'/eottae-golf-join.lib.php';

if (!$is_member || empty($member['mb_id'])) {
    alert(
        '로그인 후 골프조인을 등록할 수 있습니다.',
        function_exists('eottae_login_url') ? eottae_login_url(eottae_golf_join_create_url()) : G5_BBS_URL.'/login.php'
    );
}

eottae_golf_join_ensure_schema();

$member_token = eottae_golf_join_member_token();
$host_profile = eottae_golf_join_host_profile_from_member($member);
$host_labels = eottae_golf_join_host_profile_labels($host_profile);
$courses = eottae_golf_join_list_courses();
$courses_json = json_encode($courses, JSON_UNESCAPED_UNICODE);
$regions = eottae_golf_join_region_options();
$schedule_slots = eottae_golf_join_schedule_slot_options();
$recruit_slots = eottae_golf_join_recruit_slot_options();
$gender_options = eottae_golf_join_gender_preference_detail_labels();
$age_options = eottae_golf_join_age_preference_options();
$score_options = eottae_golf_join_score_preference_options();
$mood_tags = eottae_golf_join_mood_tag_options();
$register_modes = eottae_golf_join_register_mode_options();
$proc_url = eottae_golf_join_proc_url();
$list_url = eottae_golf_join_list_url();
$profile_edit_url = eottae_golf_join_profile_edit_url();

$prefill_mode = isset($_GET['mode']) ? preg_replace('/[^a-z_]/', '', (string) $_GET['mode']) : '';
if (!in_array($prefill_mode, array('fixed_tee', 'members_first'), true)) {
    $prefill_mode = '';
}

$host_age_options = array(
    '20s' => '20대', '30s' => '30대', '40s' => '40대', '50plus' => '50대 이상',
);
$host_gender_options = array('M' => '남성', 'F' => '여성');

g5_page_start('골프조인 만들기');
?>

<main class="golf-join-page golf-join-page--create" id="golf-join-create">
    <header class="golf-join-topbar">
        <a href="<?php echo $list_url; ?>" class="golf-join-topbar__back" aria-label="뒤로가기"><span aria-hidden="true">←</span></a>
        <h1 class="golf-join-topbar__title">골프조인 만들기</h1>
        <span class="golf-join-topbar__search" aria-hidden="true"></span>
    </header>

    <form class="golf-join-create-form" id="golf-join-create-form" method="post" action="<?php echo $proc_url; ?>" novalidate>
        <input type="hidden" name="action" value="create">
        <input type="hidden" name="eottae_golf_join_token" value="<?php echo get_text($member_token); ?>">
        <input type="hidden" name="register_mode" id="golf-join-register-mode" value="<?php echo get_text($prefill_mode); ?>">
        <input type="hidden" name="round_date" id="golf-join-round-date" value="">
        <input type="hidden" name="schedule_slot" id="golf-join-schedule-slot" value="">
        <input type="hidden" name="golf_course_id" id="golf-join-course-id" value="0">
        <input type="hidden" name="golf_course_name" id="golf-join-course-name" value="">
        <input type="hidden" name="region" id="golf-join-region" value="">
        <input type="hidden" name="recruit_slots" id="golf-join-recruit-slots" value="">
        <input type="hidden" name="gender_preference" id="golf-join-gender-pref" value="">
        <input type="hidden" name="age_preference" id="golf-join-age-pref" value="">
        <input type="hidden" name="score_preference" id="golf-join-score-pref" value="">
        <input type="hidden" name="host_nickname" value="<?php echo get_text($host_profile['nickname']); ?>">
        <input type="hidden" name="host_gender" id="golf-join-host-gender" value="<?php echo get_text($host_profile['gender']); ?>">
        <input type="hidden" name="host_age_group" id="golf-join-host-age" value="<?php echo get_text($host_profile['age_group']); ?>">
        <input type="hidden" name="host_score_range" id="golf-join-host-score" value="<?php echo get_text($host_profile['score_range']); ?>">

        <p class="golf-join-create-mode-hint" id="golf-join-mode-hint"<?php echo $prefill_mode === '' ? ' hidden' : ''; ?>>
            <span class="golf-join-create-mode-hint__label" id="golf-join-mode-label"></span>
            <button type="button" class="golf-join-create-mode-hint__change" id="golf-join-mode-change">변경</button>
        </p>

        <section class="golf-join-create-section" aria-labelledby="golf-join-sec-schedule">
            <h2 class="golf-join-create-section__title" id="golf-join-sec-schedule">라운드 일정</h2>

            <div class="golf-join-create-field">
                <p class="golf-join-create-field__label">날짜 <span class="golf-join-create-required">*</span></p>
                <input type="date" class="golf-join-create-date" id="golf-join-date-input" required min="<?php echo date('Y-m-d'); ?>">
            </div>

            <div class="golf-join-create-field">
                <p class="golf-join-create-field__label">시간대 <span class="golf-join-create-required">*</span></p>
                <div class="golf-join-option-grid golf-join-option-grid--2" data-option-group="schedule_slot">
                    <?php foreach ($schedule_slots as $code => $label) { ?>
                    <button type="button" class="golf-join-option-btn" data-value="<?php echo get_text($code); ?>"><?php echo get_text($label); ?></button>
                    <?php } ?>
                </div>
            </div>

            <div class="golf-join-create-field" id="golf-join-tee-time-wrap" hidden>
                <p class="golf-join-create-field__label">티타임 (선택)</p>
                <input type="time" class="golf-join-create-date" name="tee_time" id="golf-join-tee-time">
            </div>

            <div class="golf-join-create-field">
                <p class="golf-join-create-field__label">지역</p>
                <div class="golf-join-option-grid golf-join-option-grid--scroll" data-option-group="region_filter">
                    <button type="button" class="golf-join-option-btn is-active" data-value="">전체</button>
                    <?php foreach ($regions as $code => $label) { ?>
                    <button type="button" class="golf-join-option-btn" data-value="<?php echo get_text($code); ?>"><?php echo get_text($label); ?></button>
                    <?php } ?>
                </div>
            </div>

            <div class="golf-join-create-field">
                <p class="golf-join-create-field__label">골프장 <span class="golf-join-create-required">*</span></p>
                <div class="golf-join-course-list" id="golf-join-course-list" role="listbox" aria-label="골프장 선택"></div>
                <p class="golf-join-create-field__hint">목록에 없으면 직접 입력해 주세요.</p>
                <input type="text" class="golf-join-create-text" name="golf_course_custom" id="golf-join-course-custom" maxlength="120" placeholder="골프장명 직접 입력">
            </div>
        </section>

        <section class="golf-join-create-section" aria-labelledby="golf-join-sec-recruit">
            <h2 class="golf-join-create-section__title" id="golf-join-sec-recruit">모집 인원 <span class="golf-join-create-required">*</span></h2>
            <p class="golf-join-create-field__hint">방장을 제외하고 함께할 멤버 수입니다.</p>
            <div class="golf-join-option-grid golf-join-option-grid--3" data-option-group="recruit_slots">
                <?php foreach ($recruit_slots as $num => $label) { ?>
                <button type="button" class="golf-join-option-btn" data-value="<?php echo (int) $num; ?>"><?php echo get_text($label); ?></button>
                <?php } ?>
            </div>
        </section>

        <section class="golf-join-create-section" aria-labelledby="golf-join-sec-pref">
            <h2 class="golf-join-create-section__title" id="golf-join-sec-pref">원하는 멤버 조건</h2>

            <div class="golf-join-create-field">
                <p class="golf-join-create-field__label">성별 <span class="golf-join-create-required">*</span></p>
                <div class="golf-join-option-grid golf-join-option-grid--2" data-option-group="gender_preference">
                    <?php foreach ($gender_options as $code => $label) { ?>
                    <button type="button" class="golf-join-option-btn" data-value="<?php echo get_text($code); ?>"><?php echo get_text($label); ?></button>
                    <?php } ?>
                </div>
            </div>

            <div class="golf-join-create-field">
                <p class="golf-join-create-field__label">나이 <span class="golf-join-create-required">*</span></p>
                <div class="golf-join-option-grid golf-join-option-grid--scroll" data-option-group="age_preference">
                    <?php foreach ($age_options as $code => $label) { ?>
                    <button type="button" class="golf-join-option-btn" data-value="<?php echo get_text($code); ?>"><?php echo get_text($label); ?></button>
                    <?php } ?>
                </div>
            </div>

            <div class="golf-join-create-field">
                <p class="golf-join-create-field__label">타수 <span class="golf-join-create-required">*</span></p>
                <div class="golf-join-option-grid golf-join-option-grid--scroll" data-option-group="score_preference">
                    <?php foreach ($score_options as $code => $label) { ?>
                    <button type="button" class="golf-join-option-btn" data-value="<?php echo get_text($code); ?>"><?php echo get_text($label); ?></button>
                    <?php } ?>
                </div>
            </div>
        </section>

        <section class="golf-join-create-section" aria-labelledby="golf-join-sec-intro">
            <h2 class="golf-join-create-section__title" id="golf-join-sec-intro">우리 방 소개</h2>

            <div class="golf-join-create-field">
                <label class="golf-join-create-field__label" for="golf-join-title">방 제목 <span class="golf-join-create-required">*</span></label>
                <input type="text" class="golf-join-create-text" name="title" id="golf-join-title" maxlength="120" required placeholder="예: 주말 오전 세부 라운드 같이 가요">
            </div>

            <div class="golf-join-create-field">
                <label class="golf-join-create-field__label" for="golf-join-description">방 소개글 <span class="golf-join-create-required">*</span></label>
                <textarea class="golf-join-create-textarea" name="description" id="golf-join-description" rows="5" maxlength="2000" required placeholder="라운드 분위기, 준비물, 만남 장소 등을 적어 주세요."></textarea>
            </div>

            <div class="golf-join-create-field">
                <p class="golf-join-create-field__label">분위기 태그 <span class="golf-join-create-field__sub">(최대 3개)</span></p>
                <div class="golf-join-option-grid golf-join-option-grid--scroll" data-option-group="mood_tags" data-multi="1" data-max="3">
                    <?php foreach ($mood_tags as $tag) { ?>
                    <button type="button" class="golf-join-option-btn golf-join-option-btn--tag" data-value="<?php echo get_text($tag); ?>">#<?php echo get_text($tag); ?></button>
                    <?php } ?>
                </div>
            </div>
        </section>

        <section class="golf-join-create-section golf-join-create-section--profile" aria-labelledby="golf-join-sec-profile">
            <div class="golf-join-create-section__head">
                <h2 class="golf-join-create-section__title" id="golf-join-sec-profile">내 정보 확인</h2>
                <button type="button" class="golf-join-create-profile-edit" id="golf-join-profile-toggle">수정</button>
            </div>

            <dl class="golf-join-create-profile" id="golf-join-profile-view">
                <div><dt>닉네임</dt><dd><?php echo get_text($host_profile['nickname']); ?></dd></div>
                <div><dt>성별</dt><dd id="golf-join-profile-gender-label"><?php echo get_text($host_labels['gender']); ?></dd></div>
                <div><dt>나이대</dt><dd id="golf-join-profile-age-label"><?php echo get_text($host_labels['age']); ?></dd></div>
                <div><dt>타수</dt><dd id="golf-join-profile-score-label"><?php echo get_text($host_labels['score']); ?></dd></div>
            </dl>

            <div class="golf-join-create-profile-edit-panel" id="golf-join-profile-edit" hidden>
                <p class="golf-join-create-field__label">성별</p>
                <div class="golf-join-option-grid golf-join-option-grid--2" data-option-group="host_gender">
                    <?php foreach ($host_gender_options as $code => $label) { ?>
                    <button type="button" class="golf-join-option-btn<?php echo ($host_profile['gender'] ?? '') === $code ? ' is-active' : ''; ?>" data-value="<?php echo get_text($code); ?>"><?php echo get_text($label); ?></button>
                    <?php } ?>
                </div>
                <p class="golf-join-create-field__label">나이대</p>
                <div class="golf-join-option-grid golf-join-option-grid--scroll" data-option-group="host_age_group">
                    <?php foreach ($host_age_options as $code => $label) { ?>
                    <button type="button" class="golf-join-option-btn<?php echo ($host_profile['age_group'] ?? '') === $code ? ' is-active' : ''; ?>" data-value="<?php echo get_text($code); ?>"><?php echo get_text($label); ?></button>
                    <?php } ?>
                </div>
                <p class="golf-join-create-field__label">타수</p>
                <div class="golf-join-option-grid golf-join-option-grid--scroll" data-option-group="host_score_range">
                    <?php foreach ($score_options as $code => $label) { ?>
                    <button type="button" class="golf-join-option-btn<?php echo ($host_profile['score_range'] ?? '') === $code ? ' is-active' : ''; ?>" data-value="<?php echo get_text($code); ?>"><?php echo get_text($label); ?></button>
                    <?php } ?>
                </div>
                <p class="golf-join-create-field__hint">
                    <a href="<?php echo get_text($profile_edit_url); ?>">회원정보 수정</a>에서 저장하면 다음부터 자동 반영됩니다.
                </p>
            </div>
        </section>

        <div class="golf-join-create-spacer" aria-hidden="true"></div>
    </form>

    <footer class="golf-join-detail-bar golf-join-create-bar">
        <button type="submit" form="golf-join-create-form" class="golf-join-detail-bar__btn golf-join-detail-bar__btn--primary" id="golf-join-submit-btn">
            조인 등록하기
        </button>
    </footer>
</main>

<div class="golf-join-sheet" id="golf-join-mode-sheet" role="dialog" aria-modal="true" aria-labelledby="golf-join-mode-sheet-title"<?php echo $prefill_mode !== '' ? ' hidden' : ''; ?>>
    <div class="golf-join-sheet__backdrop" data-sheet-close></div>
    <div class="golf-join-sheet__panel">
        <div class="golf-join-sheet__handle" aria-hidden="true"></div>
        <h2 class="golf-join-sheet__title" id="golf-join-mode-sheet-title">어떤 방식으로 조인을 등록할까요?</h2>
        <ul class="golf-join-mode-list">
            <?php foreach ($register_modes as $code => $item) { ?>
            <li>
                <button type="button" class="golf-join-mode-card" data-register-mode="<?php echo get_text($code); ?>">
                    <span class="golf-join-mode-card__title"><?php echo get_text($item['title']); ?></span>
                    <span class="golf-join-mode-card__desc"><?php echo get_text($item['desc']); ?></span>
                </button>
            </li>
            <?php } ?>
        </ul>
    </div>
</div>

<script>
window.EOTTaeGolfJoinCreate = {
    courses: <?php echo $courses_json ?: '[]'; ?>,
    registerModes: <?php echo json_encode($register_modes, JSON_UNESCAPED_UNICODE); ?>,
    prefillMode: <?php echo json_encode($prefill_mode, JSON_UNESCAPED_UNICODE); ?>,
    hostLabels: {
        gender: <?php echo json_encode($host_gender_options, JSON_UNESCAPED_UNICODE); ?>,
        age: <?php echo json_encode($host_age_options, JSON_UNESCAPED_UNICODE); ?>,
        score: <?php echo json_encode($score_options, JSON_UNESCAPED_UNICODE); ?>
    }
};
</script>

<?php
add_javascript('<script src="'.G5_JS_URL.'/eottae-golf-join-create.js" defer></script>', 25);
g5_page_end();
