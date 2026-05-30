<?php
define('EOTTae_MEMBER_LANGUAGE', true);

include_once dirname(__FILE__).'/_eottae_json_bootstrap.php';
include_once G5_LIB_PATH.'/eottae-language-meta.lib.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    eottae_json_send(array('success' => false, 'message' => 'bad_request'), 405);
}

if (empty($is_member) || empty($member['mb_id'])) {
    eottae_json_send(array('success' => false, 'message' => 'auth_required'), 401);
}

$language = eottae_lang_normalize($_POST['preferred_language'] ?? '', '');
if ($language === '' || !isset(eottae_lang_supported()[$language])) {
    eottae_json_send(array('success' => false, 'message' => 'invalid_language'), 400);
}

$result = eottae_member_preferred_language_save($member['mb_id'], $language);
if (empty($result['ok'])) {
    eottae_json_send(array(
        'success' => false,
        'message' => (string) ($result['message'] ?? 'save_failed'),
    ), 400);
}

eottae_json_send(array(
    'success' => true,
    'language' => $language,
));
