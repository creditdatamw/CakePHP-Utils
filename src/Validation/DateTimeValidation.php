<?php
namespace CreditData\Cake\Validation;

use Cake\Validation\Validator;
use Cake\Validation\Validation as CakeValidation;
use Cake\Chronos\Chronos;
/**
 * Validation
 * 
 * Custom Validation provider for validating various things across the application
 * 
 */
class DateTimeValidation
{

    private static function _parseValueToDateTime($value)
    {
        if ($value instanceof \Cake\I18n\Time) {
            return Chronos::parse($value->i18nFormat('yyyy-MM-dd'));
        } else if (is_array($value)) {
           return Chronos::parse(self::implode_fields('-', $value, ['year', 'month', 'day']));
        }
        return Chronos::parse($value);
    }

    /**
     * Checks that a given date is chronologically before another date
     *
     * @param array $check with keys 'year', 'month', 'day'
     * @param string|array A string with the name of the field to check from the context or an array with keys 'year', 'month', 'day'
     * @param array $context The context data for the validation method  
     */
    public static function dateIsBefore($check, $valueOrField, array $context)
    {
        $first = self::_parseValueToDateTime($check);
        if (isset($context['data'][$valueOrField])) {
            $second = self::_parseValueToDateTime($context['data'][$valueOrField]);
        } else {
            $second = Chronos::parse($valueOrField);
        }
        return $first->lte($second);
    }

    /**
     * Checks that a given date is chronologically after another date
     *
     * @param array $check with keys 'year', 'month', 'day'
     * @param string|array A string with the name of the field to check from the context or an array with keys 'year', 'month', 'day'
     * @param array $context The context data for the validation method  
     */
    public static function dateIsAfter($check, $valueOrField, array $context)
    {
        $first = self::_parseValueToDateTime($check);
        if (isset($context['data'][$valueOrField])) {
            $second = self::_parseValueToDateTime($context['data'][$valueOrField]);
        } else {
            $second = Chronos::parse($valueOrField);
        }
        return $second->lte($first);
    }



    /**
     * Checks that the value is not over the `minimumAge`
     *
     * @return {boolean}
     */
    public static function minimumAge($check, $minimumAge)
    {
        $todayMinusMinimumAge = Chronos::now()->subYears($minimumAge);
        return self::_parseValueToDateTime($check)->lte($todayMinusMinimumAge);
    }

    /**
     * Implode an associative array with the values in a specific order
     * 
     * @param string $glue
     * @param array $pieces
     * @param array $fieldOrder Order and filter the fields to get from the associative array
     */
    private static function implode_fields($glue, array $pieces, array $fieldOrder) 
    {
        $newPieces = [];
        foreach ($fieldOrder as $f) {
            array_push($newPieces, $pieces[$f]);
        }
        return implode($glue, $newPieces);
    }
}