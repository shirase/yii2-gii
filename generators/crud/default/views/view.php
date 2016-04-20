<?php

use yii\helpers\Inflector;
use yii\helpers\StringHelper;

/* @var $this yii\web\View */
/* @var $generator yii\gii\generators\crud\Generator */

$urlParams = $generator->generateUrlParams();

echo "<?php\n";
?>

use yii\helpers\Html;
use kartik\detail\DetailView;
use kartik\datecontrol\DateControl;

/* @var $this yii\web\View */
/* @var $model <?= ltrim($generator->modelClass, '\\') ?> */

$this->title = $model-><?= $generator->getNameAttribute() ?>;
$this->params['breadcrumbs'][] = ['label' => <?= $generator->generateString(Inflector::pluralize(Inflector::camel2words(StringHelper::basename($generator->modelClass)))) ?>, 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="<?= Inflector::camel2id(StringHelper::basename($generator->modelClass)) ?>-view">

    <h1><?= "<?= " ?>Html::encode($this->title) ?></h1>

    <p>
        <?= "<?= " ?>Html::a(<?= $generator->generateString('Back') ?>, ['index', 'returned'=>true], ['class' => 'btn btn-default']) ?>
        <!--<?= "<?= " ?>Html::a(<?= $generator->generateString('Update') ?>, ['update', <?= $urlParams ?>], ['class' => 'btn btn-primary']) ?>-->
        <!--<?= "<?= " ?>Html::a(<?= $generator->generateString('Delete') ?>, ['delete', <?= $urlParams ?>], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => <?= $generator->generateString('Are you sure you want to delete this item?') ?>,
                'method' => 'post',
            ],
        ]) ?>-->
    </p>

    <?= "<?= " ?>DetailView::widget([
        'model' => $model,
        'attributes' => [
<?php
if (($tableSchema = $generator->getTableSchema()) === false) {
    foreach ($generator->getColumnNames() as $name) {
        echo "            '" . $name . "',\n";
    }
} else {
    foreach ($generator->getTableSchema()->columns as $column) {
        if ($format = $generator->generateColumnViewFormat($column)) {
            if(is_array($format)) {
                foreach($format as $line) {
                    echo "            ".$line.",\n";
                }
            } elseif ($column->type === 'datetime' || $column->type === 'timestamp') {
                echo "            [
                'attribute'=>'$column->name',
                'format'=>'datetime',
                'type'=>DetailView::INPUT_WIDGET,
                'widgetOptions'=> [
                    'class'=>DateControl::classname(),
                    'type'=>DateControl::FORMAT_DATE,
                    'saveFormat'=>((\$m = Yii::\$app->getModule('datecontrol')) ? \$m->saveSettings['datetime'] : 'php:Y-m-d H:i:s'),
                ]
            ],\n";
            } elseif ($column->type === 'date') {
                echo "            [
                'attribute'=>'$column->name',
                'format'=>'date',
                'type'=>DetailView::INPUT_WIDGET,
                'widgetOptions'=> [
                    'class'=>DateControl::classname(),
                    'type'=>DateControl::FORMAT_DATE
                ]
            ],\n";
            } elseif ($column->type === 'time') {
                echo "            [
                'attribute'=>'$column->name',
                'format'=>'time',
                'type'=>DetailView::INPUT_WIDGET,
                'widgetOptions'=> [
                    'class'=>DateControl::classname(),
                    'type'=>DateControl::FORMAT_TIME
                ]
            ],\n";
            } elseif ($column->phpType === 'boolean' || $column->size == 1) {
                echo "            [
                'attribute'=>'$column->name',
                'format'=>'boolean',
                'type'=>DetailView::INPUT_CHECKBOX,
            ],\n";
            } else {
                //echo "            '" . $column->name . ($format === 'text' ? "" : ":" . $format) . "',\n";
                echo "            [
                'attribute'=>'$column->name',".($format === 'text' ? "" : "\n                'format'=>'$format',")."
            ],\n";
            }
        }
    }
}
?>
        ],
        'panel'=>[
            //'heading'=>(Yii::$app->user->can('/' . \common\components\helpers\Url::normalizeRoute('update')) ? $this->title : null),
        ],
        'deleteOptions'=>[
            'url' => ['delete', $model->primaryKey()[0]=>$model->primaryKey],
            'params' => ['j-delete' => true],
        ],
    ]) ?>

</div>
