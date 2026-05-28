<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('eottae_estate_deal_statuses')) {
    /**
     * @return array<string, string>
     */
    function eottae_estate_deal_statuses()
    {
        return array(
            'trading'   => '거래중',
            'completed' => '거래완료',
        );
    }
}

if (!function_exists('eottae_estate_normalize_deal_status')) {
    function eottae_estate_normalize_deal_status($status)
    {
        $status = preg_replace('/[^a-z]/', '', (string) $status);

        return array_key_exists($status, eottae_estate_deal_statuses()) ? $status : 'trading';
    }
}

if (!function_exists('eottae_estate_deal_status_meta')) {
    /**
     * @return array{key:string, label:string, class:string}
     */
    function eottae_estate_deal_status_meta($status)
    {
        $status = eottae_estate_normalize_deal_status($status);
        $labels = eottae_estate_deal_statuses();

        return array(
            'key'   => $status,
            'label' => $labels[$status],
            'class' => 'estate-deal-badge--'.$status,
        );
    }
}

if (!function_exists('eottae_estate_deal_status_from_row')) {
    function eottae_estate_deal_status_from_row($row)
    {
        if (!is_array($row)) {
            return 'trading';
        }

        return eottae_estate_normalize_deal_status($row['wr_2'] ?? '');
    }
}

if (!function_exists('eottae_estate_member_thumb_url')) {
    function eottae_estate_member_thumb_url($mb_id)
    {
        $mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) $mb_id);
        if ($mb_id === '') {
            return '';
        }

        if (!function_exists('get_mb_icon_name')) {
            return '';
        }

        $dir = substr($mb_id, 0, 2);
        $file = get_mb_icon_name($mb_id).'.gif';
        $path = G5_DATA_PATH.'/member_image/'.$dir.'/'.$file;
        if (!is_file($path)) {
            $path = G5_DATA_PATH.'/member/'.$dir.'/'.$file;
        }
        if (!is_file($path)) {
            return '';
        }

        $url = (strpos($path, G5_DATA_PATH.'/member_image/') === 0)
            ? G5_DATA_URL.'/member_image/'.$dir.'/'.$file
            : G5_DATA_URL.'/member/'.$dir.'/'.$file;

        if (defined('G5_USE_MEMBER_IMAGE_FILETIME') && G5_USE_MEMBER_IMAGE_FILETIME) {
            $url .= '?'.filemtime($path);
        }

        return $url;
    }
}

if (!function_exists('eottae_estate_member_initial')) {
    function eottae_estate_member_initial($nick)
    {
        $nick = trim(strip_tags((string) $nick));
        if ($nick === '') {
            return '?';
        }

        return function_exists('mb_substr')
            ? mb_substr($nick, 0, 1, 'UTF-8')
            : substr($nick, 0, 1);
    }
}

if (!function_exists('eottae_estate_can_change_deal_status')) {
    function eottae_estate_can_change_deal_status(array $write, $mb_id, $is_super_admin = false)
    {
        if ($is_super_admin) {
            return true;
        }

        $mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) $mb_id);
        if ($mb_id === '') {
            return false;
        }

        $author = preg_replace('/[^a-z0-9_@.-]/i', '', (string) ($write['mb_id'] ?? ''));

        return $author !== '' && $author === $mb_id;
    }
}

if (!function_exists('eottae_estate_render_deal_badge')) {
    function eottae_estate_render_deal_badge($status, $extra_class = '')
    {
        $meta = eottae_estate_deal_status_meta($status);
        $class = 'estate-deal-badge '.$meta['class'];
        if ($extra_class !== '') {
            $class .= ' '.trim($extra_class);
        }

        return '<span class="'.$class.'">'.get_text($meta['label']).'</span>';
    }
}

if (!function_exists('eottae_estate_render_list_thumb')) {
    /**
     * 부동산 목록 썸네일 — 매물 사진 또는 작성자 프로필 + 거래상태·활동뱃지
     *
     * @param array<string, mixed> $item
     */
    function eottae_estate_render_list_thumb($item, $post_thumb_url = '', $options = array())
    {
        $options = is_array($options) ? $options : array();
        $is_view_profile = !empty($options['view_profile']);

        $mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) ($item['mb_id'] ?? ''));
        $author = strip_tags((string) ($item['name'] ?? ($item['wr_name'] ?? '')));
        $deal_status = eottae_estate_deal_status_from_row($item);
        $deal_badge = $is_view_profile
            ? ''
            : eottae_estate_render_deal_badge($deal_status, 'estate-deal-badge--thumb');

        $profile_badge_html = '';
        if ($mb_id !== '' && is_file(G5_PATH.'/components/eottae/member-growth-display.php')) {
            include_once G5_PATH.'/components/eottae/member-growth-display.php';
            if (function_exists('eottae_member_growth_get_profile') && function_exists('eottae_member_growth_render_profile_badge_icon')) {
                $profile = eottae_member_growth_get_profile($mb_id);
                $profile_badge_html = eottae_member_growth_render_profile_badge_icon($profile, array(
                    'href'  => '',
                    'class' => 'estate-profile-thumb__badge-icon',
                ));
            }
        }

        $post_thumb_url = trim((string) $post_thumb_url);
        $member_thumb_url = $mb_id !== '' ? eottae_estate_member_thumb_url($mb_id) : '';
        $use_profile = ($post_thumb_url === '');

        $outer_class = $is_view_profile
            ? 'estate-profile-thumb estate-profile-thumb--view'
            : 'community-post__thumb estate-profile-thumb';

        ob_start();
        ?>
        <div class="<?php echo $outer_class; ?>"<?php echo $is_view_profile ? '' : ' aria-hidden="true"'; ?>>
            <div class="estate-profile-thumb__media<?php echo $use_profile ? ' estate-profile-thumb__media--profile' : ''; ?>">
                <?php if ($use_profile) { ?>
                    <?php if ($member_thumb_url !== '') { ?>
                <img src="<?php echo htmlspecialchars($member_thumb_url, ENT_QUOTES, 'UTF-8'); ?>" alt="" class="estate-profile-thumb__img" width="104" height="104" loading="lazy" decoding="async">
                    <?php } else { ?>
                <span class="estate-profile-thumb__initial" aria-hidden="true"><?php echo htmlspecialchars(eottae_estate_member_initial($author), ENT_QUOTES, 'UTF-8'); ?></span>
                    <?php } ?>
                <?php } else { ?>
                <img src="<?php echo htmlspecialchars($post_thumb_url, ENT_QUOTES, 'UTF-8'); ?>" alt="" class="estate-profile-thumb__img" width="104" height="104" loading="lazy" decoding="async">
                <?php } ?>
                <?php echo $deal_badge; ?>
                <?php if ($profile_badge_html !== '') { ?>
                <span class="estate-profile-thumb__badge" aria-hidden="true"><?php echo $profile_badge_html; ?></span>
                <?php } ?>
            </div>
        </div>
        <?php

        return (string) ob_get_clean();
    }
}

if (!function_exists('eottae_estate_set_deal_status')) {
    /**
     * @return array{ok:bool, message:string, status?:string, label?:string}
     */
    function eottae_estate_set_deal_status($bo_table, $wr_id, $status, $mb_id, $is_super_admin = false)
    {
        if (!function_exists('eottae_is_estate_board') || !eottae_is_estate_board($bo_table)) {
            return array('ok' => false, 'message' => '부동산 게시판이 아닙니다.');
        }

        $wr_id = (int) $wr_id;
        if ($wr_id < 1) {
            return array('ok' => false, 'message' => '글 정보가 올바르지 않습니다.');
        }

        global $g5;

        $write_table = $g5['write_prefix'].$bo_table;
        $write = sql_fetch(" SELECT * FROM `{$write_table}` WHERE wr_id = '{$wr_id}' AND wr_is_comment = 0 LIMIT 1 ");
        if (!$write) {
            return array('ok' => false, 'message' => '게시글을 찾을 수 없습니다.');
        }

        if (!eottae_estate_can_change_deal_status($write, $mb_id, $is_super_admin)) {
            return array('ok' => false, 'message' => '거래 상태를 변경할 권한이 없습니다.');
        }

        $status = eottae_estate_normalize_deal_status($status);
        $status_sql = sql_escape_string($status);
        sql_query(" UPDATE `{$write_table}` SET wr_2 = '{$status_sql}' WHERE wr_id = '{$wr_id}' ", false);

        $meta = eottae_estate_deal_status_meta($status);

        return array(
            'ok'      => true,
            'message' => '거래 상태가 «'.$meta['label'].'»(으)로 변경되었습니다.',
            'status'  => $status,
            'label'   => $meta['label'],
        );
    }
}
