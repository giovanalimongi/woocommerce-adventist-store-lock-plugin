document.addEventListener('DOMContentLoaded', function () {
  var overlay = document.getElementById('wcasl-modal-overlay');
  var closeBtn = document.getElementById('wcasl-modal-close');
  var actionBtn = document.getElementById('wcasl-modal-button');

  if (!overlay || typeof wcaslModal === 'undefined') {
    return;
  }

  if (wcaslModal.blockPageInteraction) {
    document.body.classList.add('wcasl-no-scroll');
  }

  function closeModal() {
    overlay.style.display = 'none';
    document.body.classList.remove('wcasl-no-scroll');
  }

  if (wcaslModal.showCloseButton) {
    if (closeBtn) {
      closeBtn.addEventListener('click', closeModal);
    }
    if (actionBtn) {
      actionBtn.addEventListener('click', closeModal);
    }
  } else if (actionBtn) {
    actionBtn.addEventListener('click', function (e) {
      e.preventDefault();
    });
  }

  if (wcaslModal.showCountdown) {
    var hoursEl = document.getElementById('wcasl-countdown-hours');
    var minutesEl = document.getElementById('wcasl-countdown-minutes');
    var secondsEl = document.getElementById('wcasl-countdown-seconds');
    var targetTime = parseInt(wcaslModal.unlockAt || 0, 10) * 1000;

    function pad(value) {
      return String(value).padStart(2, '0');
    }

    function updateCountdown() {
      if (!hoursEl || !minutesEl || !secondsEl || !targetTime) {
        return;
      }

      var now = Date.now();
      var diff = Math.max(0, Math.floor((targetTime - now) / 1000));
      var hours = Math.floor(diff / 3600);
      var minutes = Math.floor((diff % 3600) / 60);
      var seconds = diff % 60;

      hoursEl.textContent = pad(hours);
      minutesEl.textContent = pad(minutes);
      secondsEl.textContent = pad(seconds);

      if (diff <= 0) {
        clearInterval(timer);
        var label = document.querySelector('.wcasl-countdown-label');
        if (label && wcaslModal.countdownExpired) {
          label.textContent = wcaslModal.countdownExpired;
        }
      }
    }

    updateCountdown();
    var timer = setInterval(updateCountdown, 1000);
  }
});
