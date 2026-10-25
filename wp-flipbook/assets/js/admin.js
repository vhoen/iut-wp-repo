(function ($) {
  'use strict';

  function openMediaFrame($field) {
    var frame = wp.media({
      title: (wpFlipbookAdmin && wpFlipbookAdmin.i18n.selectImage) || 'Select image',
      button: {
        text: (wpFlipbookAdmin && wpFlipbookAdmin.i18n.useImage) || 'Use image'
      },
      multiple: false
    });

    frame.on('select', function () {
      var attachment = frame.state().get('selection').first().toJSON();
      var url = (attachment.sizes && attachment.sizes.medium)
        ? attachment.sizes.medium.url
        : attachment.url;

      if (attachment.sizes && attachment.sizes.thumbnail && $field.hasClass('wp-flipbook-page-image')) {
        url = attachment.sizes.thumbnail.url;
      }

      $field.find('.wp-flipbook-image-id').val(attachment.id);
      $field.find('.wp-flipbook-image-preview').html(
        $('<img />', { src: url, alt: '' })
      );
    });

    frame.open();
  }

  function reindexPages() {
    $('#wp-flipbook-pages .wp-flipbook-page-row').each(function (index) {
      $(this).find('[name^="flipbook_pages"]').each(function () {
        var name = $(this).attr('name');
        if (!name) {
          return;
        }
        $(this).attr(
          'name',
          name.replace(/flipbook_pages\[[^\]]+\]/, 'flipbook_pages[' + index + ']')
        );
      });
    });
    $('#wp-flipbook-pages').attr(
      'data-next-index',
      $('#wp-flipbook-pages .wp-flipbook-page-row').length
    );
  }

  $(function () {
    var $pages = $('#wp-flipbook-pages');

    if ($pages.length && $.fn.sortable) {
      $pages.sortable({
        handle: '.wp-flipbook-page-handle',
        items: '.wp-flipbook-page-row',
        update: reindexPages
      });
    }

    $(document).on('click', '.wp-flipbook-select-image', function (e) {
      e.preventDefault();
      openMediaFrame($(this).closest('.wp-flipbook-image-field'));
    });

    $(document).on('click', '.wp-flipbook-remove-image', function (e) {
      e.preventDefault();
      var $field = $(this).closest('.wp-flipbook-image-field');
      $field.find('.wp-flipbook-image-id').val('0');
      $field.find('.wp-flipbook-image-preview').empty();
    });

    $('#wp-flipbook-add-page').on('click', function (e) {
      e.preventDefault();
      var template = $('#wp-flipbook-page-template').html();
      if (!template) {
        return;
      }
      var index = parseInt($pages.attr('data-next-index'), 10) || 0;
      var html = template.replace(/__INDEX__/g, String(index));
      $pages.append(html);
      $pages.attr('data-next-index', index + 1);
      reindexPages();
    });

    $(document).on('click', '.wp-flipbook-remove-page', function (e) {
      e.preventDefault();
      var $rows = $pages.find('.wp-flipbook-page-row');
      if ($rows.length <= 1) {
        var $row = $(this).closest('.wp-flipbook-page-row');
        $row.find('input[type="text"], textarea').val('');
        $row.find('.wp-flipbook-image-id').val('0');
        $row.find('.wp-flipbook-image-preview').empty();
        return;
      }
      $(this).closest('.wp-flipbook-page-row').remove();
      reindexPages();
    });
  });
})(jQuery);
