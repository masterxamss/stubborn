<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class PasswordMatchValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
      $formData = $this->context->getRoot()->getData(); // Pega os dados completos do formulário

      // Aqui, o $formData deve ser uma instância da sua entidade ou objeto associado ao formulário
      // Agora podemos acessar o campo 'password' diretamente
      $password = $formData->getPassword(); // Acessa o campo 'password' do objeto (assumindo que você tenha um método getter)

        // Checks that the value of the 'confirmPassword' field is equal to the value of the 'password' field
        if ($password !== $value) {
            // Adds an error if the passwords don't match
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}
