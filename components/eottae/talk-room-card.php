<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('eottae_talkroom_card_html')) {
    function eottae_talkroom_card_html(array $room)
    {
        if (empty($room['room_id'])) {
            return '';
        }

        $visibility_class = ($room['visibility'] ?? 'public') === 'private'
            ? ' talk-room-card__badge--private'
            : ' talk-room-card__badge--public';

        ob_start();
        ?>
        <article class="talk-room-card">
            <div class="talk-room-card__main">
                <div class="talk-room-card__avatar" aria-hidden="true"><?php echo $room['emoji']; ?></div>
                <div class="talk-room-card__body">
                    <div class="talk-room-card__head">
                        <h2 class="talk-room-card__title"><?php echo $room['room_name']; ?></h2>
                        <span class="talk-room-card__badge<?php echo $visibility_class; ?>"><?php echo $room['visibility_label']; ?></span>
                    </div>
                    <?php if (!empty($room['room_description'])) { ?>
                    <p class="talk-room-card__desc"><?php echo $room['room_description']; ?></p>
                    <?php } ?>
                    <div class="talk-room-card__tags">
                        <?php if (!empty($room['category'])) { ?>
                        <span class="talk-room-card__tag"><?php echo $room['category']; ?></span>
                        <?php } ?>
                        <?php if (!empty($room['owner_nick'])) { ?>
                        <span class="talk-room-card__tag talk-room-card__tag--owner">방장 <?php echo $room['owner_nick']; ?></span>
                        <?php } ?>
                    </div>
                    <div class="talk-room-card__stats">
                        <div class="talk-room-card__stat">
                            <span class="talk-room-card__stat-label">참여</span>
                            <span class="talk-room-card__stat-value"><?php echo number_format((int) $room['member_count']); ?></span>
                        </div>
                        <div class="talk-room-card__stat">
                            <span class="talk-room-card__stat-label">게시글</span>
                            <span class="talk-room-card__stat-value"><?php echo number_format((int) $room['post_count']); ?></span>
                        </div>
                        <?php if (!empty($room['updated_label'])) { ?>
                        <div class="talk-room-card__stat talk-room-card__stat--time">
                            <span class="talk-room-card__stat-label">업데이트</span>
                            <span class="talk-room-card__stat-value"><?php echo $room['updated_label']; ?></span>
                        </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
            <div class="talk-room-card__actions">
                <a href="<?php echo $room['enter_href']; ?>" class="talk-room-card__enter">입장하기</a>
                <?php if (!empty($room['can_delete'])) { ?>
                <button type="button"
                    class="talk-room-card__delete"
                    data-talk-room-delete="<?php echo (int) $room['room_id']; ?>"
                    data-room-name="<?php echo htmlspecialchars($room['room_name'], ENT_QUOTES, 'UTF-8'); ?>">삭제</button>
                <?php } ?>
            </div>
        </article>
        <?php

        return (string) ob_get_clean();
    }
}
