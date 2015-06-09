<?php
/**
 * register form type
 *
 * @package Projects
 * @copyright 2015 Demand Media, Inc. All Rights Reserved.
 */
namespace UserBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use UserBundle\Entity\User;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * RegisterFormType
 *
 * @author Michael Funk <mike.funk@demandmedia.com>
 */
class RegisterFormType extends AbstractType
{

    /**
     * get name
     *
     * @return string
     */
    public function getName()
    {
        return 'user_register';
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        // define the fields
        return $builder->add('username', 'text')

            // no field type guessing :(
            // ->add('email', 'email')

            // field type guessing! :) set second arg to null
            ->add('email', null)
            ->add('plainPassword', 'repeated', [
                'type' => 'password'
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $settings = ['data_class' => 'UserBundle\Entity\User'];
        $resolver->setDefaults($settings);
        $this->configureOptions($resolver);
    }
}
