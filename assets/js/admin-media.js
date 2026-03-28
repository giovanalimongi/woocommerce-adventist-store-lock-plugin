jQuery(function ($) {
  var frame;
  var $imageId = $('#wcasl-modal-image-id');
  var $preview = $('#wcasl-modal-image-preview');

  $('.wcasl-color-field').wpColorPicker();

  function renderPreview(url) {
    if (!url) {
      $preview.removeClass('has-image').html('<span>' + (wcaslAdminMedia.emptyImage || 'No image selected') + '</span>');
      return;
    }

    $preview.addClass('has-image').html('<img src="' + url + '" alt="' + (wcaslAdminMedia.previewText || '') + '">');
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
