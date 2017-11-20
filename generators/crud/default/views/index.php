<?php

use yii\helpers\Inflector;
use yii\helpers\Json;
use yii\helpers\StringHelper;

/* @var $this yii\web\View */
/* @var $generator yii\gii\generators\crud\Generator */

$urlParams = $generator->generateUrlParams();
$nameAttribute = $generator->getNameAttribute();

echo "<?php\n";
?>

use yii\helpers\Url;
use yii\helpers\Html;
use <?= $generator->indexWidgetType === 'grid' ? "kartik\\grid\\GridView" : "yii\\widgets\\ListView" ?>;
<?= $generator->enablePjax ? 'use yii\widgets\Pjax;' : '' ?>

/* @var $this yii\web\View */
<?= !empty($generator->searchModelClass) ? "/* @var \$searchModel " . ltrim($generator->searchModelClass, '\\') . " */\n" : '' ?>
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = <?= $generator->generateString(Inflector::pluralize(Inflector::camel2words(StringHelper::basename($generator->modelClass)))) ?>;
$this->params['breadcrumbs'][] = $this->title;
/** @var \yii\web\Controller $controller */
$controller = $this->context;
$controller->layout = 'common';
?>
<div class="<?= Inflector::camel2id(StringHelper::basename($generator->modelClass)) ?>-index">
<?php if(!empty($generator->searchModelClass)): ?>
    <?= "<?php /* ?>\n" ?>
    <div class="box collapsed-box">
        <div class="box-header">
            <div class="box-title" onclick="$.AdminLTE.boxWidget.collapse($(this).next().find('button'))" style="cursor: pointer">Поиск</div>
            <div class="box-tools pull-right"><button class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-plus"></i></button></div>
        </div>
        <div class="box-body">
            <?= "<?php " ?>echo $this->render('_search', ['model' => $searchModel]); ?>
        </div>
    </div>
    <?= "<?php */ ?>\n" ?>
<?php endif; ?>

    <div class="box">
        <div class="box-body">
            <h1><?= "<?= " ?>Html::encode($this->title) ?></h1>
<?= $generator->enablePjax ? "            <?php Pjax::begin(); ?>\n" : '' ?>
            <p>
                <?= "<?php " ?>if (\Yii::$app->user->can('/'.$this->context->uniqueId.'/create')) echo Html::a(<?= $generator->generateString('Create') ?>, ['create']+$this->context->actionParams, ['class' => 'btn btn-success', 'data-pjax'=>0]) ?>
            </p>
<?php if ($generator->indexWidgetType === 'grid'): ?>
            <?= "<?= " ?>GridView::widget([
                'id' => '<?= Inflector::camel2id(StringHelper::basename($generator->modelClass)) ?>-grid',
                <?= $generator->enablePjax ? '\'pjax\' => false,'."\n" : '' ?>
                'dataProvider' => $dataProvider,
                <?= (!empty($generator->searchModelClass) ? "//'filterModel' => \$searchModel,\n                " : '') . "'columns' => ".(($trColumns = $generator->generateTransliterableColumns()) ? "array_merge(\n".$trColumns."\n                " : '')."[\n"; ?>
                    ['class' => 'shirase\grid\sortable\SerialColumn'],

<?php
$count = 0;
if (($tableSchema = $generator->getTableSchema()) === false) {
    foreach ($generator->getColumnNames() as $name) {
        if (++$count < 10) {
            echo "            '" . $name . "',\n";
        } else {
            echo "            //'" . $name . "',\n";
        }
    }
} else {
    $prefix = '';
    foreach ($tableSchema->columns as $column) {
        if($column->isPrimaryKey) continue;
        if($format = $generator->generateColumnFormat($column)) {
            if (++$count == 10) {
                $prefix = '//';
            }
            if(is_array($format)) {
                foreach($format as $line) {
                    echo "                    ".$prefix.$line.",\n";
                }
            } elseif($format == 'boolean') {
                echo "                    ".$prefix."['class'=>'kartik\grid\BooleanColumn', 'attribute'=>'$column->name'],\n";
            } else {
                echo "                    ".$prefix."['attribute'=>'$column->name'".($format === 'text' ? "" : ", 'format'=>'$format'")."],\n";
            }
        }
    }
}
?>

                    [
                        'class' => 'kartik\grid\ActionColumn',
                        'visibleButtons'=>[
                            'view' => \Yii::$app->user->can('/' . \common\components\helpers\Url::normalizeRoute('view')),
                            'update' => \Yii::$app->user->can('/' . \common\components\helpers\Url::normalizeRoute('update')),
                            'delete' => \Yii::$app->user->can('/' . \common\components\helpers\Url::normalizeRoute('delete')),
                        ],
                        'urlCreator' =>
                            function ($action, $model, $key, $index) {
                                $params = is_array($key) ? $key : ['id' => (string) $key];
                                $params[0] = $action;
                                return Url::toRoute($params+$this->context->actionParams);
                            }
                    ],
                ]
<?php if ($trColumns) echo "                )\n"; ?>
            ]); ?>
<?= $generator->enablePjax ? "            <?php Pjax::end(); ?>\n" : '' ?>
<?php else: ?>
    <?= '<?php' ?> \shirase\grid\sortable\Sortable::begin(['id'=>'<?= Inflector::camel2id(StringHelper::basename($generator->modelClass)) ?>-index-sortable', 'dataProvider'=>$dataProvider, 'sortItemsSelector'=>'.item']); ?>
<?= $generator->enablePjax ? "    <?php Pjax::begin(); ?>\n" : '' ?>
    <?= "<?= " ?>ListView::widget([
        'dataProvider' => $dataProvider,
        'layout' => '<div class="row">{items}</div>{pager}',
        'itemOptions' => ['class' => 'item col-md-3'],
        'itemView' => '_item',
    ]) ?>
<?= $generator->enablePjax ? "    <?php Pjax::end(); ?>\n" : '' ?>
    <?= '<?php' ?> \shirase\grid\sortable\Sortable::end() ?>
<?php endif; ?>
        </div>
    </div>
</div>
