<?php
include_once(dirname(__FILE__).'/_init.php');

if (!function_exists('g5site_cfg')) {
    if (is_file(G5_PATH . '/_site.config.php')) {
        include_once G5_PATH . '/_site.config.php';
    }
}

$g5_contact_tel_display = function_exists('g5site_cfg') ? g5site_cfg('phone', '010-5227-8040') : '010-5227-8040';
$g5_contact_email       = function_exists('g5site_cfg') ? g5site_cfg('email', 'jong8040@gmail.com') : 'jong8040@gmail.com';
$g5_contact_address     = function_exists('g5site_cfg') ? g5site_cfg('address', 'Banilad Townhouse One Paseo St. Banilad Cebu City') : 'Banilad Townhouse One Paseo St. Banilad Cebu City';
$g5_contact_tel_digits  = preg_replace('/[^0-9+]/', '', $g5_contact_tel_display);
$g5_contact_tel_link    = 'tel:'.$g5_contact_tel_digits;

g5_page_start('문의하기');
?>
<div class="page-template page-contact">
  <header class="page-hero reveal">
    <div class="page-inner">
      <p class="page-eyebrow">Contact</p>
      <h1 class="page-title">문의하기</h1>
      <p class="page-desc">전화·이메일·온라인 문의 중 편한 방법으로 연락해 주세요. 영업일 기준 24시간 내 회신합니다.</p>
    </div>
  </header>

  <section class="page-section reveal">
    <div class="page-inner">
      <h2 class="page-section__title">연락처</h2>
      <dl class="page-contact-dl">
        <div class="page-contact-dl__row">
          <dt>전화</dt>
          <dd><a href="<?php echo $g5_contact_tel_link; ?>"><?php echo get_text($g5_contact_tel_display); ?></a></dd>
        </div>
        <div class="page-contact-dl__row">
          <dt>이메일</dt>
          <dd><a href="mailto:<?php echo get_text($g5_contact_email); ?>"><?php echo get_text($g5_contact_email); ?></a></dd>
        </div>
        <div class="page-contact-dl__row">
          <dt>주소</dt>
          <dd><?php echo get_text($g5_contact_address); ?></dd>
        </div>
        <div class="page-contact-dl__row">
          <dt>운영시간</dt>
          <dd>평일 09:00 – 18:00 (주말·공휴일 휴무)</dd>
        </div>
      </dl>
    </div>
  </section>

  <section class="page-section page-section--alt reveal">
    <div class="page-inner">
      <h2 class="page-section__title">문의 전 안내</h2>
      <ul class="page-list">
        <li>문의 종류 (앱·웹 이용, 업체 등록, 광고, 제휴 등)</li>
        <li>관련 게시글·페이지 URL (있을 경우)</li>
        <li>연락 가능한 시간대</li>
      </ul>
      <p class="page-section__desc">상세 문의는 1:1 Q&amp;A 게시판을 이용하셔도 됩니다.</p>
      <a href="<?php echo G5_BBS_URL; ?>/qalist.php" class="btn btn-outline">Q&amp;A 게시판</a>
    </div>
  </section>

  <section class="page-section page-cta page-cta--dark reveal">
    <div class="page-inner page-cta__inner">
      <h2 class="page-cta__title">지금 바로 상담받기</h2>
      <p class="page-cta__desc">빠른 상담을 원하시면 전화 또는 온라인 문의를 이용해 주세요.</p>
      <div class="page-cta__actions">
        <a href="<?php echo $g5_contact_tel_link; ?>" class="btn btn-primary">
          <i class="fa fa-phone" aria-hidden="true"></i> <?php echo get_text($g5_contact_tel_display); ?>
        </a>
        <button type="button" class="btn btn-outline consult-modal-open" data-target="#pageConsultModal">온라인 문의</button>
      </div>
    </div>
  </section>
</div>

<div id="pageConsultModal" class="consult-modal" aria-hidden="true" role="dialog" aria-labelledby="pageConsultModalTitle">
  <div class="consult-modal-overlay"></div>
  <div class="consult-modal__panel">
    <h3 id="pageConsultModalTitle" class="consult-modal__title">온라인 문의</h3>
    <p class="consult-modal__desc">아래 게시판에서 문의를 남겨 주시면 담당자가 연락드립니다.</p>
    <div class="consult-modal__actions">
      <a href="<?php echo G5_BBS_URL; ?>/qalist.php" class="btn btn-primary">Q&amp;A 작성</a>
      <button type="button" class="btn btn-outline consult-modal-close">닫기</button>
    </div>
  </div>
</div>
<?php
g5_page_end();
