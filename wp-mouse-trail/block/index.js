(function (wp) {
  'use strict';

  var registerBlockType = wp.blocks.registerBlockType;
  var el = wp.element.createElement;
  var Fragment = wp.element.Fragment;
  var InspectorControls = wp.blockEditor.InspectorControls;
  var InnerBlocks = wp.blockEditor.InnerBlocks;
  var useBlockProps = wp.blockEditor.useBlockProps;
  var PanelBody = wp.components.PanelBody;
  var RangeControl = wp.components.RangeControl;
  var ColorPicker = wp.components.ColorPicker;
  var BaseControl = wp.components.BaseControl;
  var __ = wp.i18n.__;

  registerBlockType('wp-mouse-trail/zone', {
    edit: function (props) {
      var attributes = props.attributes;
      var setAttributes = props.setAttributes;
      var duration = typeof attributes.duration === 'number' ? attributes.duration : 850;
      var width = typeof attributes.width === 'number' ? attributes.width : 36;
      var color = typeof attributes.color === 'string' && attributes.color ? attributes.color : '#ffffff';
      var blockProps = useBlockProps({ className: 'wp-mouse-trail-block-editor' });

      return el(
        Fragment,
        null,
        el(
          InspectorControls,
          null,
          el(
            PanelBody,
            { title: __('Mouse Trail', 'wp-mouse-trail'), initialOpen: true },
            el(RangeControl, {
              label: __('Trail duration (ms)', 'wp-mouse-trail'),
              value: duration,
              min: 100,
              max: 3000,
              step: 50,
              onChange: function (value) {
                setAttributes({ duration: value || 850 });
              }
            }),
            el(RangeControl, {
              label: __('Trail width (px)', 'wp-mouse-trail'),
              value: width,
              min: 2,
              max: 80,
              step: 1,
              onChange: function (value) {
                setAttributes({ width: value || 36 });
              }
            }),
            el(
              BaseControl,
              { label: __('Trail color', 'wp-mouse-trail'), id: 'wp-mouse-trail-color' },
              el(ColorPicker, {
                color: color,
                enableAlpha: false,
                defaultValue: '#ffffff',
                onChange: function (value) {
                  setAttributes({ color: value || '#ffffff' });
                }
              })
            )
          )
        ),
        el(
          'div',
          blockProps,
          el(InnerBlocks, {
            template: [['core/paragraph', { placeholder: __('Add content inside the trail zone…', 'wp-mouse-trail') }]],
            templateLock: false
          })
        )
      );
    },
    save: function () {
      // Persist InnerBlocks HTML for the PHP render_callback $content argument.
      return el(InnerBlocks.Content);
    }
  });
})(window.wp);
