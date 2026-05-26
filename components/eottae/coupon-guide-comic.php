<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('eottae_coupon_guide_comic_html')) {
    function eottae_coupon_guide_comic_html($context = 'member')
    {
        $context = $context === 'business' ? 'business' : 'member';
        $img_base = defined('G5_URL') ? G5_URL.'/img/guide' : '/img/guide';

        $panels = array(
            array(
                'step'  => '1',
                'title' => '쿠폰함에서 쿠폰 열기',
                'desc'  => '세부어때 모바일에서 쿠폰함 → 사용할 쿠폰 → 매장에서 보여주기',
                'image' => $img_base.'/coupon-use-step-01.png',
            ),
            array(
                'step'  => '2',
                'title' => '직원에게 화면 보여주기',
                'desc'  => '계산·주문 전에 쿠폰 화면을 직원에게 보여 주세요',
                'image' => $img_base.'/coupon-use-step-02.png',
            ),
            array(
                'step'  => '3',
                'title' => '사용 완료 누르기',
                'desc'  => $context === 'business'
                    ? '혜택 적용 후 직원이 화면에서 사용 완료를 누릅니다'
                    : '혜택 적용 후 회원 또는 직원이 사용 완료를 누릅니다',
                'image' => $img_base.'/coupon-use-step-03.png',
            ),
            array(
                'step'  => '4',
                'title' => '사용 완료 확인',
                'desc'  => '쿠폰이 사용 처리되면 화면에 완료 표시가 나타납니다',
                'image' => $img_base.'/coupon-use-step-04.png',
            ),
        );

        ob_start();
        ?>
        <section class="eottae-guide-comic" aria-label="쿠폰 사용 만화 안내">
            <div class="eottae-guide-comic__head">
                <h2 class="eottae-guide-comic__title">한눈에 보는 쿠폰 사용 방법</h2>
                <p class="eottae-guide-comic__lead">
                    <?php if ($context === 'business') { ?>
                    손님이 매장에서 쿠폰을 보여줄 때 아래 순서로 진행됩니다.
                    <?php } else { ?>
                    매장에서 쿠폰을 사용할 때 아래 순서대로 진행하면 됩니다.
                    <?php } ?>
                </p>
            </div>
            <ol class="eottae-guide-comic__grid">
                <?php foreach ($panels as $panel) { ?>
                <li class="eottae-guide-comic__panel">
                    <figure class="eottae-guide-comic__figure">
                        <img
                            src="<?php echo get_text($panel['image']); ?>"
                            alt="<?php echo get_text($panel['step'].'단계: '.$panel['title']); ?>"
                            class="eottae-guide-comic__image"
                            loading="lazy"
                            width="640"
                            height="640"
                        >
                        <figcaption class="eottae-guide-comic__caption">
                            <span class="eottae-guide-comic__step"><?php echo get_text($panel['step']); ?></span>
                            <strong class="eottae-guide-comic__panel-title"><?php echo get_text($panel['title']); ?></strong>
                            <p class="eottae-guide-comic__panel-desc"><?php echo get_text($panel['desc']); ?></p>
                        </figcaption>
                    </figure>
                </li>
                <?php } ?>
            </ol>
        </section>
        <?php

        return (string) ob_get_clean();
    }
}
