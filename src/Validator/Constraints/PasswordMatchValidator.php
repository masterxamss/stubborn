<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class PasswordMatchValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        $formData = $this->context->getRoot()->getData();

        $password = $formData->getPassword();

        // Checks that the value of the 'confirmPassword' field is equal to the value of the 'password' field
        if ($password !== $value) {
            // Adds an error if the passwords don't match
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}
