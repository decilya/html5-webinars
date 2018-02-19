disableInputButtons();

var connection = new RTCMultiConnection();
connection.socketURL = '/';
connection.channel = 666;

if ($('#userBlock').data('user') != undefined) {
    connection.userid = $('#userBlock').data('user');
}

connection.videoContainer = {
    main: undefined,
    forHost: undefined
};

connection.hasErrors = true;
connection.errorsBlock = undefined;
connection.errorsElement = undefined;
connection.isHost = false;
connection.isClient = true;

connection.socketMessageEvent = 'audio-video-screen';
connection.session = {
    audio: true,
    video: true,
    oneway: true,
    data: true
};

connection.sdpConstraints.mandatory = {
    OfferToReceiveAudio: true,
    OfferToReceiveVideo: true,
    oneway: true
};

connection.videosContainer = document.getElementById('videos-container');

//let roomid = '';

if (localStorage.getItem(connection.socketMessageEvent)) {
    roomid = localStorage.getItem(connection.socketMessageEvent);
} else {
    roomid = connection.token();
}

document.getElementById('room-id').value = roomid;

document.getElementById('room-id').onkeyup = function () {
    localStorage.setItem(connection.socketMessageEvent, this.value);
};

var myRoomId = document.getElementById('roomId').value;

if ((myRoomId == null) || (myRoomId == '') || (myRoomId == undefined)) {

    var hashString = location.hash.replace('#', '');
    if (hashString.length && hashString.indexOf('comment-') == 0) {
        hashString = '';
    }

    var roomid = params.roomid;
    if (!roomid && hashString.length) {
        roomid = hashString;
    }

} else {
    hashString = document.getElementById('roomId').value;
    roomid = hashString;
}

hashString = document.getElementById('roomId').value;
roomid = hashString;
var xxx = 0;

(function conect() {
    if ((roomid) && (roomid.length)) {
        (function reCheckRoomPresence() {
            connection.checkPresence(roomid, function (isRoomExists) {
                if (isRoomExists) {
                    connection.join(roomid);
                    $('#errorConnection').text("");
                    return true;
                } else {
                    $('#errorConnection').text("Подождите начала трансляции");
                    setTimeout(conect, 5000);
                }
            });
        })();
    } else {
        setTimeout(conect, 5000);
    }
})();

connection.videosContainer = document.getElementById('videos-container');

connection.getScreenConstraints = function (callback) {
    getScreenConstraints(function (error, screen_constraints) {
        if (!error) {
            screen_constraints = connection.modifyScreenConstraints(screen_constraints);
            callback(error, screen_constraints);
            return;
        }
        throw error;
    });
};

/////////////////////////////// !! //////////////////////////////////
let count = 0;
connection.onstream = function (event) {

    count++;

    if (document.getElementById(event.streamid)) {
        var existing = document.getElementById(event.streamid);
        existing.parentNode.removeChild(existing);
    }

    var width = parseInt(connection.videosContainer.clientWidth / 2) - 20;

    event.tmp = 'tmp';

    console.log(event);

    if (event.stream.isScreen === true) {
        width = connection.videosContainer.clientWidth - 20;
    }

    var mediaElement = getMediaElement(event.mediaElement, {
        title: event.userid,
        buttons: ['full-screen'],
        width: width,
        showOnMouseEnter: true
    });

    connection.videosContainer.appendChild(mediaElement);
    setTimeout(function () {
        mediaElement.media.play();
    }, 5000);

    var options = {
        preload: "auto",
        width: '100%',
        aspectRatio: "16:9"
    };

    var tmp = event.streamid;

    window.player = videojs(tmp, options, function onPlayerReady() {
        videojs.log('Your player is ready!');

        /*
         const baseUrl = $("#urlHost").val();
         let myMedElemWidth = $('.vjs-controls-disabled:eq(1)').parent().width();
         const urlToCreatePng = "https://" + baseUrl + "/site/create-png?id=" + connection.userid + "&width=" + myMedElemWidth;

         if (count == 2) {
         this.watermark({
         image: urlToCreatePng
         });
         }

         else {
         this.watermark2({
         image: urlToCreatePng
         });
         }*/

        // In this context, `this` is the player that was created by Video.js.
        this.play();

        // How about an event listener?
        this.on('ended', function () {
            videojs.log('Awww...over so soon?!');
        });
    });

    mediaElement.id = event.streamid;
};

connection.onstreamended = function (event) {

    if (event.userid != roomid) return;

    var mediaElement = document.getElementById(event.streamid);
    if (mediaElement) {
        mediaElement.parentNode.removeChild(mediaElement);
    }
};

function disableInputButtons() {
    document.getElementById('open-or-join-room').disabled = true;
    document.getElementById('open-room').disabled = true;
    document.getElementById('join-room').disabled = true;
    document.getElementById('room-id').disabled = true;
    document.getElementById('share-screen').disabled = true;
}

// ......................................................
// ......................Handling Room-ID................
// ......................................................

function showRoomURL(roomid) {
    var roomHashURL = '#' + roomid;
    var roomQueryStringURL = '?roomid=' + roomid;
    var html = '';
    html += 'Hash URL: <a href="' + roomHashURL + '" target="_blank">' + roomHashURL + '</a>';
    html += '<br>';
    html += 'QueryString URL: <a href="' + roomQueryStringURL + '" target="_blank">' + roomQueryStringURL + '</a>';
    var roomURLsDiv = document.getElementById('room-urls');
    roomURLsDiv.innerHTML = html;
    roomURLsDiv.style.display = 'block';
}

(function () {
    var params = {},
        r = /([^&=]+)=?([^&]*)/g;

    function d(s) {
        return decodeURIComponent(s.replace(/\+/g, ' '));
    }

    var match, search = window.location.search;
    while (match = r.exec(search.substring(1)))
        params[d(match[1])] = d(match[2]);
    window.params = params;
})();

let wm = function () {

    let cnt = 1;

    wm.altTextForWmImg = '';

    $.ajax({
        method: 'POST',
        dataType: 'json',
        url: '/site/get-alt-for-watermark',
        success: function (altTextForWmImg) {
            wm.altTextForWmImg = altTextForWmImg;
        }
    });

    let baseUrl = $("#urlHost").val();
    let myMedElemWidth = $('.vjs-controls-disabled:eq(1)').parent().width();

    if (myMedElemWidth == undefined) myMedElemWidth = 745.328;

    let urlToCreatePng = "https://" + baseUrl + "/site/create-png?id=" + connection.userid + "&width=" + myMedElemWidth;

    let tmpHeight = (myMedElemWidth / 2.6);
    let widthImg = (myMedElemWidth * 25) / 100;
    let heightImg = (tmpHeight * 25) / 100;

    let tmpWmImg = document.createElement('img');
    tmpWmImg.setAttribute('src', urlToCreatePng);

    setInterval(function () {

        let vs = $('video:eq(1)');

        $.each(vs, function (key, video) {
            cnt = selfRandom(1, 4);
            reBuildBlock(video, cnt);
        });

        function reBuildBlock(containerPlayer, cnt) {

            let p = $(containerPlayer).parent('div');

            p.children('.vjs-watermark').detach();

            let wm = document.createElement('div');
            let wmImg = document.createElement('img');

            wmImg.setAttribute('src', tmpWmImg.src);
            wmImg.setAttribute('style', "color: red; font: 35px 'Roboto'; font-weight: bold; display: block !important; width: " + widthImg + 'px !important; height: ' + heightImg + 'px !important; z-index:99999999999999999999999 !important; visibility: visible !important; float: none !important;');
            wmImg.setAttribute('alt', wm.altTextForWmImg);

            wm.classList.add("vjs-watermark");

            var strStyle = 'position: absolute !important; opacity: 100; display: block !important; width: ' + widthImg + 'px !important; height: ' + heightImg + 'px !important; z-index:99999999999999999999999 !important; visibility: visible !important; float: none !important;';

            wm.setAttribute('style', strStyle);

            switch (cnt) {
                case 1:
                    wm.setAttribute('style', 'top: 0px !important; left: 0px !important;');
                    break;

                case 2:
                    wm.setAttribute('style', 'bottom: 0px !important; left: 0px !important;');
                    break;

                case 3:
                    wm.setAttribute('style', 'top: 0px !important; right: 0px!important;');
                    break;

                default:
                    wm.setAttribute('style', 'bottom: 0px !important; right: 0px !important;')
            }

            p.append(wm);
            wm.appendChild(wmImg);


        }

        function selfRandom(min, max) {
            return Math.floor(Math.random() * (max - min + 1)) + min;
        }

    }, 10000);

    connection.onclose = function (event) {
        console.log(event);
        console.log(roomid);
        if (event == roomid) {
            window.location.reload();
        }
    };

    connection.onUserStatusChanged = function (event, dontWriteLogs) {
        "use strict";

        if ((event.userid == roomid) && (event.status == "offline")) {
            window.location.reload();
        }
    };

    let reCon = 0;

    (function reConFunction() {
        reCon++;
        setTimeout(
            function () {
                reCon++;

                let statusHasRoomId = false;
                connection.getAllParticipants().forEach(function (userId) {

                    if (userId == roomid) {
                        statusHasRoomId = true;
                        let myVideoBlocks = $('video').length;

                        if (myVideoBlocks == 0) {
                            window.location.reload();
                        } else {
                            return true;
                        }
                    }

                });

                if ((statusHasRoomId == false) && ($('#errorConnection').text() == '')) {
                    window.location.reload();
                }

                if (reCon < 3) reConFunction();
            }, 2000
        );

    })();


};

wm();






