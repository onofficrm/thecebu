/**
 * TWA/앱 환경에서 geolocation 위임 지연·실패 대응 (재시도 + 옵션 완화)
 */
(function (global) {
  'use strict';

  function isInstalledApp() {
    return (
      (global.matchMedia && global.matchMedia('(display-mode: standalone)').matches) ||
      global.navigator.standalone === true ||
      (global.document.referrer && global.document.referrer.indexOf('android-app://') === 0)
    );
  }

  function permissionMessage(error) {
    if (!global.isSecureContext && global.location.protocol !== 'https:' && global.location.hostname !== 'localhost') {
      return '현재 위치 검색은 HTTPS 보안 연결에서만 사용할 수 있습니다.';
    }
    if (error && error.code === 1) {
      if (isInstalledApp()) {
        return '앱 위치 권한이 필요합니다. 설정 → 앱 → 세부어때 → 권한 → 위치를 허용한 뒤, 버튼을 다시 눌러 주세요.';
      }
      return '브라우저 위치 권한이 차단되었습니다. 주소창의 위치 권한을 허용한 뒤 다시 시도해 주세요.';
    }
    if (error && error.code === 2) {
      return '현재 위치를 확인하지 못했습니다. Wi-Fi/GPS를 켠 뒤 다시 시도해 주세요.';
    }
    if (error && error.code === 3) {
      if (isInstalledApp()) {
        return '위치 확인 시간이 초과되었습니다. 잠시 후 다시 눌러 주세요. (앱 첫 실행 시 위치 권한 팝업이 늦게 뜰 수 있습니다)';
      }
      return '현재 위치 확인 시간이 초과되었습니다. 잠시 후 다시 시도해 주세요.';
    }
    return '현재 위치를 확인하지 못했습니다. 위치 권한을 확인해 주세요.';
  }

  function getCurrentPosition(options) {
    var opts = options || {};
    var retries = typeof opts.retries === 'number' ? opts.retries : (isInstalledApp() ? 4 : 2);
    var retryDelay = typeof opts.retryDelay === 'number' ? opts.retryDelay : (isInstalledApp() ? 900 : 400);
    var geoProfiles = opts.geoProfiles || [
      { enableHighAccuracy: false, timeout: 20000, maximumAge: 120000 },
      { enableHighAccuracy: true, timeout: 25000, maximumAge: 60000 },
      { enableHighAccuracy: true, timeout: 30000, maximumAge: 0 }
    ];

    return new Promise(function (resolve, reject) {
      if (!global.navigator || !global.navigator.geolocation) {
        reject({ code: 0, message: 'unsupported' });
        return;
      }

      var attempt = 0;

      function run() {
        var profile = geoProfiles[Math.min(attempt, geoProfiles.length - 1)];
        global.navigator.geolocation.getCurrentPosition(
          function (pos) {
            resolve(pos);
          },
          function (err) {
            attempt += 1;
            if (attempt < retries) {
              global.setTimeout(run, retryDelay);
              return;
            }
            reject(err || { code: 0, message: 'unknown' });
          },
          profile
        );
      }

      run();
    });
  }

  global.eottaeGeolocation = {
    isInstalledApp: isInstalledApp,
    permissionMessage: permissionMessage,
    getCurrentPosition: getCurrentPosition
  };
})(typeof window !== 'undefined' ? window : this);
