$(document).ready(function () {

    function scrollButton() {
        var chat_scroll = $('.tab-content-wrapper');
        chat_scroll.scrollTop(chat_scroll.prop('scrollHeight'));
    }

    $('#inputMessageChat').on('focus', function () {
        $('.glyphicon-send').css({
            color: '#5BC0DE'
        });
        $('#inputMessageChat').addClass('borderBlue');
    });

    setInterval(function () {
        var hei = $('.nav-tabs li:nth-child(3)').height();
        $('.nav-tabs li:nth-child(1)').height(hei);
    }, 10);

    $('#inputMessageChat').on('focusout', function () {
        $('.glyphicon-send').css({
            color: '#888888'
        });
        $('#inputMessageChat').removeClass('borderBlue');
    });

    var textarea = document.querySelector('#inputMessageChat');
    textarea.addEventListener('contextmenu', autosize2);
    //textarea.addEventListener('click', autosize);
    textarea.addEventListener('keydown', autosize);


    function autosize() {
        var el = this;
        setTimeout(function () {
            el.style.cssText = 'height:' + el.scrollHeight + 'px';
            scrollButton();
        }, 1000);

    }

    function autosize2() {
        var el = this;
        setTimeout(function () {
            el.style.cssText = 'height:' + el.scrollHeight + 'px';
            scrollButton();
        }, 111);

    }

    /**
     * Chat msg window set default size by click
     */
    $('#sendMessage').on('click', function () {
        setTimeout(function () {
            $('#inputMessageChat').css('height', '40px');
            scrollButton();
        }, 110);
    });

    /**
     * Chat msg window set default size by enter
     */
    $('#inputMessageChat').keypress(function (e) {
        if (e.keyCode == 13) {
            e.preventDefault();
            //whenEnterPressed();
            setTimeout(function () {
                $('#inputMessageChat').css('height', '40px');
                scrollButton();
            }, 1000);

        }
    });


    //setTimeout(function () {
    //
    //}, 1000);

    scrollButton();

    $(window).resize(function () {
        scrollButton();

    });


    setInterval(function () {

        $('#videos-container .media-container .media-box video').attr('style', 'width: 100% !important;max-height: 949px;');

    }, 10);


});
