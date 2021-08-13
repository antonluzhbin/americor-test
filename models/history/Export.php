<?php

namespace app\models\history;

use app\models\History;
use yii\data\ActiveDataProvider;
use Yii;
use app\widgets\HistoryList\helpers\HistoryListHelper;

/**
 * HistoryExport represents the model behind the search form about `app\models\History`.
 *
 */
class Export
{
    /**
     * Возвращает все данные из History
     *
     * @return array
     */
    public static function getItems()
    {
        $query = History::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => false,
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

        $items = $dataProvider->getModels();
        return $items;
    }

    /**
     * Эта версия быстрее
     *
     * @param $dataProvider
     * @throws \yii\base\InvalidConfigException
     */
    public static function saveToCSVFileAndDownload($items)
    {
        $filename = 'history-' . time();
        $filepath = dirname(__DIR__) . "/../runtime/{$filename}.csv";

        // сохраняем данные в файл
        self::saveToCSVFile($filepath, $items);

        // отдаем файл для скачивания
        self::fileForceDownload($filepath);
    }

    /**
     * @param $filepath
     * @param $items
     * @throws \yii\base\InvalidConfigException
     */
    public static function saveToCSVFile($filepath, $items)
    {
        $csv_file = fopen($filepath, "w+");
        // сохраняем названия столбцов
        $csv_str = '"' .Yii::t('app', 'Date') . '",' .
            '"' .Yii::t('app', 'User') . '",' .
            '"' .Yii::t('app', 'Type') . '",' .
            '"' .Yii::t('app', 'Message') . "\"\n";
        fwrite($csv_file, $csv_str);

        // сохраняем данные
        foreach ($items as $item) {
            $csv_str = '"' . Yii::$app->formatter->asDatetime($item->ins_ts, 'MM/dd/y (hh:mm a)') . '",' .
                '"' . (isset($item->user) ? $item->user->username : Yii::t('app', 'System')) . '",' .
                '"' . $item->object . '",' .
                '"' . $item->eventText . '",' .
                '"' . strip_tags(HistoryListHelper::getBodyByModel($item)) . "\"\n";
            fwrite($csv_file, $csv_str);
        }
        fclose($csv_file);
    }

    /**
     * @param $file
     */
    public static function fileForceDownload($file)
    {
        if (file_exists($file)) {
            // сбрасываем буфер вывода PHP, чтобы избежать переполнения памяти выделенной под скрипт
            // если этого не сделать файл будет читаться в память полностью!
            if (ob_get_level()) {
                ob_end_clean();
            }
            // заставляем браузер показать окно сохранения файла
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename=' . basename($file));
            header('Content-Transfer-Encoding: binary');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($file));
            // читаем файл и отправляем его пользователю
            if ($fd = fopen($file, 'rb')) {
                while (!feof($fd)) {
                    print fread($fd, 1024);
                }
                fclose($fd);
            }

            unlink($file);
            exit;
        }
    }
}
