<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('eottae_lang_supported')) {
    function eottae_lang_supported()
    {
        return array(
            'ko' => array('label' => '한국어', 'badge' => 'KO', 'shop_filter' => '한국어 가능'),
            'en' => array('label' => 'English', 'badge' => 'EN', 'shop_filter' => 'English Available'),
            'ja' => array('label' => '日本語', 'badge' => 'JA', 'shop_filter' => '日本語対応'),
            'zh' => array('label' => '中文', 'badge' => 'ZH', 'shop_filter' => '中文可'),
        );
    }
}

if (!function_exists('eottae_lang_ensure_board_columns')) {
    function eottae_lang_ensure_board_columns($bo_table)
    {
        global $g5;

        $bo_table = preg_replace('/[^a-z0-9_]/i', '', (string) $bo_table);
        if ($bo_table === '') {
            return;
        }

        static $done = array();
        if (isset($done[$bo_table])) {
            return;
        }
        $done[$bo_table] = true;

        $write_table = $g5['write_prefix'].$bo_table;
        $exists = sql_fetch(" show tables like '".sql_escape_string($write_table)."' ", false);
        if (empty($exists)) {
            return;
        }

        if (function_exists('eottae_is_shop_board') && eottae_is_shop_board($bo_table)) {
            $col = sql_fetch(" show columns from `{$write_table}` like 'available_languages' ", false);
            if (empty($col)) {
                sql_query(" alter table `{$write_table}` add `available_languages` varchar(100) not null default '' after `wr_10` ", false);
            }
        } else {
            $col = sql_fetch(" show columns from `{$write_table}` like 'language' ", false);
            if (empty($col)) {
                sql_query(" alter table `{$write_table}` add `language` varchar(10) not null default 'ko' after `wr_10` ", false);
                sql_query(" alter table `{$write_table}` add index `idx_language` (`language`) ", false);
            }
        }
    }
}

if (!function_exists('eottae_lang_on_board_head_before')) {
    function eottae_lang_on_board_head_before($board, $write = null, $wr_id = 0)
    {
        if (empty($board['bo_table'])) {
            return;
        }

        eottae_lang_ensure_board_columns($board['bo_table']);
    }
}

if (!function_exists('eottae_lang_normalize')) {
    function eottae_lang_normalize($language, $default = 'ko')
    {
        $language = strtolower(trim((string) $language));
        if (strpos($language, 'ko') === 0) {
            return 'ko';
        }
        if (strpos($language, 'en') === 0) {
            return 'en';
        }
        if (strpos($language, 'ja') === 0) {
            return 'ja';
        }
        if ($language === 'zh' || strpos($language, 'zh-') === 0) {
            return 'zh';
        }

        if ($default === '') {
            return '';
        }

        return isset(eottae_lang_supported()[$default]) ? $default : 'ko';
    }
}

if (!function_exists('eottae_lang_label')) {
    function eottae_lang_label($language)
    {
        $language = eottae_lang_normalize($language);
        $supported = eottae_lang_supported();

        return $supported[$language]['label'];
    }
}

if (!function_exists('eottae_lang_badge')) {
    function eottae_lang_badge($language)
    {
        $language = eottae_lang_normalize($language);
        $supported = eottae_lang_supported();

        return $supported[$language]['badge'];
    }
}

if (!function_exists('eottae_lang_from_row')) {
    function eottae_lang_from_row($row)
    {
        $raw = is_array($row) && isset($row['language']) ? trim((string) $row['language']) : '';

        return eottae_lang_normalize($raw !== '' ? $raw : 'ko');
    }
}

if (!function_exists('eottae_lang_from_request')) {
    function eottae_lang_from_request($key = 'lang')
    {
        if (function_exists('eottae_lang_seo_enabled') && eottae_lang_seo_enabled() && function_exists('eottae_lang_seo_current')) {
            $seo_lang = eottae_lang_seo_current();
            if ($seo_lang !== '' && isset(eottae_lang_supported()[$seo_lang])) {
                return $seo_lang;
            }
        }

        $raw = isset($_REQUEST[$key]) ? trim((string) $_REQUEST[$key]) : '';
        if ($raw === '' && !empty($_REQUEST['eottae_lang'])) {
            $raw = trim((string) $_REQUEST['eottae_lang']);
        }
        if ($raw === '') {
            return '';
        }
        $normalized = eottae_lang_normalize($raw, '');

        return isset(eottae_lang_supported()[$normalized]) ? $normalized : '';
    }
}

if (!function_exists('eottae_lang_options_html')) {
    function eottae_lang_options_html($selected = 'ko')
    {
        $selected = eottae_lang_normalize($selected);
        $html = '';
        foreach (eottae_lang_supported() as $code => $meta) {
            $html .= '<option value="'.$code.'"'.($selected === $code ? ' selected' : '').'>'.get_text($meta['label']).'</option>';
        }

        return $html;
    }
}

if (!function_exists('eottae_lang_badge_html')) {
    function eottae_lang_badge_html($language, $class = '')
    {
        $language = eottae_lang_normalize($language);
        $label = eottae_lang_label($language);
        $class = trim('eottae-lang-badge '.(string) $class);

        return '<span class="'.get_text($class).'" title="'.get_text($label).'">'.get_text($label).'</span>';
    }
}

if (!function_exists('eottae_lang_short_badges_html')) {
    function eottae_lang_short_badges_html($languages, $class = '')
    {
        $languages = eottae_shop_available_languages($languages);
        if (!$languages) {
            return '';
        }

        $class = trim('eottae-lang-badges '.(string) $class);
        $html = '<span class="'.get_text($class).'">';
        foreach ($languages as $language) {
            $html .= '<span class="eottae-lang-badge eottae-lang-badge--short" title="'.get_text(eottae_lang_label($language)).'">'.get_text(eottae_lang_badge($language)).'</span>';
        }
        $html .= '</span>';

        return $html;
    }
}

if (!function_exists('eottae_shop_available_languages')) {
    function eottae_shop_available_languages($raw)
    {
        if (is_array($raw)) {
            $values = $raw;
        } else {
            $raw = trim(stripslashes((string) $raw));
            if ($raw === '') {
                return array('ko');
            }
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) {
                $values = $decoded;
            } else {
                $values = preg_split('/\s*,\s*/', $raw);
            }
        }

        $langs = array();
        foreach ((array) $values as $value) {
            $lang = eottae_lang_normalize($value, '');
            if ($lang !== '' && isset(eottae_lang_supported()[$lang])) {
                $langs[] = $lang;
            }
        }
        $langs = array_values(array_unique($langs));

        return $langs ? $langs : array('ko');
    }
}

if (!function_exists('eottae_shop_available_languages_from_row')) {
    function eottae_shop_available_languages_from_row($row)
    {
        if (!is_array($row)) {
            return array('ko');
        }

        if (isset($row['available_languages'])) {
            return eottae_shop_available_languages($row['available_languages']);
        }

        return array('ko');
    }
}

if (!function_exists('eottae_shop_available_languages_json_from_post')) {
    function eottae_shop_available_languages_json_from_post()
    {
        $raw = isset($_POST['available_languages']) ? (array) $_POST['available_languages'] : array('ko');
        $langs = eottae_shop_available_languages($raw);

        return json_encode($langs, JSON_UNESCAPED_UNICODE);
    }
}

if (!function_exists('eottae_lang_current_site_language')) {
    function eottae_lang_current_site_language()
    {
        $raw = isset($_COOKIE['cebuatteLanguage']) ? (string) $_COOKIE['cebuatteLanguage'] : '';

        return eottae_lang_normalize($raw !== '' ? $raw : 'ko');
    }
}

if (!function_exists('eottae_lang_post_default')) {
    function eottae_lang_post_default($write = array())
    {
        if (is_array($write) && !empty($write['language'])) {
            return eottae_lang_from_row($write);
        }

        return eottae_lang_current_site_language();
    }
}

if (!function_exists('eottae_lang_on_write_update_after')) {
    function eottae_lang_on_write_update_after($board, $wr_id, $w, $qstr = '', $redirect_url = '')
    {
        global $g5;

        if (empty($board['bo_table']) || (int) $wr_id < 1) {
            return;
        }

        $bo_table = preg_replace('/[^a-z0-9_]/i', '', (string) $board['bo_table']);
        $write_table = $g5['write_prefix'].$bo_table;
        if (function_exists('eottae_is_shop_board') && eottae_is_shop_board($bo_table)) {
            $value = eottae_shop_available_languages_json_from_post();
            sql_query(" update `{$write_table}` set available_languages = '".sql_escape_string($value)."' where wr_id = '".(int) $wr_id."' ", false);
        } else {
            $posted = isset($_POST['language']) ? (string) $_POST['language'] : 'ko';
            $value = eottae_lang_normalize($posted);
            sql_query(" update `{$write_table}` set language = '".sql_escape_string($value)."' where wr_id = '".(int) $wr_id."' ", false);
        }
    }
}

if (!function_exists('eottae_lang_get_sql_search_filter')) {
    function eottae_lang_get_sql_search_filter($sql, $search_ca_name, $search_field, $search_text, $search_operator)
    {
        $lang = eottae_lang_from_request('lang');
        if ($lang === '' || !$GLOBALS['bo_table']) {
            return $sql;
        }
        if (function_exists('eottae_is_shop_board') && eottae_is_shop_board($GLOBALS['bo_table'])) {
            return $sql;
        }

        $condition = "language = '".sql_escape_string($lang)."'";
        if ($sql === '0' || trim((string) $sql) === '') {
            return $condition;
        }

        return '('.$sql.') and '.$condition;
    }
}

if (!function_exists('eottae_lang_post_list_segment_sql')) {
    function eottae_lang_post_list_segment_sql()
    {
        $lang = eottae_lang_from_request('lang');
        if ($lang === '' || !isset($GLOBALS['bo_table'])) {
            return '';
        }
        if (function_exists('eottae_is_shop_board') && eottae_is_shop_board($GLOBALS['bo_table'])) {
            return '';
        }

        return " and language = '".sql_escape_string($lang)."' ";
    }
}

if (!function_exists('eottae_lang_filter_url')) {
    function eottae_lang_filter_url($bo_table, $lang = '', $extra = array())
    {
        $params = array_merge(array('bo_table' => $bo_table), (array) $extra);
        if ($lang !== '') {
            $params['lang'] = $lang;
        }

        return G5_BBS_URL.'/board.php?'.http_build_query($params);
    }
}

if (!function_exists('eottae_lang_seo_config')) {
    function eottae_lang_seo_config()
    {
        if (function_exists('eottae_lang_seo_config_resolve')) {
            return eottae_lang_seo_config_resolve();
        }

        return array(
            'enabled' => false,
            'prefixes' => array('en', 'ja', 'zh'),
            'default_language' => 'ko',
            'index_auto_translations' => false,
            'manual_review_required' => true,
            'hreflang_map' => array(
                'ko' => 'ko',
                'en' => 'en',
                'ja' => 'ja',
                'zh' => 'zh-Hans',
            ),
        );
    }
}

if (!function_exists('eottae_member_preferred_language_field')) {
    function eottae_member_preferred_language_field()
    {
        return 'mb_9';
    }
}

if (!function_exists('eottae_member_preferred_language_get')) {
    function eottae_member_preferred_language_get($member)
    {
        if (!is_array($member)) {
            return '';
        }

        $field = eottae_member_preferred_language_field();
        $raw = isset($member[$field]) ? trim((string) $member[$field]) : '';

        return eottae_lang_normalize($raw, '');
    }
}

if (!function_exists('eottae_member_preferred_language_save')) {
    function eottae_member_preferred_language_save($mb_id, $language)
    {
        global $g5;

        $mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) $mb_id);
        $language = eottae_lang_normalize($language, '');
        if ($mb_id === '' || $language === '') {
            return array('ok' => false, 'message' => 'invalid_params');
        }

        $field = eottae_member_preferred_language_field();
        sql_query(" update {$g5['member_table']} set {$field} = '".sql_escape_string($language)."' where mb_id = '".sql_escape_string($mb_id)."' ");

        return array('ok' => true, 'language' => $language);
    }
}

if (!function_exists('eottae_member_preferred_language_options')) {
    function eottae_member_preferred_language_options()
    {
        $options = array();
        foreach (eottae_lang_supported() as $code => $meta) {
            $options[$code] = $meta['label'] ?? strtoupper($code);
        }

        return $options;
    }
}

if (!function_exists('eottae_render_member_preferred_language_field')) {
    function eottae_render_member_preferred_language_field($member = array(), $w = '')
    {
        $current = eottae_member_preferred_language_get($member);
        if ($current === '') {
            $current = 'ko';
        }
        $options = eottae_member_preferred_language_options();

        ob_start();
        ?>
        <div class="eottae-field">
            <label for="reg_preferred_language" data-i18n="member.preferred_language">선호 언어</label>
            <select name="<?php echo eottae_member_preferred_language_field(); ?>" id="reg_preferred_language" class="eottae-select">
                <?php foreach ($options as $code => $label) { ?>
                <option value="<?php echo get_text($code); ?>"<?php echo $current === $code ? ' selected' : ''; ?>><?php echo get_text($label); ?></option>
                <?php } ?>
            </select>
            <p class="eottae-field__hint" data-i18n="member.preferred_language_hint">로그인 후에도 동일한 언어로 사이트를 이용할 수 있습니다.</p>
        </div>
        <?php

        return (string) ob_get_clean();
    }
}
