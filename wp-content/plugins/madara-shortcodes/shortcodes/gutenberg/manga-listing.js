var el = wp.element.createElement,
    registerBlockType = wp.blocks.registerBlockType,
    blockStyle = {};

registerBlockType( 'wp-manga/gutenberg-manga-listing-block', {
    title: 'WP Manga - Manga Listing',

    icon: 'list-view',

    category: 'wp-manga',

    edit: function() {
        return el( 'div', { style: blockStyle }, '[manga_listing]' );
    },

    save: function() {
        return el( 'div', { style: blockStyle }, '[manga_listing]' );
    },
} );