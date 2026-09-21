<?php
/**
 * 세부어때 개인정보처리방침
 * URL: https://thecebu.co.kr/page/privacy.php
 */
include_once(dirname(__FILE__).'/_init.php');

if (!function_exists('g5site_cfg')) {
    if (is_file(G5_PATH . '/_site.config.php')) {
        include_once G5_PATH . '/_site.config.php';
    }
}

$privacy_site_name = function_exists('g5site_cfg') ? g5site_cfg('site_name', '세부어때') : '세부어때';
$privacy_company   = function_exists('g5site_cfg') ? g5site_cfg('company_name', $privacy_site_name) : $privacy_site_name;
$privacy_email     = function_exists('g5site_cfg') ? g5site_cfg('email', 'help@thecebu.co.kr') : 'help@thecebu.co.kr';
$privacy_phone     = function_exists('g5site_cfg') ? g5site_cfg('phone', '') : '';
$privacy_address   = function_exists('g5site_cfg') ? g5site_cfg('address', '') : '';
$privacy_manager   = function_exists('g5site_cfg') ? g5site_cfg('privacy_manager', '') : '';
if ($privacy_manager === '') {
    $privacy_manager = function_exists('g5site_cfg') ? g5site_cfg('ceo_name', '') : '';
}
if ($privacy_manager === '') {
    $privacy_manager = $privacy_company.' 운영팀';
}

$page_title       = '개인정보처리방침';
$page_description = $privacy_site_name.' 앱과 웹사이트의 개인정보 수집·이용·보관 및 이용자 권리에 관한 안내입니다.';
$page_robots      = 'index,follow';

g5_page_start('개인정보처리방침');
?>
<div class="page-template page-privacy">
    <header class="page-hero reveal">
        <div class="page-inner">
            <p class="page-eyebrow">Privacy</p>
            <h1 class="page-title">개인정보처리방침</h1>
            <p class="page-desc">
                <?php echo htmlspecialchars($privacy_company, ENT_QUOTES, 'UTF-8'); ?>(이하 &quot;회사&quot;)는
                세부어때 안드로이드 앱과 웹사이트(https://thecebu.co.kr)를 운영하며,
                「개인정보 보호법」과 「위치정보의 보호 및 이용 등에 관한 법률」을 준수합니다.
            </p>
            <p class="page-section__desc">시행일: 2026년 9월 21일</p>
        </div>
    </header>

    <article class="page-section page-privacy__body reveal">
        <div class="page-inner">
            <section class="page-privacy__block">
                <h2 class="page-section__title">1. 서비스</h2>
                <p>세부어때는 필리핀 세부 교민, 사업자, 관광객을 위한 위치 기반 생활정보 커뮤니티입니다. 커뮤니티, 내주변 업체, 중고장터, 구인구직, 부동산, 지도, AI 도우미를 제공합니다.</p>
                <ul class="page-list">
                    <li>앱 이름: 세부어때</li>
                    <li>앱 패키지: kr.co.thecebu.app</li>
                    <li>웹사이트: <a href="https://thecebu.co.kr/">https://thecebu.co.kr/</a></li>
                </ul>
            </section>

            <section class="page-privacy__block">
                <h2 class="page-section__title">2. 수집하는 개인정보</h2>
                <ul class="page-list">
                    <li><strong>회원가입:</strong> 아이디, 비밀번호, 닉네임, 이메일, 회원 유형(일반인·사업자). 휴대폰 번호를 입력한 경우 그 번호. 구글 계정으로 가입하면 구글이 제공하는 이메일, 이름, 계정 식별자.</li>
                    <li><strong>글·거래 등록:</strong> 게시글, 사진, 연락처, 이메일, 카카오톡 ID, 업체·매물·근무지 주소와 지도 좌표. 이용자가 글에 직접 적은 내용이 포함됩니다.</li>
                    <li><strong>위치정보:</strong> 내주변, 지도에서 현재 위치를 허용하면 기기 위치(위도·경도)를 주변 업체와 글을 보여주는 데 사용합니다. 위치는 해당 기능을 쓸 때만 확인하고, 프로필에 상시 저장하지 않습니다. 글을 등록할 때 이용자가 지정한 장소의 좌표는 그 글과 함께 저장됩니다.</li>
                    <li><strong>문의:</strong> 이름, 연락처, 이메일, 문의 내용.</li>
                    <li><strong>알림:</strong> 알림을 허용하면 푸시 구독 주소, 회원 아이디, 기기·브라우저 정보가 저장됩니다. 허용하지 않으면 수집하지 않습니다.</li>
                    <li><strong>자동 수집:</strong> 접속 IP, 쿠키, 접속 일시, 서비스 이용 기록, 기기 종류, 운영체제, 앱 또는 브라우저 버전.</li>
                </ul>
                <p>연락처, 사진첩, 마이크, 카메라, 결제 정보는 앱 운영을 위해 상시 수집하지 않습니다. 사진이나 연락처를 글에 직접 넣는 경우에만 그 내용이 저장됩니다. 앱 안 결제는 사용하지 않습니다.</p>
            </section>

            <section class="page-privacy__block">
                <h2 class="page-section__title">3. 이용 목적</h2>
                <ul class="page-list">
                    <li>회원 식별, 로그인, 커뮤니티와 거래 글 제공</li>
                    <li>현재 위치 기준 내주변 정보와 지도 표시</li>
                    <li>문의 회신, 알림 발송, 부정 이용 방지</li>
                    <li>AI 도우미의 답변과 글 작성 보조</li>
                    <li>서비스 오류 확인과 법령상 의무 이행</li>
                </ul>
            </section>

            <section class="page-privacy__block">
                <h2 class="page-section__title">4. 보유 기간</h2>
                <ul class="page-list">
                    <li>회원정보: 탈퇴 시까지. 부정 이용 방지가 필요하면 아이디를 제한된 기간 보관할 수 있습니다.</li>
                    <li>게시글과 거기에 포함된 연락처·위치: 이용자가 삭제하거나 탈퇴로 지워질 때까지. 다른 회원이 거래·분쟁을 위해 보관한 내용은 별도로 남을 수 있습니다.</li>
                    <li>문의 기록: 처리 완료 후 3년</li>
                    <li>접속 로그: 3개월</li>
                    <li>푸시 구독 정보: 알림을 끄거나 구독이 만료될 때까지</li>
                </ul>
                <p>전자상거래 등 관계 법령에 보존 의무가 있으면 그 기간 동안 보관합니다.</p>
            </section>

            <section class="page-privacy__block">
                <h2 class="page-section__title">5. 제3자 제공과 처리 위탁</h2>
                <p>회사는 이용자의 개인정보를 팔지 않습니다. 아래 업무를 위해 필요한 범위에서 처리가 이전될 수 있습니다.</p>
                <ul class="page-list">
                    <li><strong>Google:</strong> 지도 표시, 구글 로그인, 앱 배포. 지도를 열거나 현재 위치를 쓰면 위치와 지도 요청이 Google에 전달됩니다.</li>
                    <li><strong>AI 제공자:</strong> AI 도우미나 글 자동 작성을 쓰면 이용자가 입력한 문장과 글 초안이 답변 생성을 위해 OpenAI 등 AI 제공자에게 전송될 수 있습니다. 광고 목적의 프로필 판매에는 사용하지 않습니다.</li>
                    <li><strong>호스팅·알림:</strong> 서버 보관, 이메일, 웹 푸시 발송.</li>
                </ul>
                <p>글에 올린 연락처와 위치는 서비스를 보는 다른 이용자에게 공개됩니다. 공개를 원하지 않는 연락처는 글에 넣지 마세요.</p>
            </section>

            <section class="page-privacy__block">
                <h2 class="page-section__title">6. 위치정보의 보호</h2>
                <p>현재 위치는 내주변·지도 기능을 사용할 때 기기에서 확인하고, 주변 결과를 보여준 뒤 회원 프로필로 계속 보관하지 않습니다. 기기 설정 또는 앱의 사이트 설정에서 위치 권한을 끌 수 있으며, 그 경우 내주변 기능이 제한됩니다. 글에 저장한 장소 좌표는 해당 글을 삭제하면 함께 지웁니다.</p>
            </section>

            <section class="page-privacy__block">
                <h2 class="page-section__title">7. 이용자의 권리</h2>
                <p>이용자는 자신의 개인정보를 열람·수정·삭제하거나 처리정지를 요청할 수 있습니다. 회원은 앱 또는 웹사이트의 회원정보에서 직접 수정·탈퇴할 수 있고, 본인이 쓴 글을 삭제할 수 있습니다. 요청은 아래 연락처로 하면 지체 없이 조치합니다.</p>
                <p>계정 삭제 절차 안내: <a href="<?php echo G5_URL; ?>/page/account-deletion.php">계정 및 데이터 삭제</a></p>
            </section>

            <section class="page-privacy__block">
                <h2 class="page-section__title">8. 파기와 안전조치</h2>
                <p>보유 기간이 끝나거나 목적이 달성된 개인정보는 지체 없이 삭제합니다. 비밀번호는 암호화해 저장하고, 개인정보에 접근할 수 있는 사람을 최소한으로 둡니다.</p>
            </section>

            <section class="page-privacy__block">
                <h2 class="page-section__title">9. 쿠키</h2>
                <p>로그인 유지와 이용 환경 저장을 위해 쿠키를 사용합니다. 브라우저에서 쿠키를 거부할 수 있으며, 거부하면 로그인이 유지되지 않을 수 있습니다.</p>
            </section>

            <section class="page-privacy__block">
                <h2 class="page-section__title">10. 개인정보 보호책임자</h2>
                <ul class="page-list">
                    <li><strong>서비스:</strong> <?php echo htmlspecialchars($privacy_company, ENT_QUOTES, 'UTF-8'); ?></li>
                    <li><strong>책임자:</strong> <?php echo htmlspecialchars($privacy_manager, ENT_QUOTES, 'UTF-8'); ?></li>
                    <?php if ($privacy_phone !== '') { ?>
                    <li><strong>전화:</strong> <?php echo htmlspecialchars($privacy_phone, ENT_QUOTES, 'UTF-8'); ?></li>
                    <?php } ?>
                    <li><strong>이메일:</strong> <a href="mailto:<?php echo htmlspecialchars($privacy_email, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($privacy_email, ENT_QUOTES, 'UTF-8'); ?></a></li>
                    <?php if ($privacy_address !== '') { ?>
                    <li><strong>주소:</strong> <?php echo htmlspecialchars($privacy_address, ENT_QUOTES, 'UTF-8'); ?></li>
                    <?php } ?>
                </ul>
            </section>

            <section class="page-privacy__block">
                <h2 class="page-section__title">11. 권익침해 구제</h2>
                <ul class="page-list">
                    <li>개인정보분쟁조정위원회: 1833-6972, www.kopico.go.kr</li>
                    <li>개인정보침해신고센터: 118, privacy.kisa.or.kr</li>
                    <li>대검찰청: 1301, www.spo.go.kr</li>
                    <li>경찰청: 182, ecrm.cyber.go.kr</li>
                </ul>
            </section>

            <section class="page-privacy__block">
                <h2 class="page-section__title">12. 방침 변경</h2>
                <p>이 방침을 바꾸면 시행 7일 전부터 이 페이지에 안내합니다. 이용자 권리에 중요한 변경은 30일 전에 안내합니다.</p>
            </section>
        </div>
    </article>
</div>
<?php
g5_page_end();
