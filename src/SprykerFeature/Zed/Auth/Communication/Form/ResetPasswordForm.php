<?php
/**
 * (c) Spryker Systems GmbH copyright protected
 */

namespace Spryker\Zed\Auth\Communication\Form;

use Spryker\Zed\Gui\Communication\Form\AbstractForm;

class ResetPasswordForm extends AbstractForm
{

    const PASSWORD = 'password';

    /**
     * Prepares form
     *
     * @return self
     */
    protected function buildFormFields()
    {
        return $this->addRepeated(
            self::PASSWORD,
            [
                'constraints' => [
                    $this->getConstraints()->createConstraintNotBlank(),
                ],
                'invalid_message' => 'The password fields must match.',
                'first_options' => [
                    'label' => 'Password',
                    'attr' => [
                        'placeholder' => 'Password',
                    ],
                ],
                'second_options' => [
                    'label' => 'Repeat Password',
                    'attr' => [
                        'placeholder' => 'Repeat Password',
                    ],
                ],
                'required' => true,
                'type' => 'password',
                'attr' => [
                    'class' => 'btn btn-default btn-block btn-outline',
                ],
            ]
        );
    }

    /**
     * Set the values for fields
     *
     * @return array
     */
    protected function populateFormFields()
    {
        return [];
    }

}
