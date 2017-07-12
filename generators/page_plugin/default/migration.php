<?php
/* @var $this yii\web\View */
/* @var $generator yii\gii\generators\page_plugin\Generator */
?>
<?= "<?php\n" ?>

use yii\db\Migration;

class <?= $generator->prefix ?>_page_type_<?= $generator->pluginName ?> extends Migration
{

    public function init()
    {
        $this->db = 'db';
        parent::init();
    }

    public function safeUp()
    {
        $this->insert(
            '{{%page_type}}',
            [
                'pos' => new \yii\db\Expression('(SELECT MAX(pos)+1 FROM {{%page_type}} as t)'),
                'name' => '<?= $generator->pluginName ?>',
                'plugin'=> 'common\plugins\page_type\<?= $generator->pluginName ?>\Plugin',
            ]
        );
    }

    public function safeDown()
    {
        return false;
    }
}
