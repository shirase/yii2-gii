<?php

use yii\helpers\Inflector;
use yii\helpers\StringHelper;

/* @var $this yii\web\View */
/* @var $generator yii\gii\generators\crud\Generator */

/* @var $model \yii\db\ActiveRecord */
$model = new $generator->modelClass();
$safeAttributes = $model->safeAttributes();
if (empty($safeAttributes)) {
    $safeAttributes = $model->attributes();
}

echo "<?php\n";
?>

use yii\helpers\Html;
use shirase\form\ActiveForm;
use kartik\datecontrol\DateControl;
use yii\helpers\ArrayHelper;

/* @var $this yii\web\View */
/* @var $model <?= ltrim($generator->modelClass, '\\') ?> */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="<?= Inflector::camel2id(StringHelper::basename($generator->modelClass)) ?>-form">

    <?= "<?php " ?>$form = ActiveForm::begin(); ?>

<?php foreach ($generator->getColumnNames() as $attribute) {
    if (in_array($attribute, $safeAttributes)) {
        echo "    <?= " . $generator->generateActiveField($attribute) . " ?>\n\n";
    }
} ?>
<?= $generator->generateTransliterableFields() ?>
<?php foreach ($generator->getManyRelations() as $attribute => $relation) {
    /** @var \yii\db\ActiveRecord $relationModel */
    $relationModel = new $relation->modelClass;
    $nameField = $relationModel->primaryKey()[0];
    foreach ($relationModel->getValidators() as $validator) {
        if ($validator instanceof \yii\validators\StringValidator) {
            $nameField = $validator->attributes[0];
            break;
        }
    }
    echo "    <?= \$form->field(\$model, '$attribute')->widget(kartik\\select2\\Select2::className(), ['options'=>['multiple'=>true], 'data'=>ArrayHelper::map({$relation->modelClass}::find()->all(), 'id', '{$nameField}')]) ?>\n\n";
} ?>
    <div class="form-group">
        <?= "<?= " ?>Html::submitButton(<?= $generator->generateString('Save') ?>, ['class' => 'btn btn-success']) ?>
        <?= "<?= " ?>Html::a(<?= $generator->generateString('Back') ?>, ['index', 'returned'=>true], ['class' => 'btn btn-default']) ?>
    </div>

    <?= "<?php " ?>ActiveForm::end(); ?>

</div>
