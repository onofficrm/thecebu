<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('eottae_market_parse_contact')) {
    include_once G5_LIB_PATH.'/eottae-market.lib.php';
}

$market_contact_raw = isset($market_contact_raw) ? (string) $market_contact_raw : '';
$market_contact = eottae_market_parse_contact($market_contact_raw);
$market_contact_legacy = trim((string) ($market_contact['legacy'] ?? ''));
?>

<div class="market-contact-fields" data-market-contact-fields>
    <p class="market-field__help">구매자가 연락할 수 있는 방법을 하나 이상 선택해 주세요.</p>

    <?php if ($market_contact_legacy !== '') { ?>
    <p class="market-contact-fields__legacy">기존 연락방법: <?php echo get_text($market_contact_legacy); ?> — 아래에서 새 방식으로 다시 설정해 주세요.</p>
    <?php } ?>

    <div class="market-contact-fields__options">
        <label class="market-contact-option">
            <input type="checkbox" name="market_contact_use_phone" value="1" id="market_contact_use_phone"<?php echo !empty($market_contact['use_phone']) ? ' checked' : ''; ?> data-market-contact-toggle="phone">
            <span>전화번호</span>
        </label>
        <div class="market-contact-fields__detail<?php echo empty($market_contact['use_phone']) ? ' is-hidden' : ''; ?>" data-market-contact-panel="phone">
            <label for="market_contact_phone">전화번호</label>
            <input type="tel" name="market_contact_phone" id="market_contact_phone" value="<?php echo get_text($market_contact['phone'] ?? ''); ?>" class="market-input" maxlength="40" placeholder="예: 09171234567" autocomplete="tel">
        </div>

        <label class="market-contact-option">
            <input type="checkbox" name="market_contact_use_kakao" value="1" id="market_contact_use_kakao"<?php echo !empty($market_contact['use_kakao']) ? ' checked' : ''; ?> data-market-contact-toggle="kakao">
            <span>카카오톡</span>
        </label>
        <div class="market-contact-fields__detail<?php echo empty($market_contact['use_kakao']) ? ' is-hidden' : ''; ?>" data-market-contact-panel="kakao">
            <label for="market_contact_kakao">카카오톡 ID</label>
            <input type="text" name="market_contact_kakao" id="market_contact_kakao" value="<?php echo get_text($market_contact['kakao'] ?? ''); ?>" class="market-input" maxlength="80" placeholder="카카오톡 ID 또는 오픈채팅 링크">
        </div>

        <label class="market-contact-option">
            <input type="checkbox" name="market_contact_use_message" value="1" id="market_contact_use_message"<?php echo !empty($market_contact['use_message']) ? ' checked' : ''; ?>>
            <span>쪽지(메시지) 받기</span>
        </label>
        <p class="market-contact-fields__message-help">세부어때 쪽지로 구매 문의를 받을 수 있습니다. 구매자는 로그인 후 메시지를 보낼 수 있습니다.</p>
    </div>
</div>
