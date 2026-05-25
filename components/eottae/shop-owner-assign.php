<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if ($is_admin !== 'super') {
    return;
}

include_once G5_LIB_PATH.'/eottae-shop-owner.lib.php';

$owner_mb_id = '';
$owner_nick = '';
if ($w === 'u' && !empty($write['mb_id'])) {
    $owner_mb_id = eottae_shop_owner_mb_id_from_write($write);
    if ($owner_mb_id !== '') {
        $owner_member = get_member($owner_mb_id);
        $owner_nick = isset($owner_member['mb_nick']) ? get_text($owner_member['mb_nick']) : '';
    }
}

$author_mb_id = ($w === 'u' && !empty($write['mb_id'])) ? get_text($write['mb_id']) : '';
$author_is_business = $owner_mb_id !== '';
$owner_members = eottae_shop_owner_member_list($owner_mb_id);
?>

<div class="eottae-field eottae-field--shop-owner">
    <label for="eottae_owner_mb_id">업체 관리 회원</label>
    <select name="eottae_owner_mb_id" id="eottae_owner_mb_id" class="eottae-select eottae-select--shop-owner">
        <option value="">— 회원 선택 —</option>
        <?php foreach ($owner_members as $member_row) {
            $option_mb_id = $member_row['mb_id'];
            $selected = ($owner_mb_id !== '' && $owner_mb_id === $option_mb_id) ? ' selected' : '';
            ?>
        <option value="<?php echo get_text($option_mb_id); ?>"<?php echo $selected; ?>><?php echo eottae_shop_owner_member_option_label($member_row); ?></option>
        <?php } ?>
    </select>
    <p class="eottae-field__hint">회원아이디 · 이름 · 닉네임 순으로 표시됩니다. 선택한 사업자 회원이 로그인 후 업체 정보를 수정할 수 있습니다.</p>
    <?php if ($w === 'u' && $author_mb_id !== '') { ?>
    <p class="eottae-field__hint">
        현재 글 작성자: <strong><?php echo $author_mb_id; ?></strong>
        <?php if ($author_is_business && $owner_nick !== '') { ?>
        (<?php echo $owner_nick; ?> · 수정 권한 부여됨)
        <?php } elseif (!$author_is_business) { ?>
        (관리자 등록 · 사업자 미지정)
        <?php } ?>
    </p>
    <?php } ?>
</div>
