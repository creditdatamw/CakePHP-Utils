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
class Validation
{
    /**
     * Checks that the value of another dependent field is set 
     *
     * @param array $check value to check
     * @param string the field to check existence of in the context
     * @param array $context The context data for the validation method  
     */
    public static function requirePresenceOf($check, $otherField, array $context)
    {
        if (! isset($context['data'][$otherField])) {
            return false;
        }
        return CakeValidation::notBlank($context['data'][$otherField]);
    }
}