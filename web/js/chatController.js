"use strict";

var chatController = {

    name: 'chatController',
    connection: undefined,
    systemMessage: 'SYSTEM_reload_SYSTEM',

    sendMessage: function () {
        $('#sendMessage').on('click', function (event) {

            var attr = $(this).attr('send');
            if (!(typeof attr !== typeof undefined && attr !== false)) {
                chatController.sendAjax();
            }

            setTimeout(
                function () {
                    $('#sendMessage').removeAttr("send");
                }, 800
            );

        });
    },

    sendMessageByEnter: function () {
        document.getElementById('inputMessageChat').onkeyup = function (e) {

            if (e.keyCode != 13) return;

            var attr = $(this).attr('send');
            if (!(typeof attr !== typeof undefined && attr !== false)) {
                chatController.sendAjax();
            }

            setTimeout(
                function () {
                    $('#inputMessageChat').removeAttr("send");
                }, 800
            );

        };
    },

    sendAjax: function (system = null) {

        $('#sendMessage').attr("send", "send");
        $('#inputMessageChat').attr("send", "send");

        const INPUT = $("#inputMessageChat");
        const USER_BLOCK = $('#userBlock');
        const USER_ID = USER_BLOCK.data('user');
        const USER_NAME = USER_BLOCK.data('name');
        const SURNAME = USER_BLOCK.data('surname');
        const PATRONYMIC = USER_BLOCK.data('patronymic');
        const ROOM_ID = $("#room-id").val();

        const MESSAGE = {
            'message': (system == null) ? INPUT.val().replace(/^\s+|\s+$/g, '') : 'SYSTEM_reload_SYSTEM',
            'userId': USER_ID,
            'userName': USER_NAME,
            'userSurname': SURNAME,
            'userPatronymic': PATRONYMIC
        };
        const COURSE_ID = $('#courseId').val();

        $.ajax({
            method: 'POST',
            dataType: 'json',
            data: {
                massage: MESSAGE,
                userId: USER_ID,
                courseId: COURSE_ID,
                userTmpId: connection.userid,
                roomId: ROOM_ID
            },
            url: '/site/send-message',
            success: function (data) {
                chatController.connection.send(MESSAGE);
                let line = chatController.setDataForChat();
                line = "<span class='msg-user-date'>" + line + "</span>" + " <span class='msg-user-name'>" +
                MESSAGE.userSurname + " " + MESSAGE.userName[0] +
                "." + MESSAGE.userPatronymic[0] +
                ".</span>: " + MESSAGE.message;

                if (MESSAGE.message != chatController.systemMessage) {
                    chatController.appendDIV(line);
                }

                INPUT.val('');
            }
        });
    },

    onMessage: function () {

        connection.onmessage = function (event) {

            if (event.data.message !== chatController.systemMessage) {
                let line = chatController.setDataForChat();
                line = line + " " + event.data.userSurname + " " + event.data.userName[0] +
                "." + event.data.userPatronymic[0] + ": " + event.data.message;
                chatController.appendDIV(line);
            } else {
                // если это не страница трансялции, ибо на странице трансляции есть панель2
                if (!($("div").is("#panel2"))) {
                    chatController.checkForReloadAndLocationReload();
                }
            }
        };
    },


    appendDIV: function (event) {
        let div = document.createElement('div');
        div.classList.add('msg-item');
        let chatContainer = document.querySelector('.chat-output');

        div.innerHTML = event.data || event;
        chatContainer.appendChild(div);
        div.tabIndex = 0;
        div.focus();
        document.getElementById('inputMessageChat').focus();
    },

    setDataForChat: function () {
        let now = new Date();
        const OPTIONS = {
            hour: 'numeric',
            minute: 'numeric'
        };
        return now.toLocaleString('ru', OPTIONS);
    },

    checkForReloadAndLocationReload: function () {
        window.location.reload();
    },

    hasVideoBlock: function () {
        // если не админ
        //if (!$("div").is("#panel2")) {
        //
        //}
    },

    init: function () {
        this.connection = connection;
        this.sendMessageByEnter();
        this.sendMessage();
        this.onMessage();
        this.hasVideoBlock();

        // не делать то что ниже, если не админ
        const userType = $('#userBlock').data('type');

        if (((userType == 1) || (userType == 2) || (userType == 3)) && ($("div").is("#panel2"))) {

            let numberOfUsersInTheRoom = connection.getAllParticipants().length;
            $('#nowOnline').text(numberOfUsersInTheRoom);

            setInterval(function () {

                let numberOfUsersInTheRoom = connection.getAllParticipants().length;
                $('#nowOnline').text(numberOfUsersInTheRoom);

                // пробежимся и добавим пользователей в присутсвующих
                connection.getAllParticipants().forEach(function (userId) {
                    $.ajax({
                        method: 'POST',
                        dataType: 'json',
                        data: {userId: userId},
                        url: '/site/user-info',
                        success: function (data) {
                            let tmpId = 'c' + userId;

                            if (!$('.presentUser').is("#" + tmpId)) {
                                var html = "<div class='presentUser' id='" + tmpId + "'>" + data.surname + ' ' +
                                    data.name + ' ' + data.patronymic + "</div>";
                                $('#present').append(html);

                                //  теперь удалим из отсутсвующих, если он там есть
                                $("#notPresent #c" + userId).remove();
                            }
                        }
                    });
                });

                // пробежимся и удалим отсутсвующих из присуствующих
                $('.presentUser').each(function (i, elem) {
                    let tmpMyId = elem.id;
                    tmpMyId = tmpMyId.replace('c', '');
                    if (connection.getAllParticipants().indexOf(tmpMyId.toString()) == -1) {
                        $("#present #c" + tmpMyId).remove();
                    }
                });

                // теперь определим всех отствующих
                // для этого получим список всех зареганых пользователей
                $.ajax({
                    method: 'POST',
                    dataType: 'json',
                    url: '/site/all-users',
                    success: function (data) {

                        for (let i = 0; i <= data.length - 1; i++) {
                            var tmpMyUser = data[i];

                            if (tmpMyUser.id !== undefined) {
                                var tmpUserId = tmpMyUser.id;

                                if (connection.getAllParticipants().indexOf(tmpUserId.toString()) == -1) {
                                    if (!$('.notPresent').is("#c" + tmpMyUser.id) && (!$('.present').is("#c" + tmpMyUser.id))) {

                                        var html = "<div class='notPresent' id='c" + data[i].id + "'>" + data[i].surname + ' ' +
                                            data[i].name + ' ' + data[i].patronymic + "</div>";
                                        $("#notPresent").append(html);
                                    }
                                }
                            }
                        }
                    }
                });

            }, 3777);

        }
    }
};

chatController.init();