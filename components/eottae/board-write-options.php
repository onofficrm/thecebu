<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

$option = '';
$option_hidden = '';

if ($is_notice || $is_html || $is_secret || $is_mail) {
    if ($is_notice) {
        $option .= PHP_EOL.'<li class="chk_box"><input type="checkbox" id="notice" name="notice" class="selec_chk" value="1" '.$notice_checked.'>'.PHP_EOL.'<label for="notice"><span></span>공지</label></li>';
    }
    if ($is_html) {
        if ($is_dhtml_editor) {
            $option_hidden .= '<input type="hidden" value="html1" name="html">';
        } else {
            $option .= PHP_EOL.'<li class="chk_box"><input type="checkbox" id="html" name="html" onclick="html_auto_br(this);" class="selec_chk" value="'.$html_value.'" '.$html_checked.'>'.PHP_EOL.'<label for="html"><span></span>html</label></li>';
        }
    }
    if ($is_secret) {
        if ($is_admin || $is_secret == 1) {
            $option .= PHP_EOL.'<li class="chk_box"><input type="checkbox" id="secret" name="secret" class="selec_chk" value="secret" '.$secret_checked.'>'.PHP_EOL.'<label for="secret"><span></span>비밀글</label></li>';
        } else {
            $option_hidden .= '<input type="hidden" name="secret" value="secret">';
        }
    }
    if ($is_mail) {
        $option .= PHP_EOL.'<li class="chk_box"><input type="checkbox" id="mail" name="mail" class="selec_chk" value="mail" '.$recv_email_checked.'>'.PHP_EOL.'<label for="mail"><span></span>답변메일받기</label></li>';
    }
}

echo $option_hidden;
