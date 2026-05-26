<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('eottae_coupon_guide_business_story_comic_html')) {
    function eottae_coupon_guide_business_story_comic_html()
    {
        $img_base = defined('G5_URL') ? G5_URL.'/img/guide' : '/img/guide';

        $panels = array(
            array(
                'step'  => '1',
                'title' => '손님이 없어 고민',
                'desc'  => '한산한 매장, 손님이 왜 이렇게 없을까요?',
                'image' => $img_base.'/coupon-issue-story-01.png',
            ),
            array(
                'step'  => '2',
                'title' => '프로모션이 필요해',
                'desc'  => '이벤트나 할인을 하고 싶은데, 어디서 어떻게 시작할지 막막합니다.',
                'image' => $img_base.'/coupon-issue-story-02.png',
            ),
            array(
                'step'  => '3',
                'title' => '세부어때 쿠폰 발행!',
                'desc'  => '세부어때에서 쿠폰을 만들어 회원에게 바로 보낼 수 있다는 걸 알게 됩니다.',
                'image' => $img_base.'/coupon-issue-story-03.png',
            ),
            array(
                'step'  => '4',
                'title' => '모바일로 간편 발행',
                'desc'  => '휴대폰에서 할인·무료 혜택 쿠폰을 만들고 회원에게 발행합니다.',
                'image' => $img_base.'/coupon-issue-story-04.png',
            ),
            array(
                'step'  => '5',
                'title' => '손님이 찾아와요',
                'desc'  => '쿠폰을 받은 회원들이 휴대폰을 들고 매장을 방문합니다.',
                'image' => $img_base.'/coupon-issue-story-05.png',
            ),
        );

        ob_start();
        ?>
        <section class="eottae-guide-comic eottae-guide-comic--story eottae-guide-comic--5" aria-label="쿠폰 발행 스토리 만화">
            <div class="eottae-guide-comic__head">
                <h2 class="eottae-guide-comic__title">쿠폰 하나로 손님을 불러 모으세요</h2>
                <p class="eottae-guide-comic__lead">
                    손님이 줄어 고민하던 사장님이 세부어때 쿠폰을 발행하고, 매장이 다시 활기를 되찾는 이야기입니다.
                </p>
            </div>
            <ol class="eottae-guide-comic__grid">
                <?php foreach ($panels as $panel) { ?>
                <li class="eottae-guide-comic__panel">
                    <figure class="eottae-guide-comic__figure">
                        <img
                            src="<?php echo get_text($panel['image']); ?>"
                            alt="<?php echo get_text($panel['step'].'단계: '.$panel['title']); ?>"
                            class="eottae-guide-comic__image eottae-guide-comic__image--wide"
                            loading="lazy"
                            width="800"
                            height="533"
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
