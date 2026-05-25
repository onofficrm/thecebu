<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('eottae_talkroom_dashboard_my_talk_type_badge_class')) {
    function eottae_talkroom_dashboard_my_talk_type_badge_class($type_label)
    {
        $label = trim(strip_tags((string) $type_label));
        if ($label === '') {
            return 'my-talk-type-badge--default';
        }

        $rules = array(
            'my-talk-type-badge--notice'  => array('공지'),
            'my-talk-type-badge--meetup'  => array('모임'),
            'my-talk-type-badge--question'=> array('질문', 'Q&A', '궁금'),
            'my-talk-type-badge--sale'    => array('판매', '거래', '중고'),
        );

        foreach ($rules as $class => $keywords) {
            foreach ($keywords as $keyword) {
                if (mb_stripos($label, $keyword) !== false) {
                    return $class;
                }
            }
        }

        return 'my-talk-type-badge--default';
    }
}

if (!function_exists('eottae_talkroom_dashboard_feed_item_html')) {
    /**
     * @param array<string, mixed> $item
     */
    function eottae_talkroom_dashboard_feed_item_html(array $item)
    {
        if (empty($item['href'])) {
            return '';
        }

        $is_ai_post = !empty($item['is_ai']);
        $is_new = !empty($item['is_new']);
        $has_thumb = !empty($item['thumbnail']);

        ob_start();
        ?>
        <li class="my-talk-feed__item<?php echo $is_ai_post ? ' my-talk-feed__item--ai is-talk-ai-message' : ''; ?><?php echo $is_new ? ' my-talk-feed__item--new' : ''; ?>">
            <a href="<?php echo $item['href']; ?>" class="my-talk-feed__link">
                <?php if ($has_thumb) { ?>
                <span class="my-talk-feed__thumb">
                    <img src="<?php echo get_text($item['thumbnail']); ?>" alt="" loading="lazy" width="72" height="72">
                </span>
                <?php } ?>
                <span class="my-talk-feed__body">
                    <span class="my-talk-feed__headline">
                        <span class="my-talk-room-badge my-talk-feed__room">[<?php echo $item['room_name']; ?>]</span>
                        <?php if ($is_new) { ?>
                        <span class="my-talk-badge my-talk-badge--alert my-talk-feed__new">NEW</span>
                        <?php } ?>
                    </span>
                    <?php if (!empty($item['category'])) { ?>
                    <span class="my-talk-feed__category"><?php echo $item['category']; ?></span>
                    <?php } ?>
                    <strong class="my-talk-feed__subject my-talk-title-clamp"><?php echo $item['subject']; ?></strong>
                    <span class="my-talk-feed__meta">
                        <?php if ((int) ($item['comment_count'] ?? 0) > 0) { ?>
                        <span class="my-talk-feed__comments my-talk-meta-pill">댓글 <?php echo number_format((int) $item['comment_count']); ?></span>
                        <?php } ?>
                        <?php if (!empty($item['time_label'])) { ?>
                        <span class="my-talk-feed__time"><?php echo get_text($item['time_label']); ?></span>
                        <?php } ?>
                        <?php if ($is_ai_post && function_exists('eottae_talkroom_ai_message_render_badge')) {
                            include_once G5_PATH.'/components/eottae/talk-ai-message-ui.php';
                            echo eottae_talkroom_ai_message_render_badge($item, 'sm');
                        } elseif (!$is_ai_post && !empty($item['type_label'])) { ?>
                        <span class="my-talk-type-badge <?php echo eottae_talkroom_dashboard_my_talk_type_badge_class($item['type_label']); ?>"><?php echo get_text($item['type_label']); ?></span>
                        <?php } ?>
                        <?php if (!$is_ai_post && !empty($item['author'])) { ?>
                        <span class="my-talk-feed__author"><?php echo $item['author']; ?></span>
                        <?php } elseif ($is_ai_post) { ?>
                        <span class="my-talk-feed__author talk-ai-msg__author-line"><?php echo get_text($item['ai_display_name'] ?? ($item['author'] ?? '')); ?></span>
                        <?php } ?>
                    </span>
                </span>
            </a>
        </li>
        <?php

        return (string) ob_get_clean();
    }
}

if (!function_exists('eottae_talkroom_dashboard_feed_items_html')) {
    /**
     * @param array<int, array<string, mixed>> $items
     */
    function eottae_talkroom_dashboard_feed_items_html(array $items)
    {
        $html = '';
        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }
            $html .= eottae_talkroom_dashboard_feed_item_html($item);
        }

        return $html;
    }
}

if (!function_exists('eottae_talkroom_dashboard_notice_item_html')) {
    /**
     * @param array<string, mixed> $item
     */
    function eottae_talkroom_dashboard_notice_item_html(array $item)
    {
        if (empty($item['href'])) {
            return '';
        }

        $is_unread = !empty($item['is_unread']);

        ob_start();
        ?>
        <li class="my-talk-notice__item<?php echo $is_unread ? ' my-talk-notice__item--unread' : ''; ?>" data-my-talk-notice-id="<?php echo (int) ($item['wr_id'] ?? 0); ?>">
            <a href="<?php echo $item['href']; ?>" class="my-talk-notice__link" data-my-talk-notice-link data-room-id="<?php echo (int) ($item['room_id'] ?? 0); ?>">
                <span class="my-talk-notice__head">
                    <span class="my-talk-room-badge my-talk-notice__room"><?php echo eottae_talkroom_display_emoji($item['room_emoji'] ?? ''); ?> <?php echo get_text($item['room_name'] ?? ''); ?></span>
                    <?php if ($is_unread) { ?>
                    <span class="my-talk-badge my-talk-badge--alert my-talk-notice__unread">미확인</span>
                    <?php } else { ?>
                    <span class="my-talk-badge my-talk-badge--muted my-talk-notice__confirmed">확인함</span>
                    <?php } ?>
                </span>
                <strong class="my-talk-notice__subject my-talk-title-clamp"><?php echo get_text($item['subject'] ?? ''); ?></strong>
                <span class="my-talk-notice__meta">
                    <?php if (!empty($item['author'])) { ?>
                    <span class="my-talk-notice__author"><?php echo get_text($item['author']); ?></span>
                    <?php } ?>
                    <?php if (!empty($item['time_label'])) { ?>
                    <span class="my-talk-notice__time"><?php echo get_text($item['time_label']); ?></span>
                    <?php } ?>
                </span>
            </a>
            <?php if ($is_unread) { ?>
            <button type="button" class="my-talk-notice__read-btn" data-my-talk-notice-read="<?php echo (int) ($item['room_id'] ?? 0); ?>" aria-label="확인 처리">확인</button>
            <?php } ?>
        </li>
        <?php

        return (string) ob_get_clean();
    }
}

if (!function_exists('eottae_talkroom_dashboard_notice_items_html')) {
    /**
     * @param array<int, array<string, mixed>> $items
     */
    function eottae_talkroom_dashboard_notice_items_html(array $items)
    {
        $html = '';
        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }
            $html .= eottae_talkroom_dashboard_notice_item_html($item);
        }

        return $html;
    }
}

if (!function_exists('eottae_talkroom_dashboard_meetup_item_html')) {
    /**
     * @param array<string, mixed> $item
     */
    function eottae_talkroom_dashboard_meetup_item_html(array $item)
    {
        if (empty($item['href'])) {
            return '';
        }

        ob_start();
        ?>
        <li class="my-talk-meetup__item">
            <article class="my-talk-meetup__inner">
                <header class="my-talk-meetup__head">
                    <span class="my-talk-room-badge my-talk-meetup__room"><?php echo eottae_talkroom_display_emoji($item['room_emoji'] ?? ''); ?> <?php echo get_text($item['room_name'] ?? ''); ?></span>
                    <?php if (!empty($item['has_participation'])) { ?>
                    <span class="my-talk-badge my-talk-badge--owner my-talk-meetup__status"><?php echo get_text($item['participation_label'] ?? '참여 의사 있음'); ?></span>
                    <?php } ?>
                </header>
                <h3 class="my-talk-meetup__subject">
                    <a href="<?php echo $item['href']; ?>" class="my-talk-meetup__link my-talk-title-clamp"><?php echo get_text($item['subject'] ?? ''); ?></a>
                </h3>
                <div class="my-talk-meetup__meta">
                    <div class="my-talk-meetup__meta-row">
                        <span class="my-talk-meetup__meta-label">날짜</span>
                        <span class="my-talk-meetup__meta-value"><?php echo get_text($item['date_label'] ?? '-'); ?></span>
                    </div>
                    <div class="my-talk-meetup__meta-row">
                        <span class="my-talk-meetup__meta-label">시간</span>
                        <span class="my-talk-meetup__meta-value"><?php echo get_text($item['time_label'] ?? '-'); ?></span>
                    </div>
                    <div class="my-talk-meetup__meta-row">
                        <span class="my-talk-meetup__meta-label">장소</span>
                        <span class="my-talk-meetup__meta-value"><?php echo get_text($item['location'] ?? '-'); ?></span>
                    </div>
                    <div class="my-talk-meetup__meta-row">
                        <span class="my-talk-meetup__meta-label">댓글</span>
                        <span class="my-talk-meetup__meta-value"><?php echo number_format((int) ($item['comment_count'] ?? 0)); ?></span>
                    </div>
                </div>
                <footer class="my-talk-meetup__actions">
                    <a href="<?php echo $item['href']; ?>" class="my-talk-btn my-talk-btn--primary my-talk-btn--sm">모임글 보기</a>
                    <?php if (empty($item['has_participation'])) { ?>
                    <a href="<?php echo $item['href']; ?>#bo_vc_w" class="my-talk-btn my-talk-btn--ghost my-talk-btn--sm">댓글 확인</a>
                    <?php } ?>
                </footer>
            </article>
        </li>
        <?php

        return (string) ob_get_clean();
    }
}

if (!function_exists('eottae_talkroom_dashboard_meetup_items_html')) {
    /**
     * @param array<int, array<string, mixed>> $items
     */
    function eottae_talkroom_dashboard_meetup_items_html(array $items)
    {
        $html = '';
        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }
            $html .= eottae_talkroom_dashboard_meetup_item_html($item);
        }

        return $html;
    }
}

if (!function_exists('eottae_talkroom_dashboard_bookmark_item_html')) {
    /**
     * @param array<string, mixed> $item
     */
    function eottae_talkroom_dashboard_bookmark_item_html(array $item)
    {
        $bookmark_id = (int) ($item['bookmark_id'] ?? 0);
        if ($bookmark_id < 1) {
            return '';
        }

        $can_open = !empty($item['can_open']) && !empty($item['href']);
        $access_state = (string) ($item['access_state'] ?? 'ok');
        $is_restricted = ($access_state === 'deleted' || $access_state === 'missing' || $access_state === 'restricted');

        ob_start();
        ?>
        <li class="my-talk-bookmark__item<?php echo $is_restricted ? ' my-talk-bookmark__item--restricted' : ''; ?>" data-my-talk-bookmark-id="<?php echo $bookmark_id; ?>">
            <article class="my-talk-bookmark__inner">
                <header class="my-talk-bookmark__head">
                    <span class="my-talk-room-badge my-talk-bookmark__room"><?php echo eottae_talkroom_display_emoji($item['room_emoji'] ?? ''); ?> <?php echo get_text($item['room_name'] ?? ''); ?></span>
                    <?php if (!empty($item['status_label'])) { ?>
                    <span class="my-talk-badge my-talk-badge--muted"><?php echo get_text($item['status_label']); ?></span>
                    <?php } ?>
                </header>
                <h3 class="my-talk-bookmark__subject">
                    <?php if ($can_open) { ?>
                    <a href="<?php echo $item['href']; ?>" class="my-talk-bookmark__link my-talk-title-clamp"><?php echo get_text($item['subject'] ?? ''); ?></a>
                    <?php } else { ?>
                    <span class="my-talk-bookmark__subject-text"><?php echo get_text($item['subject'] ?? ''); ?></span>
                    <?php } ?>
                </h3>
                <p class="my-talk-bookmark__meta">
                    <?php if (!empty($item['type_label'])) { ?>
                    <span class="my-talk-bookmark__type"><?php echo get_text($item['type_label']); ?></span>
                    <?php } ?>
                    <?php if (!empty($item['author'])) { ?>
                    <span class="my-talk-bookmark__author"><?php echo get_text($item['author']); ?></span>
                    <?php } ?>
                    <?php if (!empty($item['saved_label'])) { ?>
                    <span class="my-talk-bookmark__saved">저장 <?php echo get_text($item['saved_label']); ?></span>
                    <?php } ?>
                    <span class="my-talk-bookmark__comments">댓글 <?php echo number_format((int) ($item['comment_count'] ?? 0)); ?></span>
                </p>
                <footer class="my-talk-bookmark__actions">
                    <?php if ($can_open) { ?>
                    <a href="<?php echo $item['href']; ?>" class="my-talk-btn my-talk-btn--primary my-talk-btn--sm">글 보기</a>
                    <?php } ?>
                    <button type="button"
                        class="my-talk-btn my-talk-btn--ghost my-talk-btn--sm"
                        data-my-talk-bookmark-remove
                        data-room-id="<?php echo (int) ($item['room_id'] ?? 0); ?>"
                        data-post-id="<?php echo (int) ($item['post_id'] ?? 0); ?>">저장 취소</button>
                </footer>
            </article>
        </li>
        <?php

        return (string) ob_get_clean();
    }
}

if (!function_exists('eottae_talkroom_dashboard_bookmark_items_html')) {
    /**
     * @param array<int, array<string, mixed>> $items
     */
    function eottae_talkroom_dashboard_bookmark_items_html(array $items)
    {
        $html = '';
        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }
            $html .= eottae_talkroom_dashboard_bookmark_item_html($item);
        }

        return $html;
    }
}

if (!function_exists('eottae_talkroom_dashboard_notify_item_html')) {
    /**
     * @param array<string, mixed> $note
     */
    function eottae_talkroom_dashboard_notify_item_html(array $note)
    {
        $id = (int) ($note['id'] ?? 0);
        if ($id < 1) {
            return '';
        }

        $is_read = !empty($note['is_read']);
        $href = function_exists('eottae_talkroom_sanitize_internal_href')
            ? eottae_talkroom_sanitize_internal_href($note['href'] ?? '', '#')
            : trim((string) ($note['href'] ?? '#'));

        ob_start();
        ?>
        <li class="my-talk-notify__item<?php echo $is_read ? '' : ' my-talk-notify__item--unread'; ?>" data-my-talk-notify-id="<?php echo $id; ?>">
            <a href="<?php echo htmlspecialchars($href !== '' ? $href : '#', ENT_QUOTES, 'UTF-8'); ?>" class="my-talk-notify__link" data-my-talk-notify-link data-notification-id="<?php echo $id; ?>">
                <span class="my-talk-notify__head">
                    <strong class="my-talk-notify__title my-talk-title-clamp"><?php echo get_text($note['title'] ?? ''); ?></strong>
                    <?php if (!$is_read) { ?>
                    <span class="my-talk-badge my-talk-badge--alert my-talk-notify__unread-dot">NEW</span>
                    <?php } ?>
                </span>
                <?php if (!empty($note['message'])) { ?>
                <span class="my-talk-notify__message"><?php echo get_text($note['message']); ?></span>
                <?php } ?>
                <span class="my-talk-notify__meta">
                    <?php if (!empty($note['room_name'])) { ?>
                    <span class="my-talk-room-badge my-talk-notify__room"><?php echo get_text($note['room_name']); ?></span>
                    <?php } ?>
                    <?php if (!empty($note['type_label'])) { ?>
                    <span class="my-talk-notify__type"><?php echo get_text($note['type_label']); ?></span>
                    <?php } ?>
                    <?php if (!empty($note['time_label'])) { ?>
                    <span class="my-talk-notify__time"><?php echo get_text($note['time_label']); ?></span>
                    <?php } ?>
                </span>
            </a>
            <?php if (!$is_read) { ?>
            <button type="button" class="my-talk-notify__read-btn" data-my-talk-notify-read="<?php echo $id; ?>" aria-label="읽음 처리">읽음</button>
            <?php } ?>
        </li>
        <?php

        return (string) ob_get_clean();
    }
}

if (!function_exists('eottae_talkroom_dashboard_notify_items_html')) {
    /**
     * @param array<int, array<string, mixed>> $items
     */
    function eottae_talkroom_dashboard_notify_items_html(array $items)
    {
        $html = '';
        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }
            $html .= eottae_talkroom_dashboard_notify_item_html($item);
        }

        return $html;
    }
}

if (!function_exists('eottae_talkroom_dashboard_owner_card_html')) {
    /**
     * @param array<string, mixed> $room
     */
    function eottae_talkroom_dashboard_owner_card_html(array $room)
    {
        $room_id = (int) ($room['room_id'] ?? 0);
        if ($room_id < 1) {
            return '';
        }

        $links = isset($room['manage_links']) && is_array($room['manage_links']) ? $room['manage_links'] : array();
        $ai = isset($room['ai']) && is_array($room['ai']) ? $room['ai'] : array('available' => false, 'label' => '미설정', 'class' => 'my-talk-badge--muted');
        $pending = (int) ($room['pending_members'] ?? 0);
        $reports = (int) ($room['pending_reports'] ?? 0);
        $today_posts = (int) ($room['today_posts'] ?? 0);
        $today_comments = (int) ($room['today_comments'] ?? 0);
        $kicked = (int) ($room['kicked_members'] ?? 0);
        $pending_class = $pending > 0 ? 'my-talk-badge--alert' : 'my-talk-badge--muted';
        $reports_class = $reports > 0 ? 'my-talk-badge--alert' : 'my-talk-badge--muted';

        ob_start();
        ?>
        <li class="my-talk-owner-card<?php echo !empty($room['has_tasks']) ? ' my-talk-owner-card--tasks' : ''; ?>">
            <article class="my-talk-owner-card__inner">
                <header class="my-talk-owner-card__head">
                    <div class="my-talk-owner-card__title-row">
                        <span class="my-talk-room-badge"><?php echo eottae_talkroom_display_emoji($room['emoji'] ?? '', $room['category_code'] ?? ''); ?> <?php echo get_text($room['room_name'] ?? ''); ?></span>
                        <?php if (!empty($room['category_label'])) { ?>
                        <span class="my-talk-owner-card__category"><?php echo get_text($room['category_label']); ?></span>
                        <?php } ?>
                    </div>
                    <?php if (!empty($room['updated_label'])) { ?>
                    <span class="my-talk-owner-card__updated">최근 활동 <?php echo get_text($room['updated_label']); ?></span>
                    <?php } ?>
                </header>
                <ul class="my-talk-owner-card__stats">
                    <li><span class="my-talk-owner-card__stat-label">참여</span> <span class="my-talk-badge my-talk-badge--muted"><?php echo number_format((int) ($room['member_count'] ?? 0)); ?>명</span></li>
                    <li><span class="my-talk-owner-card__stat-label">오늘 새 글</span> <span class="my-talk-badge my-talk-badge--muted"><?php echo number_format($today_posts); ?></span></li>
                    <li><span class="my-talk-owner-card__stat-label">오늘 새 댓글</span> <span class="my-talk-badge my-talk-badge--muted"><?php echo number_format($today_comments); ?></span></li>
                    <li><span class="my-talk-owner-card__stat-label">승인 대기</span> <span class="my-talk-badge <?php echo $pending_class; ?>"><?php echo number_format($pending); ?></span></li>
                    <li><span class="my-talk-owner-card__stat-label">신고 대기</span> <span class="my-talk-badge <?php echo $reports_class; ?>"><?php echo number_format($reports); ?></span></li>
                    <li><span class="my-talk-owner-card__stat-label">강퇴 회원</span> <span class="my-talk-badge my-talk-badge--muted"><?php echo number_format($kicked); ?></span></li>
                    <?php if (!empty($ai['available'])) { ?>
                    <li><span class="my-talk-owner-card__stat-label">AI 도우미</span> <span class="my-talk-badge <?php echo get_text($ai['class'] ?? 'my-talk-badge--muted'); ?>"><?php echo get_text($ai['label'] ?? 'OFF'); ?></span></li>
                    <?php } ?>
                </ul>
                <footer class="my-talk-owner-card__actions">
                    <?php if (!empty($links['manage'])) { ?>
                    <a href="<?php echo $links['manage']; ?>" class="my-talk-btn my-talk-btn--primary my-talk-btn--sm">톡방 관리</a>
                    <?php } ?>
                    <?php if (!empty($links['pending'])) { ?>
                    <a href="<?php echo $links['pending']; ?>" class="my-talk-btn my-talk-btn--ghost my-talk-btn--sm">참여 신청<?php if ($pending > 0) { ?> (<?php echo number_format($pending); ?>)<?php } ?></a>
                    <?php } ?>
                    <?php if (!empty($links['reports'])) { ?>
                    <a href="<?php echo $links['reports']; ?>" class="my-talk-btn my-talk-btn--ghost my-talk-btn--sm">신고 관리<?php if ($reports > 0) { ?> (<?php echo number_format($reports); ?>)<?php } ?></a>
                    <?php } ?>
                    <?php if (!empty($links['notice'])) { ?>
                    <a href="<?php echo $links['notice']; ?>" class="my-talk-btn my-talk-btn--ghost my-talk-btn--sm">공지 작성</a>
                    <?php } ?>
                    <?php if (!empty($links['ai'])) { ?>
                    <a href="<?php echo $links['ai']; ?>" class="my-talk-btn my-talk-btn--ghost my-talk-btn--sm">AI 설정</a>
                    <?php } ?>
                    <?php if (!empty($links['members'])) { ?>
                    <a href="<?php echo $links['members']; ?>" class="my-talk-btn my-talk-btn--ghost my-talk-btn--sm">회원 관리</a>
                    <?php } ?>
                </footer>
            </article>
        </li>
        <?php

        return (string) ob_get_clean();
    }
}

if (!function_exists('eottae_talkroom_render_mypage_dashboard')) {
    /**
     * @param array<string, mixed> $ctx
     */
    function eottae_talkroom_render_mypage_dashboard(array $ctx)
    {
        $stats = isset($ctx['stats']) && is_array($ctx['stats']) ? $ctx['stats'] : eottae_talkroom_dashboard_default_stats();
        $news_rooms = isset($ctx['news_rooms']) && is_array($ctx['news_rooms']) ? $ctx['news_rooms'] : array();
        $owner_summaries = isset($ctx['owner_summaries']) && is_array($ctx['owner_summaries'])
            ? $ctx['owner_summaries']
            : (isset($ctx['owner_rooms']) && is_array($ctx['owner_rooms']) ? $ctx['owner_rooms'] : array());
        $feed_items = isset($ctx['feed_items']) && is_array($ctx['feed_items']) ? $ctx['feed_items'] : array();
        $notifications = isset($ctx['notifications']) && is_array($ctx['notifications']) ? $ctx['notifications'] : array();
        $has_owner = !empty($ctx['has_owner']);
        $list_url = function_exists('eottae_talkroom_list_url') ? eottae_talkroom_list_url() : G5_URL.'/talk';
        $create_url = function_exists('eottae_talkroom_create_url') ? eottae_talkroom_create_url() : G5_URL.'/page/eottae-talk-create.php';
        $read_token = isset($ctx['read_token']) ? (string) $ctx['read_token'] : '';
        $reads_proc_url = isset($ctx['reads_proc_url']) ? (string) $ctx['reads_proc_url'] : G5_URL.'/proc/eottae-talkroom-reads.php';
        $notify_proc_url = isset($ctx['notify_proc_url']) ? (string) $ctx['notify_proc_url'] : G5_URL.'/proc/eottae-talkroom-notifications.php';
        $notify_unread = (int) ($ctx['notify_unread'] ?? ($stats['notifications'] ?? 0));
        $feed_proc_url = isset($ctx['feed_proc_url']) ? (string) $ctx['feed_proc_url'] : G5_URL.'/proc/eottae-talkroom-dashboard-feed.php';
        $mypage_talk_url = isset($ctx['mypage_talk_url']) ? (string) $ctx['mypage_talk_url'] : G5_URL.'/mypage/talk.php';
        $feed = isset($ctx['feed']) && is_array($ctx['feed']) ? $ctx['feed'] : array();
        $feed_has_more = !empty($feed['has_more']);
        $feed_next_offset = (int) ($feed['next_offset'] ?? count($feed_items));
        $feed_room_options = isset($ctx['feed_room_options']) && is_array($ctx['feed_room_options']) ? $ctx['feed_room_options'] : array();
        $feed_type_options = isset($ctx['feed_type_options']) && is_array($ctx['feed_type_options']) ? $ctx['feed_type_options'] : array();
        $feed_filters = isset($ctx['feed_filters']) && is_array($ctx['feed_filters']) ? $ctx['feed_filters'] : array('room_id' => 0, 'type' => '');
        $filter_room_id = (int) ($feed_filters['room_id'] ?? 0);
        $filter_type = (string) ($feed_filters['type'] ?? '');
        $notice_items = isset($ctx['notice_items']) && is_array($ctx['notice_items']) ? $ctx['notice_items'] : array();
        $notices = isset($ctx['notices']) && is_array($ctx['notices']) ? $ctx['notices'] : array();
        $notices_has_more = !empty($notices['has_more']);
        $notices_next_offset = (int) ($notices['next_offset'] ?? count($notice_items));
        $notices_proc_url = isset($ctx['notices_proc_url']) ? (string) $ctx['notices_proc_url'] : G5_URL.'/proc/eottae-talkroom-dashboard-notices.php';
        $meetup_items = isset($ctx['meetup_items']) && is_array($ctx['meetup_items']) ? $ctx['meetup_items'] : array();
        $bookmark_items = isset($ctx['bookmark_items']) && is_array($ctx['bookmark_items']) ? $ctx['bookmark_items'] : array();
        $bookmarks_proc_url = isset($ctx['bookmarks_proc_url']) ? (string) $ctx['bookmarks_proc_url'] : G5_URL.'/proc/eottae-talkroom-bookmarks.php';
        $briefing = isset($ctx['briefing']) && is_array($ctx['briefing']) ? $ctx['briefing'] : array('is_empty' => true, 'priority_posts' => array());
        if (!function_exists('render_my_talk_briefing')) {
            include_once G5_LIB_PATH.'/eottae-talkroom-briefing.lib.php';
        }
        $room_ids_json = json_encode(isset($ctx['room_ids']) && is_array($ctx['room_ids']) ? $ctx['room_ids'] : array(), JSON_UNESCAPED_UNICODE);

        $today_cards = array(
            array(
                'key'   => 'new_posts',
                'label' => '새 글',
                'icon'  => '📝',
                'href'  => '#my-talk-rooms-news',
                'value' => (int) ($stats['new_posts'] ?? 0),
            ),
            array(
                'key'   => 'new_comments',
                'label' => '새 댓글',
                'icon'  => '💬',
                'href'  => '#my-talk-rooms-news',
                'value' => (int) ($stats['new_comments'] ?? 0),
            ),
            array(
                'key'   => 'notifications',
                'label' => '내 알림',
                'icon'  => '🔔',
                'href'  => '#my-talk-notifications',
                'value' => (int) ($stats['notifications'] ?? 0),
            ),
            array(
                'key'   => 'owner_tasks',
                'label' => '방장 관리',
                'icon'  => '⚙️',
                'href'  => '#my-talk-owner',
                'value' => (int) ($stats['owner_tasks'] ?? 0),
            ),
        );
        if (!$has_owner) {
            $today_cards = array_values(array_filter($today_cards, function ($card) {
                return ($card['key'] ?? '') !== 'owner_tasks';
            }));
        }

        $member_nick = get_text($briefing['member_nick'] ?? '회원');
        $activity_total = (int) ($stats['new_posts'] ?? 0)
            + (int) ($stats['new_comments'] ?? 0)
            + $notify_unread
            + (int) ($stats['owner_tasks'] ?? 0);
        $greeting_title = $activity_total > 0
            ? sprintf('%s님, 오늘 확인할 세부톡 소식이 있습니다.', $member_nick)
            : sprintf('%s님, 세부톡방 소식을 한눈에 확인하세요.', $member_nick);

        $nav_items = array(
            array('href' => '#my-talk-rooms-news', 'label' => '새소식'),
            array('href' => '#my-talk-feed', 'label' => '피드'),
            array('href' => '#my-talk-notifications', 'label' => '알림', 'badge' => $notify_unread),
            array('href' => '#my-talk-notices', 'label' => '공지'),
            array('href' => '#my-talk-meetups', 'label' => '모임'),
            array('href' => '#my-talk-bookmarks', 'label' => '저장글'),
        );
        if ($has_owner) {
            $nav_items[] = array(
                'href'  => '#my-talk-owner',
                'label' => '방장관리',
                'badge' => (int) ($stats['owner_tasks'] ?? 0),
            );
        }
        ?>
        <div class="my-talk-dashboard" data-my-talk-dashboard>
            <header class="my-talk-hero">
                <p class="my-talk-hero__greeting"><?php echo $greeting_title; ?></p>
                <p class="my-talk-hero__sub">가입한 톡방의 새 글, 댓글, 공지, 모임을 모바일에 맞춰 빠르게 확인하세요.</p>
            </header>

            <?php echo render_my_talk_briefing($briefing); ?>

            <!-- 오늘 확인할 것 -->
            <section class="my-talk-section my-talk-section--panel my-talk-section--today" aria-labelledby="my-talk-today-title">
                <h2 class="my-talk-section__title" id="my-talk-today-title">오늘 확인할 것</h2>
                <ul class="my-talk-today-grid">
                    <?php foreach ($today_cards as $card) {
                        $card_value = (int) ($card['value'] ?? 0);
                        $card_alert = $card_value > 0 ? ' my-talk-today-card--alert' : '';
                        ?>
                    <li class="my-talk-today-card<?php echo $card_alert; ?>">
                        <a href="<?php echo get_text($card['href']); ?>" class="my-talk-today-card__link">
                            <span class="my-talk-today-card__icon" aria-hidden="true"><?php echo $card['icon']; ?></span>
                            <span class="my-talk-today-card__label"><?php echo get_text($card['label']); ?></span>
                            <span class="my-talk-today-card__value" aria-label="<?php echo get_text($card['label']); ?> <?php echo number_format($card_value); ?>">
                                <?php echo number_format($card_value); ?>
                            </span>
                        </a>
                    </li>
                    <?php } ?>
                </ul>
            </section>

            <nav class="my-talk-nav" aria-label="대시보드 바로가기">
                <ul class="my-talk-nav__list">
                    <?php foreach ($nav_items as $nav) {
                        $nav_badge = (int) ($nav['badge'] ?? 0);
                        ?>
                    <li class="my-talk-nav__item">
                        <a href="<?php echo get_text($nav['href']); ?>" class="my-talk-nav__link">
                            <span><?php echo get_text($nav['label']); ?></span>
                            <?php if ($nav_badge > 0) { ?>
                            <span class="my-talk-nav__badge"><?php echo number_format($nav_badge); ?></span>
                            <?php } ?>
                        </a>
                    </li>
                    <?php } ?>
                </ul>
            </nav>

            <!-- 내 톡방 새소식 -->
            <section class="my-talk-section my-talk-section--panel" id="my-talk-rooms-news" aria-labelledby="my-talk-news-title">
                <div class="my-talk-section__head">
                    <h2 class="my-talk-section__title" id="my-talk-news-title">내 톡방 새소식</h2>
                    <div class="my-talk-section__tools">
                        <?php if (!empty($news_rooms)) { ?>
                        <button type="button" class="my-talk-btn my-talk-btn--ghost my-talk-btn--sm" data-my-talk-mark-all>모두 읽음 처리</button>
                        <?php } ?>
                        <a href="<?php echo $list_url; ?>" class="my-talk-section__more">톡방 목록</a>
                    </div>
                </div>
                <?php if (empty($news_rooms)) { ?>
                <div class="my-talk-empty">
                    <p class="my-talk-empty__title">가입한 톡방이 없습니다</p>
                    <p class="my-talk-empty__desc">관심 있는 톡방에 참여하거나 직접 만들어 보세요.</p>
                    <div class="my-talk-empty__actions">
                        <a href="<?php echo $list_url; ?>" class="my-talk-btn my-talk-btn--primary">세부톡방 둘러보기</a>
                        <a href="<?php echo $create_url; ?>" class="my-talk-btn my-talk-btn--ghost">톡방 만들기</a>
                    </div>
                </div>
                <?php } else { ?>
                <ul class="my-talk-room-list">
                    <?php foreach ($news_rooms as $room) {
                        $new_posts = (int) ($room['new_posts'] ?? 0);
                        $new_comments = (int) ($room['new_comments'] ?? 0);
                        $has_unread = !empty($room['has_unread']);
                        $post_badge_class = $new_posts > 0 ? 'my-talk-badge--alert' : 'my-talk-badge--muted';
                        $comment_badge_class = $new_comments > 0 ? 'my-talk-badge--alert' : 'my-talk-badge--muted';
                        ?>
                    <li class="my-talk-room-card<?php echo $has_unread ? ' my-talk-room-card--unread' : ''; ?>" data-my-talk-room-id="<?php echo (int) ($room['room_id'] ?? 0); ?>">
                        <article class="my-talk-room-card__inner">
                            <header class="my-talk-room-card__head">
                                <span class="my-talk-room-card__emoji" aria-hidden="true"><?php echo eottae_talkroom_display_emoji($room['emoji'] ?? '', $room['category_code'] ?? ''); ?></span>
                                <div class="my-talk-room-card__titles">
                                    <h3 class="my-talk-room-card__name">
                                        <span class="my-talk-room-badge"><?php echo get_text($room['room_name'] ?? ''); ?></span>
                                    </h3>
                                    <?php if (!empty($room['category'])) { ?>
                                    <span class="my-talk-room-card__category"><?php echo get_text($room['category']); ?></span>
                                    <?php } ?>
                                </div>
                                <?php if (!empty($room['is_owner'])) { ?>
                                <span class="my-talk-badge my-talk-badge--owner">방장</span>
                                <?php } ?>
                            </header>
                            <p class="my-talk-room-card__unread-summary<?php echo $has_unread ? ' my-talk-room-card__unread-summary--active' : ''; ?>" data-my-talk-field="summary">
                                <?php echo get_text($room['unread_summary'] ?? '새 소식 없음'); ?>
                            </p>
                            <dl class="my-talk-room-card__meta">
                                <?php if (!empty($room['owner_nick'])) { ?>
                                <div class="my-talk-room-card__meta-row">
                                    <dt>방장</dt>
                                    <dd><?php echo get_text($room['owner_nick']); ?></dd>
                                </div>
                                <?php } ?>
                                <div class="my-talk-room-card__meta-row">
                                    <dt>참여</dt>
                                    <dd><?php echo number_format((int) ($room['member_count'] ?? 0)); ?>명</dd>
                                </div>
                                <div class="my-talk-room-card__meta-row">
                                    <dt>게시글</dt>
                                    <dd><?php echo number_format((int) ($room['post_count'] ?? 0)); ?>개</dd>
                                </div>
                                <?php if (!empty($room['updated_label'])) { ?>
                                <div class="my-talk-room-card__meta-row">
                                    <dt>최근 활동</dt>
                                    <dd><?php echo get_text($room['updated_label']); ?></dd>
                                </div>
                                <?php } ?>
                                <div class="my-talk-room-card__meta-row">
                                    <dt>새 글</dt>
                                    <dd>
                                        <span class="my-talk-badge <?php echo $post_badge_class; ?>" data-my-talk-field="new_posts"><?php echo number_format($new_posts); ?></span>
                                    </dd>
                                </div>
                                <div class="my-talk-room-card__meta-row">
                                    <dt>새 댓글</dt>
                                    <dd>
                                        <span class="my-talk-badge <?php echo $comment_badge_class; ?>" data-my-talk-field="new_comments"><?php echo number_format($new_comments); ?></span>
                                    </dd>
                                </div>
                            </dl>
                            <footer class="my-talk-room-card__actions">
                                <a href="<?php echo $room['enter_href'] ?? '#'; ?>" class="my-talk-btn my-talk-btn--primary my-talk-btn--sm">바로가기</a>
                                <button type="button" class="my-talk-btn my-talk-btn--ghost my-talk-btn--sm" data-my-talk-mark-room="<?php echo (int) ($room['room_id'] ?? 0); ?>">읽음 처리</button>
                                <?php if (!empty($room['is_owner']) && !empty($room['manage_href'])) { ?>
                                <a href="<?php echo $room['manage_href']; ?>" class="my-talk-btn my-talk-btn--ghost my-talk-btn--sm">관리</a>
                                <?php } ?>
                            </footer>
                        </article>
                    </li>
                    <?php } ?>
                </ul>
                <?php } ?>
            </section>

            <!-- 내 톡방 피드 -->
            <section class="my-talk-section my-talk-section--panel" id="my-talk-feed" aria-labelledby="my-talk-feed-title">
                <h2 class="my-talk-section__title" id="my-talk-feed-title">내 톡방 피드</h2>
                <p class="my-talk-section__desc">가입한 모든 톡방의 최신글을 한곳에서 볼 수 있습니다.</p>

                <?php if (!empty($feed_room_options) || !empty($feed_type_options)) { ?>
                <form class="my-talk-feed-filters" method="get" action="<?php echo $mypage_talk_url; ?>#my-talk-feed">
                    <?php if (!empty($feed_room_options)) { ?>
                    <label class="my-talk-feed-filters__field">
                        <span class="my-talk-feed-filters__label">톡방</span>
                        <select name="feed_room" class="my-talk-feed-filters__select">
                            <option value="0">전체 톡방</option>
                            <?php foreach ($feed_room_options as $room_opt) { ?>
                            <option value="<?php echo (int) $room_opt['room_id']; ?>"<?php echo $filter_room_id === (int) $room_opt['room_id'] ? ' selected' : ''; ?>><?php echo get_text($room_opt['room_name']); ?></option>
                            <?php } ?>
                        </select>
                    </label>
                    <?php } ?>
                    <?php if (!empty($feed_type_options)) { ?>
                    <label class="my-talk-feed-filters__field">
                        <span class="my-talk-feed-filters__label">글 유형</span>
                        <select name="feed_type" class="my-talk-feed-filters__select">
                            <option value="">전체 유형</option>
                            <?php foreach ($feed_type_options as $type_opt) { ?>
                            <option value="<?php echo get_text($type_opt['value']); ?>"<?php echo $filter_type === (string) $type_opt['value'] ? ' selected' : ''; ?>><?php echo get_text($type_opt['label']); ?></option>
                            <?php } ?>
                        </select>
                    </label>
                    <?php } ?>
                    <button type="submit" class="my-talk-btn my-talk-btn--ghost my-talk-btn--sm">적용</button>
                </form>
                <?php } ?>

                <?php if (empty($feed_items)) { ?>
                <div class="my-talk-feed my-talk-feed--empty">
                    <p class="my-talk-empty__title">참여 중인 톡방의 새 글이 없습니다.</p>
                    <p class="my-talk-empty__desc">관심 있는 톡방에 참여해보세요.</p>
                    <div class="my-talk-empty__actions">
                        <a href="<?php echo $list_url; ?>" class="my-talk-btn my-talk-btn--primary">세부톡방 둘러보기</a>
                    </div>
                </div>
                <?php } else { ?>
                <div class="my-talk-feed my-talk-feed--list" data-my-talk-feed-wrap>
                    <ul class="my-talk-feed__list" data-my-talk-feed-list>
                        <?php echo eottae_talkroom_dashboard_feed_items_html($feed_items); ?>
                    </ul>
                    <?php if ($feed_has_more) { ?>
                    <div class="my-talk-feed__more-wrap">
                        <button type="button" class="my-talk-btn my-talk-btn--ghost" data-my-talk-feed-more data-next-offset="<?php echo $feed_next_offset; ?>">더보기</button>
                    </div>
                    <?php } ?>
                </div>
                <?php } ?>
            </section>

            <!-- 나의 알림 -->
            <section class="my-talk-section my-talk-section--panel" id="my-talk-notifications" aria-labelledby="my-talk-notify-title">
                <div class="my-talk-section__head">
                    <h2 class="my-talk-section__title" id="my-talk-notify-title">나의 알림</h2>
                    <?php if (!empty($notifications)) { ?>
                    <button type="button" class="my-talk-btn my-talk-btn--ghost my-talk-btn--sm" data-my-talk-notify-mark-all>전체 읽음</button>
                    <?php } ?>
                </div>
                <p class="my-talk-section__desc">내 글 댓글, 승인 결과, 방장 관리 알림이 표시됩니다.<?php if ($notify_unread > 0) { ?> <span class="my-talk-badge my-talk-badge--alert"><?php echo number_format($notify_unread); ?>건 미확인</span><?php } ?></p>
                <?php if (empty($notifications)) { ?>
                <div class="my-talk-notify my-talk-notify--empty">
                    <span class="my-talk-notify__icon" aria-hidden="true">🔔</span>
                    <p class="my-talk-empty__title">새로운 알림이 없습니다.</p>
                </div>
                <?php } else { ?>
                <ul class="my-talk-notify__list" data-my-talk-notify-list>
                    <?php echo eottae_talkroom_dashboard_notify_items_html($notifications); ?>
                </ul>
                <?php } ?>
            </section>

            <!-- 내 톡방 공지 -->
            <section class="my-talk-section my-talk-section--panel" id="my-talk-notices" aria-labelledby="my-talk-notices-title">
                <h2 class="my-talk-section__title" id="my-talk-notices-title">내 톡방 공지</h2>
                <p class="my-talk-section__desc">가입한 톡방의 최근 공지를 한곳에서 확인할 수 있습니다.</p>
                <?php if (empty($notice_items)) { ?>
                <div class="my-talk-notice my-talk-notice--empty">
                    <p class="my-talk-empty__title">확인할 공지가 없습니다.</p>
                </div>
                <?php } else { ?>
                <div class="my-talk-notice my-talk-notice--list" data-my-talk-notice-wrap>
                    <ul class="my-talk-notice__list" data-my-talk-notice-list>
                        <?php echo eottae_talkroom_dashboard_notice_items_html($notice_items); ?>
                    </ul>
                    <?php if ($notices_has_more) { ?>
                    <div class="my-talk-notice__more-wrap">
                        <button type="button" class="my-talk-btn my-talk-btn--ghost" data-my-talk-notice-more data-next-offset="<?php echo $notices_next_offset; ?>">더보기</button>
                    </div>
                    <?php } ?>
                </div>
                <?php } ?>
            </section>

            <!-- 내 모임 / 참여 가능한 모임 -->
            <section class="my-talk-section my-talk-section--panel" id="my-talk-meetups" aria-labelledby="my-talk-meetups-title">
                <h2 class="my-talk-section__title" id="my-talk-meetups-title">내 모임 · 참여 가능한 모임</h2>
                <p class="my-talk-section__desc">예정된 모임과 최근 모임 공지를 확인하고 참여 의사를 남길 수 있습니다.</p>
                <?php if (empty($meetup_items)) { ?>
                <div class="my-talk-meetup my-talk-meetup--empty">
                    <p class="my-talk-empty__title">예정된 모임이 없습니다.</p>
                </div>
                <?php } else { ?>
                <ul class="my-talk-meetup__list">
                    <?php echo eottae_talkroom_dashboard_meetup_items_html($meetup_items); ?>
                </ul>
                <?php } ?>
            </section>

            <!-- 저장한 글 -->
            <section class="my-talk-section my-talk-section--panel" id="my-talk-bookmarks" aria-labelledby="my-talk-bookmarks-title">
                <h2 class="my-talk-section__title" id="my-talk-bookmarks-title">저장한 글</h2>
                <p class="my-talk-section__desc">나중에 다시 보고 싶은 톡방 글을 모아볼 수 있습니다.</p>
                <?php if (empty($bookmark_items)) { ?>
                <div class="my-talk-bookmark my-talk-bookmark--empty">
                    <p class="my-talk-empty__title">저장한 글이 없습니다.</p>
                    <p class="my-talk-empty__desc">나중에 다시 보고 싶은 글을 저장해보세요.</p>
                </div>
                <?php } else { ?>
                <ul class="my-talk-bookmark__list" data-my-talk-bookmark-list>
                    <?php echo eottae_talkroom_dashboard_bookmark_items_html($bookmark_items); ?>
                </ul>
                <?php } ?>
            </section>


            <?php if ($has_owner) { ?>
            <!-- 방장 관리 요약 -->
            <section class="my-talk-section my-talk-section--panel" id="my-talk-owner" aria-labelledby="my-talk-owner-title">
                <h2 class="my-talk-section__title" id="my-talk-owner-title">방장 관리 요약</h2>
                <p class="my-talk-section__desc">운영 중인 톡방의 승인·신고·활동 현황을 확인하고 바로 관리할 수 있습니다.</p>
                <ul class="my-talk-owner-list">
                    <?php foreach ($owner_summaries as $room) {
                        echo eottae_talkroom_dashboard_owner_card_html($room);
                    } ?>
                </ul>
            </section>
            <?php } ?>
        </div>
        <?php if ($read_token !== '') { ?>
        <script>
        (function () {
            var readToken = <?php echo json_encode($read_token, JSON_UNESCAPED_UNICODE); ?>;
            var readsUrl = <?php echo json_encode($reads_proc_url, JSON_UNESCAPED_UNICODE); ?>;
            var notifyUrl = <?php echo json_encode($notify_proc_url, JSON_UNESCAPED_UNICODE); ?>;
            var bookmarksUrl = <?php echo json_encode($bookmarks_proc_url, JSON_UNESCAPED_UNICODE); ?>;
            var roomIds = <?php echo $room_ids_json !== '' ? $room_ids_json : '[]'; ?>;

            function postNotifyAction(action, notificationId) {
                var fd = new FormData();
                fd.append('action', action);
                fd.append('eottae_talkroom_member_token', readToken);
                if (notificationId) {
                    fd.append('notification_id', String(notificationId));
                }
                return fetch(notifyUrl, { method: 'POST', body: fd, credentials: 'same-origin' })
                    .then(function (r) { return r.json(); });
            }

            function postReadAction(action, roomId) {
                var fd = new FormData();
                fd.append('action', action);
                fd.append('eottae_talkroom_member_token', readToken);
                if (action === 'mark_room') {
                    fd.append('room_id', String(roomId));
                } else if (action === 'mark_all') {
                    fd.append('room_ids', JSON.stringify(roomIds));
                }
                return fetch(readsUrl, { method: 'POST', body: fd, credentials: 'same-origin' })
                    .then(function (r) { return r.json(); });
            }

            function resetRoomCard(card) {
                if (!card) {
                    return;
                }
                card.classList.remove('my-talk-room-card--unread');
                var summary = card.querySelector('[data-my-talk-field="summary"]');
                if (summary) {
                    summary.textContent = '새 소식 없음';
                    summary.classList.remove('my-talk-room-card__unread-summary--active');
                }
                ['new_posts', 'new_comments'].forEach(function (field) {
                    var el = card.querySelector('[data-my-talk-field="' + field + '"]');
                    if (!el) {
                        return;
                    }
                    el.textContent = '0';
                    el.classList.remove('my-talk-badge--alert');
                    el.classList.add('my-talk-badge--muted');
                });
            }

            function resetTodayCards() {
                document.querySelectorAll('.my-talk-today-card').forEach(function (item) {
                    var label = item.querySelector('.my-talk-today-card__label');
                    if (!label) {
                        return;
                    }
                    var text = label.textContent || '';
                    if (text.indexOf('새 글') === 0 || text.indexOf('새 댓글') === 0) {
                        var valueEl = item.querySelector('.my-talk-today-card__value');
                        if (valueEl) {
                            valueEl.textContent = '0';
                        }
                        var badge = item.querySelector('.my-talk-badge--count');
                        if (badge) {
                            badge.remove();
                        }
                    }
                });
            }

            document.querySelectorAll('[data-my-talk-mark-room]').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    var roomId = btn.getAttribute('data-my-talk-mark-room');
                    btn.disabled = true;
                    postReadAction('mark_room', roomId)
                        .then(function (data) {
                            if (data.success) {
                                var card = document.querySelector('[data-my-talk-room-id="' + roomId + '"]');
                                resetRoomCard(card);
                                location.reload();
                            } else {
                                alert(data.message || '읽음 처리에 실패했습니다.');
                                btn.disabled = false;
                            }
                        })
                        .catch(function () {
                            alert('네트워크 오류가 발생했습니다.');
                            btn.disabled = false;
                        });
                });
            });

            var markAllBtn = document.querySelector('[data-my-talk-mark-all]');
            if (markAllBtn) {
                markAllBtn.addEventListener('click', function () {
                    if (!confirm('가입한 모든 톡방을 읽음 처리할까요?')) {
                        return;
                    }
                    markAllBtn.disabled = true;
                    postReadAction('mark_all')
                        .then(function (data) {
                            if (data.success) {
                                location.reload();
                            } else {
                                alert(data.message || '읽음 처리에 실패했습니다.');
                                markAllBtn.disabled = false;
                            }
                        })
                        .catch(function () {
                            alert('네트워크 오류가 발생했습니다.');
                            markAllBtn.disabled = false;
                        });
                });
            }

            document.querySelectorAll('[data-my-talk-notify-read]').forEach(function (btn) {
                btn.addEventListener('click', function (event) {
                    event.preventDefault();
                    event.stopPropagation();
                    var id = btn.getAttribute('data-my-talk-notify-read');
                    btn.disabled = true;
                    postNotifyAction('mark_read', id).then(function (data) {
                        if (data.success) {
                            location.reload();
                        } else {
                            alert(data.message || '읽음 처리에 실패했습니다.');
                            btn.disabled = false;
                        }
                    }).catch(function () {
                        alert('네트워크 오류가 발생했습니다.');
                        btn.disabled = false;
                    });
                });
            });

            document.querySelectorAll('[data-my-talk-notify-link]').forEach(function (link) {
                link.addEventListener('click', function (event) {
                    var item = link.closest('.my-talk-notify__item');
                    if (!item || !item.classList.contains('my-talk-notify__item--unread')) {
                        return;
                    }
                    var id = link.getAttribute('data-notification-id');
                    if (!id) {
                        return;
                    }
                    event.preventDefault();
                    var href = link.getAttribute('href') || '#';
                    postNotifyAction('mark_read', id).finally(function () {
                        if (href && href !== '#') {
                            window.location.href = href;
                        } else {
                            location.reload();
                        }
                    });
                });
            });

            document.querySelectorAll('[data-my-talk-notice-read]').forEach(function (btn) {
                btn.addEventListener('click', function (event) {
                    event.preventDefault();
                    event.stopPropagation();
                    var roomId = btn.getAttribute('data-my-talk-notice-read');
                    btn.disabled = true;
                    postReadAction('mark_room', roomId).then(function (data) {
                        if (data.success) {
                            var item = btn.closest('.my-talk-notice__item');
                            if (item) {
                                item.classList.remove('my-talk-notice__item--unread');
                                var unreadBadge = item.querySelector('.my-talk-notice__unread');
                                if (unreadBadge) {
                                    unreadBadge.className = 'my-talk-badge my-talk-badge--muted my-talk-notice__confirmed';
                                    unreadBadge.textContent = '확인함';
                                }
                                btn.remove();
                            }
                        } else {
                            alert(data.message || '확인 처리에 실패했습니다.');
                            btn.disabled = false;
                        }
                    }).catch(function () {
                        alert('네트워크 오류가 발생했습니다.');
                        btn.disabled = false;
                    });
                });
            });

            document.querySelectorAll('[data-my-talk-notice-link]').forEach(function (link) {
                link.addEventListener('click', function (event) {
                    var item = link.closest('.my-talk-notice__item');
                    if (!item || !item.classList.contains('my-talk-notice__item--unread')) {
                        return;
                    }
                    var roomId = link.getAttribute('data-room-id');
                    if (!roomId) {
                        return;
                    }
                    event.preventDefault();
                    var href = link.getAttribute('href') || '#';
                    postReadAction('mark_room', roomId).finally(function () {
                        if (href && href !== '#') {
                            window.location.href = href;
                        }
                    });
                });
            });

            function postBookmarkAction(action, roomId, postId) {
                var fd = new FormData();
                fd.append('action', action);
                fd.append('room_id', String(roomId));
                fd.append('post_id', String(postId));
                fd.append('eottae_talkroom_member_token', readToken);
                return fetch(bookmarksUrl, { method: 'POST', body: fd, credentials: 'same-origin' })
                    .then(function (r) { return r.json(); });
            }

            document.querySelectorAll('[data-my-talk-bookmark-remove]').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    var roomId = btn.getAttribute('data-room-id');
                    var postId = btn.getAttribute('data-post-id');
                    if (!roomId || !postId) {
                        return;
                    }
                    if (!confirm('저장을 취소할까요?')) {
                        return;
                    }
                    btn.disabled = true;
                    postBookmarkAction('remove', roomId, postId).then(function (data) {
                        if (data.success) {
                            var item = btn.closest('.my-talk-bookmark__item');
                            if (item) {
                                item.remove();
                            }
                            var list = document.querySelector('[data-my-talk-bookmark-list]');
                            if (list && !list.querySelector('.my-talk-bookmark__item')) {
                                location.reload();
                            }
                        } else {
                            alert(data.message || '저장 취소에 실패했습니다.');
                            btn.disabled = false;
                        }
                    }).catch(function () {
                        alert('네트워크 오류가 발생했습니다.');
                        btn.disabled = false;
                    });
                });
            });

            var notifyMarkAllBtn = document.querySelector('[data-my-talk-notify-mark-all]');
            if (notifyMarkAllBtn) {
                notifyMarkAllBtn.addEventListener('click', function () {
                    notifyMarkAllBtn.disabled = true;
                    postNotifyAction('mark_all').then(function (data) {
                        if (data.success) {
                            location.reload();
                        } else {
                            alert(data.message || '읽음 처리에 실패했습니다.');
                            notifyMarkAllBtn.disabled = false;
                        }
                    }).catch(function () {
                        alert('네트워크 오류가 발생했습니다.');
                        notifyMarkAllBtn.disabled = false;
                    });
                });
            }
        })();
        </script>
        <script>
        (function () {
            var readToken = <?php echo json_encode($read_token, JSON_UNESCAPED_UNICODE); ?>;
            var noticesUrl = <?php echo json_encode($notices_proc_url, JSON_UNESCAPED_UNICODE); ?>;
            var noticeMoreBtn = document.querySelector('[data-my-talk-notice-more]');
            var noticeList = document.querySelector('[data-my-talk-notice-list]');

            if (!noticeMoreBtn || !noticeList) {
                return;
            }

            noticeMoreBtn.addEventListener('click', function () {
                var offset = noticeMoreBtn.getAttribute('data-next-offset') || '0';
                var fd = new FormData();
                fd.append('eottae_talkroom_member_token', readToken);
                fd.append('offset', String(offset));

                noticeMoreBtn.disabled = true;
                noticeMoreBtn.textContent = '불러오는 중…';

                fetch(noticesUrl, { method: 'POST', body: fd, credentials: 'same-origin' })
                    .then(function (r) { return r.json(); })
                    .then(function (data) {
                        if (!data.success) {
                            alert(data.message || '공지를 불러오지 못했습니다.');
                            noticeMoreBtn.disabled = false;
                            noticeMoreBtn.textContent = '더보기';
                            return;
                        }
                        if (data.html) {
                            noticeList.insertAdjacentHTML('beforeend', data.html);
                        }
                        if (data.has_more) {
                            noticeMoreBtn.setAttribute('data-next-offset', String(data.next_offset || 0));
                            noticeMoreBtn.disabled = false;
                            noticeMoreBtn.textContent = '더보기';
                        } else {
                            var wrap = noticeMoreBtn.closest('.my-talk-notice__more-wrap');
                            if (wrap) {
                                wrap.remove();
                            }
                        }
                    })
                    .catch(function () {
                        alert('네트워크 오류가 발생했습니다.');
                        noticeMoreBtn.disabled = false;
                        noticeMoreBtn.textContent = '더보기';
                    });
            });
        })();
        </script>
        <script>
        (function () {
            var readToken = <?php echo json_encode($read_token, JSON_UNESCAPED_UNICODE); ?>;
            var feedUrl = <?php echo json_encode($feed_proc_url, JSON_UNESCAPED_UNICODE); ?>;
            var feedRoomId = <?php echo (int) $filter_room_id; ?>;
            var feedType = <?php echo json_encode($filter_type, JSON_UNESCAPED_UNICODE); ?>;
            var feedMoreBtn = document.querySelector('[data-my-talk-feed-more]');
            var feedList = document.querySelector('[data-my-talk-feed-list]');

            if (!feedMoreBtn || !feedList) {
                return;
            }

            feedMoreBtn.addEventListener('click', function () {
                var offset = feedMoreBtn.getAttribute('data-next-offset') || '0';
                var fd = new FormData();
                fd.append('eottae_talkroom_member_token', readToken);
                fd.append('offset', String(offset));
                fd.append('room_id', String(feedRoomId));
                fd.append('type', feedType);

                feedMoreBtn.disabled = true;
                feedMoreBtn.textContent = '불러오는 중…';

                fetch(feedUrl, { method: 'POST', body: fd, credentials: 'same-origin' })
                    .then(function (r) { return r.json(); })
                    .then(function (data) {
                        if (!data.success) {
                            alert(data.message || '피드를 불러오지 못했습니다.');
                            feedMoreBtn.disabled = false;
                            feedMoreBtn.textContent = '더보기';
                            return;
                        }
                        if (data.html) {
                            feedList.insertAdjacentHTML('beforeend', data.html);
                        }
                        if (data.has_more) {
                            feedMoreBtn.setAttribute('data-next-offset', String(data.next_offset || 0));
                            feedMoreBtn.disabled = false;
                            feedMoreBtn.textContent = '더보기';
                        } else {
                            var wrap = feedMoreBtn.closest('.my-talk-feed__more-wrap');
                            if (wrap) {
                                wrap.remove();
                            }
                        }
                    })
                    .catch(function () {
                        alert('네트워크 오류가 발생했습니다.');
                        feedMoreBtn.disabled = false;
                        feedMoreBtn.textContent = '더보기';
                    });
            });
        })();
        </script>
        <?php } ?>
        <?php
    }
}
