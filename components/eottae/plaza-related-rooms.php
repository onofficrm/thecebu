<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('eottae_plaza_render_related_rooms')) {
    /**
     * @param array<string, mixed> $post
     */
    function eottae_plaza_render_related_rooms(array $post, $limit = 3)
    {
        include_once G5_LIB_PATH.'/eottae-plaza.lib.php';
        $rooms = eottae_plaza_related_rooms($post, $limit);
        $talk_list_url = eottae_plaza_talk_list_url();
        $has_rooms = !empty($rooms);
        $related_href = $has_rooms ? '#plaza-related-rooms-list' : $talk_list_url;
        ?>
        <section class="plaza-related-rooms" id="plaza-related-rooms" aria-labelledby="plaza-related-rooms-title">
            <h2 class="plaza-related-rooms__title" id="plaza-related-rooms-title">이 이야기는 아래 톡방에서도 이어갈 수 있어요.</h2>

            <?php if ($has_rooms) { ?>
            <ul class="plaza-related-rooms__list" id="plaza-related-rooms-list">
                <?php foreach ($rooms as $room) {
                    if (empty($room['room_id'])) {
                        continue;
                    }
                    $is_private = ($room['visibility'] ?? 'public') === 'private';
                    ?>
                <li class="plaza-related-rooms__item">
                    <a href="<?php echo $room['enter_href']; ?>" class="plaza-related-rooms__link">
                        <span class="plaza-related-rooms__emoji" aria-hidden="true"><?php echo $room['emoji']; ?></span>
                        <span class="plaza-related-rooms__info">
                            <strong class="plaza-related-rooms__name"><?php echo $room['room_name']; ?></strong>
                            <?php if (!empty($room['category'])) { ?>
                            <span class="plaza-related-rooms__category"><?php echo $room['category']; ?></span>
                            <?php } ?>
                            <?php if ($is_private) { ?>
                            <span class="plaza-related-rooms__private">비공개 · 가입 신청</span>
                            <?php } ?>
                        </span>
                    </a>
                </li>
                <?php } ?>
            </ul>
            <?php } else { ?>
            <p class="plaza-related-rooms__empty">지금은 딱 맞는 톡방이 없어요. 세부톡방에서 관심 주제를 찾아보세요.</p>
            <?php } ?>

            <div class="plaza-related-rooms__actions">
                <a href="<?php echo $related_href; ?>" class="plaza-btn plaza-btn--primary">관련 톡방 보기</a>
                <a href="<?php echo $talk_list_url; ?>" class="plaza-btn plaza-btn--ghost">세부톡방 둘러보기</a>
            </div>
        </section>
        <?php
    }
}
