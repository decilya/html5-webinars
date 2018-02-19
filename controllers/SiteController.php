<?php

namespace app\controllers;

use app\models\Chat;
use app\models\File;
use app\models\Message;
use app\models\Organization;
use Faker\Provider\Image;
use Yii;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\Response;
use app\models\LoginForm;
use app\models\User;
use yii\web\UploadedFile;
use yii\imagine;

class SiteController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => 'yii\filters\AccessControl',
                'rules' => [
                    [
                        'actions' => ['login', 'error', 'save', 'send-message', 'chat', 'user-info', 'all-users', 'create-png',
                            'get-png', 'get-chat', 'get-alt-for-watermark', 'login-user-info'],
                        'allow' => true,
                    ],
                    [
                        'allow' => true,
                        'actions' => ['logout', 'index'],
                        'roles' => ['@'],
                    ],
                    [
                        'actions' => ['translate-room'],
                        'roles' => ['@'],
                        'allow' => true,
                        'matchCallback' => function ($rule, $action) {
                            return ((Yii::$app->user->identity->type === User::TYPE_USER_ADMIN) ||
                                (Yii::$app->user->identity->type === User::TYPE_USER_MANAGER) ||
                                (Yii::$app->user->identity->type === User::TYPE_USER_MASTER));
                        }
                    ],
                    [
                        'actions' => ['organization'],
                        'roles' => ['@'],
                        'allow' => true,
                        'matchCallback' => function ($rule, $action) {
                            return ((Yii::$app->user->identity->type === User::TYPE_USER_ADMIN) ||
                                (Yii::$app->user->identity->type === User::TYPE_USER_MANAGER));
                        }
                    ],
                ],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        if ($action->id == 'save') $this->enableCsrfValidation = false;

        if (!isset(Yii::$app->user->identity->id)) {
            $this->layout = '@app/views/layouts/forLogin';
        } else {
            $this->layout = '@app/views/layouts/main';
        }

        $this->enableCsrfValidation = false;

        return parent::beforeAction($action);
    }

    /**
     * Страница просмотра вебинара. Id'шник комнаты можно передавать 2мя способами: 1) через ?id=123 2) через #123, где 123 - номер комнаты
     * https://via.yii2-ipap-tr.dsite/site/index?id=123 == https://via.yii2-ipap-tr.dsite/site/index#123
     *
     * @@param string $id
     * @return string
     */
    public function actionIndex($id = null)
    {
        if (User::checkUserAgent()) {
            return $this->renderPartial('dummy');
        }

        // Если это гость, то отправим его авторизироваться
        $this->guestGoLogin();

        // потом надо будет как-то генерировать комнаты
        if ($id === null) $id = 2147483647;

        $chat = Chat::getChatByDate((time() - (8400 * 20)), 1);

        return $this->render('index', [
            'roomId' => $id,
            'chat' => $chat
        ]);
    }

    /**
     * Если это гость, то отправим его авторизироваться
     */
    private function guestGoLogin()
    {
        if (Yii::$app->user->isGuest) {
            return $this->redirect(Url::to(['site/login']));
        }

        return false;
    }

    /**
     * Login action.
     *
     * @return Response|string
     */
    public function actionLogin()
    {
        if (User::checkUserAgent()) {
            return $this->renderPartial('dummy');
        }

        $this->layout = '@app/views/layouts/forLogin';

        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();


        if ($model->load(Yii::$app->request->post())) {

            if ($model->validate()) {
                $userTmp = User::find()->where(['username' => $model->username])->one();

                $timeFinal = $userTmp->isLogin + 45;

                if (time() > $timeFinal) {

                    if ($model->login()) {

                        if (Yii::$app->user->identity->type == User::TYPE_USER_ADMIN)
                            return $this->redirect(Url::to(['site/translate-room', 'id' => 777]));

                        if (Yii::$app->user->identity->type == User::TYPE_USER_MANAGER)
                            return $this->redirect(Url::to(['site/translate-room', 'id' => 777]));

                        if (Yii::$app->user->identity->type == User::TYPE_USER_MASTER)
                            return $this->redirect(Url::to(['site/translate-room', 'id' => 777]));

                        if (Yii::$app->user->identity->type == User::TYPE_USER_STUDENT)
                            return $this->redirect(Url::to(['site/index', 'id' => 777]));

                        return $this->goBack();
                    }
                } else {
                    Yii::$app->session->setFlash('error', "Похоже, что Вы уже залогинены в системе где-то в другом браузере... разлогинтесь и подождите 40 секунд.");
                }
            }
        }

        $model->password = '';
        return $this->render('login', [
            'model' => $model,
        ]);
    }

    /**
     * Logout action.
     *
     * @return Response
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    /**
     * Страница ведения трансляции
     *
     * @@param int $id
     * @return Response|string
     */
    public function actionTranslateRoom($id = null)
    {
        if (User::checkUserAgent()) {
            return $this->renderPartial('dummy');
        }

        // потом надо будет как-то генерировать комнаты
        if ($id === null) $id = 2147483647;

        /** @var int $userCount Кол-во студентов (потом это будет кол-во студентов на конкретом курсе */
        $userCount = count(User::find()
            ->where(['status_id' => User::STATUS_ACTIVE])
            ->andWhere(['type' => User::TYPE_USER_STUDENT])
            ->all());

        $chat = Chat::getChatByDate((time() - (8400 * 20)), 1);

        return $this->render('translateRoom', [
            'roomId' => $id,
            'userCount' => $userCount,
            'chat' => $chat
        ]);
    }

    public function actionSave()
    {
        $model = new File();
        $model->file = UploadedFile::getInstance($model, 'file');

        $path = realpath(dirname(dirname(__FILE__))) . '/uploads/';

        if ($model->file->saveAs($path . $model->file)) {
            return true;
        }

        return false;
    }

    public function actionOrganization()
    {
        if (empty($organization = Organization::find()->one())) {
            $organization = new Organization();
        }

        if (Yii::$app->request->post() && $organization->load(Yii::$app->request->post())) {
            $organization->save();
        }

        return $this->render('organizationForm', [
            'organization' => $organization
        ]);
    }

    /**
     * Запись сообщения чата
     */
    public function actionSendMessage()
    {
        // Если это не аякс, то просто дальше не будем обрабатывать скрипт
        if (!Yii::$app->request->isAjax) {
            return false;
        }

        if (!Yii::$app->request->post()) {
            return false;
        }

        /** @var Chat $message */
        $message = new Chat();
        $message->user_id = (int)Yii::$app->request->post('userId');
        $message->course_id = (int)Yii::$app->request->post('courseId');
        $tmp = Yii::$app->request->post('massage');
        $message->text = $tmp['message'];
        $message->user_tmp_id = $tmp['userId'];
        $message->room_id = Yii::$app->request->post('roomId');

        if ($message->isNotSystem($message->text)) {
            if ($message->save()) {
                return true;
            }
        } else {
            return true;
        }

        return false;
    }

    public function actionUserInfo()
    {
        // Если это не аякс, то просто дальше не будем обрабатывать скрипт
        if (!Yii::$app->request->isAjax) {
            return false;
        }

        if (!Yii::$app->request->post()) {
            return false;
        }

        $userId = (int)Yii::$app->request->post('userId');

        if (empty($userId)) return false;

        /** @var User $userTmp */
        $userTmp = User::find()->where(['id' => $userId])->one();

        if (empty($userTmp)) return false;

        $user = [
            'id' => $userTmp->id,
            'name' => $userTmp->name,
            'surname' => $userTmp->surname,
            'patronymic' => $userTmp->patronymic,
            'type' => $userTmp->type
        ];

        if (!empty($user)) {
            return json_encode($user);
        } else {
            return false;
        }
    }

    /** Получение всех пользователей кроме запрашивающего  */
    public function actionAllUsers()
    {
        // Если это не аякс, то просто дальше не будем обрабатывать скрипт
        if (!Yii::$app->request->isAjax) {
            return false;
        }

        $userId = Yii::$app->user->identity->id;
        $users = User::find()
            ->where(['status_id' => User::STATUS_ACTIVE])
            ->andWhere(['type' => User::TYPE_USER_STUDENT])
            ->all();

        $result = [];

        /**
         * @var User $user
         */
        foreach ($users as $user) {
            if ($user->id != $userId) {
                $result[] = [
                    'id' => $user->id,
                    'name' => $user->name, -
                    'surname' => $user->surname,
                    'patronymic' => $user->patronymic,
                    'type' => $user->type
                ];
            }
        }

        if (!empty($result)) {
            return json_encode($result);
        } else {
            return false;
        }
    }

    public function actionCreatePng($id, $width = null, $percent = null)
    {
        header('Content-Type: image/png; charset=utf-8');

        if ($percent == null) $percent = 25;
        if (($width == null) || ($width == 'undefined')) $width = 709;
        $width = (int)$width;
        if ($width > 1530) $width = 1530;

        $oldWidth = 575;
        $height = (int)($width / 2.6);

        $imgWidth = (int)(($width * $percent) / 100);
        $imgHeight = (int)(($height * $percent) / 100);

        $img = imagecreatetruecolor($imgWidth, $imgHeight);

        $transparent = imagecolorallocatealpha($img, 0, 0, 0, 127);
        imagefill($img, 5, 0, $transparent);

        imagesavealpha($img, true);

        /** @var User $user */
        $user = User::find()->where(['id' => $id])->one();

        if (!empty($user)) {
            $user->name = htmlspecialchars($user->name);
            mb_regex_encoding('UTF-8');
            mb_internal_encoding("UTF-8");
            $user->name = preg_split('/(?<!^)(?!$)/u', $user->name);

            $user->patronymic = htmlspecialchars($user->patronymic);
            mb_regex_encoding('UTF-8');
            mb_internal_encoding("UTF-8");
            $user->patronymic = preg_split('/(?<!^)(?!$)/u', $user->patronymic);

            $text1 = $user->surname . ' ' . $user->name[0] . '.' . $user->patronymic[0] . '. ';
            $text2 = $user->email;
            $text3 = $user->phone;
        } else {
            $text1 = 'пр. Стачек, д.47, БЦ «Шереметев»';
            $text2 = 'info@ipap.ru';
            $text3 = '+7(812)655-63-21';
        }

        $text1 = trim($text1);
        $text1 = htmlspecialchars($text1);

        $text2 = trim($text2);
        $text2 = htmlspecialchars($text2);

        $text3 = trim($text3);
        $text3 = htmlspecialchars($text3);

        $firstLine = 10;
        $secondLine = 21;
        $thirdLine = 34;
        $fontSize = (int)(($width * 10) / $oldWidth);
        $x = (int)(($width * 3) / $oldWidth);

        $y1 = (($width * $firstLine) / $oldWidth);
        $y2 = (($width * $secondLine) / $oldWidth);
        $y3 = (($width * $thirdLine) / $oldWidth);

        $color = imagecolorallocate($img, 250, 0, 0);
        $font_path = Yii::$app->basePath . '/web/doc/roboto.ttf';

        imagettftext($img, $fontSize, 0, $x, $y1, $color, $font_path, $text1);
        imagettftext($img, $fontSize, 0, $x, $y2, $color, $font_path, $text2);
        imagettftext($img, $fontSize, 0, $x, $y3, $color, $font_path, $text3);

        imagepng($img);
        imagedestroy($img);
    }

    /**
     * site/get-alt-for-watermark
     *
     * @return JSON
     */
    public function actionGetAltForWatermark()
    {
        // Если это не аякс, то просто дальше не будем обрабатывать скрипт
        if (!Yii::$app->request->isAjax) {
            return false;
        }

        $id = Yii::$app->user->identity->id;
        /** @var User $user */
        $user = User::find()->where(['id' => $id])->one();

        if (!empty($user)) {
            $user->name = htmlspecialchars($user->name);
            mb_regex_encoding('UTF-8');
            mb_internal_encoding("UTF-8");
            $user->name = preg_split('/(?<!^)(?!$)/u', $user->name);

            $user->patronymic = htmlspecialchars($user->patronymic);
            mb_regex_encoding('UTF-8');
            mb_internal_encoding("UTF-8");
            $user->patronymic = preg_split('/(?<!^)(?!$)/u', $user->patronymic);

            $text1 = $user->surname . ' ' . $user->name[0] . '.' . $user->patronymic[0] . '. ';
            $text2 = $user->email;
            $text3 = $user->phone;
        } else {
            $text1 = 'пр. Стачек, д.47, БЦ «Шереметев»';
            $text2 = 'info@ipap.ru';
            $text3 = '+7(812)655-63-21';
        }

        $text2 = trim($text2);
        $text2 = htmlspecialchars($text2);
        $text3 = trim($text3);
        $text3 = htmlspecialchars($text3);

        $text = $text1 . ' ' . $text2 . ' ' . $text3;

        return json_encode($text);
    }

    /**
     * Получение чата
     */
    public function actionGetChat()
    {
        // Если это не аякс, то просто дальше не будем обрабатывать скрипт
        if (!Yii::$app->request->isAjax) {
            return false;
        }

        if (!Yii::$app->request->post()) {
            return false;
        }

        $courseId = Yii::$app->request->post('courseId');
        $courseId = 1;

        $chat = [];
        // Берем чат за прошедшие 20 часов
        $chatTmp = Chat::getChatByDate((time() - (8400 * 20)), $courseId);

        /** @var Chat $message */
        foreach ($chatTmp as $message) {
            $chat[] = [
                'created_at' => $message->created_at,
                'user_id' => $message->user_id,
                'text' => $message->text
            ];
        }

        return json_encode($chat);
    }

    public function actionGetPng()
    {
        return $this->render('test', [

        ]);
    }

    /**
     * Сохранение времени активности пользователя
     */
    public function actionLoginUserInfo()
    {
        // Если это не аякс, то просто дальше не будем обрабатывать скрипт
        if (!Yii::$app->request->isAjax) {
            return false;
        }

        if (!Yii::$app->request->post()) {
            return false;
        }

        $userId = Yii::$app->request->post('userId');

        /** @var User $user */
        $user = User::find()->where(['id' => $userId])->one();

        if (!empty($user)) {
            $user->isLogin = time();

            if ($user->save(false)) return true;
        }

        return false;
    }

}
