var el = wp.element.createElement,
    registerBlockType = wp.blocks.registerBlockType,
    blockStyle = {};

registerBlockType( 'wp-manga/gutenberg-manga-sliders-block', {
    title: 'WP Manga - Post Slider',

    icon: 'image-flip-horizontal',

    category: 'wp-manga',

    edit: function() {
        return el( 'div', { style: blockStyle }, '[manga_post_slider]' );
    },

    save: function() {
        return el( 'div', { style: blockStyle }, '[manga_post_slider]' );
    },
} );