<?php

use app\models\Chat;

/**
 * @var $this yii\web\View
 * @var integer $roomId
 */

$this->title = 'Присоединиться к трансляции';
$this->registerCssFile('/css/video-js.css');
$this->registerJsFile('/js/lib/video.js', ['position' => \yii\web\View::POS_HEAD]);
$this->registerJsFile('/js/lib/videojs-watermark.js', ['position' => \yii\web\View::POS_HEAD]);
$this->registerJsFile('/js/lib/videojs-watermark2.js', ['position' => \yii\web\View::POS_HEAD]);

$this->registerJsFile('/js/lib/getMediaElement.js', [
    'depends' => 'yii\web\JqueryAsset'
]);

$this->registerJsFile('/js/lib/getScreenId.js', [
    'depends' => 'yii\web\JqueryAsset'
]);

$this->registerJsFile('/js/clientScreenSharing.js', [
    'depends' => 'yii\web\JqueryAsset'
]);

$this->registerJsFile('/js/chatController.js', [
    'depends' => 'yii\web\JqueryAsset'
]);

$this->registerJsFile('/js/chat.js', [
    'depends' => 'yii\web\JqueryAsset'
]);

$js = <<< EOT
$(document).ready(function(){
    //
});
EOT;

$this->registerJs($js);
?>

<div class="">
    <div class="">
        <input id="room-id" type="hidden" value="<?= $roomId; ?>">
        <input id="roomId" type="hidden" value="<?= $roomId; ?>">
    </div>
</div>

<div class="row video-chat-wrapper main-page">
    <div class="b-row">

        <div class="left-side col-sm-8 col-lg-8 col-md-8">
            <input type="hidden" id="courseId" value="1">

            <div id="errorConnection"></div>
            <article>
                <section class="experiment">
                    <div class="make-center" style="display: none">
                        <input type="text" id="room-id" value="abcdef" autocorrect=off autocapitalize=off size=20>
                        <button id="open-room">Open Room</button>
                        <button id="join-room">Join Room</button>
                        <button id="open-or-join-room">Auto Open Or Join Room</button>
                        <hr>
                        <button id="share-screen" disabled>Share Screen</button>
                        <hr>

                        <div id="room-urls"></div>
                    </div>
                    <div id="videos-container"></div>
                </section>
            </article>
        </div>

        <div class="right-side col-sm-4 col-lg-4 col-md-4">
            <div class="right-table">

                <ul class="nav nav-tabs">
                    <li><span>Чат</span></li>
                </ul>
                <div style="display: table-row;height: 10px"></div>
                <div class="tab-content">
                    <div class="tab-content-wrapper panel panel-default">
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
                <div style="display: table-row;height: 10px"></div>

                <div class="enter-msg">
                        <textarea maxlength="255" id="inputMessageChat" name="inputMessageChat" rows='1'
                                  placeholder='Добавить комментарий...'></textarea>
                    <a id="sendMessage" style=""><span class="glyphicon glyphicon-send"></span></a>
                </div>

            </div>
        </div>

    </div>
</div>