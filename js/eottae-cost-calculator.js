(function () {
  'use strict';

  var root = document.querySelector('[data-cost-calculator]');
  if (!root) {
    return;
  }

  var KRW_RATE = 24;
  var PRESETS = {
    single: {
      rent: 25000,
      utilities: 6500,
      food: 18000,
      transport: 4500,
      mobile: 2500,
      visa: 3500,
      education: 0,
      health: 2500,
      leisure: 9000,
      buffer: 8000
    },
    couple: {
      rent: 38000,
      utilities: 9000,
      food: 32000,
      transport: 8000,
      mobile: 4500,
      visa: 7000,
      education: 0,
      health: 5000,
      leisure: 15000,
      buffer: 12000
    },
    family: {
      rent: 65000,
      utilities: 16000,
      food: 52000,
      transport: 14000,
      mobile: 7000,
      visa: 12000,
      education: 45000,
      health: 10000,
      leisure: 22000,
      buffer: 25000
    },
    nomad: {
      rent: 32000,
      utilities: 8000,
      food: 22000,
      transport: 5500,
      mobile: 4500,
      visa: 4500,
      education: 0,
      health: 3500,
      leisure: 14000,
      buffer: 12000
    }
  };

  var LABELS = {
    rent: '월세·숙소',
    utilities: '공과금',
    food: '식비',
    transport: '교통',
    mobile: '통신',
    visa: '비자',
    education: '교육',
    health: '병원·보험',
    leisure: '여가',
    buffer: '예비비'
  };

  function money(value) {
    var num = Math.max(0, parseInt(value, 10) || 0);
    return '₱' + String(num).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
  }

  function krw(value) {
    var num = Math.max(0, parseInt(value, 10) || 0) * KRW_RATE;
    return '약 ' + String(num).replace(/\B(?=(\d{3})+(?!\d))/g, ',') + '원';
  }

  function fields() {
    return root.querySelectorAll('[data-cost-field]');
  }

  function getValues() {
    var values = {};
    fields().forEach(function (input) {
      var key = input.getAttribute('data-cost-field');
      values[key] = Math.max(0, parseInt(input.value, 10) || 0);
    });
    return values;
  }

  function sum(values) {
    return Object.keys(values).reduce(function (total, key) {
      return total + (parseInt(values[key], 10) || 0);
    }, 0);
  }

  function levelText(total) {
    if (total < 60000) {
      return '절약형 생활비입니다. 숙소 위치와 외식 빈도에 따라 체감 차이가 큽니다.';
    }
    if (total < 120000) {
      return '일반적인 장기체류 예산에 가깝습니다. 월세와 식비가 핵심 변수입니다.';
    }
    if (total < 220000) {
      return '여유 있는 생활비입니다. 가족 체류나 콘도 생활을 고려한 예산입니다.';
    }
    return '프리미엄 또는 가족형 예산입니다. 학교·차량·의료비 조건을 꼭 별도로 확인하세요.';
  }

  function renderBreakdown(values, total) {
    var target = root.querySelector('[data-cost-breakdown]');
    if (!target) {
      return;
    }
    var rows = Object.keys(LABELS).map(function (key) {
      var value = values[key] || 0;
      var percent = total > 0 ? Math.round((value / total) * 100) : 0;
      return '<div><dt>' + LABELS[key] + '</dt><dd>' + money(value) + ' · ' + percent + '%</dd></div>';
    });
    target.innerHTML = rows.join('');
  }

  function calculate() {
    var values = getValues();
    var total = sum(values);
    var totalEl = root.querySelector('[data-cost-total]');
    var krwEl = root.querySelector('[data-cost-total-krw]');
    var levelEl = root.querySelector('[data-cost-level]');
    var barEl = root.querySelector('[data-cost-bar]');

    if (totalEl) totalEl.textContent = money(total);
    if (krwEl) krwEl.textContent = krw(total);
    if (levelEl) levelEl.textContent = levelText(total);
    if (barEl) barEl.style.width = Math.max(8, Math.min(100, Math.round((total / 250000) * 100))) + '%';
    renderBreakdown(values, total);
  }

  function applyPreset(key) {
    var preset = PRESETS[key];
    if (!preset) {
      return;
    }
    fields().forEach(function (input) {
      var field = input.getAttribute('data-cost-field');
      if (typeof preset[field] !== 'undefined') {
        input.value = preset[field];
      }
    });
    root.querySelectorAll('[data-cost-preset]').forEach(function (button) {
      button.classList.toggle('is-active', button.getAttribute('data-cost-preset') === key);
    });
    calculate();
  }

  fields().forEach(function (input) {
    input.addEventListener('input', calculate);
    input.addEventListener('change', calculate);
  });

  root.querySelectorAll('[data-cost-preset]').forEach(function (button) {
    button.addEventListener('click', function () {
      applyPreset(button.getAttribute('data-cost-preset'));
    });
  });

  applyPreset('single');
}());
