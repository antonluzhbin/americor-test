<?php

/**
 * @var $this yii\web\View
 * @var $dataProvider yii\data\ActiveDataProvider
 */

use app\models\history\Export;

ini_set('max_execution_time', 0);
ini_set('memory_limit', '2048M');

Export::saveToCSVFileAndDownload($items);
