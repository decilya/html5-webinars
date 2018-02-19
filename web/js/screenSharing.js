document.getElementById('share-screen').onclick = function () {

    $('#share-screen').attr('disabled', true);

    setTimeout(
        function () {
            let myVideoBlocks = $('video').length;
            if (myVideoBlocks < 2) {
                $('#share-screen').attr('disabled', false);
            }
        }, 2000
    );

    connection.addStream({
        screen: true,
        // oneway: true
    });
};

document.getElementById('open-room').onclick = function () {
    disableInputButtons();
    connection.open(document.getElementById('room-id').value, function () {
        showRoomURL(connection.sessionid);
        connection.session.oneway = true;
    });
};

document.getElementById('close-room').onclick = function () {

    connection.attachStreams.forEach(function (localStream) {
        localStream.stop();
    });

    // close socket.io connection
    connection.close();

    enableInputButtons();
};

// ......................................................
// ..................RTCMultiConnection Code.............
// ......................................................
var connection = new RTCMultiConnection();

// Using getScreenId.js to capture screen from any domain
// You do NOT need to deploy Chrome Extension YOUR-Self!!
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

// by default, socket.io server is assumed to be deployed on your own URL
connection.socketURL = '/';
connection.channel = 666;
connection.videoContainer = {
    main: undefined,
    forHost: undefined
};

connection.hasErrors = false;
connection.errorsBlock = undefined;
connection.errorsElement = undefined;

connection.audio = false;
connection.video = false;

connection.isHost = true;
connection.isClient = false;

// comment-out below line if you do not have your own socket.io server
// connection.socketURL = 'https://rtcmulticonnection.herokuapp.com:443/';

connection.socketMessageEvent = 'audio-video-screen';
connection.session = {
    data: true,
    audio: false,
    video: true,
    //   oneway: true
};

connection.sdpConstraints.mandatory = {
    OfferToReceiveAudio: false,
    OfferToReceiveVideo: false,
    //   oneway: true
};

//connection.enableFileSharing = true;

connection.onopen = function () {
    if (connection.alreadyOpened) return;
    connection.alreadyOpened = true;
};

//connection.mainUserName = $('userBlock').data('name');

connection.videosContainer = document.getElementById('videos-container');
connection.onstream = function (event) {

    if ($('#room-id').val() != event.userid) return;

    if (document.getElementById(event.streamid)) {
        var existing = document.getElementById(event.streamid);
        existing.parentNode.removeChild(existing);
    }

    var width = parseInt(connection.videosContainer.clientWidth / 2) - 20;

    if (event.stream.isScreen === true) {
        width = connection.videosContainer.clientWidth - 20;
    }

    var mediaElement = getMediaElement(event.mediaElement, {
        title: event.userid,
        buttons: ['full-screen'],
        width: width,
        showOnMouseEnter: false
    });

    mediaElement.muteType = 'audio';
    mediaElement.muted = true;

    connection.videosContainer.appendChild(mediaElement);
    setTimeout(function () {
        mediaElement.media.play();
    }, 5000);

    mediaElement.id = event.streamid;
};

connection.onstreamended = function (event) {
    var mediaElement = document.getElementById(event.streamid);
    if (mediaElement) {
        mediaElement.parentNode.removeChild(mediaElement);
    }
};

function disableInputButtons() {
    document.getElementById('open-room').disabled = true;
    document.getElementById('share-screen').disabled = false;
}

function enableInputButtons() {
    document.getElementById('open-room').disabled = false;
    document.getElementById('share-screen').disabled = false;
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

    html = '';
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

    let socket = connection.getSocket();
    socket.on('custom-message', function (message) {
        alert(message);

        // custom message
        if (message.joinMyRoom) {
            connection.join(message.roomid);
        }
    });

})();

localStorage.setItem(connection.socketMessageEvent, document.getElementById('room-id').value);

let roomid = document.getElementById('room-id').value;

var hashString = location.hash.replace('#', '');
if (hashString.length && hashString.indexOf('comment-') == 0) {
    hashString = '';
}

roomid = params.roomid;
if (!roomid && hashString.length) {
    roomid = hashString;
}

if (roomid && roomid.length) {
    document.getElementById('room-id').value = roomid;
    localStorage.setItem(connection.socketMessageEvent, roomid);
    // auto-join-room
    (function reCheckRoomPresence() {
        connection.checkPresence(roomid, function (isRoomExists) {
            if (isRoomExists) {
                connection.join(roomid);

                return;
            }
            setTimeout(reCheckRoomPresence, 5000);
        });
    })();
    disableInputButtons();
}