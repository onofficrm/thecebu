self.addEventListener('install', function (event) {
  event.waitUntil(self.skipWaiting());
});

self.addEventListener('activate', function (event) {
  event.waitUntil(self.clients.claim());
});

var DEFAULT_NOTIFICATION = {
  success: true,
  has_notification: true,
  title: '세부어때 알림',
  body: '새 알림을 확인해 주세요.',
  url: '/page/eottae-notifications.php',
  icon: '/img/logo/android-chrome-192x192.png',
  badge: '/img/logo/favicon-32x32.png'
};

function absoluteUrl(url) {
  try {
    return new URL(url || DEFAULT_NOTIFICATION.url, self.location.origin).href;
  } catch (error) {
    return new URL(DEFAULT_NOTIFICATION.url, self.location.origin).href;
  }
}

function pushPayload(event) {
  if (!event.data) {
    return null;
  }
  try {
    return event.data.json();
  } catch (jsonError) {
    try {
      var text = event.data.text();
      return text ? { title: '세부어때 알림', body: text } : null;
    } catch (textError) {
      return null;
    }
  }
}

function fetchLatestNotification() {
  var controller = typeof AbortController !== 'undefined' ? new AbortController() : null;
  var timer = controller ? setTimeout(function () {
    controller.abort();
  }, 2800) : null;

  return fetch('/proc/eottae-push.php?action=latest&from=sw', {
    credentials: 'include',
    cache: 'no-store',
    signal: controller ? controller.signal : undefined
  })
    .then(function (response) {
      return response.ok ? response.json() : null;
    })
    .catch(function () {
      return null;
    })
    .then(function (data) {
      if (timer) {
        clearTimeout(timer);
      }
      return data;
    });
}

function showAppNotification(data, forceFallback) {
  data = data && data.success !== false ? data : null;
  if (!data || (!data.has_notification && !forceFallback)) {
    return null;
  }

  var notification = Object.assign({}, DEFAULT_NOTIFICATION, data || {});
  var url = absoluteUrl(notification.url);
  var tag = notification.tag || ('thecebu-' + (notification.type || 'notification'));

  return self.registration.showNotification(notification.title || DEFAULT_NOTIFICATION.title, {
    body: notification.body || DEFAULT_NOTIFICATION.body,
    icon: notification.icon || DEFAULT_NOTIFICATION.icon,
    badge: notification.badge || DEFAULT_NOTIFICATION.badge,
    image: notification.image || undefined,
    tag: tag,
    renotify: true,
    requireInteraction: !!notification.requireInteraction,
    timestamp: Date.now(),
    data: {
      url: url,
      receivedAt: Date.now(),
      type: notification.type || 'notification'
    },
    actions: [
      { action: 'open', title: '확인하기' },
      { action: 'close', title: '닫기' }
    ]
  });
}

self.addEventListener('push', function (event) {
  var directPayload = pushPayload(event);
  event.waitUntil(
    Promise.resolve(directPayload)
      .then(function (payload) {
        if (payload && (payload.title || payload.body || payload.has_notification)) {
          return showAppNotification(payload, true);
        }
        return fetchLatestNotification().then(function (data) {
          return showAppNotification(data, true);
        });
      })
      .catch(function () {
        return showAppNotification(DEFAULT_NOTIFICATION, true);
      })
  );
});

self.addEventListener('notificationclick', function (event) {
  event.notification.close();
  if (event.action === 'close') {
    return;
  }
  var url = absoluteUrl(event.notification.data && event.notification.data.url);
  event.waitUntil(
    clients.matchAll({ type: 'window', includeUncontrolled: true }).then(function (clientList) {
      for (var i = 0; i < clientList.length; i++) {
        var client = clientList[i];
        if ('focus' in client && client.url && new URL(client.url).origin === self.location.origin) {
          if ('navigate' in client) {
            return client.navigate(url).then(function (navigatedClient) {
              return navigatedClient ? navigatedClient.focus() : client.focus();
            });
          }
          return client.focus();
        }
      }
      return clients.openWindow(url);
    })
  );
});

self.addEventListener('pushsubscriptionchange', function (event) {
  event.waitUntil(
    self.clients.matchAll({ type: 'window', includeUncontrolled: true }).then(function (clientList) {
      for (var i = 0; i < clientList.length; i++) {
        if (clientList[i] && clientList[i].postMessage) {
          clientList[i].postMessage({ type: 'EOTTAE_PUSH_RESUBSCRIBE' });
        }
      }
    })
  );
});
