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
?>
<div class="<?= Inflector::camel2id(StringHelper::basename($generator->modelClass)) ?>-index">

    <h1><?= "<?= " ?>Html::encode($this->title) ?></h1>
<?php if(!empty($generator->searchModelClass)): ?>
<?= "    <?php " . ($generator->indexWidgetType === 'grid' ? "//" : "//") ?>echo $this->render('_search', ['model' => $searchModel]); ?>
<?php endif; ?>

    <p>
        <?= "<?php " ?>if (\Yii::$app->user->can('/'.$this->context->uniqueId.'/create')) echo Html::a(<?= $generator->generateString('Create ' . Inflector::camel2words(StringHelper::basename($generator->modelClass))) ?>, ['create']+$this->context->actionParams, ['class' => 'btn btn-success']) ?>
    </p>
<?php if ($generator->indexWidgetType === 'grid'): ?>
    <?= "<?= " ?>GridView::widget([
        'id' => '<?= Inflector::camel2id(StringHelper::basename($generator->modelClass)) ?>-grid',
        <?= $generator->enablePjax ? '\'pjax\' => true,'."\n" : '' ?>
        'dataProvider' => $dataProvider,
        <?= !empty($generator->searchModelClass) ? "//'filterModel' => \$searchModel,\n        'columns' => [\n" : "'columns' => [\n"; ?>
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
    foreach ($tableSchema->columns as $column) {
        if($column->isPrimaryKey) continue;
        if($format = $generator->generateColumnFormat($column)) {
            $prefix = '';
            if (++$count == 10) {
                $prefix = '//';
            }
            if(is_array($format)) {
                foreach($format as $line) {
                    echo "            ".$prefix.$line.",\n";
                }
            } elseif($format == 'boolean') {
                echo "            ".$prefix."['class'=>'kartik\grid\BooleanColumn', 'attribute'=>'$column->name'],\n";
            } else {
                echo "            ".$prefix."['attribute'=>'$column->name'".($format === 'text' ? "" : ", 'format'=>'$format'")."],\n";
                //echo "            ".$prefix."'" . $column->name . ($format === 'text' ? "" : ":" . $format) . "',\n";
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
        ],
    ]); ?>
<?php else: ?>
    <?= $generator->enablePjax ? '<?php Pjax::begin(); ?>' : '' ?>
    <?= "<?= " ?>ListView::widget([
        'dataProvider' => $dataProvider,
        'itemOptions' => ['class' => 'item'],
        'itemView' => function ($model, $key, $index, $widget) {
            return Html::a(Html::encode($model-><?= $nameAttribute ?>), ['view', <?= $urlParams ?>]);
        },
    ]) ?>
    <?= $generator->enablePjax ? '<?php Pjax::end(); ?>' : '' ?>
<?php endif; ?>
</div>
