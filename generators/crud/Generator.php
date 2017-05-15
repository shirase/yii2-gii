<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\gii\generators\crud;

use Yii;
use yii\db\ActiveRecord;
use yii\db\BaseActiveRecord;
use yii\db\Schema;
use yii\gii\CodeFile;
use yii\helpers\Inflector;
use yii\helpers\VarDumper;
use yii\validators\StringValidator;
use yii\web\Controller;

/**
 * Generates CRUD
 *
 * @property array $columnNames Model column names. This property is read-only.
 * @property string $controllerID The controller ID (without the module ID prefix). This property is
 * read-only.
 * @property array $searchAttributes Searchable attributes. This property is read-only.
 * @property bool|\yii\db\TableSchema $tableSchema This property is read-only.
 * @property string $viewPath The controller view path. This property is read-only.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Generator extends \yii\gii\Generator
{
    public $modelClass = 'common\models\\';
    public $controllerClass;
    public $viewPath;
    public $baseControllerClass = 'common\components\web\Controller';
    public $indexWidgetType = 'grid';
    public $searchModelClass = '';
    public $enableI18N = true;
    public $messageCategory = 'backend';

    /**
     * @var bool whether to wrap the `GridView` or `ListView` widget with the `yii\widgets\Pjax` widget
     * @since 2.0.5
     */
    public $enablePjax = true;


    /**
     * @inheritdoc
     */
    public function getName()
    {
        return 'CRUD Generator';
    }

    /**
     * @inheritdoc
     */
    public function getDescription()
    {
        return 'This generator generates a controller and views that implement CRUD (Create, Read, Update, Delete)
            operations for the specified data model.';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array_merge(parent::rules(), [
            [['controllerClass', 'modelClass', 'searchModelClass', 'baseControllerClass'], 'filter', 'filter' => 'trim'],
            [['modelClass', 'controllerClass', 'viewPath', 'baseControllerClass', 'indexWidgetType'], 'required'],
            [['searchModelClass'], 'compare', 'compareAttribute' => 'modelClass', 'operator' => '!==', 'message' => 'Search Model Class must not be equal to Model Class.'],
            [['modelClass', 'controllerClass', 'baseControllerClass', 'searchModelClass'], 'match', 'pattern' => '/^[\w\\\\]*$/', 'message' => 'Only word characters and backslashes are allowed.'],
            [['modelClass'], 'validateClass', 'params' => ['extends' => BaseActiveRecord::className()]],
            [['baseControllerClass'], 'validateClass', 'params' => ['extends' => Controller::className()]],
            [['controllerClass'], 'match', 'pattern' => '/Controller$/', 'message' => 'Controller class name must be suffixed with "Controller".'],
            [['controllerClass'], 'match', 'pattern' => '/(^|\\\\)[A-Z][^\\\\]+Controller$/', 'message' => 'Controller class name must start with an uppercase letter.'],
            [['controllerClass', 'searchModelClass'], 'validateNewClass'],
            [['indexWidgetType'], 'in', 'range' => ['grid', 'list']],
            [['modelClass'], 'validateModelClass'],
            [['enableI18N', 'enablePjax'], 'boolean'],
            [['messageCategory'], 'validateMessageCategory', 'skipOnEmpty' => false],
            ['viewPath', 'safe'],
        ]);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            'modelClass' => 'Model Class',
            'controllerClass' => 'Controller Class',
            'viewPath' => 'View Path',
            'baseControllerClass' => 'Base Controller Class',
            'indexWidgetType' => 'Widget Used in Index Page',
            'searchModelClass' => 'Search Model Class',
            'enablePjax' => 'Enable Pjax',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function hints()
    {
        return array_merge(parent::hints(), [
            'modelClass' => 'This is the ActiveRecord class associated with the table that CRUD will be built upon.
                You should provide a fully qualified class name, e.g., <code>app\models\Post</code>.',
            'controllerClass' => 'This is the name of the controller class to be generated. You should
                provide a fully qualified namespaced class (e.g. <code>app\controllers\PostController</code>),
                and class name should be in CamelCase with an uppercase first letter. Make sure the class
                is using the same namespace as specified by your application\'s controllerNamespace property.',
            'viewPath' => 'Specify the directory for storing the view scripts for the controller. You may use path alias here, e.g.,
                <code>/var/www/basic/controllers/views/post</code>, <code>@app/views/post</code>.',
            'baseControllerClass' => 'This is the class that the new CRUD controller class will extend from.
                You should provide a fully qualified class name, e.g., <code>yii\web\Controller</code>.',
            'indexWidgetType' => 'This is the widget type to be used in the index page to display list of the models.
                You may choose either <code>GridView</code> or <code>ListView</code>',
            'searchModelClass' => 'This is the name of the search model class to be generated. You should provide a fully
                qualified namespaced class name, e.g., <code>app\models\PostSearch</code>.',
            'enablePjax' => 'This indicates whether the generator should wrap the <code>GridView</code> or <code>ListView</code>
                widget on the index page with <code>yii\widgets\Pjax</code> widget. Set this to <code>true</code> if you want to get
                sorting, filtering and pagination without page refreshing.',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function requiredTemplates()
    {
        return ['controller.php'];
    }

    /**
     * @inheritdoc
     */
    public function stickyAttributes()
    {
        return array_merge(parent::stickyAttributes(), ['baseControllerClass', 'indexWidgetType']);
    }

    /**
     * Checks if model class is valid
     */
    public function validateModelClass()
    {
        /* @var $class ActiveRecord */
        $class = $this->modelClass;
        $pk = $class::primaryKey();
        if (empty($pk)) {
            $this->addError('modelClass', "The table associated with $class must have primary key(s).");
        }
    }

    /**
     * @inheritdoc
     */
    public function generate()
    {
        $controllerFile = Yii::getAlias('@' . str_replace('\\', '/', ltrim($this->controllerClass, '\\')) . '.php');

        $files = [
            new CodeFile($controllerFile, $this->render('controller.php')),
        ];

        if (!empty($this->searchModelClass)) {
            $searchModel = Yii::getAlias('@' . str_replace('\\', '/', ltrim($this->searchModelClass, '\\') . '.php'));
            $files[] = new CodeFile($searchModel, $this->render('search.php'));
        }

        $viewPath = $this->getViewPath();
        $templatePath = $this->getTemplatePath() . '/views';
        foreach (scandir($templatePath) as $file) {
            if (empty($this->searchModelClass) && $file === '_search.php') {
                continue;
            }
            if ($file === '_item.php' && $this->indexWidgetType != 'list') {
                continue;
            }
            if (is_file($templatePath . '/' . $file) && pathinfo($file, PATHINFO_EXTENSION) === 'php') {
                $files[] = new CodeFile("$viewPath/$file", $this->render("views/$file"));
            }
        }

        return $files;
    }

    /**
     * @return string the controller ID (without the module ID prefix)
     */
    public function getControllerID()
    {
        $pos = strrpos($this->controllerClass, '\\');
        $class = substr(substr($this->controllerClass, $pos + 1), 0, -10);

        return Inflector::camel2id($class);
    }

    /**
     * @return string the controller view path
     */
    public function getViewPath()
    {
        return Yii::getAlias($this->viewPath);
    }

    public function getNameAttribute()
    {
        foreach ($this->getColumnNames() as $name) {
            if (!strcasecmp($name, 'name') || !strcasecmp($name, 'title')) {
                return $name;
            }
        }
        /* @var $class \yii\db\ActiveRecord */
        $class = $this->modelClass;
        $pk = $class::primaryKey();

        return $pk[0];
    }

    /**
     * Generates code for active field
     * @param string $attribute
     * @return string
     */
    public function generateActiveField($attribute)
    {
        $relations = $this->getRelations();

        $tableSchema = $this->getTableSchema();
        if ($tableSchema === false || !isset($tableSchema->columns[$attribute])) {
            if (preg_match('/^(password|pass|passwd|passcode)$/i', $attribute)) {
                return "\$form->field(\$model, '$attribute')->passwordInput()";
            } else {
                return "\$form->field(\$model, '$attribute')";
            }
        }
        $column = $tableSchema->columns[$attribute];
        if (($p=strpos($column->name, '_path'))!==false) {
            return "\$form->field(\$model, '".substr($attribute, 0, $p)."')->widget(shirase55\\filekit\\widget\\Upload::className())";
        } elseif (isset($relations[$column->name])) {
            /** @var ActiveRecord $relationModel */
            $relationModel = new $relations[$column->name]->modelClass;
            $nameField = $relationModel->primaryKey()[0];
            foreach ($relationModel->getValidators() as $validator) {
                if ($validator instanceof StringValidator) {
                    $nameField = $validator->attributes[0];
                }
            }
            return "\$form->field(\$model, '$attribute')->widget(kartik\\select2\\Select2::className(), ['data'=>[''=>'-']+ArrayHelper::map({$relations[$column->name]->modelClass}::find()->all(), '".$relationModel->primaryKey()[0]."', '{$nameField}')])";
        } elseif ($column->phpType === 'boolean' || $column->size == 1) {
            return "\$form->field(\$model, '$attribute')->dropDownList(['1'=>Yii::t('common', 'Yes'), '0'=>Yii::t('common', 'No')])";
        } elseif ($column->type === 'text') {
            return "\$form->field(\$model, '$attribute')->textarea(['rows' => 6])";
        } elseif($column->type === 'date'){
            return "\$form->field(\$model, '$attribute')->widget(DateControl::classname(), ['type'=>DateControl::FORMAT_DATE])";
        } elseif($column->type === 'time'){
            return "\$form->field(\$model, '$attribute')->widget(DateControl::classname(), ['type'=>DateControl::FORMAT_TIME])";
        } elseif($column->type === 'datetime' || $column->type === 'timestamp'){
            return "\$form->field(\$model, '$attribute')->widget(DateControl::classname(), ['type'=>DateControl::FORMAT_DATE, 'saveFormat'=>((\$m = Yii::\$app->getModule('datecontrol')) ? \$m->saveSettings['datetime'] : 'php:Y-m-d H:i:s')])";
        } else {
            if (preg_match('/^(password|pass|passwd|passcode)$/i', $column->name)) {
                $input = 'passwordInput';
            } else {
                $input = 'textInput';
            }
            if (is_array($column->enumValues) && count($column->enumValues) > 0) {
                $dropDownOptions = [];
                foreach ($column->enumValues as $enumValue) {
                    $dropDownOptions[$enumValue] = Inflector::humanize($enumValue);
                }
                return "\$form->field(\$model, '$attribute')->dropDownList("
                    . preg_replace("/\n\s*/", ' ', VarDumper::export($dropDownOptions)).", ['prompt' => ''])";
            } elseif ($column->phpType !== 'string' || $column->size === null) {
                return "\$form->field(\$model, '$attribute')->$input()";
            } else {
                return "\$form->field(\$model, '$attribute')->$input(['maxlength' => true])";
            }
        }
    }

    /**
     * Generates code for active search field
     * @param string $attribute
     * @return string
     */
    public function generateActiveSearchField($attribute)
    {
        $relations = $this->getRelations();

        $tableSchema = $this->getTableSchema();
        if ($tableSchema === false) {
            return "\$form->field(\$model, '$attribute')";
        }
        $column = $tableSchema->columns[$attribute];
        if (strpos($column->name, '_path')!==false) return false;
        if (isset($relations[$column->name])) {
            return "\$form->field(\$model, '$attribute')->widget(kartik\\select2\\Select2::className(), ['data'=>[''=>'-']+ArrayHelper::map({$relations[$column->name]->modelClass}::find()->all(), 'id', 'name')])";
        } elseif ($column->name=='lft' || $column->name=='rgt' || $column->name=='depth' || $column->name=='pos' || $column->name=='bpath' || $column->name=='pid'  || $column->name=='created_at' || $column->name=='updated_at' || $column->name=='author_id' || $column->name=='updater_id') {
            return '';
        } elseif ($column->phpType === 'boolean' || $column->size == 1) {
            return "\$form->field(\$model, '$attribute')->dropDownList([''=>'-', '1'=>Yii::t('common', 'Yes'), '0'=>Yii::t('common', 'No')])";
        } elseif($column->type === 'date'){
            return "\$form->field(\$model, '$attribute')->widget(DateRangePicker::classname(), ['hideInput'=>true, 'convertFormat'=>true, 'pluginOptions'=>['locale'=>['format'=>((\$m=\Yii::\$app->getModule('datecontrol')) ? \kartik\datecontrol\Module::parseFormat(\$m->displaySettings['date'], 'date') : 'Y-m-d')]]])";
        } elseif($column->type === 'datetime' || $column->type === 'timestamp'){
            return "\$form->field(\$model, '$attribute')->widget(DateRangePicker::classname(), ['hideInput'=>true, 'convertFormat'=>true, 'pluginOptions'=>['locale'=>['format'=>((\$m=\Yii::\$app->getModule('datecontrol')) ? \kartik\datecontrol\Module::parseFormat(\$m->displaySettings['date'], 'date') : 'Y-m-d')]]])";
        } else {
            return "\$form->field(\$model, '$attribute')";
        }
    }

    /**
     * Generates column format
     * @param \yii\db\ColumnSchema $column
     * @return string
     */
    public function generateColumnFormat($column)
    {
        $relations = $this->getRelations();

        if (isset($relations[$column->name])) {
            return [
                '[\'attribute\'=>\''.$column->name.'\', \'value\'=>function($model) {return $model->'.$this->generateRelationName($column->name).'->name;}]'
            ];
        } elseif($column->name=='lft' || $column->name=='rgt' || $column->name=='depth' || $column->name=='pos' || $column->name=='bpath' || $column->name=='pid'  || $column->name=='created_at' || $column->name=='updated_at' || $column->name=='author_id' || $column->name=='updater_id') {
            return '';
        } elseif ($column->phpType === 'boolean' || $column->size==1) {
            return 'boolean';
        } elseif ($column->type === 'text') {
            return 'ntext';
        } elseif($column->type === 'date'){
            return 'date';
        } elseif($column->type === 'time'){
            return 'time';
        } elseif($column->type === 'datetime' || $column->type === 'timestamp'){
            return 'datetime';
        } elseif (stripos($column->name, 'email') !== false) {
            return 'email';
        } elseif (preg_match('/(\b|[_-])url(\b|[_-])/i', $column->name)) {
            return 'url';
        } else {
            return 'text';
        }
    }

    /**
     * Generates column format
     * @param \yii\db\ColumnSchema $column
     * @return string
     */
    public function generateColumnViewFormat($column)
    {
        $relations = $this->getRelations();

        if (isset($relations[$column->name])) {
            return [
                '[\'attribute\'=>\''.$column->name.'\', \'value\'=>$model->'.$this->generateRelationName($column->name).'->name]'
            ];
        }

        return $this->generateColumnFormat($column);
    }

    /**
     * Generates validation rules for the search model.
     * @return array the generated validation rules
     */
    public function generateSearchRules()
    {
        if (($table = $this->getTableSchema()) === false) {
            return ["[['" . implode("', '", $this->getColumnNames()) . "'], 'safe']"];
        }
        $types = [];
        foreach ($table->columns as $column) {
            switch ($column->type) {
                case Schema::TYPE_SMALLINT:
                case Schema::TYPE_INTEGER:
                case Schema::TYPE_BIGINT:
                    $types['integer'][] = $column->name;
                    break;
                case Schema::TYPE_BOOLEAN:
                    $types['boolean'][] = $column->name;
                    break;
                case Schema::TYPE_FLOAT:
                case Schema::TYPE_DOUBLE:
                case Schema::TYPE_DECIMAL:
                case Schema::TYPE_MONEY:
                    $types['number'][] = $column->name;
                    break;
                case Schema::TYPE_DATE:
                case Schema::TYPE_TIME:
                case Schema::TYPE_DATETIME:
                case Schema::TYPE_TIMESTAMP:
                default:
                    $types['safe'][] = $column->name;
                    break;
            }
        }

        $rules = [];
        foreach ($types as $type => $columns) {
            $rules[] = "[['" . implode("', '", $columns) . "'], '$type']";
        }

        return $rules;
    }

    /**
     * @return array searchable attributes
     */
    public function getSearchAttributes()
    {
        return $this->getColumnNames();
    }

    /**
     * Generates the attribute labels for the search model.
     * @return array the generated attribute labels (name => label)
     */
    public function generateSearchLabels()
    {
        /* @var $model \yii\base\Model */
        $model = new $this->modelClass();
        $attributeLabels = $model->attributeLabels();
        $labels = [];
        foreach ($this->getColumnNames() as $name) {
            if (isset($attributeLabels[$name])) {
                $labels[$name] = $attributeLabels[$name];
            } else {
                if (!strcasecmp($name, 'id')) {
                    $labels[$name] = 'ID';
                } else {
                    $label = Inflector::camel2words($name);
                    if (!empty($label) && substr_compare($label, ' id', -3, 3, true) === 0) {
                        $label = substr($label, 0, -3) . ' ID';
                    }
                    $labels[$name] = $label;
                }
            }
        }

        return $labels;
    }

    /**
     * Generates search conditions
     * @return array
     */
    public function generateSearchConditions()
    {
        $columns = [];
        if (($table = $this->getTableSchema()) === false) {
            $class = $this->modelClass;
            /* @var $model \yii\base\Model */
            $model = new $class();
            foreach ($model->attributes() as $attribute) {
                $columns[$attribute] = 'unknown';
            }
        } else {
            foreach ($table->columns as $column) {
                $columns[$column->name] = $column->type;
            }
        }

        $likeConditions = [];
        $hashConditions = [];
        foreach ($columns as $column => $type) {
            if($column==='lft' || $column==='rgt' || $column==='depth' || $column==='pos' || $column->name=='bpath') {
                continue;
            }

            switch ($type) {
                case Schema::TYPE_SMALLINT:
                case Schema::TYPE_INTEGER:
                case Schema::TYPE_BIGINT:
                case Schema::TYPE_BOOLEAN:
                case Schema::TYPE_FLOAT:
                case Schema::TYPE_DOUBLE:
                case Schema::TYPE_DECIMAL:
                case Schema::TYPE_MONEY:
                case Schema::TYPE_TIME:
                    $hashConditions[] = "'{$column}' => \$this->{$column},";
                    break;
                case Schema::TYPE_DATE:
                case Schema::TYPE_DATETIME:
                case Schema::TYPE_TIMESTAMP:
                    $likeConditions[] = "->andFilterDateRange('{$column}', \$this->{$column})";
                    break;
                default:
                    $likeKeyword = $this->getClassDbDriverName() === 'pgsql' ? 'ilike' : 'like';
                    $likeConditions[] = "->andFilterWhere(['{$likeKeyword}', '{$column}', \$this->{$column}])";                    
                    break;
            }
        }

        $conditions = [];
        if (!empty($hashConditions)) {
            $conditions[] = "\$query->andFilterWhere([\n"
                . str_repeat(' ', 12) . implode("\n" . str_repeat(' ', 12), $hashConditions)
                . "\n" . str_repeat(' ', 8) . "]);\n";
        }
        if (!empty($likeConditions)) {
            $conditions[] = "\$query" . implode("\n" . str_repeat(' ', 12), $likeConditions) . ";\n";
        }

        return $conditions;
    }

    /**
     * Generates URL parameters
     * @return string
     */
    public function generateUrlParams()
    {
        /* @var $class ActiveRecord */
        $class = $this->modelClass;
        $pks = $class::primaryKey();
        if (count($pks) === 1) {
            if (is_subclass_of($class, 'yii\mongodb\ActiveRecord')) {
                return "'id' => (string)\$model->{$pks[0]}";
            } else {
                return "'id' => \$model->{$pks[0]}";
            }
        } else {
            $params = [];
            foreach ($pks as $pk) {
                if (is_subclass_of($class, 'yii\mongodb\ActiveRecord')) {
                    $params[] = "'$pk' => (string)\$model->$pk";
                } else {
                    $params[] = "'$pk' => \$model->$pk";
                }
            }

            return implode(', ', $params);
        }
    }

    /**
     * Generates action parameters
     * @return string
     */
    public function generateActionParams()
    {
        /* @var $class ActiveRecord */
        $class = $this->modelClass;
        $pks = $class::primaryKey();
        if (count($pks) === 1) {
            return '$id';
        } else {
            return '$' . implode(', $', $pks);
        }
    }

    /**
     * Generates parameter tags for phpdoc
     * @return array parameter tags for phpdoc
     */
    public function generateActionParamComments()
    {
        /* @var $class ActiveRecord */
        $class = $this->modelClass;
        $pks = $class::primaryKey();
        if (($table = $this->getTableSchema()) === false) {
            $params = [];
            foreach ($pks as $pk) {
                $params[] = '@param ' . (substr(strtolower($pk), -2) == 'id' ? 'integer' : 'string') . ' $' . $pk;
            }

            return $params;
        }
        if (count($pks) === 1) {
            return ['@param ' . $table->columns[$pks[0]]->phpType . ' $id'];
        } else {
            $params = [];
            foreach ($pks as $pk) {
                $params[] = '@param ' . $table->columns[$pk]->phpType . ' $' . $pk;
            }

            return $params;
        }
    }

    /**
     * Returns table schema for current model class or false if it is not an active record
     * @return bool|\yii\db\TableSchema
     */
    public function getTableSchema()
    {
        /* @var $class ActiveRecord */
        $class = $this->modelClass;
        if (is_subclass_of($class, 'yii\db\ActiveRecord')) {
            return $class::getTableSchema();
        } else {
            return false;
        }
    }

    /**
     * @return array model column names
     */
    public function getColumnNames()
    {
        /* @var $class ActiveRecord */
        $class = $this->modelClass;
        if (is_subclass_of($class, 'yii\db\ActiveRecord')) {
            return $class::getTableSchema()->getColumnNames();
        } else {
            /* @var $model \yii\base\Model */
            $model = new $class();

            return $model->attributes();
        }
    }

    /**
     * @return string|null driver name of modelClass db connection.
     * In case db is not instance of \yii\db\Connection null will be returned.
     * @since 2.0.6
     */
    protected function getClassDbDriverName()
    {
        /* @var $class ActiveRecord */
        $class = $this->modelClass;
        $db = $class::getDb();
        return $db instanceof \yii\db\Connection ? $db->driverName : null;
    }

    protected $_relations;
    protected $_manyRelations;

    /**
     * @return array the generated relation declarations
     */
    public function getManyRelations()
    {
        if(isset($this->_manyRelations)) return $this->_manyRelations;

        $relations = [];

        /**
         * @var $modelClass ActiveRecord
         * @var $model ActiveRecord
         */
        $modelClass = $this->modelClass;
        $model = new $modelClass();

        /*foreach ($model->getBehaviors() as $behavior) {
            if($behavior->className() == 'voskobovich\\behaviors\\ManyToManyBehavior') {
                foreach (array_keys($behavior->relations) as $attribute) {

                }
            }
        }*/

        $modelTable = $modelClass::getTableSchema();
        $db = $modelClass::getDb();

        foreach ($db->getSchema()->getTableSchemas() as $table) {
            if (($junctionFks = $this->checkJunctionTable($table, $db)) !== false) {
                foreach ($junctionFks as $pair) {
                    list($firstKey, $secondKey) = $pair;
                    $table0 = $firstKey[0];
                    $table1 = $secondKey[0];
                    unset($firstKey[0], $secondKey[0]);
                    if ($table0 === $modelTable->name) {
                        $relationName = $this->generateRelationName($table1, true);
                        if($relation = $model->getRelation($relationName, false)) {
                            $relations[$table1.'_ids'] = $relation;
                        }
                    }/* elseif ($table1 === $modelTable->name) {
                        $relationName = $this->generateRelationName($table0, true);
                        if($relation = $model->getRelation($relationName, false)) {
                            $relations[$relationName] = $relation;
                        }
                    }*/
                }
            }
        }

        $this->_manyRelations = $relations;

        return $relations;
    }

    /**
     * @return array the generated relation declarations
     */
    public function getRelations()
    {
        if(isset($this->_relations)) return $this->_relations;

        $relations = [];

        /**
         * @var $modelClass ActiveRecord
         * @var $model ActiveRecord
         */
        $modelClass = $this->modelClass;
        $model = new $modelClass();
        $modelTable = $modelClass::getTableSchema();
        $db = $modelClass::getDb();

        foreach ($modelTable->foreignKeys as $refs) {
            $refTable = $refs[0];
            $refTableSchema = $db->getTableSchema($refTable);
            if ($refTableSchema === null) {
                // Foreign key could point to non-existing table: https://github.com/yiisoft/yii2-gii/issues/34
                continue;
            }
            unset($refs[0]);
            $fks = array_keys($refs);
            $relationName = $this->generateRelationName($fks[0], false);
            if($relation = $model->getRelation($relationName, false)) {
                $relations[$fks[0]] = $relation;
            }
        }

        $this->_relations = $relations;

        return $relations;
    }

    /**
     * Checks if the given table is a junction table, that is it has at least one pair of unique foreign keys.
     * @param \yii\db\TableSchema the table being checked
     * @return array|boolean all unique foreign key pairs if the table is a junction table,
     * or false if the table is not a junction table.
     */
    protected function checkJunctionTable($table, $db)
    {
        if (count($table->foreignKeys) < 2) {
            return false;
        }
        $uniqueKeys = [$table->primaryKey];
        try {
            $uniqueKeys = array_merge($uniqueKeys, $db->getSchema()->findUniqueIndexes($table));
        } catch (NotSupportedException $e) {
            // ignore
        }
        $result = [];
        // find all foreign key pairs that have all columns in an unique constraint
        $foreignKeys = array_values($table->foreignKeys);
        for ($i = 0; $i < count($foreignKeys); $i++) {
            $firstColumns = $foreignKeys[$i];
            unset($firstColumns[0]);

            for ($j = $i + 1; $j < count($foreignKeys); $j++) {
                $secondColumns = $foreignKeys[$j];
                unset($secondColumns[0]);

                $fks = array_merge(array_keys($firstColumns), array_keys($secondColumns));
                foreach ($uniqueKeys as $uniqueKey) {
                    if (count(array_diff(array_merge($uniqueKey, $fks), array_intersect($uniqueKey, $fks))) === 0) {
                        // save the foreign key pair
                        $result[] = [$foreignKeys[$i], $foreignKeys[$j]];
                        break;
                    }
                }
            }
        }
        return empty($result) ? false : $result;
    }

    protected $classNames;

    /**
     * Generates a class name from the specified table name.
     * @param string $tableName the table name (which may contain schema prefix)
     * @param boolean $useSchemaName should schema name be included in the class name, if present
     * @return string the generated class name
     */
    protected function generateClassName($tableName, $useSchemaName = null)
    {
        if (isset($this->classNames[$tableName])) {
            return $this->classNames[$tableName];
        }

        $schemaName = '';
        $fullTableName = $tableName;
        if (($pos = strrpos($tableName, '.')) !== false) {
            if (($useSchemaName === null && $this->useSchemaName) || $useSchemaName) {
                $schemaName = substr($tableName, 0, $pos) . '_';
            }
            $tableName = substr($tableName, $pos + 1);
        }

        /**
         * @var $modelClass ActiveRecord
         */
        $modelClass = $this->modelClass;
        $db = $modelClass::getDb();
        $patterns = [];
        $patterns[] = "/^{$db->tablePrefix}(.*?)$/";
        $patterns[] = "/^(.*?){$db->tablePrefix}$/";
        $className = $tableName;
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $tableName, $matches)) {
                $className = $matches[1];
                break;
            }
        }

        return $this->classNames[$fullTableName] = Inflector::id2camel($schemaName.$className, '_');
    }

    public function getModelNameSpace() {
        $m = explode('\\', $this->modelClass);
        array_pop($m);
        return implode('\\', $m).'\\';
    }

    /**
     * Generate a relation name for the specified key
     * @param string $key a base name that the relation name may be generated from
     * @param boolean $multiple whether this is a has-many relation
     * @return string the relation name
     */
    public function generateRelationName($key, $multiple=false)
    {
        if (!empty($key) && substr_compare($key, 'id', -2, 2, true) === 0 && strcasecmp($key, 'id')) {
            $key = rtrim(substr($key, 0, -2), '_');
        }
        if ($multiple) {
            $key = Inflector::pluralize($key);
        }
        $key = lcfirst(Inflector::id2camel($key, '_'));
        return $key;
    }

    public function getIsModelTranslateable() {
        /**
         * @var ActiveRecord $model
         */
        $model = new $this->modelClass;
        if ($model->behaviors) {
            foreach ($model->behaviors as $behavior) {
                if ($behavior instanceof \creocoder\translateable\TranslateableBehavior) {
                    return $behavior;
                }
            }
        }
        return false;
    }

    public function generateTransliterableFields() {
        /**
         * @var \creocoder\translateable\TranslateableBehavior $behavior
         */
        if ($behavior = $this->getIsModelTranslateable()) {
            foreach ($behavior->translationAttributes as $attrName) {
                return <<<PHP
    <?php
    foreach (\common\components\helpers\Translation::getAvailableLocales() as \$lang) {
        echo \$form->field(\$model->translate(\$lang), "[\$lang]{$attrName}")->textInput(['maxlength' => true]);
    }
    ?>


PHP;
            }
        }
    }

    public function generateTransliterableColumns() {
        /**
         * @var \creocoder\translateable\TranslateableBehavior $behavior
         */
        if ($behavior = $this->getIsModelTranslateable()) {
            foreach ($behavior->translationAttributes as $attrName) {
                return <<<PHP
        \yii\helpers\ArrayHelper::getColumn(\common\components\helpers\Translation::getAvailableLocales(), function(\$lang) {
            return ['attribute'=>'translations.name', 'label'=>Yii::t('backend', 'Name {lang}', ['lang'=>\$lang]), 'value'=>function(\$model) use(\$lang) {
                return \$model->translate(\$lang)->{$attrName};
            }];
        }),

PHP;
            }
        }
    }
}
