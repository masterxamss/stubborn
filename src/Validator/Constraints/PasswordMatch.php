<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class PasswordMatch extends Constraint
{
    public $message = 'Les mots de passe ne correspondent pas.';
}
