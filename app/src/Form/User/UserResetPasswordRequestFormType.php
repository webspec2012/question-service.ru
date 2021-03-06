<?php
namespace App\Form\User;

use App\Dto\User\UserResetPasswordRequestForm;
use App\Form\ReCaptchaType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Форма для запроса на восстановление пароля пользователю
 */
class UserResetPasswordRequestFormType extends AbstractType
{
    /**
     * @inheritdoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => 'E-mail',
            ])
            ->add('recaptcha', ReCaptchaType::class, [
                'mapped' => false,
                'type' => 'checkbox',
            ])
        ;
    }

    /**
     * @inheritdoc
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => UserResetPasswordRequestForm::class,
        ]);
    }
}
