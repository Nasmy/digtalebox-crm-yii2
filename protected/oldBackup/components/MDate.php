<?php
namespace app\components;
use yii\base\Component;

/**
 * MDate class file.
 * Manage date and time.
 */
class MDate extends Component
{
    const DB_DATE_FORMAT = 'Y-m-d';
    const DB_DATETIME_FORMAT = 'Y-m-d H:i:s';

    /**
     * Formats a date according to a predefined pattern.
     * @return string
     */
    public static function format($time, $dateWidth = 'medium', $timeWidth = 'medium')
    {
        if (empty($time))
            return null;

        return Yii::app()->dateFormatter->formatDateTime($time, $dateWidth, $timeWidth);
    }

    /**
     * Formats a date for insert in the database.
     * @return string
     */
    public static function formatToDb($time, $format = 'date', $dateWidth = 'medium', $timeWidth = 'medium')
    {
        if (empty($time))
            return null;

        if ($format === 'date') {
            return date(MDate::DB_DATE_FORMAT, CDateTimeParser::parse(trim($time),
                Yii::app()->locale->getDateFormat($dateWidth)));
        } else if ($format === 'datetime') {
            return date(MDate::DB_DATETIME_FORMAT, CDateTimeParser::parse(trim($time),
                strtr(Yii::app()->locale->dateTimeFormat,
                    array("{0}" => trim(str_replace('a', '', Yii::app()->locale->getTimeFormat($timeWidth))),
                        "{1}" => Yii::app()->locale->getDateFormat($dateWidth)))));
        } else {
            return null;
        }
    }

    public static function getDateFormat()
    {
        return Yii::app()->locale->getDateFormat('medium');
    }

    public static function getDateFormatString()
    {
        return 'yyyy-MM-dd';
    }

    public static function getDateFormatForDTP()
    {
        return str_replace('yy', 'y', strtolower(Yii::app()->locale->getDateFormat('medium')));
    }
}