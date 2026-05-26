<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('eottae_calendar_render_event_modal')) {
    function eottae_calendar_render_event_modal()
    {
        static $rendered = false;
        if ($rendered) {
            return;
        }
        $rendered = true;

        global $is_member, $member;

        $ui = array(
            'api_url'      => G5_URL.'/proc/eottae-calendar-detail.php',
            'proc_url'     => G5_URL.'/proc/eottae-calendar.php',
            'list_url'     => function_exists('eottae_calendar_list_url') ? eottae_calendar_list_url() : G5_URL.'/calendar/',
            'member_token' => (!empty($is_member) && function_exists('eottae_calendar_member_token'))
                ? eottae_calendar_member_token()
                : '',
        );
        ?>
        <div class="sebu-cal-event-modal" id="sebuCalEventModal" hidden>
            <div class="sebu-cal-event-modal__backdrop" data-sebu-cal-event-close tabindex="-1" aria-hidden="true"></div>
            <div class="sebu-cal-event-modal__panel" role="dialog" aria-modal="true" aria-labelledby="sebuCalEventModalTitle">
                <button type="button" class="sebu-cal-event-modal__close" data-sebu-cal-event-close aria-label="닫기">×</button>
                <div class="sebu-cal-event-modal__body" id="sebuCalEventModalBody">
                    <p class="sebu-cal-event-modal__loading">일정을 불러오는 중…</p>
                </div>
            </div>
        </div>
        <script>window.__EOTTae_CALENDAR_UI__=<?php echo json_encode($ui, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;</script>
        <?php
    }
}
