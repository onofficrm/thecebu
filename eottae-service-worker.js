self.addEventListener('push', function (event) {
  event.waitUntil(
    fetch('/proc/eottae-push.php?action=latest', {
      credentials: 'same-origin',
      cache: 'no-store'
    })
      .then(function (response) {
        return response.ok ? response.json() : null;
      })
      .then(function (data) {
        if (!data || !data.success || !data.has_notification) {
          return null;
        }

        return self.registration.showNotification(data.title || '세부어때 알림', {
          body: data.body || '새 알림을 확인해 주세요.',
          icon: data.icon || '/img/logo/android-chrome-192x192.png',
          badge: data.badge || '/img/logo/favicon-32x32.png',
          data: {
            url: data.url || '/page/eottae-notifications.php'
          },
          tag: 'thecebu-notification',
          renotify: true
        });
      })
      .catch(function () {
        return null;
      })
  );
});

self.addEventListener('notificationclick', function (event) {
  event.notification.close();
  var url = (event.notification.data && event.notification.data.url) || '/page/eottae-notifications.php';
  event.waitUntil(
    clients.matchAll({ type: 'window', includeUncontrolled: true }).then(function (clientList) {
      for (var i = 0; i < clientList.length; i++) {
        var client = clientList[i];
        if ('focus' in client) {
          client.navigate(url);
          return client.focus();
        }
      }
      return clients.openWindow(url);
    })
  );
});
