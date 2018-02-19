<?php
use yii\widgets\LinkPager;
use yii\grid\GridView;

$this->title = 'Пользователи';
$this->params['breadcrumbs'][] = $this->title;

$items = (!empty($dataProvider)) ? $dataProvider->getModels() : null;
?>


<h1><?= $this->title; ?></h1>

<div class="row">
    <div class="blog-header">
        <a href="<?= \yii\helpers\Url::to(['/admin/default/create-user'])?>" class="btn btn-info">Создать</a>
    </div>

</div>


<?php if (!empty($items)){ ?>
<div class="row">

    <div class="paginator-air2">
        <?php
        echo LinkPager::widget([
            'pagination' => $dataProvider->pagination,
        ]);
        ?>
    </div>

    <?php
    echo GridView::widget([
        'dataProvider' => $dataProvider,
        'id' => 'ordersTbl',
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            'username',
            'email',
            'phone',
            [
                'attribute' => 'created_at',
                'label' => 'Дата создания',
                'content' => function ($data) {
                    return date('d.m.Y H:i', $data->created_at);
                },
            ],
            [
                'attribute' => 'created_at',
                'label' => 'Дата редактирования',
                'content' => function ($data) {
                    return date('d.m.Y H:i', $data->updated_at);
                },
            ],
            [
                'label' => 'Действие',
                'content' => function ($data) {
                    if ($data->status_id == \app\models\User::STATUS_ACTIVE) {
                        return "<a class='attachUser' data-id='$data->id'>Деактивировать</a>";
                    } else  {
                        return "<a class='detachUser' data-id='$data->id'>Активировать</a>";
                    }
                },
            ]
        ],
        'emptyText' => 'Здесь будут отображаться все менеджеры зарегесированные в системе',
        'summary' => "",

    ]);
    ?>


    <div class="paginator-air2">
        <?php
        echo LinkPager::widget([
            'pagination' => $dataProvider->pagination,
        ]);
        ?>
    </div>
</div>
<?php } ?>