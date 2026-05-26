<?php
include_once(dirname(__FILE__).'/_init.php');
include_once G5_LIB_PATH.'/eottae-calendar.lib.php';
include_once G5_PATH.'/components/eottae/calendar-report.php';

$event_id = isset($_GET['event_id']) ? (int) $_GET['event_id'] : 0;
$event = eottae_calendar_get_event($event_id);

if (!$event) {
    alert('일정을 찾을 수 없습니다.', eottae_calendar_list_url());
}

$is_super = ($is_admin === 'super');
$can_edit = $is_member && eottae_calendar_can_edit_event($event, $member['mb_id'] ?? '', $is_super);
$can_delete = $is_member && eottae_calendar_can_delete_event($event, $member['mb_id'] ?? '', $is_super);
$delete_token = $can_delete ? eottae_calendar_member_token() : '';
$is_google = !empty($event['is_google']);

g5_page_start(get_text($event['title'] ?? '일정 상세'));
?>

<main class="mypage-subpage sebu-cal-page sebu-cal-page--detail">
    <p class="mypage-subpage__back"><a href="<?php echo eottae_calendar_list_url(); ?>">← 세부어때 캘린더</a></p>

    <article class="sebu-cal-detail">
        <header class="sebu-cal-detail__head">
            <div class="sebu-cal-detail__badges">
                <span class="sebu-cal-detail__category <?php echo get_text($event['category_class']); ?>"><?php echo get_text($event['category_label']); ?></span>
                <span class="sebu-cal-detail__badge <?php echo get_text($event['badge_class']); ?>"><?php echo get_text($event['badge_label']); ?></span>
                <?php if ($is_google) { ?>
                <span class="sebu-cal-detail__source">Google Calendar</span>
                <?php } ?>
            </div>
            <h1 class="sebu-cal-detail__title"><?php echo get_text($event['title']); ?></h1>
        </header>

        <dl class="sebu-cal-detail__meta">
            <div>
                <dt>날짜</dt>
                <dd><?php echo get_text($event['date_label']); ?></dd>
            </div>
            <?php if (!empty($event['time_label'])) { ?>
            <div>
                <dt>시간</dt>
                <dd><?php echo get_text($event['time_label']); ?></dd>
            </div>
            <?php } ?>
            <?php if (!empty($event['area_label'])) { ?>
            <div>
                <dt>지역</dt>
                <dd><?php echo get_text($event['area_label']); ?></dd>
            </div>
            <?php } ?>
            <?php if (!empty($event['location'])) { ?>
            <div>
                <dt>장소</dt>
                <dd><?php echo get_text($event['location']); ?></dd>
            </div>
            <?php } ?>
            <div>
                <dt>출처</dt>
                <dd><?php echo get_text($event['source_label'] ?? '세부어때'); ?></dd>
            </div>
            <div>
                <dt>작성자</dt>
                <dd><?php echo get_text($event['writer_display'] ?? $event['writer_name']); ?></dd>
            </div>
            <div>
                <dt>등록일</dt>
                <dd><?php echo get_text(substr((string) ($event['created_at'] ?? ''), 0, 16)); ?></dd>
            </div>
            <?php if (!empty($event['updated_at']) && ($event['updated_at'] ?? '') !== ($event['created_at'] ?? '')) { ?>
            <div>
                <dt>수정일</dt>
                <dd><?php echo get_text(substr((string) ($event['updated_at'] ?? ''), 0, 16)); ?></dd>
            </div>
            <?php } ?>
        </dl>

        <?php if (!empty($event['description'])) { ?>
        <section class="sebu-cal-detail__section">
            <h2 class="sebu-cal-detail__section-title">설명</h2>
            <div class="sebu-cal-detail__content"><?php echo $event['description_html']; ?></div>
        </section>
        <?php } ?>

        <?php if (!empty($event['related_url'])) { ?>
        <p class="sebu-cal-detail__link">
            <a href="<?php echo get_text($event['related_url']); ?>" target="_blank" rel="noopener noreferrer">관련 링크 열기</a>
        </p>
        <?php } ?>

        <?php if (!empty($event['related_room_name'])) { ?>
        <section class="sebu-cal-detail__section sebu-cal-detail__related">
            <h2 class="sebu-cal-detail__section-title">관련 세부톡방</h2>
            <p class="sebu-cal-detail__room">
                <?php if (!empty($event['related_room_href'])) { ?>
                <a href="<?php echo $event['related_room_href']; ?>" class="sebu-cal-btn">톡방으로 이동</a>
                <?php } ?>
                <span><?php echo get_text($event['related_room_name']); ?></span>
            </p>
            <?php if (!empty($event['related_post_url'])) { ?>
            <p class="sebu-cal-detail__link">
                <a href="<?php echo get_text($event['related_post_url']); ?>" target="_blank" rel="noopener noreferrer">관련 글 보기</a>
            </p>
            <?php } ?>
        </section>
        <?php } ?>

        <div class="sebu-cal-detail__actions">
            <?php if ($can_edit) { ?>
            <a href="<?php echo eottae_calendar_edit_url($event_id); ?>" class="sebu-cal-btn sebu-cal-btn--primary">수정</a>
            <?php } ?>
            <?php if ($can_delete) { ?>
            <form method="post" action="<?php echo G5_URL; ?>/proc/eottae-calendar.php" class="sebu-cal-detail__delete-form" onsubmit="return confirm('<?php echo $is_google ? '이 Google 일정을 숨김 처리할까요?' : '이 일정을 삭제할까요?'; ?>');">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="event_id" value="<?php echo (int) $event_id; ?>">
                <input type="hidden" name="eottae_calendar_token" value="<?php echo get_text($delete_token); ?>">
                <button type="submit" class="sebu-cal-btn sebu-cal-btn--danger"><?php echo $is_google ? '숨김' : '삭제'; ?></button>
            </form>
            <?php } ?>
            <?php eottae_calendar_render_event_report_button($event, $member ?? array(), $is_admin ?? ''); ?>
        </div>
    </article>
</main>

<?php
eottae_calendar_render_report_script();
g5_page_end();
