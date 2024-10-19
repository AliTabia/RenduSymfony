<?php

namespace App\Form;

use App\Entity\Book;
use App\Entity\Author;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\BooleanType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class BookType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Title',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('publicationDate', DateTimeType::class, [
                'label' => 'Publication Date',
                'attr' => ['class' => 'form-control'],
                'widget' => 'single_text', // Optional: use single input field for DateTime
            ])
            ->add('enabled', CheckboxType::class, [
                'label' => 'Enabled',
                'required' => false,
                'attr' => ['class' => 'form-check-input'],
            ])
            ->add('author', EntityType::class, [
                'class' => Author::class,
                'choice_label' => 'username',
                'label' => 'Author',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('category', ChoiceType::class, [ // Dropdown for categories
                'label' => 'Category',
                'choices' => [
                    'Science-Fiction' => 'science_fiction',
                    'Mystery' => 'mystery',
                    'Autobiography' => 'autobiography',
                ],
                'placeholder' => 'Select a category',
                'attr' => ['class' => 'form-select'], // Bootstrap class for styling
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Book::class,
        ]);
    }
}
