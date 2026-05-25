<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('eottae_talkroom_admin_shell_css_rules')) {
    function eottae_talkroom_admin_shell_css_rules()
    {
        return <<<'CSS'
#hd,#container_title,#ft.site-footer-wrap,.mobile-bottom-nav--global{display:none!important}
#wrapper,#container_wr,#container{width:100%!important;max-width:none!important;margin:0!important;padding:0!important;float:none!important;min-width:0!important}
#wrapper,#container_wr,#container,.talk-admin-shell-root,.talk-admin-shell-root .talk-admin-page{overflow:visible!important;height:auto!important;min-height:0!important}
.talk-admin-shell-root{box-sizing:border-box;display:block;clear:both;min-height:100vh;padding:0;background:#eef2f7;color:#0f172a;font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,"Noto Sans KR",sans-serif;line-height:1.5}
.talk-admin-shell-root *,.talk-admin-shell-root *::before,.talk-admin-shell-root *::after{box-sizing:border-box}
.talk-admin-shell-root .talk-admin-page,.talk-admin-shell-root .promo-admin-page{max-width:1120px;margin:0 auto;padding:16px 16px 32px}
.talk-admin-shell-root .promo-admin-page__header{margin-bottom:16px;padding:20px 22px;border-radius:18px;background:#fff;border:1px solid #e2e8f0;box-shadow:0 8px 24px rgba(15,23,42,.06)}
.talk-admin-shell-root .promo-admin-page__header-top{display:flex;flex-wrap:wrap;gap:12px;margin-bottom:12px}
.talk-admin-shell-root .promo-admin-page__back{display:inline-flex;align-items:center;min-height:32px;padding:0 12px;border-radius:999px;background:#f8fafc;border:1px solid #e2e8f0;font-size:12px;font-weight:700;color:#475569;text-decoration:none}
.talk-admin-shell-root .promo-admin-page__back:hover{color:#0284c7;border-color:#bae6fd;background:#f0f9ff}
.talk-admin-shell-root .promo-admin-page__title{margin:0 0 8px;font-size:1.5rem;font-weight:800;color:#0f172a}
.talk-admin-shell-root .promo-admin-page__desc{margin:0;font-size:14px;line-height:1.6;color:#64748b}
.talk-admin-shell-root .talk-admin-page__pending{display:inline-flex;align-items:center;margin-left:6px;padding:2px 10px;border-radius:999px;background:#fef3c7;color:#b45309;font-size:12px;font-weight:800}
.talk-admin-shell-root .talk-admin-nav{display:flex;flex-wrap:nowrap;gap:8px;margin-top:16px;padding-top:16px;border-top:1px solid #f1f5f9;overflow-x:auto;-webkit-overflow-scrolling:touch}
.talk-admin-shell-root .talk-admin-nav__item{flex:0 0 auto;display:inline-flex;align-items:center;min-height:38px;padding:0 14px;border-radius:999px;font-size:13px;font-weight:700;text-decoration:none;color:#334155;background:#f8fafc;border:1px solid #e2e8f0;white-space:nowrap}
.talk-admin-shell-root .talk-admin-nav__item.is-active{color:#fff;background:#0284c7;border-color:#0284c7}
.talk-admin-shell-root .talk-admin-applies__shell{margin-bottom:16px;padding:20px 22px;border-radius:18px;background:#fff;border:1px solid #e2e8f0;box-shadow:0 8px 24px rgba(15,23,42,.06)}
.talk-admin-shell-root .talk-admin-applies__shell .promo-admin-page__desc{margin-bottom:0}
.talk-admin-shell-root .talk-admin-applies__body{display:block!important;clear:both;width:100%;min-height:160px;margin:16px -22px -20px;padding:0;overflow:visible;border-top:1px solid #eef2f6;border-radius:0 0 18px 18px;background:#fff}
.talk-admin-shell-root .talk-admin-applies__panel{display:block!important;min-height:120px;margin-top:0;padding:0;overflow:visible;border:0;border-radius:0;background:transparent;box-shadow:none}
.talk-admin-shell-root .talk-admin-applies__body .talk-admin-applies__filter,.talk-admin-shell-root .talk-admin-applies__body .talk-admin-applies__empty,.talk-admin-shell-root .talk-admin-applies__body .talk-admin-applies__summary,.talk-admin-shell-root .talk-admin-applies__body .talk-admin-table-wrap{display:block!important;float:none!important;clear:both!important;width:100%!important;visibility:visible!important}
.talk-admin-shell-root .talk-admin-applies__filter{margin:0;padding:14px 18px;border-bottom:1px solid #eef2f6;background:#f8fafc}
.talk-admin-shell-root .talk-admin-filter{display:flex;flex-wrap:wrap;gap:8px;margin:0}
.talk-admin-shell-root .talk-admin-filter__item{display:inline-flex;align-items:center;min-height:36px;padding:0 14px;border-radius:999px;font-size:13px;font-weight:700;text-decoration:none;color:#64748b;background:#f8fafc;border:1px solid #e2e8f0}
.talk-admin-shell-root .talk-admin-filter__item.is-active{color:#0284c7;background:#f0f9ff;border-color:#7dd3fc}
.talk-admin-shell-root .promo-admin-panel{padding:0;overflow:visible;border-radius:18px;background:#fff;border:1px solid #e2e8f0;box-shadow:0 8px 24px rgba(15,23,42,.06)}
.talk-admin-shell-root .promo-admin-panel__title{margin:0 0 16px;padding:20px 22px 0;font-size:1rem;font-weight:800;color:#0f172a}
.talk-admin-shell-root .promo-admin-empty{margin:0;padding:48px 20px;text-align:center;color:#64748b;font-size:14px}
.talk-admin-shell-root .talk-admin-applies__summary{display:flex;flex-wrap:wrap;gap:12px;align-items:center;padding:14px 18px;border-bottom:1px solid #eef2f7;font-size:13px;color:#64748b}
.talk-admin-shell-root .talk-admin-applies__summary strong{color:#0f172a}
.talk-admin-shell-root .talk-admin-applies__summary-pending{color:#b45309;font-weight:700}
.talk-admin-shell-root .talk-admin-applies__empty{padding:28px 18px 32px;text-align:center}
.talk-admin-shell-root .talk-admin-applies__empty-hint{margin:8px 0 0;font-size:13px;color:#94a3b8;text-align:center}
.talk-admin-shell-root .talk-admin-applies__empty-hint--warn{color:#b45309}
.talk-admin-shell-root .talk-admin-applies__empty-hint a{color:#0284c7;font-weight:700;text-decoration:underline}
.talk-admin-shell-root .talk-admin-applies__room{font-weight:700;color:#0f172a}
.talk-admin-shell-root .talk-admin-applies__row.is-pending{background:#fffbeb}
.talk-admin-shell-root .talk-admin-applies__row.is-pending:hover{background:#fef3c7}
.talk-admin-shell-root .talk-admin-kicked__panel{display:block;min-height:120px}
.talk-admin-shell-root .talk-admin-kicked__empty{padding:28px 18px 32px;text-align:center}
.talk-admin-shell-root .talk-admin-table__reason{max-width:240px;font-size:12px;line-height:1.5;color:#64748b;word-break:break-word}
.talk-admin-shell-root .talk-admin-table-wrap{overflow-x:auto}
.talk-admin-shell-root .talk-admin-table{width:100%;min-width:760px;border-collapse:collapse;font-size:13px}
.talk-admin-shell-root .talk-admin-table th,.talk-admin-shell-root .talk-admin-table td{padding:14px 12px;border-bottom:1px solid #eef2f7;text-align:left;vertical-align:middle}
.talk-admin-shell-root .talk-admin-table th{background:#f8fafc;font-size:12px;font-weight:800;color:#64748b;white-space:nowrap}
.talk-admin-shell-root .talk-admin-table tbody tr:hover{background:#fafcff}
.talk-admin-shell-root .talk-admin-table__sub{display:block;margin-top:2px;font-size:11px;color:#94a3b8}
.talk-admin-shell-root .talk-admin-table__actions{display:flex;flex-wrap:wrap;gap:6px}
.talk-admin-shell-root .promo-admin-btn{display:inline-flex;align-items:center;justify-content:center;min-height:34px;padding:0 12px;border:1px solid #cbd5e1;border-radius:10px;background:#fff;font-size:12px;font-weight:700;line-height:1.2;text-decoration:none;color:#334155;cursor:pointer;font-family:inherit}
.talk-admin-shell-root .promo-admin-btn--primary{background:#0284c7;border-color:#0284c7;color:#fff}
.talk-admin-shell-root .promo-admin-btn--sm{min-height:32px;padding:0 10px}
.talk-admin-shell-root .talk-apply-status{display:inline-flex;align-items:center;min-height:26px;padding:0 10px;border-radius:999px;font-size:11px;font-weight:800}
.talk-admin-shell-root .talk-apply-status--pending{background:#fef3c7;color:#b45309}
.talk-admin-shell-root .talk-apply-status--approved,.talk-admin-shell-root .talk-apply-status--active{background:#dcfce7;color:#166534}
.talk-admin-shell-root .talk-apply-status--rejected,.talk-admin-shell-root .talk-apply-status--closed{background:#fee2e2;color:#991b1b}
@media (max-width:767px){.talk-admin-shell-root .talk-admin-page{padding:12px 12px 24px}.talk-admin-shell-root .promo-admin-page__header{padding:16px}.talk-admin-shell-root .talk-admin-applies__shell{padding:16px}.talk-admin-shell-root .talk-admin-applies__body{margin:16px -16px -16px}.talk-admin-shell-root .promo-admin-page__title{font-size:1.25rem}.talk-admin-shell-root .talk-admin-table thead{display:none}.talk-admin-shell-root .talk-admin-table,.talk-admin-shell-root .talk-admin-table tbody,.talk-admin-shell-root .talk-admin-table tr,.talk-admin-shell-root .talk-admin-table td{display:block;width:100%;min-width:0}.talk-admin-shell-root .talk-admin-table tr{margin:0 0 12px;padding:14px;border:1px solid #e2e8f0;border-radius:14px;background:#fff}.talk-admin-shell-root .talk-admin-table td{padding:6px 0;border:0}.talk-admin-shell-root .talk-admin-table td::before{content:attr(data-label) ": ";display:inline-block;min-width:72px;font-weight:800;color:#64748b}.talk-admin-shell-root .talk-admin-table td:last-child::before{display:none}.talk-admin-shell-root .talk-admin-table td:last-child{margin-top:10px;padding-top:10px;border-top:1px dashed #e2e8f0}.talk-admin-shell-root .talk-admin-table td:last-child .promo-admin-btn{flex:1 1 auto}}
CSS;
    }
}

if (!function_exists('eottae_talkroom_admin_shell_wrap_open')) {
    function eottae_talkroom_admin_shell_wrap_open()
    {
        echo '<div class="talk-admin-shell-root">'.PHP_EOL;
        echo '<style id="eottae-talk-admin-shell-css">'.eottae_talkroom_admin_shell_css_rules().'</style>'.PHP_EOL;
    }
}

if (!function_exists('eottae_talkroom_admin_shell_wrap_close')) {
    function eottae_talkroom_admin_shell_wrap_close()
    {
        echo '</div>'.PHP_EOL;
    }
}

if (!function_exists('g5_talk_admin_page_start')) {
    function g5_talk_admin_page_start($title)
    {
        g5_page_start($title);
        eottae_talkroom_admin_shell_wrap_open();
    }
}

if (!function_exists('g5_talk_admin_page_end')) {
    function g5_talk_admin_page_end()
    {
        eottae_talkroom_admin_shell_wrap_close();
        g5_page_end();
    }
}
