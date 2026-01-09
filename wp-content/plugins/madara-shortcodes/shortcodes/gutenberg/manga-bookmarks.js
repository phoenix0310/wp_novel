var el = wp.element.createElement,
    registerBlockType = wp.blocks.registerBlockType,
    blockStyle = {};

registerBlockType( 'wp-manga/gutenberg-bookmarks-block', {
    title: 'WP Manga - My Bookmarks',

    icon: 'thumbs-up',

    category: 'wp-manga',

    edit: function() {
        return el( 'div', { style: blockStyle }, '[wp-manga-my-bookmarks column="2" style="1"]' );
    },

    save: function() {
        return el( 'div', { style: blockStyle }, '[wp-manga-my-bookmarks column="2" style="1"]' );
    },
} );