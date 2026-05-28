<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('eottae_member_profile_enabled')) {
    function eottae_member_profile_enabled()
    {
        global $config;

        return !empty($config['cf_member_img_size'])
            && !empty($config['cf_member_img_width'])
            && !empty($config['cf_member_img_height']);
    }
}

if (!function_exists('eottae_member_profile_image_path')) {
    function eottae_member_profile_image_path($mb_id)
    {
        $mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) $mb_id);
        if ($mb_id === '') {
            return '';
        }

        return G5_DATA_PATH.'/member_image/'.substr($mb_id, 0, 2).'/'.get_mb_icon_name($mb_id).'.gif';
    }
}

if (!function_exists('eottae_member_profile_image_url')) {
    function eottae_member_profile_image_url($mb_id)
    {
        $mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) $mb_id);
        if ($mb_id === '') {
            return '';
        }

        $path = eottae_member_profile_image_path($mb_id);
        if ($path === '' || !is_file($path)) {
            return '';
        }

        $mtime = (defined('G5_USE_MEMBER_IMAGE_FILETIME') && G5_USE_MEMBER_IMAGE_FILETIME)
            ? '?'.filemtime($path)
            : '';

        return G5_DATA_URL.'/member_image/'.substr($mb_id, 0, 2).'/'.get_mb_icon_name($mb_id).'.gif'.$mtime;
    }
}

if (!function_exists('eottae_member_profile_ai_tmp_dir')) {
    function eottae_member_profile_ai_tmp_dir()
    {
        return G5_DATA_PATH.'/member_profile_ai_tmp';
    }
}

if (!function_exists('eottae_member_profile_ai_tmp_url_base')) {
    function eottae_member_profile_ai_tmp_url_base()
    {
        return G5_DATA_URL.'/member_profile_ai_tmp';
    }
}

if (!function_exists('eottae_member_profile_ai_enabled')) {
    function eottae_member_profile_ai_enabled()
    {
        if (!is_file(G5_LIB_PATH.'/eottae-ai-generate.lib.php')) {
            return false;
        }

        include_once G5_LIB_PATH.'/eottae-ai-generate.lib.php';
        $cfg = eottae_ai_generate_bootstrap_config();

        return !empty($cfg['enabled']) && !empty($cfg['api_key']) && function_exists('curl_init');
    }
}

if (!function_exists('eottae_member_profile_save_ai_binary')) {
    /**
     * @return array{ok:bool,tmp?:string,url?:string,message?:string}
     */
    function eottae_member_profile_save_ai_binary($binary)
    {
        if ($binary === '' || $binary === false) {
            return array('ok' => false, 'message' => '이미지 데이터가 비어 있습니다.');
        }

        $dir = eottae_member_profile_ai_tmp_dir();
        if (!is_dir($dir)) {
            @mkdir($dir, G5_DIR_PERMISSION, true);
            @chmod($dir, G5_DIR_PERMISSION);
        }

        $file = 'ai_'.date('YmdHis').'_'.substr(md5(uniqid('', true)), 0, 12).'.png';
        if (@file_put_contents($dir.'/'.$file, $binary) === false) {
            return array('ok' => false, 'message' => 'AI 이미지 저장에 실패했습니다.');
        }
        @chmod($dir.'/'.$file, G5_FILE_PERMISSION);

        return array(
            'ok'  => true,
            'tmp' => $file,
            'url' => eottae_member_profile_ai_tmp_url_base().'/'.$file,
        );
    }
}

if (!function_exists('eottae_member_profile_apply_image_file')) {
    /**
     * 회원 프로필 이미지(gnuboard member_image) 저장
     *
     * @return array{ok:bool,message?:string}
     */
    function eottae_member_profile_apply_image_file($mb_id, $source_path)
    {
        global $config;

        $mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) $mb_id);
        if ($mb_id === '' || !is_file($source_path)) {
            return array('ok' => false, 'message' => '저장할 이미지가 없습니다.');
        }

        if (!eottae_member_profile_enabled()) {
            return array('ok' => false, 'message' => '프로필 사진 기능이 비활성화되어 있습니다.');
        }

        $size = @getimagesize($source_path);
        if (!$size || !in_array((int) ($size[2] ?? 0), array(1, 2, 3), true)) {
            return array('ok' => false, 'message' => '이미지 파일 형식이 올바르지 않습니다.');
        }

        $mb_tmp_dir = G5_DATA_PATH.'/member_image/';
        $mb_dir = $mb_tmp_dir.substr($mb_id, 0, 2);
        if (!is_dir($mb_tmp_dir)) {
            @mkdir($mb_tmp_dir, G5_DIR_PERMISSION, true);
            @chmod($mb_tmp_dir, G5_DIR_PERMISSION);
        }
        if (!is_dir($mb_dir)) {
            @mkdir($mb_dir, G5_DIR_PERMISSION, true);
            @chmod($mb_dir, G5_DIR_PERMISSION);
        }

        $filename = get_mb_icon_name($mb_id).'.gif';
        $dest_path = $mb_dir.'/'.$filename;

        if (!@copy($source_path, $dest_path)) {
            return array('ok' => false, 'message' => '프로필 사진 저장에 실패했습니다.');
        }
        @chmod($dest_path, G5_FILE_PERMISSION);

        $max_w = (int) $config['cf_member_img_width'];
        $max_h = (int) $config['cf_member_img_height'];
        $size = @getimagesize($dest_path);
        if ($size && ($size[0] > $max_w || $size[1] > $max_h) && function_exists('thumbnail')) {
            $thumb = null;
            if ((int) $size[2] === 2 || (int) $size[2] === 3) {
                $thumb = thumbnail($filename, $mb_dir, $mb_dir, $max_w, $max_h, true, true);
                if ($thumb) {
                    @unlink($dest_path);
                    @rename($mb_dir.'/'.$thumb, $dest_path);
                    @chmod($dest_path, G5_FILE_PERMISSION);
                }
            }
            if (!$thumb) {
                @unlink($dest_path);

                return array('ok' => false, 'message' => '이미지 크기가 너무 큽니다. 더 작은 이미지를 사용해 주세요.');
            }
        }

        return array('ok' => true);
    }
}

if (!function_exists('eottae_member_profile_apply_ai_tmp')) {
    function eottae_member_profile_apply_ai_tmp($mb_id, $tmp_name)
    {
        $tmp_name = basename(preg_replace('/[^a-zA-Z0-9._-]/', '', (string) $tmp_name));
        if ($tmp_name === '') {
            return array('ok' => false, 'message' => '임시 이미지가 없습니다.');
        }

        $src = eottae_member_profile_ai_tmp_dir().'/'.$tmp_name;
        if (!is_file($src)) {
            return array('ok' => false, 'message' => 'AI 프로필 이미지를 찾을 수 없습니다. 다시 생성해 주세요.');
        }

        $result = eottae_member_profile_apply_image_file($mb_id, $src);
        @unlink($src);

        return $result;
    }
}

if (!function_exists('eottae_member_profile_build_ai_prompt')) {
    function eottae_member_profile_build_ai_prompt($nick, $audience = '', $role = '')
    {
        $nick = trim(strip_tags((string) $nick));
        $audience = preg_replace('/[^a-z]/', '', (string) $audience);
        $role = preg_replace('/[^a-z]/', '', (string) $role);

        $audience_label = 'community member';
        if ($audience === 'tourist') {
            $audience_label = 'tourist visiting Cebu';
        } elseif ($audience === 'expat') {
            $audience_label = 'expat living in Cebu';
        } elseif ($audience === 'both') {
            $audience_label = 'tourist and expat in Cebu';
        }

        $role_label = 'general member';
        if ($role === 'business') {
            $role_label = 'local business owner';
        }

        return "Create a friendly square profile avatar portrait for a Philippines Cebu community app user.\n"
            ."Style: clean modern illustration or soft photorealistic portrait, warm natural light, approachable smile, simple neutral background, no text, no logo, no watermark.\n"
            ."Composition: centered face or upper body, readable at small circular avatar size.\n"
            ."Nickname hint: ".($nick !== '' ? $nick : 'member')."\n"
            ."User type: {$audience_label}, {$role_label}.";
    }
}

if (!function_exists('eottae_member_profile_generate_ai_image')) {
    /**
     * @return array{ok:bool,tmp?:string,url?:string,message?:string}
     */
    function eottae_member_profile_generate_ai_image($nick, $audience = '', $role = '')
    {
        if (!is_file(G5_LIB_PATH.'/eottae-ai-generate.lib.php')) {
            return array('ok' => false, 'message' => 'AI 모듈을 불러올 수 없습니다.');
        }

        include_once G5_LIB_PATH.'/eottae-ai-generate.lib.php';
        $cfg = eottae_ai_generate_bootstrap_config();
        if (empty($cfg['enabled']) || $cfg['api_key'] === '') {
            return array('ok' => false, 'message' => 'AI 프로필 생성이 설정되지 않았습니다.');
        }

        if (!function_exists('curl_init')) {
            return array('ok' => false, 'message' => '서버 PHP cURL 확장이 필요합니다.');
        }

        $prompt = eottae_member_profile_build_ai_prompt($nick, $audience, $role);
        $payload = array(
            'model'  => $cfg['image_model'],
            'prompt' => $prompt,
            'size'   => '1024x1024',
            'n'      => 1,
        );

        $ch = curl_init('https://api.openai.com/v1/images/generations');
        curl_setopt_array($ch, array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_HTTPHEADER     => array(
                'Content-Type: application/json',
                'Authorization: Bearer '.$cfg['api_key'],
            ),
            CURLOPT_POSTFIELDS     => json_encode($payload, JSON_UNESCAPED_UNICODE),
            CURLOPT_TIMEOUT        => 90,
        ));

        $raw = curl_exec($ch);
        $curl_error = curl_error($ch);
        $http_code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($raw === false || $raw === '' || $http_code < 200 || $http_code >= 300) {
            $message = function_exists('eottae_ai_generate_openai_error_message')
                ? eottae_ai_generate_openai_error_message($http_code, $raw, $curl_error)
                : 'AI 프로필 생성에 실패했습니다.';

            return array('ok' => false, 'message' => $message);
        }

        $decoded = json_decode($raw, true);
        $b64 = isset($decoded['data'][0]['b64_json']) ? (string) $decoded['data'][0]['b64_json'] : '';
        if ($b64 === '') {
            return array('ok' => false, 'message' => 'AI 이미지 응답을 해석하지 못했습니다.');
        }

        $bin = base64_decode($b64, true);
        if ($bin === false || $bin === '') {
            return array('ok' => false, 'message' => 'AI 이미지 변환에 실패했습니다.');
        }

        return eottae_member_profile_save_ai_binary($bin);
    }
}

if (!function_exists('eottae_render_member_profile_photo_field')) {
    function eottae_render_member_profile_photo_field(array $args = array())
    {
        if (!eottae_member_profile_enabled()) {
            return '';
        }

        global $config;

        $w = isset($args['w']) ? (string) $args['w'] : '';
        $member = isset($args['member']) && is_array($args['member']) ? $args['member'] : array();
        $mb_id = isset($member['mb_id']) ? (string) $member['mb_id'] : '';
        $nick = isset($member['mb_nick']) ? get_text($member['mb_nick']) : '';

        $img_url = '';
        if (!empty($args['mb_img_url'])) {
            $img_url = (string) $args['mb_img_url'];
        } elseif ($mb_id !== '') {
            $img_url = eottae_member_profile_image_url($mb_id);
        } elseif (!empty($args['mb_img_path']) && is_file($args['mb_img_path'])) {
            $img_url = str_replace(G5_DATA_PATH, G5_DATA_URL, (string) $args['mb_img_path']);
        }

        $has_image = $img_url !== '';
        $initial = $nick !== ''
            ? (function_exists('mb_substr') ? mb_substr($nick, 0, 1, 'UTF-8') : substr($nick, 0, 1))
            : '?';

        $ai_enabled = eottae_member_profile_ai_enabled();
        $max_kb = (int) ceil(((int) $config['cf_member_img_size']) / 1024);
        $max_w = (int) $config['cf_member_img_width'];
        $max_h = (int) $config['cf_member_img_height'];

        $js_url = G5_JS_URL.'/eottae-member-profile.js';
        $js_ver = is_file(G5_PATH.'/js/eottae-member-profile.js') ? (string) @filemtime(G5_PATH.'/js/eottae-member-profile.js') : '';
        add_javascript('<script src="'.$js_url.($js_ver !== '' ? '?v='.$js_ver : '').'" defer></script>', 25);

        $proc_url = G5_URL.'/proc/eottae-member-profile-ai.php';
        $token = function_exists('get_session') ? (string) get_session('ss_token') : '';

        $file_label = $has_image ? '사진 변경' : '사진 선택';

        ob_start();
        ?>
        <div class="eottae-member-profile-field eottae-field" data-eottae-member-profile="1"
            data-proc-url="<?php echo htmlspecialchars($proc_url, ENT_QUOTES, 'UTF-8'); ?>"
            data-token="<?php echo htmlspecialchars($token, ENT_QUOTES, 'UTF-8'); ?>"
            data-ai-enabled="<?php echo $ai_enabled ? '1' : '0'; ?>"
            data-has-existing="<?php echo $has_image ? '1' : '0'; ?>">
            <span class="eottae-member-profile-field__label" id="eottae-member-profile-label">프로필 사진 <span class="eottae-member-profile-field__optional">(선택)</span></span>

            <div class="eottae-member-profile-field__card">
                <div class="eottae-member-profile-field__preview<?php echo $has_image ? ' has-image' : ''; ?>" data-profile-preview aria-labelledby="eottae-member-profile-label">
                    <img src="<?php echo $has_image ? get_text($img_url) : ''; ?>" alt="" class="eottae-member-profile-field__preview-img" data-profile-preview-img<?php echo $has_image ? '' : ' hidden'; ?>>
                    <span class="eottae-member-profile-field__preview-initial" data-profile-preview-initial<?php echo $has_image ? ' hidden' : ''; ?>"><?php echo htmlspecialchars($initial, ENT_QUOTES, 'UTF-8'); ?></span>
                </div>

                <div class="eottae-member-profile-field__upload">
                    <input type="file" name="mb_img" id="reg_mb_img" class="eottae-member-profile-field__file" accept="image/jpeg,image/png,image/gif,image/webp" data-profile-file-input>
                    <input type="hidden" name="eottae_mb_img_ai" value="" data-profile-ai-tmp>

                    <label class="eottae-member-profile-field__pick" for="reg_mb_img">
                        <span class="eottae-member-profile-field__pick-btn" data-profile-pick-label><?php echo get_text($file_label); ?></span>
                    </label>
                    <p class="eottae-member-profile-field__filename" data-profile-filename><?php echo $has_image ? '등록된 프로필 사진이 있습니다.' : '선택된 사진이 없습니다.'; ?></p>
                    <p class="eottae-member-profile-field__hint">jpg, png, gif · <?php echo number_format($max_w); ?>×<?php echo number_format($max_h); ?>px 권장 · <?php echo number_format($max_kb); ?>KB 이하</p>

                    <div class="eottae-member-profile-field__actions">
                        <?php if ($ai_enabled) { ?>
                        <button type="button" class="eottae-member-profile-field__ai-btn" data-profile-ai-generate>AI 프로필 만들기</button>
                        <?php } ?>
                        <?php if ($w === 'u' && $has_image) { ?>
                        <label class="eottae-member-profile-field__delete">
                            <input type="checkbox" name="del_mb_img" value="1" id="del_mb_img" data-profile-delete-checkbox>
                            <span>삭제</span>
                        </label>
                        <?php } ?>
                    </div>

                    <p class="eottae-member-profile-field__ai-status" data-profile-ai-status hidden></p>
                </div>
            </div>
        </div>
        <?php

        return (string) ob_get_clean();
    }
}
