var el = wp.element.createElement,
    registerBlockType = wp.blocks.registerBlockType,
    blockStyle = {};

registerBlockType( 'wp-manga/gutenberg-manga-grid-block', {
    title: 'WP Manga - Manga Grid',

    icon: 'list-view',

    category: 'wp-manga',

    edit: function() {
        return el( 'div', { style: blockStyle }, '[manga_grid]' );
    },

    save: function() {
        return el( 'div', { style: blockStyle }, '[manga_grid]' );
    },
} );