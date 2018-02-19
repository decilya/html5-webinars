<?php

namespace app\modules\admin\controllers;

use app\models\search\UserSearch;
use Yii;
use app\models\User;
use yii\base\Exception;
use yii\base\ExitException;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\HttpException;
use yii\widgets\ActiveForm;
use yii\web\Response;

/**
 * Default controller for the `admin` module
 */
class DefaultController extends Controller
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
                        // На эти страницы будем пускать только суперпользователя
                        'actions' => ['users', 'create-user', 'update-user'],
                        'allow' => true,
                        'matchCallback' => function ($rule, $action) {
                            return Yii::$app->user->identity->type === User::TYPE_USER_ADMIN;
                        }
                    ],
                    [
                        'allow' => true,
                        'roles' => ['@'],
                        'matchCallback' => function ($rule, $action) {
                            return (Yii::$app->user->identity->type === User::TYPE_USER_ADMIN ||
                                Yii::$app->user->identity->type === User::TYPE_USER_MANAGER);
                        }
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        $this->layout = '@app/views/layouts/adminUsers';

        return parent::beforeAction($action);
    }

    /**
     * Renders the index view for the module
     *
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index');
    }

    public function actionUsers()
    {
        $searchModel = new UserSearch();
        // $searchModel->typeForSearch = User::TYPE_USER_MANAGER;
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render(
            'users-list', [
                'dataProvider' => $dataProvider
            ]
        );
    }

    public function actionCreateUser()
    {
        $user = new User();

        if (Yii::$app->request->isAjax && $user->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($user);
        }

        if ($user->load((Yii::$app->request->post()))) {

            $user->type = Yii::$app->request->post('typeUser');
            $user->status_id = User::STATUS_ACTIVE;

            $pass = $user->generatePassword(6);
            $user->setPassword($pass);
            $user->generateAuthKey();

            $user->email = trim($user->email);
            $user->username = trim($user->username);

            $this->saveUserAndSandMail($user, $pass);
        }

        return $this->render(
            'user-form', [
                'user' => $user,
            ]
        );
    }

    public function actionUpdateManager($id)
    {
        /**
         * @var User $user
         */
        $user = User::find()->where(['id' => $id])->one();

        if (empty($user)) {
            throw new HttpException(404, 'Не найден пользователь с указаным id');
        }

        if (Yii::$app->request->isAjax && $user->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($user);
        }

        if ($user->load((Yii::$app->request->post()))) {

            $pass = $user->generatePassword(6);
            $user->setPassword($pass);
            $user->generateAuthKey();

            $user->email = trim($user->email);
            $user->username = trim($user->username);

            $this->saveUserAndSandMail($user, $pass);
        }

        return $this->render(
            'user-form', [
                'user' => $user,
            ]
        );
    }

    private function saveUserAndSandMail(User $user, $pass)
    {
        if ($user->save()) {

            // письмо с логином/паролем для самого добавленного менеджера
            $messageToManager = $this->renderPartial('messages/_message-create-or-update-manager.php', [
                'user' => $user,
                'pass' => $pass
            ]);

            if (!($tmp = User::sendMessage($messageToManager, $user->email, Yii::$app->params['mailToManager_subjectAboutCreateOrUpdateUser']))) {
                throw new HttpException(501, 'Не удалось отправить пиьсмо пользователю');
            }

            return $this->redirect(['/admin/default/update-manager', 'id' => $user->id]);
        } else {
            throw new HttpException(501, 'Не удалось сохранить пользователя');
        }
    }

}