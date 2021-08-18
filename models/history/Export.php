<?php

namespace app\models\history;

use app\models\History;
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

        $query->orderBy([
            'ins_ts' => SORT_DESC,
            'id' => SORT_DESC
        ]);

        $query->with([
            'sms',
            'task',
            'call',
            'fax'
        ]);

        foreach ($query->each() as $history) {
            yield $history;
        }
    }

    public static function prepareField($str){
        $str = str_replace("&nbsp;", " ", $str);
        $str = trim($str);
        $str = str_replace("\r\n", "", $str);
        $str = str_replace("\n", "", $str);
        $str = str_replace('"', '""', $str);
        $str = '"' . $str . '"';
        return $str;
    }

    /**
     * Эта версия быстрее
     *
     * @param $dataProvider
     * @throws \yii\base\InvalidConfigException
     */
    public static function saveToCSVFileAndDownload()
    {
        $filename = 'history-' . time();
        $filepath = dirname(__DIR__) . "/../runtime/{$filename}.csv";

        // сохраняем данные в файл
        self::saveToCSVFile($filepath);

        // отдаем файл для скачивания
        self::fileForceDownload($filepath);
    }

    /**
     * @param $filepath
     * @throws \yii\base\InvalidConfigException
     */
    public static function saveToCSVFile($filepath)
    {
        $csv_file = fopen($filepath, "w+");
        // сохраняем названия столбцов
        $fields = [];
        $fields[] = self::prepareField(Yii::t('app', 'Date'));
        $fields[] = self::prepareField(Yii::t('app', 'User'));
        $fields[] = self::prepareField(Yii::t('app', 'Type'));
        $fields[] = self::prepareField(Yii::t('app', 'Message'));
        $csv_str = implode(',', $fields) . "\n";
        fwrite($csv_file, $csv_str);

        // сохраняем данные
        foreach (self::getItems() as $item) {
            $fields = [];
            $fields[] = self::prepareField(Yii::$app->formatter->asDatetime($item->ins_ts, 'MM/dd/y (hh:mm a)'));
            $fields[] = self::prepareField((isset($item->user) ? $item->user->username : Yii::t('app', 'System')));
            $fields[] = self::prepareField($item->object );
            $fields[] = self::prepareField($item->eventText);
            $fields[] = self::prepareField(strip_tags(HistoryListHelper::getBodyByModel($item)));
            $csv_str = implode(',', $fields) . "\n";
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
