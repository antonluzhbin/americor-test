<?php

namespace app\widgets\HistoryList;

use app\models\history\Search;
use yii\base\Widget;
use yii\helpers\Url;
use Yii;

class HistoryList extends Widget
{
    /**
     * @return string
     */
    public function run()
    {
        $model = new Search();

        return $this->render('main', [
            'model' => $model,
            'linkExport' => $this->getLinkExport(),
            'dataProvider' => $model->getDataProvider()
        ]);
    }

    /**
     * @return string
     */
    private function getLinkExport()
    {
        $params = Yii::$app->getRequest()->getQueryParams();
        $params[0] = 'site/export';

        return Url::to($params);
    }
}
