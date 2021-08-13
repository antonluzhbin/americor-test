<?php

namespace app\models\history;

use app\models\History;
use yii\data\ActiveDataProvider;

/**
 * HistorySearch represents the model behind the search form about `app\models\History`.
 */
class Search extends History
{
    /**
     * Creates data provider instance with search query applied
     *
     * @return ActiveDataProvider
     */
    public function getDataProvider()
    {
        $query = History::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'ins_ts' => SORT_DESC,
                    'id' => SORT_DESC
                ],
            ],
        ]);

        $query->with([
            'sms',
            'task',
            'call',
            'fax'
        ]);

        return $dataProvider;
    }
}
