<?php

use yii\helpers\Inflector;
use yii\helpers\StringHelper;

/* @var $this yii\web\View */
/* @var $generator yii\gii\generators\crud\Generator */

echo "<?php\n";
?>

use yii\helpers\Html;
use shirase\form\ActiveForm;
use kartik\daterange\DateRangePicker;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;

/* @var $this yii\web\View */
/* @var $model <?= ltrim($generator->searchModelClass, '\\') ?> */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="search-form <?= Inflector::camel2id(StringHelper::basename($generator->modelClass)) ?>-search" id="<?= Inflector::camel2id(StringHelper::basename($generator->modelClass)) ?>-search">

    <?= "<?php " ?>$form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
<?php if ($generator->enablePjax): ?>
        'options' => [
            'data-pjax' => 1
        ],
<?php endif; ?>
    ]); ?>

<?php
$count = 0;
foreach ($generator->getColumnNames() as $attribute) {
    if (++$count < 6) {
        if ($s = $generator->generateActiveSearchField($attribute)) echo "    <?= " . $s . " ?>\n\n";
    } else {
        if ($s = $generator->generateActiveSearchField($attribute)) echo "    <?php //echo " . $s . " ?>\n\n";
    }
}
?>
    <div class="form-group">
        <?= "<?= " ?>Html::submitButton(<?= $generator->generateString('Search') ?>, ['class' => 'btn btn-primary']) ?>
        <?= "<?= " ?>Html::a(<?= $generator->generateString('Reset') ?>, Url::current(['TestSearch'=>null]), ['class' => 'btn btn-default']) ?>
    </div>

    <?= "<?php " ?>ActiveForm::end(); ?>

</div>
<?= '<?php ' ?>$this->registerJs('if(jQuery.pjax && jQuery("#<?= Inflector::camel2id(StringHelper::basename($generator->modelClass)) ?>-grid-pjax").length) {jQuery(document).on(\'submit\', "#<?= Inflector::camel2id(StringHelper::basename($generator->modelClass)) ?>-search form", function (event) {jQuery.pjax.submit(event, \'#<?= Inflector::camel2id(StringHelper::basename($generator->modelClass)) ?>-grid-pjax\', {"push":true,"replace":false,"timeout":1000,"scrollTo":false});});}'); ?>
