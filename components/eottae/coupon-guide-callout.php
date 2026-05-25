<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

$callout_type = isset($callout_type) ? (string) $callout_type : 'member';

if ($callout_type === 'business') {
    $guide_url = G5_URL.'/page/eottae-business-coupon-guide.php';
    $title = '처음이신가요?';
    $text = '쿠폰 만들기 · 회원 발행 · 매장 사용 완료 처리 방법을 단계별로 안내합니다.';
    $btn = '사업자 쿠폰 안내 보기';
} else {
    $guide_url = G5_URL.'/page/eottae-coupon-guide.php';
    $title = '쿠폰 사용 방법';
    $text = '쿠폰함에서 매장에 보여 주고, 사용 완료까지 하는 방법을 확인하세요.';
    $btn = '회원 쿠폰 안내 보기';
}
?>

<aside class="coupon-guide-callout coupon-guide-callout--<?php echo htmlspecialchars($callout_type, ENT_QUOTES, 'UTF-8'); ?>" aria-label="쿠폰 안내">
    <div class="coupon-guide-callout__text">
        <strong><?php echo get_text($title); ?></strong>
        <p><?php echo get_text($text); ?></p>
    </div>
    <a href="<?php echo $guide_url; ?>" class="coupon-guide-callout__link"><?php echo get_text($btn); ?></a>
</aside>
