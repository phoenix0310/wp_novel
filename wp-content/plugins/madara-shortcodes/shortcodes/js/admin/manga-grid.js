// JavaScript Document
(function () {
	tinymce.PluginManager.add('manga_grid', function (editor, url) {
		editor.addButton('manga_listing', {
			text: 'Manga Grid',
			id: 'madara_shortcode_manga_grid',
			tooltip: 'Madara Manga Grid',
			onclick: function () {
				// Open window
				var body = [
				
					{
						type: 'listbox',
						name: 'chapter_type',
						label: 'Manga Chapter Type',
						'values': [
							{text: 'All', value: 'all'},
							{text: 'Image', value: 'manga'},
							{text: 'Text', value: 'text'},
							{text: 'Video', value: 'video'},
						],
					},

					{
						type: 'listbox',
						name: 'orderby',
						label: 'Oder By',
						'values': [
							{text: 'Latest', value: 'latest'},
							{text: 'Most viewed', value: 'view'},
							{text: 'Most commented', value: 'comment'},
							{text: 'Title', value: 'title'},
							{text: 'Input(only available when using ids parameter)', value: 'input'},
							{text: 'Random', value: 'random'},
						],
						tooltip: 'condition to query items'
					},

					{
						type: 'listbox',
						name: 'order',
						label: 'Oder',
						'values': [
							{text: 'Descending', value: 'DESC'},
							{text: 'Ascending', value: 'ASC'},
						],
					},

					{
						type: 'textbox',
						name: 'genres',
						label: 'Genres',
						tooltip: 'List of Manga Genres slug to query items from, separated by a comma.'
					},

					{
						type: 'textbox',
						name: 'tags',
						label: 'Tags',
						tooltip: 'List of Manga Tags slug to query items from, separated by a comma.'
					},

					{
						type: 'textbox',
						name: 'ids',
						label: 'IDs',
						tooltip: 'list of post IDs to query, separated by a comma. If this value is not empty, cats, tags and featured are omitted'
					},

				];

				editor.windowManager.open({
					title: 'Madara Manga Grid',
					body: body,
					onsubmit: function (e) {

						var chapter_type = e.data.chapter_type;
						if (chapter_type != '') {
							chapter_type = 'chapter_type="' + chapter_type + '"';
						}

						var orderby = e.data.orderby;
						if (orderby != '') {
							orderby = 'orderby="' + orderby + '"';
						}


						var order = e.data.order;
						if (order != '') {
							order = 'order="' + order + '"';
						}

						var genres = e.data.genres;
						if (genres != '') {
							genres = 'genres="' + genres + '"';
						}

						var tags = e.data.tags;
						if (tags != '') {
							tags = 'tags="' + tags + '"';
						}

						var ids = e.data.ids;
						if (ids != '') {
							ids = 'ids="' + ids + '"';
						}

						editor.insertContent('[manga_grid ' + chapter_type + ' ' + orderby + ' ' + order + ' ' + tags + ' ' + genres + ' ' + ids + ']');
					}
				});
			}
		});
	});
})();
