jQuery(document).ready(function ($) {
    let loading = false;
    let noMoreNextChapters = false;
    let noMorePrevChapters = false;
    let lastScrollTop = 0;

    // Function to check if scrolled to the bottom of the last chapter
    function isScrolledToBottom(element) {
        return $(window).scrollTop() + $(window).height() >= element.offset().top + element.outerHeight() + 100;
    }

    // Function to check if scrolled to the top of the first chapter
    function isScrolledToTop(element) {
        return $(window).scrollTop() <= element.offset().top;
    }

    // Function to load chapters via AJAX
    function loadChapter(mangaId, chapterId, direction) {
        if($('#chapter-' + chapterId).length){
            return;
        }

        if (loading) return;
        loading = true;

        $.ajax({
            url: madara.ajaxurl,
            type: 'POST',
            data: {
                action: 'load_chapter', 
                manga_id: mangaId,
                chapter_id: chapterId
            },
            success: function (response) {
                var chapter_name = '';
                $('.page-content-listing .wp-manga-chapter').each((idx, obj) => {
                    if ($(obj).data('chapter-id') == chapterId) {
                        chapter_name = $(obj).find('a').text();
                    }
                });

                const newChapterBlock = `<div id="chapter-${chapterId}" class="reading-content" data-block-chapter-id="${chapterId}"><h3 class="chapter-name">${chapter_name}</h3>${response}</div>`;

                if (direction === 'next') {
                    $('.read-container').append(newChapterBlock);
                } else if (direction === 'prev') {
                    $('.read-container').prepend(newChapterBlock);

                    const newChapterHeight = $(`#chapter-${chapterId}`).outerHeight();
                    $(window).scrollTop($(window).scrollTop() + newChapterHeight); // Keep scroll position
                }

                if($('.read-container .premium-block').length){
                    $('.read-container .premium-block a').unbind('click', wp_manga_premium_block_click);
                    $('.read-container .premium-block a').on('click', wp_manga_premium_block_click);
                }

                var nextURL = $('#chapter-url-' + chapterId).val();
                window.history.pushState({}, chapter_name, nextURL);     
                
                if (typeof nextURL !== 'undefined' && typeof updateHistory !== 'undefined') {
                    user_history_params.chapter
                    updateHistory(1);
                }
            },
            complete: function () {
                loading = false;
            }
        });
    }

    function getSlugFromURL(url){
        // trim the last / if exists
        if(url.endsWith('/')){
            url = url.slice(0, -1);
        }
        var urlObj = new URL(url);
        return urlObj.pathname.split('/').pop();
    }

    // to support old version of Chapter Coin
    if(typeof wp_manga_premium_block_click === 'undefined'){
        window.wp_manga_premium_block_click = function (evt) {
            if (!window.wp_manga_chapter_coin_just_add_premium) {
                // update the chapter id
                $('#frm-wp-manga-buy-coin input[name=wp-manga-chapter]').val($(evt.target).closest('.reading-content').data('block-chapter-id'));

                var cl = $(evt.target).closest('.premium-block').attr('class');

                if (typeof cl !== 'undefined') {
                    var matches = cl.match(/coin-\d+/g);
                    if (matches) {
                        var coin = matches[0].replace('coin-', '');
                        $('#frm-wp-manga-buy-coin .message-sufficient .coin').html(coin);

                        var user_balance = $('#wp_manga_chapter_coin_user_balance').length > 0 ? $('#wp_manga_chapter_coin_user_balance').val() : 0;

                        if (parseInt(user_balance) < parseInt(coin)) {
                            $('#frm-wp-manga-buy-coin .message-lack-of-coin').removeClass('hidden');
                            $('#frm-wp-manga-buy-coin .message-sufficient').addClass('hidden');
                            $('#frm-wp-manga-buy-coin .btn-agree').hide();
                            $('#frm-wp-manga-buy-coin .btn-buycoin').show();
                        } else {

                            $('#frm-wp-manga-buy-coin .message-lack-of-coin').addClass('hidden');
                            $('#frm-wp-manga-buy-coin .message-sufficient').removeClass('hidden');
                            $('#frm-wp-manga-buy-coin .btn-agree').show();
                            $('#frm-wp-manga-buy-coin .btn-buycoin').hide();
                        }
                    }

                    matches = cl.match(/data-chapter-\d+/g);
                    if (matches) {
                        var chapter_id = matches[0].replace('data-chapter-', '');
                        $('#frm-wp-manga-buy-coin input[name="wp-manga-chapter"]').val(chapter_id);
                    }

                    $('#frm-wp-manga-buy-coin').modal();
                }
            } else {
                window.wp_manga_chapter_coin_just_add_premium = false;
            }

            evt.stopPropagation();
            evt.preventDefault();
            return false;
        };
    }

    // Scroll down event: Load next chapter when scrolled to the bottom
    function handleScrollDown() {
        // hide sticky panels when scrolling down
        if ($(window).scrollTop() > 500) {
            $('.reading-sticky-menu').removeClass('active');
        }
        $('.sidebar-tools').addClass('hidden');
        
        if ($('.reading-content-wrap').hasClass('disabled-load-next-chapter')) {
            return;
        }

        const lastReadingContentBlock = $('.reading-content:last');
        const currChapter = $('.chapters-list').find('.wp-manga-chapter.reading');
        const nextChapter = $('.page-content-listing').hasClass('order-asc') ? currChapter.next('.wp-manga-chapter') : currChapter.prev('.wp-manga-chapter');
        const nextChapterId = nextChapter.length ? nextChapter.data('chapter-id') : null;
        const mangaId = $('.chapters-list .page-content-listing').data('manga-id');

        // Check if scrolled to the bottom of the current last chapter block
        if (!noMoreNextChapters && isScrolledToBottom(lastReadingContentBlock)) {
            // Ensure nextChapterId is valid and not undefined
            if (nextChapterId) {
                loadChapter(mangaId, nextChapterId, 'next');
            } else {
                noMoreNextChapters = true; // Mark as no more chapters if no valid nextChapterId
                $('.read-container').append('<h3 class="last-chap">' + madara_novelhub.msg_last_chap + '</h3>');
            }
        }
    }

    // Scroll up event: Load previous chapter when scrolled to the top
    function handleScrollUp() {
        // Show sticky menu when scrolling up and scroll position > 500
        if ($(window).scrollTop() > 500) {
            $('.reading-sticky-menu').addClass('active');
        } else {
            $('.reading-sticky-menu').removeClass('active');
        }
        $('.sidebar-tools').removeClass('hidden');

        if ($('.reading-content-wrap').hasClass('disabled-load-prev-chapter')) {
            return;
        }

        const firstReadingContentBlock = $('.reading-content:first');
        const currChapter = $('.chapters-list').find('.wp-manga-chapter.reading');
        const prevChapter = $('.page-content-listing').hasClass('order-asc') ? currChapter.prev('.wp-manga-chapter') : currChapter.next('.wp-manga-chapter');
        const prevChapterId = prevChapter.length ? prevChapter.data('chapter-id') : null;
        const mangaId = $('.chapters-list .page-content-listing').data('manga-id');

        if (!noMorePrevChapters && isScrolledToTop(firstReadingContentBlock)) {
            if (prevChapterId) {
                loadChapter(mangaId, prevChapterId, 'prev');
            } else {
                noMorePrevChapters = true;
                console.log('No more previous chapters.');
            }
        }
    }

    function checkVisibleChapterContent(){
        var chapters = $('.reading-content');
        for(var i = 0; i < chapters.length; i++){
            var chapter = chapters[i];
            if($(chapter).visible(true)){
                var id = $(chapter).data('block-chapter-id');
                $('.read-container').find('.reading-content.current').removeClass('current');
                $('.chapters-list').find('.wp-manga-chapter.reading').removeClass('reading');

                $('#chapter-' + id).addClass('current');
                $('.chapters-list').find('.wp-manga-chapter[data-chapter-id=' + id + ']').addClass('reading');
                
                var current_chapter_name = $('#chapter-' + id).find('.chapter-name').text();
                $('.reading-sticky-menu .current-chapter h3').text(current_chapter_name);

                var nextURL = $('#chapter-url-' + id).val();
                var chapterSlug = getSlugFromURL(nextURL);

                if($('#btn_chapter_report').length){
                    $('#btn_chapter_report a').attr('data-chapter', chapterSlug);
                }

                if(nextURL != location.href){
                    window.history.pushState({}, current_chapter_name, nextURL);  
                }
                
                break;
            }
        }
    }

    /**
     * Support WP Manga Chapter Report plugin
     * @param {*} errortype 
     * @param {*} content 
     */
    var wp_manga_chapter_report_submit_override = function( errortype = '', content = ''){
        if(!wp_manga_reporting){
            wp_manga_reporting = true;
            $('#frm-wp-manga-report .button-primary').addClass('disabled');
            
            
            data = {
                manga: $('#btn_flag_chapter').attr('data-manga'), 
                chapter: $('#btn_flag_chapter').attr('data-chapter'), 
                action: 'chapter_flag',
                errortype: errortype,
                content: content,
                nonce: $('#btn_flag_chapter').attr('data-nonce')
            };
            
            $.ajax({
                type : 'POST',
                url : manga.ajax_url,
                data : data,
                success : function( response ){
                    obj = JSON.parse(response);
                    wp_manga_reporting = false;
                    $('#frm-wp-manga-report .button-primary').removeClass('disabled');
                    alert(obj.message);
                    $('#frm-wp-manga-report').modal('hide');
                },
                error : function(err){
                    wp_manga_reporting = false;
                    $('#frm-wp-manga-report .button-primary').removeClass('disabled');
                    alert(err);
                }
            });
        }
    }

    setTimeout(function(){
        if($('#btn_chapter_report').length){
            $(document).off('click', '#frm-wp-manga-report .btn-submit').on('click', '#frm-wp-manga-report .btn-submit', function(){
                var content = $('#frm-wp-manga-report textarea[name=wp-manga-report-description]').val();
                var errortype = $('#frm-wp-manga-report select[name=wp-manga-report-errortype]').val();
                
                var valid = true;
                if($('#frm-wp-manga-report select[name=wp-manga-report-errortype]').hasClass('required') && errortype == 0) {
                    $('#frm-wp-manga-report select[name=wp-manga-report-errortype]').addClass('missing');
                    valid = false;
                }
                
                if(content.trim() == '') {
                    $('#frm-wp-manga-report textarea[name=wp-manga-report-description]').addClass('missing');
                    valid = false;
                }
                
                if(valid){
                    wp_manga_chapter_report_submit_override( errortype, content );	
                }        
                return false;
            });

            $(document).off('click', '#btn_flag_chapter').on('click', '#btn_flag_chapter', function(evt){
                if($(this).data('mode') == 'modal'){
                    $('#frm-wp-manga-report').modal();
                } else {
                    if(confirm(wp_chapter_report.are_you_sure)){
                        wp_manga_chapter_report_submit_override();
                    }
                }
            });
        }
    }, 1000);

    // Scroll event handler: separate scroll up and down detection
    let scrollTimeout;
    let isScrolling = false;
    
    $(window).on('scroll', function () {
        checkVisibleChapterContent();

        // Clear existing timeout
        clearTimeout(scrollTimeout);
        
        // Set scrolling flag
        if (!isScrolling) {
            isScrolling = true;
        }

        // Set timeout to detect when scrolling stops
        scrollTimeout = setTimeout(function() {
            isScrolling = false;
            
            // Trigger handlers when scroll stops
            if ($(window).scrollTop() > lastScrollTop) {
                // Scrolling down
                handleScrollDown();
            } else if ($(window).scrollTop() < lastScrollTop) {
                // Scrolling up
                handleScrollUp();
            }
            
            lastScrollTop = $(window).scrollTop();
        }, 150); // Wait 150ms after scroll stops
    });

    // Initial setup
    lastScrollTop = $(window).scrollTop(); // Track scroll position
});