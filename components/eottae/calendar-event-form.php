<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('eottae_calendar_render_event_form')) {
    function eottae_calendar_render_event_form(array $old, $form_action, $token, $submit_label = '일정 등록', array $options = array())
    {
        $form_action = (string) $form_action;
        $hidden_action = isset($options['action']) ? preg_replace('/[^a-z_]/', '', (string) $options['action']) : 'create';
        $event_id = isset($options['event_id']) ? (int) $options['event_id'] : 0;
        $categories = eottae_calendar_category_options();
        $areas = eottae_calendar_area_options();
        $badges = eottae_calendar_badge_options();
        $talkrooms = eottae_calendar_talkroom_options();
        $is_all_day = !empty($old['is_all_day']);
        $start_time = (string) ($old['start_time'] ?? '');
        $end_time = (string) ($old['end_time'] ?? '');
        if (strlen($start_time) >= 5) {
            $start_time = substr($start_time, 0, 5);
        }
        if (strlen($end_time) >= 5) {
            $end_time = substr($end_time, 0, 5);
        }

        ob_start();
        ?>
        <form class="sebu-cal-form" method="post" action="<?php echo get_text($form_action); ?>" novalidate data-sebu-cal-form>
            <input type="hidden" name="action" value="<?php echo get_text($hidden_action); ?>">
            <?php if ($event_id > 0) { ?>
            <input type="hidden" name="event_id" value="<?php echo (int) $event_id; ?>">
            <?php } ?>
            <input type="hidden" name="eottae_calendar_token" value="<?php echo get_text($token); ?>">
            <?php if (!empty($options['from'])) { ?>
            <input type="hidden" name="from" value="<?php echo get_text($options['from']); ?>">
            <?php } ?>
            <?php if (!empty($options['room_id'])) { ?>
            <input type="hidden" name="prefill_room_id" value="<?php echo (int) $options['room_id']; ?>">
            <?php } ?>
            <?php if (!empty($old['related_post_url'])) { ?>
            <input type="hidden" name="related_post_url" value="<?php echo get_text($old['related_post_url']); ?>">
            <?php } ?>

            <div class="sebu-cal-form__field">
                <label for="sebu_cal_title">일정 제목 <span class="sebu-cal-form__required">*</span></label>
                <input type="text" id="sebu_cal_title" name="title" class="sebu-cal-form__input" maxlength="200" required value="<?php echo get_text($old['title'] ?? ''); ?>">
            </div>

            <div class="sebu-cal-form__field">
                <label for="sebu_cal_description">일정 설명</label>
                <textarea id="sebu_cal_description" name="description" class="sebu-cal-form__textarea" rows="5" maxlength="10000"><?php echo get_text($old['description'] ?? ''); ?></textarea>
            </div>

            <div class="sebu-cal-form__grid sebu-cal-form__grid--2">
                <div class="sebu-cal-form__field">
                    <label for="sebu_cal_start_date">시작일 <span class="sebu-cal-form__required">*</span></label>
                    <input type="date" id="sebu_cal_start_date" name="start_date" class="sebu-cal-form__input" required value="<?php echo get_text($old['start_date'] ?? ''); ?>">
                </div>
                <div class="sebu-cal-form__field">
                    <label for="sebu_cal_end_date">종료일 <span class="sebu-cal-form__required">*</span></label>
                    <input type="date" id="sebu_cal_end_date" name="end_date" class="sebu-cal-form__input" required value="<?php echo get_text($old['end_date'] ?? ''); ?>">
                </div>
            </div>

            <div class="sebu-cal-form__field">
                <label class="sebu-cal-form__check">
                    <input type="checkbox" name="is_all_day" value="1"<?php echo $is_all_day ? ' checked' : ''; ?> data-sebu-cal-all-day>
                    <span>하루종일</span>
                </label>
            </div>

            <div class="sebu-cal-form__grid sebu-cal-form__grid--2" data-sebu-cal-time-fields<?php echo $is_all_day ? ' hidden' : ''; ?>>
                <div class="sebu-cal-form__field">
                    <label for="sebu_cal_start_time">시작 시간</label>
                    <input type="time" id="sebu_cal_start_time" name="start_time" class="sebu-cal-form__input" value="<?php echo get_text($start_time); ?>">
                </div>
                <div class="sebu-cal-form__field">
                    <label for="sebu_cal_end_time">종료 시간</label>
                    <input type="time" id="sebu_cal_end_time" name="end_time" class="sebu-cal-form__input" value="<?php echo get_text($end_time); ?>">
                </div>
            </div>

            <div class="sebu-cal-form__grid sebu-cal-form__grid--2">
                <div class="sebu-cal-form__field">
                    <label for="sebu_cal_location">장소</label>
                    <input type="text" id="sebu_cal_location" name="location" class="sebu-cal-form__input" maxlength="255" value="<?php echo get_text($old['location'] ?? ''); ?>">
                </div>
                <div class="sebu-cal-form__field">
                    <label for="sebu_cal_area">지역</label>
                    <select id="sebu_cal_area" name="area" class="sebu-cal-form__select">
                        <?php foreach ($areas as $code => $label) { ?>
                        <option value="<?php echo get_text($code); ?>"<?php echo (($old['area'] ?? '') === $code) ? ' selected' : ''; ?>><?php echo get_text($label); ?></option>
                        <?php } ?>
                    </select>
                </div>
            </div>

            <div class="sebu-cal-form__grid sebu-cal-form__grid--2">
                <div class="sebu-cal-form__field">
                    <label for="sebu_cal_category">분류 <span class="sebu-cal-form__required">*</span></label>
                    <select id="sebu_cal_category" name="category" class="sebu-cal-form__select" required>
                        <?php foreach ($categories as $code => $label) { ?>
                        <option value="<?php echo get_text($code); ?>"<?php echo (($old['category'] ?? '') === $code) ? ' selected' : ''; ?>><?php echo get_text($label); ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="sebu-cal-form__field">
                    <label for="sebu_cal_badge">배지 스타일</label>
                    <select id="sebu_cal_badge" name="badge_style" class="sebu-cal-form__select">
                        <?php foreach ($badges as $code => $meta) { ?>
                        <option value="<?php echo get_text($code); ?>"<?php echo (($old['badge_style'] ?? 'default') === $code) ? ' selected' : ''; ?>><?php echo get_text($meta['label']); ?></option>
                        <?php } ?>
                    </select>
                </div>
            </div>

            <div class="sebu-cal-form__field">
                <label for="sebu_cal_related_url">관련 링크</label>
                <input type="url" id="sebu_cal_related_url" name="related_url" class="sebu-cal-form__input" maxlength="500" placeholder="https://" value="<?php echo get_text($old['related_url'] ?? ''); ?>">
            </div>

            <div class="sebu-cal-form__field">
                <label for="sebu_cal_related_room">관련 세부톡방</label>
                <select id="sebu_cal_related_room" name="related_room_id" class="sebu-cal-form__select">
                    <option value="0">선택 안 함</option>
                    <?php foreach ($talkrooms as $room_id => $room_name) { ?>
                    <option value="<?php echo (int) $room_id; ?>"<?php echo ((int) ($old['related_room_id'] ?? 0) === (int) $room_id) ? ' selected' : ''; ?>><?php echo get_text($room_name); ?></option>
                    <?php } ?>
                </select>
            </div>

            <div class="sebu-cal-form__actions">
                <button type="submit" class="sebu-cal-btn sebu-cal-btn--primary"><?php echo get_text($submit_label); ?></button>
                <a href="<?php echo eottae_calendar_list_url(); ?>" class="sebu-cal-btn">취소</a>
            </div>
        </form>
        <?php

        return (string) ob_get_clean();
    }
}
