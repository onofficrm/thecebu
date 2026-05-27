<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('eottae_golf_join_render_admin_nav')) {
    function eottae_golf_join_render_admin_nav($active = 'posts')
    {
        $tabs = array(
            'posts'   => array('label' => '모집글', 'url' => eottae_golf_join_admin_url('posts')),
            'reports' => array('label' => '신고', 'url' => eottae_golf_join_admin_url('reports')),
            'courses' => array('label' => '골프장', 'url' => eottae_golf_join_admin_url('courses')),
        );
        ?>
        <nav class="talk-admin-filter" aria-label="골프조인 관리">
            <?php foreach ($tabs as $key => $tab) { ?>
            <a href="<?php echo $tab['url']; ?>" class="talk-admin-filter__item<?php echo $active === $key ? ' is-active' : ''; ?>"><?php echo get_text($tab['label']); ?></a>
            <?php } ?>
        </nav>
        <?php
    }
}
