"use strict";

$(function () {

    var socket = connection.getSocket();
    socket.on('custom-message', function (message) {
        alert(message);

        // custom message
        if (message.joinMyRoom) {
            connection.join(message.roomid);
        }
    });

    $("#stopRecording").hide();

});

document.getElementById('startRecording').onclick = function () {

    $("#statusRec").val(1);

    $("#startRecording").hide();
    $("#stopRecording").show();

    setTimeout(
        function () {
            $('#startRecording').attr('disabled', true);
            $('#stopRecording').attr('disabled', false);
            myFoo();
        },
        2500
    );

    myFoo();
};

function myFoo() {

    var statusRec = $("#statusRec").val();

    if (statusRec == 1) {

        let maxKeyVal = '';
        $.each(connection.streamEvents, function (key) {
            maxKeyVal = key;
        });

        $.each(connection.streamEvents, function (key, video) {

            if ((key === maxKeyVal) && (video.streamid != undefined)) {

                goRec(video);

            }
        });
    } else {
        return true;
    }
}

function goRec(video) {

    var mediaConstraints = {
        audio: true,
        video: true
    };

    navigator.getUserMedia(mediaConstraints, onMediaSuccess);

    function onMediaSuccess(video) {
        var mediaRecorder = new MediaStreamRecorder(video);
        mediaRecorder.mimeType = 'video/webm';
        mediaRecorder.ondataavailable = function (blob) {
            // POST/PUT "Blob" using FormData/XHR2
            var blobURL = URL.createObjectURL(blob);
            document.write('<a href="' + blobURL + '">' + blobURL + '</a>');
        };
        mediaRecorder.start(3000);
    }

    /*
     const myVideo = video;

     var mimeType = 'video/webm';
     var fileExtension = 'webm';
     var statusRec = $("#statusRec").val();

     var options = {
     recorderType: MediaStreamRecorder,
     mimeType: 'video/webm\;codecs=h264',
     type: 'video',
     fileExtension: 'webm',
     autoWriteToDisk: true
     };

     if (statusRec == 1) {

     var recorder = new RecordRTC(video.stream, options);

     var fiveMinutes = 0.1 * 1000 * 60;
     recorder.setRecordingDuration(fiveMinutes).onRecordingStopped(function () {
     var blob = this.getBlob();
     video.src = this.toURL();

     var fileName = getFileName(fileExtension);
     if (!this) return alert('No recording found.');

     var file = new File([blob], fileName, {
     type: mimeType
     });

     var formData = new FormData();
     formData.append('File[file]', file);

     xhr('/site/save', formData, function (fName) {
     window.open(location.href + fName);
     });

     function xhr(url, data, callback) {
     var request = new XMLHttpRequest();
     request.onreadystatechange = function () {
     if (request.readyState == 4 && request.status == 200) {
     callback(location.href + request.responseText);
     }
     };
     request.open('POST', url, false);
     request.send(data);

     if (request.status != 200) {
     // обработать ошибку
     // goRec(myVideo);
     } else {
     goRec(myVideo);
     }
     }

     });

     recorder.startRecording();

     }*/
}

document.getElementById('stopRecording').onclick = function () {
    $("#stopRecording").hide();
    $("#startRecording").show();

    $('#stopRecording').attr('disabled', true);
    $('#startRecording').attr('disabled', false);

    $("#statusRec").val(0);
};

function getRandomString() {
    return (Math.random() * new Date().getTime()).toString(36).replace(/\./g, '');
}

function getFileName(fileExtension) {
    var d = new Date();
    var year = d.getUTCFullYear();
    var month = d.getUTCMonth();
    var date = d.getUTCDate();
    return 'RecordRTC-' + year + month + date + '-' + getRandomString() + '.' + fileExtension;
}
