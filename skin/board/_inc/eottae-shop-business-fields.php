<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

$hours_form = function_exists('eottae_shop_hours_form_state')
    ? eottae_shop_hours_form_state($v['wr_6'], $business_hour_options)
    : array('select' => $v['wr_6'], 'custom' => '');
$closed_form = function_exists('eottae_shop_parse_closed_days')
    ? eottae_shop_parse_closed_days($v['wr_7'])
    : array('weekdays' => array(), 'special' => '', 'custom' => $v['wr_7']);
$weekday_options = function_exists('eottae_shop_weekday_options')
    ? eottae_shop_weekday_options()
    : array('월', '화', '수', '목', '금', '토', '일');
?>
<div class="eottae-field shop-register-page__hours-field" data-shop-hours-field>
    <label for="wr_6_preset">영업시간</label>
    <select id="wr_6_preset" name="wr_6_preset" class="eottae-select" data-shop-hours-preset>
        <option value="">영업시간 선택</option>
        <?php foreach ($business_hour_options as $hour) { ?>
        <option value="<?php echo get_text($hour); ?>"<?php echo $hours_form['select'] === $hour ? ' selected' : ''; ?>><?php echo get_text($hour); ?></option>
        <?php } ?>
        <option value="__custom__"<?php echo $hours_form['select'] === '__custom__' ? ' selected' : ''; ?>>직접 입력</option>
    </select>
    <input type="hidden" name="wr_6" id="wr_6" value="<?php echo get_text($v['wr_6']); ?>">
    <input type="text" name="wr_6_custom" id="wr_6_custom" class="shop-register-page__hours-custom" value="<?php echo get_text($hours_form['custom']); ?>" placeholder="예: 10:00 - 22:00 (브레이크타임 15:00-17:00)" data-shop-hours-custom<?php echo $hours_form['select'] === '__custom__' ? '' : ' hidden'; ?>>
    <p class="eottae-field__hint">목록에 없으면 「직접 입력」을 선택해 자유롭게 적어 주세요.</p>
</div>
<div class="eottae-field shop-register-page__closed-field" data-shop-closed-field>
    <span class="shop-register-page__closed-label">휴무일</span>
    <div class="shop-register-page__closed-days">
        <label class="shop-register-page__check shop-register-page__check--special">
            <input type="checkbox" value="연중무휴" data-closed-special="연중무휴"<?php echo $closed_form['special'] === '연중무휴' ? ' checked' : ''; ?>>
            <span>연중무휴</span>
        </label>
        <div class="shop-register-page__closed-weekdays" data-closed-weekdays>
            <?php foreach ($weekday_options as $weekday) {
                $checked = in_array($weekday, $closed_form['weekdays'], true);
                ?>
            <label class="shop-register-page__check">
                <input type="checkbox" name="eottae_closed_weekday[]" value="<?php echo $weekday; ?>" data-closed-weekday="<?php echo $weekday; ?>"<?php echo $checked ? ' checked' : ''; ?><?php echo ($closed_form['special'] === '연중무휴' || $closed_form['special'] === '비정기') ? ' disabled' : ''; ?>>
                <span><?php echo $weekday; ?></span>
            </label>
            <?php } ?>
        </div>
        <label class="shop-register-page__check shop-register-page__check--special">
            <input type="checkbox" value="비정기" data-closed-special="비정기"<?php echo $closed_form['special'] === '비정기' ? ' checked' : ''; ?>>
            <span>비정기 휴무</span>
        </label>
        <input type="hidden" name="eottae_closed_special" id="eottae_closed_special" value="<?php echo get_text($closed_form['special']); ?>">
        <input type="text" name="eottae_closed_custom" id="eottae_closed_custom" class="shop-register-page__closed-custom" value="<?php echo get_text($closed_form['custom']); ?>" placeholder="기타 휴무 안내 (선택) — 예: 설·추석 당일 휴무" data-closed-custom>
    </div>
    <input type="hidden" name="wr_7" id="wr_7" value="<?php echo get_text($v['wr_7']); ?>">
</div>
<div class="eottae-field">
    <label for="wr_8">영업상태</label>
    <select name="wr_8" id="wr_8" class="eottae-select">
        <?php foreach (array('영업중', '휴업', '폐업', '준비중') as $st) { ?>
        <option value="<?php echo $st; ?>"<?php echo $v['wr_8'] === $st ? ' selected' : ''; ?>><?php echo $st; ?></option>
        <?php } ?>
    </select>
</div>
