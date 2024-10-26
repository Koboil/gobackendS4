<?php


namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Contracts\Translation\TranslatorInterface;

class RegistrationType extends AbstractType
{

    public function __construct(private readonly TranslatorInterface $translator)
    {
     }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $passwordAttrs = [
            'minlength' => 6,
        ];

        $builder
            ->add('email', null, [
                'constraints' => [
                    new Assert\Sequentially(
                        [
                            new Assert\NotBlank(),
                            new  Assert\Email(),
                            new Assert\Length([
                                'min' => 6,
                                'max' => 255,
                            ])
                        ]
                    )
                ],
            ])


            ->add('terms', CheckboxType::class, [
                'label_html' => true,
                'mapped' => false,
                'label'    => $this->translator->trans("J'ai lu la politique de confidentialité et j'accepte les conditions générales d'utilisation."),
                'required' => true,
                // 'constraints' => [
                //     new IsTrue([
                //         'message' => $this->translator->trans("You must agree to our Privacy Policy and Terms and Condition")
                //     ])
                // ]
            ])
            ->add('plainPassword', RepeatedType::class, [
                'mapped' => false,
                'type' => PasswordType::class,
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length([
                        'min' => 6,
                        'max' => 255,
                    ]),
                ],
                'first_options' => ['label' => 'Mot de passe', 'attr' => $passwordAttrs],
                'second_options' => ['label' => 'Confirmer le mot de passe', 'attr' => $passwordAttrs],
            ]);;
    }

    public function getBlockPrefix(): string
    {
        return 'submitProject';
    }


    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
