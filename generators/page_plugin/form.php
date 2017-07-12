<?php

use yii\gii\generators\model\Generator;

/* @var $this yii\web\View */
/* @var $form yii\widgets\ActiveForm */
/* @var $generator yii\gii\generators\model\Generator */

echo $form->field($generator, 'pluginName')->textInput();
echo $form->field($generator, 'prefix')->hiddenInput()->label(false);