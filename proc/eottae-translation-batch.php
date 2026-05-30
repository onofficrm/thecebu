<?php
define('EOTTae_TRANSLATION_BATCH', true);

include_once dirname(__FILE__).'/_eottae_json_bootstrap.php';
include_once G5_LIB_PATH.'/eottae-translation.lib.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    eottae_json_send(array('success' => false, 'message' => 'bad_request'), 405);
}

$target_language = eottae_translation_normalize_language($_POST['target_language'] ?? '', '');
if ($target_language === '' || $target_language === 'ko' || !isset(eottae_translation_supported_languages()[$target_language])) {
    eottae_json_send(array('success' => false, 'message' => 'invalid_language'), 400);
}

$raw_items = isset($_POST['items']) ? $_POST['items'] : '';
if (is_string($raw_items) && $raw_items !== '') {
    $decoded_items = json_decode($raw_items, true);
    $items = is_array($decoded_items) ? $decoded_items : array();
} elseif (is_array($raw_items)) {
    $items = $raw_items;
} else {
    $items = array();
}

if (count($items) > 50) {
    $items = array_slice($items, 0, 50);
}

$translations = eottae_translation_cache_batch_lookup($items, $target_language);

eottae_json_send(array(
    'success' => true,
    'targetLanguage' => $target_language,
    'translations' => $translations,
));
