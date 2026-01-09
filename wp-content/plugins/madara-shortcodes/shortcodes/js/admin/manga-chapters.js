// JavaScript Document
(function () {
	tinymce.PluginManager.add('manga_chapters', function (editor, url) {
		editor.addButton('manga_chapters', {
			text: 'Manga Chapters',
			id: 'madara_shortcode_manga_chapters',
			tooltip: 'WP Manga - Chapters',
			onclick: function () {
				// Open window
				var body = [

					{
						type: 'textbox',
						name: 'id',
						label: 'Manga ID',
						tooltip: 'Enter ID of manga to display'
					},

					{
						type: 'listbox',
						name: 'order',
						label: 'Chapter Oder',
						'values': [
							{text: 'Ascending', value: 'ASC'},
							{text: 'Descending', value: 'DESC'},
						],
					}
				];

				editor.windowManager.open({
					title: 'Madara Manga Chapters',
					body: body,
					onsubmit: function (e) {

						var id = e.data.id;
						if (id != '') {
							id = 'id="' + id + '"';
						}

						var order = e.data.order;
						if (order != '') {
							order = 'order="' + order + '"';
						}

						editor.insertContent('[manga_chapters ' + id + ' ' + order + ']');
					}
				});
			}
		});
	});
})();
