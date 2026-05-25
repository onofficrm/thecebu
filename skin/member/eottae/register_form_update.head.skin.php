<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('eottae_normalize_member_type_fields')) {
    return;
}

list($mb_1, $mb_2) = eottae_normalize_member_type_fields($mb_1, $mb_2);

$msg = eottae_validate_member_type_fields($mb_1, $mb_2, ($w === ''));
if ($msg !== '') {
    alert($msg);
}
