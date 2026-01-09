var el = wp.element.createElement,
    registerBlockType = wp.blocks.registerBlockType,
    blockStyle = {};

registerBlockType( 'wp-manga/gutenberg-heading-block', {
    title: 'WP Manga - Heading',

    icon: 'editor-textcolor',

    category: 'wp-manga',

    edit: function() {
        return el( 'div', { style: blockStyle }, '[manga_heading] Your Heading [/manga_heading]' );
    },

    save: function() {
        return el( 'div', { style: blockStyle }, '[manga_heading] Your Heading [/manga_heading]' );
    },
} );