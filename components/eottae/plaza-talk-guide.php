<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('eottae_plaza_render_talk_guide')) {
    function eottae_plaza_render_talk_guide($placement = 'default')
    {
        include_once G5_LIB_PATH.'/eottae-plaza.lib.php';
        $talk_list_url = eottae_plaza_talk_list_url();
        $talk_create_url = eottae_plaza_talk_create_url();
        $class = 'plaza-talk-guide';
        if ($placement !== '') {
            $class .= ' plaza-talk-guide--'.preg_replace('/[^a-z0-9_-]/', '', (string) $placement);
        }
        ?>
        <aside class="<?php echo $class; ?>" aria-label="세부톡방 안내">
            <p class="plaza-talk-guide__text">
                세부광장은 누구나 볼 수 있는 공개 공간입니다.<br>
                더 깊은 대화는 관심 있는 세부톡방에 참여해서 이어가보세요.
            </p>
            <div class="plaza-talk-guide__actions">
                <a href="<?php echo $talk_list_url; ?>" class="plaza-btn plaza-btn--primary">세부톡방 둘러보기</a>
                <a href="<?php echo $talk_create_url; ?>" class="plaza-btn plaza-btn--ghost">톡방 만들기</a>
            </div>
        </aside>
        <?php
    }
}
