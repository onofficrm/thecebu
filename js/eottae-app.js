(function () {
  'use strict';

  var stateUrl = '/proc/eottae-push.php?action=state';
  var swUrl = '/eottae-service-worker.js';
  var loginBannerId = 'eottae-app-login-banner';
  var appHomeUrl = '/page/eottae-app-home.php';

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

    navigator.serviceWorker.register(swUrl).then(function (registration) {
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

  function init() {
    if (!maybeRedirectToAppHome()) {
      return;
    }

    fetchState().then(function (state) {
      if (!state || !state.success) {
        return;
      }
      showLoginBanner(state);
      subscribePush(state);
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
