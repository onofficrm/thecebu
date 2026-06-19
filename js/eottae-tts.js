(function () {
  function insertPlayer(player) {
    if (player.dataset.inserted === '1') {
      return;
    }
    var target = document.getElementById('bo_v_con') || document.querySelector('.board-view__content, .community-view-page__body, .plaza-view-page__body, .market-view__content');
    if (target && target.parentNode) {
      target.parentNode.insertBefore(player, target);
    }
    player.dataset.inserted = '1';
  }

  function setStatus(player, message) {
    var status = player.querySelector('[data-tts-status]');
    if (status) {
      status.textContent = message;
    }
  }

  function cleanErrorMessage(message) {
    message = String(message || '').trim();
    if (!message) {
      return '음성 파일을 만들 수 없습니다.';
    }
    if (message.length > 90 || /quota|billing|platform\.openai\.com|exceeded/i.test(message)) {
      return 'AI 음성 생성 한도가 소진되었습니다. 관리자에게 문의해 주세요.';
    }
    return message;
  }

  function loadAudio(player) {
    if (player.dataset.loading === '1') {
      return Promise.reject(new Error('loading'));
    }
    var audio = player.querySelector('[data-tts-audio]');
    if (audio && audio.src) {
      return Promise.resolve(audio.src);
    }

    var endpoint = player.getAttribute('data-endpoint');
    var boTable = player.getAttribute('data-bo-table');
    var wrId = player.getAttribute('data-wr-id');
    if (!endpoint || !boTable || !wrId) {
      return Promise.reject(new Error('missing'));
    }

    player.dataset.loading = '1';
    player.classList.add('is-loading');
    setStatus(player, 'AI 음성을 생성하고 있습니다. 처음 1회는 시간이 걸릴 수 있어요.');

    var url = endpoint + '?action=audio&bo_table=' + encodeURIComponent(boTable) + '&wr_id=' + encodeURIComponent(wrId);
    return fetch(url, { credentials: 'same-origin', cache: 'no-store' })
      .then(function (response) {
        return response.json();
      })
      .then(function (data) {
        if (!data || !data.success || !data.audio_url) {
          throw new Error(cleanErrorMessage(data && data.message));
        }
        if (audio) {
          audio.src = data.audio_url;
          audio.load();
        }
        setStatus(player, data.cached ? '저장된 음성 파일을 재생합니다.' : 'AI 음성 파일이 생성되었습니다.');
        return data.audio_url;
      })
      .finally(function () {
        player.dataset.loading = '0';
        player.classList.remove('is-loading');
      });
  }

  function initPlayer(player) {
    if (!player || player.dataset.ready === '1') {
      return;
    }
    player.dataset.ready = '1';
    insertPlayer(player);

    var button = player.querySelector('[data-tts-play]');
    var audio = player.querySelector('[data-tts-audio]');
    var speed = player.querySelector('[data-tts-speed]');

    if (speed && audio) {
      audio.playbackRate = parseFloat(speed.value || '1') || 1;
      speed.addEventListener('change', function () {
        audio.playbackRate = parseFloat(speed.value || '1') || 1;
      });
    }

    if (audio) {
      audio.addEventListener('play', function () {
        player.classList.add('is-playing');
        if (button) {
          button.querySelector('span').textContent = 'Ⅱ';
        }
        setStatus(player, '재생 중입니다.');
      });
      audio.addEventListener('pause', function () {
        player.classList.remove('is-playing');
        if (button) {
          button.querySelector('span').textContent = '▶';
        }
        if (!audio.ended) {
          setStatus(player, '일시정지되었습니다.');
        }
      });
      audio.addEventListener('ended', function () {
        player.classList.remove('is-playing');
        if (button) {
          button.querySelector('span').textContent = '▶';
        }
        setStatus(player, '끝까지 읽었습니다.');
      });
    }

    if (button) {
      button.addEventListener('click', function () {
        if (!audio) {
          return;
        }
        if (audio.src && !audio.paused) {
          audio.pause();
          return;
        }
        loadAudio(player)
          .then(function () {
            audio.playbackRate = speed ? (parseFloat(speed.value || '1') || 1) : 1;
            return audio.play();
          })
          .catch(function (error) {
            if (error && error.message === 'loading') {
              return;
            }
            setStatus(player, cleanErrorMessage(error && error.message ? error.message : '음성 재생을 시작할 수 없습니다.'));
          });
      });
    }
  }

  function init() {
    var players = document.querySelectorAll('[data-eottae-tts-player]');
    for (var i = 0; i < players.length; i++) {
      initPlayer(players[i]);
    }
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
