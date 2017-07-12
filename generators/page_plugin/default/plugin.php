<?php
/* @var $this yii\web\View */
/* @var $generator yii\gii\generators\page_plugin\Generator */
?>
<?= "<?php\n" ?>
namespace common\plugins\page_type\<?= $generator->pluginName ?>;

use common\models\Page;
use common\plugins\page_type\PageTypePlugin;

class Plugin implements PageTypePlugin
{
    /**
     * @param null|Page $page
     * @return null
     */
    public static function model($page = null) {
        return null;
    }

    public static function link($model, $page) {

    }

    public static function widget($form, $model, $options=[]) {
        return null;
    }

    public static function URI($page) {
        $urlManager = \Yii::$app->urlManagerFrontend;

        if (isset($page->slug)) {
            return $urlManager->createAbsoluteUrl(['/<?= $generator->pluginName ?>/index', 'slug'=>$page->slug]);
        } else {
            return $urlManager->createAbsoluteUrl(['/<?= $generator->pluginName ?>/index', 'id'=>$page->id]);
        }
    }

    public static function route($page) {
        return '<?= $generator->pluginName ?>/index';
    }
}