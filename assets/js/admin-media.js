jQuery(function ($) {
  var frame;
  var $imageId = $('#wcasl-modal-image-id');
  var $preview = $('#wcasl-modal-image-preview');

  function renderPreview(url) {
    if (!url) {
      $preview.empty();
      return;
    }

    $preview.html(
      '<img src="' + url + '" alt="' + (wcaslAdminMedia.previewText || '') + '" style="max-width:240px;height:auto;display:block;border:1px solid #dcdcde;padding:4px;background:#fff;">'
    );
  }

  $('#wcasl-select-image').on('click', function (e) {
    e.preventDefault();

    if (frame) {
      frame.open();
      return;
    }

    frame = wp.media({
      title: wcaslAdminMedia.title || 'Select image',
      button: {
        text: wcaslAdminMedia.button || 'Use this image'
      },
      multiple: false,
      library: {
        type: 'image'
      }
    });

    frame.on('select', function () {
      var attachment = frame.state().get('selection').first().toJSON();
      $imageId.val(attachment.id || '');
      renderPreview((attachment.sizes && attachment.sizes.medium && attachment.sizes.medium.url) || attachment.url || '');
    });

    frame.open();
  });

  $('#wcasl-remove-image').on('click', function (e) {
    e.preventDefault();
    $imageId.val('');
    renderPreview('');
  });
});
