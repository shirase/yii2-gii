<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\gii\generators\page_plugin;

use Yii;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\db\Connection;
use yii\db\Schema;
use yii\db\TableSchema;
use yii\gii\CodeFile;
use yii\helpers\Inflector;
use yii\base\NotSupportedException;
use yii\helpers\Json;

/**
 * This generator will generate one or multiple ActiveRecord classes for the specified database table.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Generator extends \yii\gii\Generator
{
    public $pluginName;
    public $prefix;

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return 'Page plugin Generator';
    }

    /**
     * @inheritdoc
     */
    public function getDescription()
    {
        return 'This generator generates an page plugin and migration.';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array_merge(parent::rules(), [
            [['prefix'], 'string'],
            [['pluginName'], 'filter', 'filter' => 'trim'],
        ]);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            'pluginName' => 'Plugin Name',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function hints()
    {
        return array_merge(parent::hints(), [
            'pluginName' => 'Plugin name',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function autoCompleteData()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function requiredTemplates()
    {
        return ['plugin.php', 'migration.php'];
    }

    /**
     * @inheritdoc
     */
    public function stickyAttributes()
    {
        return array_merge(parent::stickyAttributes());
    }

    /**
     * @inheritdoc
     */
    public function generate()
    {
        $files = [];

        if (!$this->prefix) {
            $this->prefix = 'm' . gmdate('ymd_His');
        }

        $files[] = new CodeFile(
            Yii::getAlias('@common/plugins/page_type/' . $this->pluginName) . '/Plugin.php',
            $this->render('plugin.php', [])
        );

        $files[] = new CodeFile(
            Yii::getAlias('@common/migrations/db') . '/'.$this->prefix . '_page_type_' . $this->pluginName.'.php',
            $this->render('migration.php', [])
        );

        return $files;
    }
}
