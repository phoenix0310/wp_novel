var el = wp.element.createElement,
    registerBlockType = wp.blocks.registerBlockType,
    blockStyle = {};
	
registerBlockType( 'wp-manga/gutenberg-manga-chapters-block', {
    title: 'WP Manga - Manga Chapters',

    icon: 'list-view',

    category: 'wp-manga',

    edit: function() {
        return el( 'div', { style: blockStyle }, '[manga_chapters id=""]' );
    },

    save: function() {
        return el( 'div', { style: blockStyle }, '[manga_chapters id=""]' );
    },
} );