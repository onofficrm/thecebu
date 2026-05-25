<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('eottae_inquiry_buttons_html')) {
    function eottae_inquiry_buttons_html($context, $opts = array())
    {
        $phone        = isset($opts['phone']) ? trim((string) $opts['phone']) : '';
        $inquiry_code = isset($opts['inquiry_code']) ? trim((string) $opts['inquiry_code']) : '';
        $lat          = isset($opts['lat']) ? trim((string) $opts['lat']) : '';
        $lng          = isset($opts['lng']) ? trim((string) $opts['lng']) : '';
        $address      = isset($opts['address']) ? trim((string) $opts['address']) : '';
        $share_url    = isset($opts['share_url']) ? trim((string) $opts['share_url']) : '';

        $tel_href = eottae_tel_href($phone);
        $map_href = eottae_maps_directions_url($lat, $lng, $address);
        $inquiry_attr = $inquiry_code !== '' ? ' data-inquiry-code="'.htmlspecialchars($inquiry_code, ENT_QUOTES, 'UTF-8').'"' : '';

        ob_start();

        switch ($context) {
            case 'detail':
                ?>
                <div class="inquiry-button inquiry-button--detail shop-detail-page__actions">
                    <button type="button" class="inquiry-button__btn inquiry-button__btn--inquiry inquiry-button"<?php echo $inquiry_attr; ?> data-inquiry-action="open">문의하기</button>
                    <button type="button" class="inquiry-button__btn inquiry-button__btn--share" data-share-url="<?php echo htmlspecialchars($share_url, ENT_QUOTES, 'UTF-8'); ?>">공유하기</button>
                    <a href="<?php echo $map_href; ?>" class="inquiry-button__btn inquiry-button__btn--map" target="_blank" rel="noopener noreferrer">길찾기</a>
                </div>
                <?php
                break;

            case 'mobile-bar':
                ?>
                <nav class="mobile-bottom-nav inquiry-button inquiry-button--mobile-bar" aria-label="업체 액션">
                    <a href="<?php echo $tel_href; ?>" class="mobile-bottom-nav__item">전화</a>
                    <button type="button" class="mobile-bottom-nav__item inquiry-button__btn--inquiry"<?php echo $inquiry_attr; ?> data-inquiry-action="open">문의</button>
                    <a href="<?php echo $map_href; ?>" class="mobile-bottom-nav__item" target="_blank" rel="noopener noreferrer">길찾기</a>
                    <button type="button" class="mobile-bottom-nav__item inquiry-button__btn--share" data-share-url="<?php echo htmlspecialchars($share_url, ENT_QUOTES, 'UTF-8'); ?>">공유</button>
                </nav>
                <?php
                break;

            case 'reservation':
                ?>
                <button type="button" class="inquiry-button inquiry-button__btn inquiry-button__btn--reservation inquiry-button__btn--inquiry"<?php echo $inquiry_attr; ?> data-inquiry-action="open">예약 문의</button>
                <?php
                break;

            case 'business':
                ?>
                <div class="inquiry-button inquiry-button--business">
                    <button type="button" class="inquiry-button__btn inquiry-button__btn--inquiry"<?php echo $inquiry_attr; ?> data-inquiry-action="open">광고 문의하기</button>
                    <a href="<?php echo G5_BBS_URL; ?>/board.php?bo_table=<?php echo EOTTae_SHOP_TABLE; ?>&amp;mode=write" class="inquiry-button__btn">업체등록 문의</a>
                    <button type="button" class="inquiry-button__btn inquiry-button__btn--inquiry" data-inquiry-action="consult">상담 요청</button>
                </div>
                <?php
                break;

            case 'list':
                ?>
                <div class="inquiry-button inquiry-button--list shop-list-card__actions">
                    <button type="button" class="inquiry-button__btn inquiry-button__btn--inquiry inquiry-button__btn--primary"<?php echo $inquiry_attr; ?> data-inquiry-action="open">문의하기</button>
                    <a href="<?php echo $map_href; ?>" class="inquiry-button__btn inquiry-button__btn--map inquiry-button__btn--outline" target="_blank" rel="noopener noreferrer">길찾기</a>
                </div>
                <?php
                break;

            case 'card':
            default:
                ?>
                <div class="inquiry-button inquiry-button--card shop-card__actions">
                    <a href="<?php echo $tel_href; ?>" class="inquiry-button__btn inquiry-button__btn--phone">전화</a>
                    <button type="button" class="inquiry-button__btn inquiry-button__btn--inquiry"<?php echo $inquiry_attr; ?> data-inquiry-action="open">문의하기</button>
                    <a href="<?php echo $map_href; ?>" class="inquiry-button__btn inquiry-button__btn--map" target="_blank" rel="noopener noreferrer">길찾기</a>
                </div>
                <?php
                break;
        }

        return ob_get_clean();
    }
}
