(function () {
  'use strict';

  var stateUrl = '/proc/eottae-push.php?action=state';
  var swUrl = '/eottae-service-worker.js';
  var loginBannerId = 'eottae-app-login-banner';
  var appStyleId = 'eottae-app-context-style';
  var appHomeUrl = '/page/eottae-app-home.php';
  var lastState = null;

  function isAppContext() {
    return !!(
      (window.matchMedia && window.matchMedia('(display-mode: standalone)').matches) ||
      window.navigator.standalone ||
      (document.referrer && document.referrer.indexOf('android-app://') === 0)
    );
  }

  function base64UrlToUint8Array(base64Url) {
    var padding = '='.repeat((4 - (base64Url.length % 4)) % 4);
    var base64 = (base64Url + padding).replace(/-/g, '+').replace(/_/g, '/');
    var rawData = window.atob(base64);
    var outputArray = new Uint8Array(rawData.length);
    for (var i = 0; i < rawData.length; ++i) {
      outputArray[i] = rawData.charCodeAt(i);
    }
    return outputArray;
  }

  function isSiteHome() {
    var path = window.location.pathname || '/';
    return path === '/' || path === '/index.php';
  }

  function injectAppContextStyle() {
    if (document.getElementById(appStyleId)) {
      return;
    }
    var style = document.createElement('style');
    style.id = appStyleId;
    style.textContent = [
      'html.eottae-app-context,html.eottae-app-context body{background:#f6f8fc;}',
      'html.eottae-app-context body{padding-bottom:calc(var(--eottae-bottom-nav-h,64px) + 18px + env(safe-area-inset-bottom,0px));}',
      'html.eottae-app-context #ft,html.eottae-app-context footer.site-footer,html.eottae-app-context .site-footer-wrap,html.eottae-app-context .site-footer,html.eottae-app-context .eottae-legacy-mobile-footer,html.eottae-app-context #device_change{display:none!important;}',
      'html.eottae-app-context #poll,html.eottae-app-context .poll,html.eottae-app-context #visit,html.eottae-app-context .visit,html.eottae-app-context #visit_list,html.eottae-app-context .visit_list{display:none!important;}',
      'html.eottae-app-context #wrapper,html.eottae-app-context #container,html.eottae-app-context #container_wr{min-width:0;max-width:100%;}',
      'html.eottae-app-context .mobile-bottom-nav--global{border-top:0;border-radius:22px 22px 0 0;box-shadow:0 -12px 30px rgba(15,23,42,.12);}'
    ].join('');
    (document.head || document.documentElement).appendChild(style);
  }

  function markAppContext() {
    if (!isAppContext()) {
      return false;
    }
    document.documentElement.classList.add('eottae-app-context');
    if (document.body) {
      document.body.classList.add('eottae-app-context');
    }
    injectAppContextStyle();
    return true;
  }

  function maybeRedirectToAppHome() {
    if (!isAppContext() || !isSiteHome()) {
      return true;
    }
    if (window.location.search && window.location.search.indexOf('web_home=1') !== -1) {
      return true;
    }

    window.location.replace(appHomeUrl);
    return false;
  }

  function fetchState() {
    return fetch(stateUrl, {
      credentials: 'same-origin',
      cache: 'no-store'
    }).then(function (response) {
      return response.ok ? response.json() : null;
    }).catch(function () {
      return null;
    });
  }

  function showLoginBanner(state) {
    if (!isAppContext() || !state || state.logged_in) {
      return;
    }
    if (document.getElementById(loginBannerId)) {
      return;
    }

    var banner = document.createElement('div');
    banner.id = loginBannerId;
    banner.style.cssText = 'position:fixed;left:14px;right:14px;bottom:calc(78px + env(safe-area-inset-bottom,0px));z-index:99999;padding:14px 15px;border-radius:16px;background:#0f172a;color:#fff;box-shadow:0 12px 30px rgba(15,23,42,.24);font:14px/1.45 system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif';
    banner.innerHTML = '<strong style="display:block;margin-bottom:4px">앱 로그인이 필요합니다</strong><span style="display:block;color:#cbd5e1">세부톡 메시지와 생활정보 알림을 받으려면 로그인 상태를 유지해 주세요.</span><a href="' + encodeURI(state.login_url || '/bbs/login.php') + '" style="display:inline-block;margin-top:10px;padding:8px 12px;border-radius:999px;background:#38bdf8;color:#082f49;text-decoration:none;font-weight:700">로그인하기</a>';
    document.body.appendChild(banner);
  }

  function postJson(url, payload) {
    return fetch(url, {
      method: 'POST',
      credentials: 'same-origin',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify(payload || {})
    }).then(function (response) {
      return response.ok ? response.json() : null;
    });
  }

  function subscribePush(state) {
    if (!state || !state.logged_in || !state.enabled || !state.public_key) {
      return;
    }
    if (state.app_only && !isAppContext()) {
      return;
    }
    if (!('serviceWorker' in navigator) || !('PushManager' in window) || !('Notification' in window)) {
      return;
    }

    var swVersion = state.sw_version ? '?ver=' + encodeURIComponent(state.sw_version) : '';
    navigator.serviceWorker.register(swUrl + swVersion, { scope: '/' }).then(function (registration) {
      if (registration.update) {
        registration.update().catch(function () {});
      }
      if (Notification.permission === 'denied') {
        return null;
      }
      var permissionPromise = Notification.permission === 'granted'
        ? Promise.resolve('granted')
        : Notification.requestPermission();

      return permissionPromise.then(function (permission) {
        if (permission !== 'granted') {
          return null;
        }
        return registration.pushManager.getSubscription().then(function (subscription) {
          if (subscription) {
            return subscription;
          }
          return registration.pushManager.subscribe({
            userVisibleOnly: true,
            applicationServerKey: base64UrlToUint8Array(state.public_key)
          });
        });
      });
    }).then(function (subscription) {
      if (!subscription) {
        return null;
      }
      return postJson('/proc/eottae-push.php?action=subscribe', {
        token: state.token,
        subscription: subscription.toJSON()
      });
    }).catch(function () {
      return null;
    });
  }

  function bindServiceWorkerMessages() {
    if (!('serviceWorker' in navigator) || bindServiceWorkerMessages.bound) {
      return;
    }
    bindServiceWorkerMessages.bound = true;
    navigator.serviceWorker.addEventListener('message', function (event) {
      var data = event && event.data ? event.data : {};
      if (data.type === 'EOTTAE_PUSH_RESUBSCRIBE') {
        fetchState().then(function (state) {
          if (state && state.success) {
            lastState = state;
            subscribePush(state);
          }
        });
      }
    });
  }

  function refreshPushSubscription() {
    if (!lastState || !lastState.logged_in || !lastState.enabled) {
      return;
    }
    subscribePush(lastState);
  }

  function init() {
    markAppContext();
    if (!maybeRedirectToAppHome()) {
      return;
    }

    bindServiceWorkerMessages();
    fetchState().then(function (state) {
      if (!state || !state.success) {
        return;
      }
      lastState = state;
      showLoginBanner(state);
      subscribePush(state);
    });

    if ('serviceWorker' in navigator) {
      navigator.serviceWorker.ready.then(function (registration) {
        if (registration && registration.update) {
          registration.update().catch(function () {});
        }
      }).catch(function () {});
      document.addEventListener('visibilitychange', function () {
        if (document.visibilityState === 'visible') {
          refreshPushSubscription();
        }
      });
      window.addEventListener('online', refreshPushSubscription);
    }
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
