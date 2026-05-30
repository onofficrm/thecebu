/**
 * 홈(빌더/React) — 푸터에 서비스 안내 열 보장 · PHP 푸터와 중복 React 푸터 숨김
 */
(function (global) {
  'use strict';

  var SERVICE_COL_ID = 'eottae-footer-service-col';
  var PATCHED_ATTR = 'data-eottae-footer-patched';

  function cfg() {
    return global.__EOTTae_HOME_FOOTER__ || {};
  }

  function esc(value) {
    return String(value == null ? '' : value)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;');
  }

  function hideReactFooter() {
    var root = document.getElementById('root');
    if (!root) {
      return;
    }

    var footers = root.querySelectorAll('footer');
    var i;
    for (i = 0; i < footers.length; i += 1) {
      footers[i].setAttribute('hidden', 'hidden');
      footers[i].style.display = 'none';
    }
  }

  function findHeadingCol(root, label) {
    var headings = root.querySelectorAll('h2, h3, h4, p, span');
    var i;
    for (i = 0; i < headings.length; i += 1) {
      if ((headings[i].textContent || '').trim() !== label) {
        continue;
      }
      var col = headings[i].closest('nav, [class*="footer"], [class*="Footer"]');
      if (!col) {
        col = headings[i].parentElement;
      }
      if (col) {
        return col;
      }
    }
    return null;
  }

  function buildServiceCol() {
    var data = cfg();
    var col = document.createElement('nav');
    col.className = 'eottae-gnb-footer__col eottae-gnb-footer__col--service';
    col.id = SERVICE_COL_ID;
    col.setAttribute('aria-label', '서비스 안내');
    col.innerHTML =
      '<h3 class="eottae-gnb-footer__heading">서비스 안내</h3>'
      + '<ul class="eottae-gnb-footer__links">'
      + '<li class="eottae-gnb-footer__subheading">[이용 안내]</li>'
      + '<li><a href="' + esc(data.talk_url || '/talk/ai.php') + '">세부톡 AI 도우미</a></li>'
      + '<li><a href="' + esc(data.coupon_guide_url || '/page/eottae-coupon-guide.php') + '">쿠폰사용방법</a></li>'
      + '<li><a href="' + esc(data.challenge_guide_url || '/page/eottae-challenge-guide.php') + '">챌린지 참여 안내</a></li>'
      + '<li><a href="' + esc(data.member_growth_guide_url || '/page/eottae-member-growth-guide.php') + '">활동 등급·뱃지 안내</a></li>'
      + '<li><a href="' + esc(data.badge_book_url || '/badges/') + '">뱃지 도감</a></li>'
      + '<li><a href="' + esc(data.cost_calculator_url || '/cost-calculator/') + '">세부 생활비 계산기</a></li>'
      + '<li class="eottae-gnb-footer__subheading eottae-gnb-footer__subheading--spaced">[참여·제휴 안내]</li>'
      + '<li><a href="' + esc(data.business_coupon_guide_url || '/page/eottae-business-coupon-guide.php') + '">쿠폰발행방법</a></li>'
      + '<li><a href="' + esc(data.columnist_recruit_url || '/columnist/') + '">컬럼리스트 모집</a></li>'
      + '<li><a href="' + esc(data.briefing_url || '/briefing/') + '">오늘의 세부 브리핑</a></li>'
      + '</ul>';
    return col;
  }

  function patchReactFooter() {
    if (document.getElementById(SERVICE_COL_ID) || document.querySelector('.eottae-gnb-footer__col--service')) {
      return true;
    }

    var root = document.getElementById('root');
    if (!root) {
      return false;
    }

    var supportCol = findHeadingCol(root, '고객지원');
    var shortcutCol = findHeadingCol(root, '바로가기');
    if (!supportCol || !shortcutCol || supportCol.getAttribute(PATCHED_ATTR) === '1') {
      return false;
    }

    var serviceCol = buildServiceCol();
    serviceCol.setAttribute(PATCHED_ATTR, '1');
    if (supportCol.parentNode === shortcutCol.parentNode) {
      supportCol.parentNode.insertBefore(serviceCol, supportCol);
    } else {
      supportCol.parentNode.insertBefore(serviceCol, supportCol);
    }
    supportCol.setAttribute(PATCHED_ATTR, '1');
    return true;
  }

  function run() {
    if (document.getElementById('ft') && document.querySelector('.eottae-gnb-footer__col--service')) {
      hideReactFooter();
      return;
    }

    if (patchReactFooter()) {
      return;
    }

    hideReactFooter();
  }

  function schedule() {
    run();
    var attempts = 0;
    var timer = global.setInterval(function () {
      attempts += 1;
      run();
      if (attempts >= 24 || document.querySelector('.eottae-gnb-footer__col--service, #' + SERVICE_COL_ID)) {
        global.clearInterval(timer);
      }
    }, 500);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', schedule);
  } else {
    schedule();
  }
}(window));
