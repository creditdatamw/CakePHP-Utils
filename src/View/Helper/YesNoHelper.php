<?php
namespace CreditData\Cake\View\Helper;

use Cake\View\Helper;

class YesNoHelper extends Helper
{
    /**
     * Creates Yes or No output from value
     *
     * @param mixed  $value The list to be joined.
     * @return string 'Yes' or 'No'
     */
    public function toYesNo($value, $decorate = false)
    {
        if (! $decorate) return ((bool) $value) ? 'Yes' : 'No';
        return ((bool) $value) ? '<span class="label label-info">Yes</span>' : '<span class="label label-info">No</span>' ;
    }
    
    public function toBoolean($value)
    {
        return ((bool) $value) ? 'True' : 'False';
    }
}