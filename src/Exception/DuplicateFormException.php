<?php
namespace CreditData\Cake\Exception;

use Cake\Http\Exception\ForbiddenException; 

class DuplicateFormException extends ForbiddenException
{
    // Using Forbidden (HTTP status code 403) following the same
    // spirit as Cake deals with CSRF.
}
