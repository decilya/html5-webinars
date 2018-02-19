<?php
use app\models\Chat;

$this->registerJsFile('/js/lib/ConcatenateBlobs.js', [
    'depends' => 'yii\web\JqueryAsset'
]);

$this->registerJsFile('/js/lib/adapter.js', [
    'depends' => 'yii\web\JqueryAsset'
]);

$this->registerJsFile('/js/lib/getMediaElement.js', [
    'depends' => 'yii\web\JqueryAsset'
]);

$this->registerJsFile('/js/lib/getScreenId.js', [
    'depends' => 'yii\web\JqueryAsset'
]);

$this->registerJsFile('/js/lib/video.js', [
    'depends' => 'yii\web\JqueryAsset'
]);

$this->registerJsFile('/js/lib/videojs-watermark.js', [
    'depends' => 'yii\web\JqueryAsset'
]);

$this->registerJsFile('/js/screenSharing.js', [
    'depends' => 'yii\web\JqueryAsset'
]);

$this->registerJsFile('/js/lib/gumadapter.js', [
    'depends' => 'yii\web\JqueryAsset'
]);

/*
$this->registerJsFile('/js/recordRoomController.js', [
    'depends' => 'yii\web\JqueryAsset'
]);*/

$this->registerJsFile('/js/chat.js', [
    'depends' => 'yii\web\JqueryAsset'
]);

$js = <<< EOT
    $("#open-room").click();

    /*setTimeout(
        function () {
            chatController.sendAjax("SYSTEM_reload_SYSTEM");
        }, 500
    );*/
EOT;

$this->registerJs($js);
?>
<div class="row video-chat-wrapper first-page">

    <div class="b-row">
        <div class="left-side col-sm-8 col-lg-8 col-md-8">
            <article>
                <section class="experiment">
                    <div class="make-center">
                        <input id="room-id" type="hidden" value="<?= $roomId; ?>"
                               autocorrect=off
                               autocapitalize=off size=20>
                        <input type="hidden" id="statusRec" value="0">
                        <input type="hidden" id="courseId" value="1">

                        <button class="btn btn-success" id="open-room">Открыть комнату</button>
                        <button class="btn btn-info" id="share-screen">Транслировать рабочий стол</button>
                        <br>
                        <span class="v_line"></span>
                        <button class="btn btn-danger" id="close-room">Закрыть комнату</button>
                        <!-- <button class="btn btn-success" style="margin-top: 10px" id="startRecording">Начать запись
                        </button>
                        <br>
                        <button style="display: none" class="btn btn-danger" id="stopRecording">Завершить запись
                        </button> -->
                        <hr>

                        <div id="room-urls"></div>
                    </div>
                    <div id="videos-container"></div>
                </section>
            </article>

            <div class="make-center dataIsHost">
                <div id="videos-wrapper">
                    <div id="videoContainerMain"></div>
                    <div id="videoContainerForHost"></div>
                </div>
                <ol id="public-rooms" style="text-align:left;"></ol>
                <div class="make-center" id="errorsBlock">
                </div>
            </div>
        </div>

        <div class="right-side col-sm-4 col-lg-4 col-md-4">
            <div class="right-table">

                <ul class="nav nav-tabs">
                    <li class="active"><a data-toggle="tab" href="#panel1">Чат</a></li>
                    <?php /** @var int $userCount Кол-во пользователей в системе */ ?>
                    <li>&nbsp;</li>
                    <li>
                        <a data-toggle="tab" href="#panel2">
                            Участники
                            <div class="bg"><span id="nowOnline">1</span>
                                / <?= $userCount; ?></div>
                        </a>
                    </li>
                </ul>


                <div style="display: table-row; height: 10px"></div>

                <div class="tab-content">
                    <div class="tab-content-wrapper panel panel-default">
                        <div id="panel1" class="tab-pane fade in active">
                            <div class="row chat">
                                <div class="">
                                    <div id="historyChat">
                                        <div class="chat-output">
                                            <?php
                                            /**
                                             * @var Chat $message
                                             * @var Chat[] $chat
                                             */
                                            foreach ($chat as $message) { ?>
                                                <?php if ($message->isNotSystem($message->text)) { ?>
                                                    <div class="msg-item">
                                                        <span
                                                            class="msg-user-date"><?= date('H:i', $message->created_at) ?></span>
                                                        <span class="msg-user-name">
                                                            <?php
                                                            $nameUserPatronymic = htmlspecialchars($message->user->patronymic);
                                                            mb_regex_encoding('UTF-8');
                                                            mb_internal_encoding("UTF-8");
                                                            $nameUserPatronymic = preg_split('/(?<!^)(?!$)/u', $nameUserPatronymic);

                                                            $nameUserTmp = htmlspecialchars($message->user->name);
                                                            mb_regex_encoding('UTF-8');
                                                            mb_internal_encoding("UTF-8");
                                                            $nameUserTmp = preg_split('/(?<!^)(?!$)/u', $nameUserTmp);

                                                            $strMesUser = $message->user->surname . ' ' . $nameUserTmp[0] .
                                                                '.' . $nameUserPatronymic[0] . '.';

                                                            echo $strMesUser;
                                                            ?>
                                                        </span>
                                                        <?= $message->text; ?>
                                                    </div>
                                                <?php } ?>
                                            <?php } ?>

                                        </div>
                                    </div>
                                </div>


                            </div>
                        </div>
                        <div id="panel2" class="tab-pane fade">
                            <h5 class="text-center"><b>Присутствуют</b></h5>

                            <div id="present"></div>
                            <h5 class="text-center"><b>Отсуствуют</b></h5>

                            <div id="notPresent"></div>
                        </div>
                    </div>
                </div>
                <div style="display: table-row; height: 10px"></div>

                <div class="enter-msg">
                    <textarea maxlength="255" id="inputMessageChat" name="inputMessageChat" rows='1'
                              placeholder='Добавить комментарий...'></textarea>
                    <a id="sendMessage" style=""><span class="glyphicon glyphicon-send"></span></a>
                </div>

            </div>
        </div>
    </div>

</div>