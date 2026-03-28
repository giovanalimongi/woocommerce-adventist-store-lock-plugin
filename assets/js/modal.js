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
});
