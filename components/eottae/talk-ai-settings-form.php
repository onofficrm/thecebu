<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('eottae_talkroom_ai_min_messages_per_day')) {
    include_once G5_LIB_PATH.'/eottae-talkroom-ai-guard.lib.php';
}

/**
 * @param array<string, mixed> $settings
 * @param array<string, mixed> $options
 */
function eottae_talkroom_ai_render_settings_form(array $settings, array $options = array())
{
    $room_id = (int) ($options['room_id'] ?? 0);
    $readonly = !empty($options['readonly']);
    $token = (string) ($options['token'] ?? '');
    $token_field = (string) ($options['token_field'] ?? 'eottae_talkroom_owner_token');
    $saved = !empty($options['saved']);
    $policy_notices = isset($options['policy_notices']) && is_array($options['policy_notices']) ? $options['policy_notices'] : array();

    $name_options = eottae_talkroom_ai_name_options();
    $persona_options = eottae_talkroom_ai_persona_options();
    $tone_options = eottae_talkroom_ai_tone_options();
    $feature_fields = eottae_talkroom_ai_feature_fields();

    if (!isset($name_options[$settings['ai_name']])) {
        $settings['ai_name'] = '어때봇';
    }
    if (!isset($persona_options[$settings['ai_persona']])) {
        $settings['ai_persona'] = 'community_manager';
    }
    if (!isset($tone_options[$settings['ai_tone']])) {
        $settings['ai_tone'] = 'friendly';
    }
    ?>
    <?php if ($saved) { ?>
    <p class="talk-ai-settings__saved" role="status">AI 도우미 설정이 저장되었습니다.</p>
    <?php } ?>

    <?php foreach ($policy_notices as $notice) { ?>
    <p class="talk-ai-settings__notice talk-ai-settings__notice--blocked"><?php echo get_text($notice); ?></p>
    <?php } ?>

    <p class="talk-ai-settings__intro">
        AI 도우미는 톡방 분위기를 해치지 않도록, 대화 맥락에 맞을 때만 질문·모임 제안·요약·리액션을 작성합니다. 하루 최대 발언 수는 안전장치이며, 실제 참여는 방 상황에 따라 더 적을 수 있습니다.
    </p>

    <?php if ($readonly) { ?>
    <p class="talk-ai-settings__notice talk-ai-settings__notice--blocked">
        최고관리자가 톡방 AI 설정을 일시 중지했습니다. 설정을 변경할 수 없습니다.
    </p>
    <?php } ?>

    <form class="talk-apply-form talk-ai-settings-form" id="talkAiSettingsForm" method="post" action="<?php echo G5_URL; ?>/proc/eottae-talkroom-ai-settings.php"<?php echo $readonly ? ' data-readonly="1"' : ''; ?>>
        <input type="hidden" name="action" value="save_settings">
        <input type="hidden" name="room_id" value="<?php echo (int) $room_id; ?>">
        <input type="hidden" name="<?php echo get_text($token_field); ?>" value="<?php echo get_text($token); ?>">

        <div class="talk-ai-settings-form__body">
        <fieldset class="talk-apply-form__fieldset talk-ai-settings__toggle-group"<?php echo $readonly ? ' disabled' : ''; ?>>
            <legend>AI 사용 여부</legend>
            <label class="talk-ai-settings__switch talk-ai-settings__switch--master">
                <input type="checkbox" name="ai_enabled" value="1" class="talk-ai-settings__switch-input"<?php echo !empty($settings['ai_enabled']) ? ' checked' : ''; ?>>
                <span class="talk-ai-settings__switch-ui" aria-hidden="true"></span>
                <span class="talk-ai-settings__switch-label">AI 도우미 사용</span>
            </label>
        </fieldset>

        <div class="talk-apply-form__field">
            <label for="talk_ai_name">AI 이름</label>
            <select id="talk_ai_name" name="ai_name" class="talk-apply-form__select"<?php echo $readonly ? ' disabled' : ''; ?>>
                <?php foreach ($name_options as $value => $label) { ?>
                <option value="<?php echo get_text($value); ?>"<?php echo $settings['ai_name'] === $value ? ' selected' : ''; ?>><?php echo get_text($label); ?></option>
                <?php } ?>
            </select>
        </div>

        <div class="talk-apply-form__field">
            <label for="talk_ai_persona">AI 캐릭터/역할</label>
            <select id="talk_ai_persona" name="ai_persona" class="talk-apply-form__select"<?php echo $readonly ? ' disabled' : ''; ?>>
                <?php foreach ($persona_options as $value => $label) { ?>
                <option value="<?php echo get_text($value); ?>"<?php echo $settings['ai_persona'] === $value ? ' selected' : ''; ?>><?php echo get_text($label); ?></option>
                <?php } ?>
            </select>
        </div>

        <div class="talk-apply-form__field">
            <label for="talk_ai_tone">말투</label>
            <select id="talk_ai_tone" name="ai_tone" class="talk-apply-form__select"<?php echo $readonly ? ' disabled' : ''; ?>>
                <?php foreach ($tone_options as $value => $label) { ?>
                <option value="<?php echo get_text($value); ?>"<?php echo $settings['ai_tone'] === $value ? ' selected' : ''; ?>><?php echo get_text($label); ?></option>
                <?php } ?>
            </select>
        </div>

        <fieldset class="talk-apply-form__fieldset talk-ai-settings__features"<?php echo $readonly ? ' disabled' : ''; ?>>
            <legend>기능별 ON/OFF</legend>
            <?php foreach ($feature_fields as $field => $label) { ?>
            <label class="talk-ai-settings__switch talk-ai-settings__feature">
                <input type="checkbox" name="<?php echo get_text($field); ?>" value="1" class="talk-ai-settings__switch-input"<?php echo !empty($settings[$field]) ? ' checked' : ''; ?>>
                <span class="talk-ai-settings__switch-ui" aria-hidden="true"></span>
                <span class="talk-ai-settings__switch-label"><?php echo get_text($label); ?></span>
            </label>
            <?php } ?>
        </fieldset>

        <fieldset class="talk-apply-form__fieldset talk-ai-settings__limits"<?php echo $readonly ? ' disabled' : ''; ?>>
            <legend>발언 빈도 제한</legend>
            <div class="talk-apply-form__field">
                <label for="talk_ai_max_messages">하루 최대 AI 발언 수</label>
                <input type="number" id="talk_ai_max_messages" name="max_messages_per_day" class="talk-apply-form__input" min="<?php echo (int) eottae_talkroom_ai_min_messages_per_day(); ?>" max="<?php echo (int) eottae_talkroom_ai_max_messages_per_day_cap(); ?>" step="1" value="<?php echo (int) $settings['max_messages_per_day']; ?>"<?php echo $readonly ? ' disabled' : ''; ?>>
                <p class="talk-ai-settings__hint">최소 <?php echo (int) eottae_talkroom_ai_min_messages_per_day(); ?>회, 최대 <?php echo (int) eottae_talkroom_ai_max_messages_per_day_cap(); ?>회까지 설정할 수 있습니다. AI는 대화 맥락에 맞을 때만 참여하며, 회원 대화가 활발하면 자동으로 말수를 줄입니다.</p>
            </div>
            <div class="talk-apply-form__field">
                <label for="talk_ai_silence">조용한 방 판단 기준 (분)</label>
                <input type="number" id="talk_ai_silence" name="min_silence_minutes" class="talk-apply-form__input" min="30" max="10080" step="30" value="<?php echo (int) $settings['min_silence_minutes']; ?>"<?php echo $readonly ? ' disabled' : ''; ?>>
                <p class="talk-ai-settings__hint">이 시간 동안 새 글/댓글이 없으면 조용한 방으로 판단합니다.</p>
            </div>
            <div class="talk-apply-form__field talk-ai-settings__hours">
                <label>AI 활동 시간</label>
                <div class="talk-ai-settings__time-row">
                    <input type="time" name="active_start_time" class="talk-apply-form__input" value="<?php echo get_text(eottae_talkroom_ai_time_for_input($settings['active_start_time'])); ?>"<?php echo $readonly ? ' disabled' : ''; ?>>
                    <span>~</span>
                    <input type="time" name="active_end_time" class="talk-apply-form__input" value="<?php echo get_text(eottae_talkroom_ai_time_for_input($settings['active_end_time'])); ?>"<?php echo $readonly ? ' disabled' : ''; ?>>
                </div>
            </div>
        </fieldset>

        </div>

        <?php if (!$readonly) { ?>
        <div class="talk-ai-settings-form__actions talk-ai-settings-form__actions--sticky">
            <button type="submit" class="talk-page__btn talk-page__btn--primary talk-ai-settings-form__submit">설정 저장</button>
        </div>
        <?php } ?>
    </form>
    <?php
}
