<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('eottae_challenge_guide_comic_html')) {
    function eottae_challenge_guide_comic_html()
    {
        $img_base = defined('G5_URL') ? G5_URL.'/img/guide' : '/img/guide';

        $panels = array(
            array(
                'step'  => '1',
                'title' => '챌린지 고르기',
                'desc'  => '세부어때 챌린지 목록에서 참여하고 싶은 주제를 선택하세요',
                'image' => $img_base.'/challenge-step-01.png',
            ),
            array(
                'step'  => '2',
                'title' => '사진과 후기 올리기',
                'desc'  => '맛집·풍경·생활팁 등 사진 1장과 짧은 글을 작성해 인증해 주세요',
                'image' => $img_base.'/challenge-step-02.png',
            ),
            array(
                'step'  => '3',
                'title' => '참여 완료 · 포인트 받기',
                'desc'  => '제출이 완료되면 참여 포인트와 뱃지 기록이 쌓입니다',
                'image' => $img_base.'/challenge-step-03.png',
            ),
            array(
                'step'  => '4',
                'title' => '공감과 댓글 나누기',
                'desc'  => '다른 회원 인증글에 공감·댓글을 남기며 정보를 나눠 보세요',
                'image' => $img_base.'/challenge-step-04.png',
            ),
        );

        ob_start();
        ?>
        <section class="eottae-guide-comic eottae-guide-comic--challenge" aria-label="챌린지 참여 만화 안내">
            <div class="eottae-guide-comic__head">
                <h2 class="eottae-guide-comic__title">한눈에 보는 챌린지 참여 방법</h2>
                <p class="eottae-guide-comic__lead">사진 한 장과 한 줄 후기만 올리면 참여 완료! 아래 순서대로 진행해 보세요.</p>
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
