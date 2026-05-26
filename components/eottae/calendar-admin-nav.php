<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('eottae_calendar_render_admin_nav')) {
    function eottae_calendar_render_admin_nav($active = 'reports')
    {
        $items = array(
            'calendar' => array('label' => '캘린더 보기', 'href' => eottae_calendar_list_url()),
            'reports'  => array('label' => '일정 신고 관리', 'href' => eottae_calendar_admin_reports_url('pending')),
        );
        ?>
        <nav class="talk-admin-nav sebu-cal-admin-nav" aria-label="세부어때 캘린더 관리">
            <?php foreach ($items as $key => $item) { ?>
            <a href="<?php echo $item['href']; ?>" class="talk-admin-nav__item<?php echo $active === $key ? ' is-active' : ''; ?>"><?php echo get_text($item['label']); ?></a>
            <?php } ?>
        </nav>
        <?php
    }
}
