var el = wp.element.createElement,
    registerBlockType = wp.blocks.registerBlockType,
    blockStyle = {};
	
registerBlockType( 'wp-manga/gutenberg-manga-info-block', {
    title: 'WP Manga - Manga Info',

    icon: 'list-view',

    category: 'wp-manga',

    edit: function() {
        return el( 'div', { style: blockStyle }, '[manga_info id=""]' );
    },

    save: function() {
        return el( 'div', { style: blockStyle }, '[manga_info id=""]' );
    },
} );