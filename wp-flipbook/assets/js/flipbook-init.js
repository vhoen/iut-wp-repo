(function ($) {
  'use strict';

  function initFlipbook($el) {
    if (!$el.length || $el.data('turn')) {
      return;
    }

    var width = parseInt($el.attr('data-width'), 10) || 400;
    var height = parseInt($el.attr('data-height'), 10) || 300;

    $el.css({ width: width, height: height });

    $el.turn({
      width: width,
      height: height,
      autoCenter: true,
      gradients: true,
      acceleration: true
    });
  }

  function initAll(context) {
    $(context || document)
      .find('.wp-flipbook .flipbook')
      .addBack('.wp-flipbook .flipbook')
      .each(function () {
        initFlipbook($(this));
      });
  }

  $(function () {
    initAll(document);
  });

  // Re-init after Gutenberg server-side preview / dynamic block updates.
  if (window.MutationObserver) {
    var observer = new MutationObserver(function (mutations) {
      mutations.forEach(function (mutation) {
        mutation.addedNodes.forEach(function (node) {
          if (node.nodeType === 1) {
            initAll(node);
          }
        });
      });
    });
    observer.observe(document.body, { childList: true, subtree: true });
  }
})(jQuery);
