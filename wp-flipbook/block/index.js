(function (wp) {
  'use strict';

  var registerBlockType = wp.blocks.registerBlockType;
  var el = wp.element.createElement;
  var Fragment = wp.element.Fragment;
  var InspectorControls = wp.blockEditor.InspectorControls;
  var useBlockProps = wp.blockEditor.useBlockProps;
  var PanelBody = wp.components.PanelBody;
  var SelectControl = wp.components.SelectControl;
  var Placeholder = wp.components.Placeholder;
  var ServerSideRender = wp.serverSideRender;
  var __ = wp.i18n.__;

  function getOptions() {
    if (window.wpFlipbookBlock && Array.isArray(window.wpFlipbookBlock.options)) {
      return window.wpFlipbookBlock.options;
    }
    return [{ label: __('— Select a flipbook —', 'wp-flipbook'), value: 0 }];
  }

  registerBlockType('wp-flipbook/book', {
    edit: function (props) {
      var attributes = props.attributes;
      var setAttributes = props.setAttributes;
      var flipbookId = attributes.flipbookId || 0;
      var blockProps = useBlockProps({ className: 'wp-flipbook-block-editor' });
      var options = getOptions().map(function (opt) {
        return {
          label: opt.label,
          value: String(opt.value)
        };
      });

      return el(
        Fragment,
        null,
        el(
          InspectorControls,
          null,
          el(
            PanelBody,
            { title: __('Flipbook', 'wp-flipbook'), initialOpen: true },
            el(SelectControl, {
              label: __('Book', 'wp-flipbook'),
              value: String(flipbookId),
              options: options,
              onChange: function (value) {
                setAttributes({ flipbookId: parseInt(value, 10) || 0 });
              }
            })
          )
        ),
        el(
          'div',
          blockProps,
          flipbookId
            ? el(ServerSideRender, {
                block: 'wp-flipbook/book',
                attributes: { flipbookId: flipbookId }
              })
            : el(Placeholder, {
                icon: 'book-alt',
                label: __('Flipbook', 'wp-flipbook'),
                instructions: __(
                  'Select a flipbook in the block settings sidebar.',
                  'wp-flipbook'
                )
              })
        )
      );
    },
    save: function () {
      return null;
    }
  });
})(window.wp);
