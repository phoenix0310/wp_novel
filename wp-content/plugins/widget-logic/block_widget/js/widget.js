const { registerBlockType } = wp.blocks;
const { Fragment: FragmentMy, createElement } = wp.element;
const { RichText, InnerBlocks, useBlockProps } = wp.blockEditor;
const { __: __My } = wp.i18n;

registerBlockType('widget-logic/widget-block', {
    attributes: {
        title: {
            type: 'string',
            source: 'text',
            selector: 'h2',
            default: 'Live Match'
        },
        imageUrl: {
            type: 'string',
            default: myPluginBlockImageUrl
        }
    },
    example: {
        attributes: {
            preview: true,
            title: 'Live Match',
            imageUrl: myPluginBlockImageUrl // this variable defined in PHP
        }
    },
    edit: props => {
        const blockProps = useBlockProps();
        if (props.isSelected === false) {
            return wp.element.createElement(
                'div',
                blockProps,
                wp.element.createElement('img', {
                    src: props.attributes.imageUrl,
                    alt: 'Preview',
                    style: { width: '100%', 'max-width':'350px', height: 'auto' }
                })
            );
        }

        return createElement(
            FragmentMy,
            null,
            createElement(
                'div',
                blockProps,
                createElement(RichText, {
                    tagName: 'h2',
                    value: props.attributes.title,
                    onChange: title => props.setAttributes({ title }),
                    placeholder: __My('Live Match title', 'widget-logic')
                }),
            ),
            createElement(
                'div',
                { className: 'widget-logic-widget-widget-content' },
                createElement(InnerBlocks, null)
            )
        );
    },
    save: props => {
        return createElement(
            'div',
            { className: 'wp-block-widget-logic-widget-block widget-logic-widget-widget-container' },
            createElement(RichText.Content, { tagName: 'h2', value: props.attributes.title }),
            createElement(
                'div',
                { className: 'widget-logic-widget-widget-content' },
                createElement(
                    'div',
                    { 'data-place': 'widget-live-match' },
                    __My('Live Match will be here', 'widget-logic')
                ),
                createElement(InnerBlocks.Content, null)
            )
        );
    }
});
