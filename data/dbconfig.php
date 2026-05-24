<?php
if (!defined('_GNUBOARD_')) exit;

// 로컬 개발: data/dbconfig.local.php 가 있으면 원격 대신 로컬 DB 사용 (git 제외)
if (is_file(__DIR__ . '/dbconfig.local.php')) {
    include_once(__DIR__ . '/dbconfig.local.php');
}

if (!defined('G5_MYSQL_HOST')) {
    define('G5_MYSQL_HOST', 'localhost');
}
if (!defined('G5_MYSQL_USER')) {
    define('G5_MYSQL_USER', 'your_db_user');
}
if (!defined('G5_MYSQL_PASSWORD')) {
    define('G5_MYSQL_PASSWORD', 'your_db_password');
}
if (!defined('G5_MYSQL_DB')) {
    define('G5_MYSQL_DB', 'your_db_name');
}
if (!defined('G5_MYSQL_SET_MODE')) {
    define('G5_MYSQL_SET_MODE', true);
}

if (!function_exists('onoff_dbconfig_needs_setup')) {
    function onoff_dbconfig_needs_setup()
    {
        $placeholder_passwords = array('your_db_password', '여기에_로컬_DB_비밀번호', '');
        $placeholder_dbs = array('your_db_name', 'your_local_db_name', '');

        if (G5_MYSQL_USER === 'your_db_user' || G5_MYSQL_USER === '') {
            return 'missing_user';
        }
        if (in_array(G5_MYSQL_PASSWORD, $placeholder_passwords, true)) {
            return 'missing_password';
        }
        if (in_array(G5_MYSQL_DB, $placeholder_dbs, true)) {
            return 'missing_db';
        }

        return '';
    }
}

if (!function_exists('onoff_dbconfig_render_setup_page')) {
    function onoff_dbconfig_render_setup_page($reason, $connect_errno = 0)
    {
        header('Content-Type: text/html; charset=utf-8');
        http_response_code(503);
        echo '<!DOCTYPE html><html lang="ko"><head><meta charset="utf-8"><title>DB 설정 필요</title>';
        echo '<style>body{font-family:"Malgun Gothic",sans-serif;max-width:40rem;margin:3rem auto;padding:0 1rem;color:#334155;line-height:1.6}';
        echo 'code{background:#f1f5f9;padding:.15rem .4rem;border-radius:4px}ol{padding-left:1.25rem}</style></head><body>';
        echo '<h1>데이터베이스 설정이 필요합니다</h1>';

        if ($reason === 'missing_password' || $reason === 'missing_db' || $reason === 'missing_user') {
            echo '<p><strong>data/dbconfig.local.php</strong> 파일이 있지만 아직 예시 값입니다. 아래를 실제 로컬 DB 정보로 바꿔 주세요.</p>';
            echo '<pre style="background:#f8fafc;padding:1rem;border-radius:8px;font-size:13px">';
            echo "define('G5_MYSQL_HOST', '127.0.0.1');\n";
            echo "define('G5_MYSQL_USER', 'root');\n";
            echo "define('G5_MYSQL_PASSWORD', '실제비밀번호');\n";
            echo "define('G5_MYSQL_DB', '실제DB이름');\n";
            echo '</pre>';
            echo '<ol><li>로컬 MySQL/MariaDB 실행 확인</li><li>DB 생성 후 그누보드 SQL import</li><li>저장 후 브라우저 새로고침</li></ol>';
        } else {
            echo '<p>DB 접속에 실패했습니다. <strong>data/dbconfig.local.php</strong> 계정·비밀번호·DB명을 확인하세요.</p>';
            if ((int) $connect_errno === 1698) {
                echo '<p><strong>Mac/MariaDB 1698</strong>: root가 비밀번호 로그인이 아닐 수 있습니다. 전용 DB 사용자를 만들거나 Homebrew MariaDB 설정을 확인하세요.</p>';
            } elseif ((int) $connect_errno === 1045) {
                echo '<p><strong>1045</strong>: 비밀번호가 틀렸습니다.</p>';
            } elseif ((int) $connect_errno === 1049) {
                echo '<p><strong>1049</strong>: DB가 없습니다. DB를 생성하고 dump를 import 하세요.</p>';
            }
        }

        echo '<p>운영 서버: <code>data/dbconfig.php</code>에 서버 DB 정보 입력 · <code>dbconfig.local.php</code>는 서버 업로드 금지</p>';
        echo '</body></html>';
        exit;
    }
}

$__obb_setup_reason = onoff_dbconfig_needs_setup();
if ($__obb_setup_reason !== '') {
    onoff_dbconfig_render_setup_page($__obb_setup_reason);
}

if (is_file(__DIR__ . '/dbconfig.local.php')) {
    mysqli_report(MYSQLI_REPORT_OFF);
    $__obb_mysqli = mysqli_init();
    $__obb_connected = $__obb_mysqli && @$__obb_mysqli->real_connect(
        G5_MYSQL_HOST,
        G5_MYSQL_USER,
        G5_MYSQL_PASSWORD,
        G5_MYSQL_DB
    );
    if (!$__obb_connected) {
        $__obb_errno = $__obb_mysqli ? (int) $__obb_mysqli->connect_errno : -1;
        onoff_dbconfig_render_setup_page('connect_fail', $__obb_errno);
    }
    if ($__obb_mysqli) {
        $__obb_mysqli->close();
    }
}

define('G5_TABLE_PREFIX', 'g5_');

// 복사 후 임의 32자 이상 문자열로 교체 (기존 운영 DB와 키를 공유하지 마세요)
define('G5_TOKEN_ENCRYPTION_KEY', 'change-me-after-copy-use-random-32chars');

$g5['write_prefix'] = G5_TABLE_PREFIX.'write_'; // 게시판 테이블명 접두사

$g5['auth_table'] = G5_TABLE_PREFIX.'auth'; // 관리권한 설정 테이블
$g5['config_table'] = G5_TABLE_PREFIX.'config'; // 기본환경 설정 테이블
$g5['group_table'] = G5_TABLE_PREFIX.'group'; // 게시판 그룹 테이블
$g5['group_member_table'] = G5_TABLE_PREFIX.'group_member'; // 게시판 그룹+회원 테이블
$g5['board_table'] = G5_TABLE_PREFIX.'board'; // 게시판 설정 테이블
$g5['board_file_table'] = G5_TABLE_PREFIX.'board_file'; // 게시판 첨부파일 테이블
$g5['board_good_table'] = G5_TABLE_PREFIX.'board_good'; // 게시물 추천,비추천 테이블
$g5['board_new_table'] = G5_TABLE_PREFIX.'board_new'; // 게시판 새글 테이블
$g5['login_table'] = G5_TABLE_PREFIX.'login'; // 로그인 테이블 (접속자수)
$g5['mail_table'] = G5_TABLE_PREFIX.'mail'; // 회원메일 테이블
$g5['member_table'] = G5_TABLE_PREFIX.'member'; // 회원 테이블
$g5['member_auto_login_table'] = G5_TABLE_PREFIX.'member_auto_login'; // 자동 로그인 토큰 테이블
$g5['memo_table'] = G5_TABLE_PREFIX.'memo'; // 메모 테이블
$g5['poll_table'] = G5_TABLE_PREFIX.'poll'; // 투표 테이블
$g5['poll_etc_table'] = G5_TABLE_PREFIX.'poll_etc'; // 투표 기타의견 테이블
$g5['point_table'] = G5_TABLE_PREFIX.'point'; // 포인트 테이블
$g5['popular_table'] = G5_TABLE_PREFIX.'popular'; // 인기검색어 테이블
$g5['scrap_table'] = G5_TABLE_PREFIX.'scrap'; // 게시글 스크랩 테이블
$g5['visit_table'] = G5_TABLE_PREFIX.'visit'; // 방문자 테이블
$g5['visit_sum_table'] = G5_TABLE_PREFIX.'visit_sum'; // 방문자 합계 테이블
$g5['uniqid_table'] = G5_TABLE_PREFIX.'uniqid'; // 유니크한 값을 만드는 테이블
$g5['autosave_table'] = G5_TABLE_PREFIX.'autosave'; // 게시글 작성시 일정시간마다 글을 임시 저장하는 테이블
$g5['cert_history_table'] = G5_TABLE_PREFIX.'cert_history'; // 인증내역 테이블
$g5['qa_config_table'] = G5_TABLE_PREFIX.'qa_config'; // 1:1문의 설정테이블
$g5['qa_content_table'] = G5_TABLE_PREFIX.'qa_content'; // 1:1문의 테이블
$g5['content_table'] = G5_TABLE_PREFIX.'content'; // 내용(컨텐츠)정보 테이블
$g5['faq_table'] = G5_TABLE_PREFIX.'faq'; // 자주하시는 질문 테이블
$g5['faq_master_table'] = G5_TABLE_PREFIX.'faq_master'; // 자주하시는 질문 마스터 테이블
$g5['new_win_table'] = G5_TABLE_PREFIX.'new_win'; // 새창 테이블
$g5['menu_table'] = G5_TABLE_PREFIX.'menu'; // 메뉴관리 테이블
$g5['social_profile_table'] = G5_TABLE_PREFIX.'member_social_profiles'; // 소셜 로그인 테이블
$g5['member_cert_history_table'] = G5_TABLE_PREFIX.'member_cert_history'; // 본인인증 변경내역 테이블

define('G5_USE_SHOP', true);

define('G5_SHOP_TABLE_PREFIX', 'g5_shop_');

$g5['g5_shop_default_table'] = G5_SHOP_TABLE_PREFIX.'default'; // 쇼핑몰설정 테이블
$g5['g5_shop_banner_table'] = G5_SHOP_TABLE_PREFIX.'banner'; // 배너 테이블
$g5['g5_shop_cart_table'] = G5_SHOP_TABLE_PREFIX.'cart'; // 장바구니 테이블
$g5['g5_shop_category_table'] = G5_SHOP_TABLE_PREFIX.'category'; // 상품분류 테이블
$g5['g5_shop_event_table'] = G5_SHOP_TABLE_PREFIX.'event'; // 이벤트 테이블
$g5['g5_shop_event_item_table'] = G5_SHOP_TABLE_PREFIX.'event_item'; // 상품, 이벤트 연결 테이블
$g5['g5_shop_item_table'] = G5_SHOP_TABLE_PREFIX.'item'; // 상품 테이블
$g5['g5_shop_item_option_table'] = G5_SHOP_TABLE_PREFIX.'item_option'; // 상품옵션 테이블
$g5['g5_shop_item_use_table'] = G5_SHOP_TABLE_PREFIX.'item_use'; // 상품 사용후기 테이블
$g5['g5_shop_item_qa_table'] = G5_SHOP_TABLE_PREFIX.'item_qa'; // 상품 질문답변 테이블
$g5['g5_shop_item_relation_table'] = G5_SHOP_TABLE_PREFIX.'item_relation'; // 관련 상품 테이블
$g5['g5_shop_order_table'] = G5_SHOP_TABLE_PREFIX.'order'; // 주문서 테이블
$g5['g5_shop_order_delete_table'] = G5_SHOP_TABLE_PREFIX.'order_delete'; // 주문서 삭제 테이블
$g5['g5_shop_wish_table'] = G5_SHOP_TABLE_PREFIX.'wish'; // 보관함(위시리스트) 테이블
$g5['g5_shop_coupon_table'] = G5_SHOP_TABLE_PREFIX.'coupon'; // 쿠폰정보 테이블
$g5['g5_shop_coupon_zone_table'] = G5_SHOP_TABLE_PREFIX.'coupon_zone'; // 쿠폰존 테이블
$g5['g5_shop_coupon_log_table'] = G5_SHOP_TABLE_PREFIX.'coupon_log'; // 쿠폰사용정보 테이블
$g5['g5_shop_sendcost_table'] = G5_SHOP_TABLE_PREFIX.'sendcost'; // 추가배송비 테이블
$g5['g5_shop_personalpay_table'] = G5_SHOP_TABLE_PREFIX.'personalpay'; // 개인결제 정보 테이블
$g5['g5_shop_order_address_table'] = G5_SHOP_TABLE_PREFIX.'order_address'; // 배송지이력 정보 테이블
$g5['g5_shop_item_stocksms_table'] = G5_SHOP_TABLE_PREFIX.'item_stocksms'; // 재입고SMS 알림 정보 테이블
$g5['g5_shop_post_log_table'] = G5_SHOP_TABLE_PREFIX.'order_post_log'; // 주문요청 로그 테이블
$g5['g5_shop_order_data_table'] = G5_SHOP_TABLE_PREFIX.'order_data'; // 모바일 결제정보 임시저장 테이블
$g5['g5_shop_inicis_log_table'] = G5_SHOP_TABLE_PREFIX.'inicis_log'; // 이니시스 모바일 계좌이체 로그 테이블
?>