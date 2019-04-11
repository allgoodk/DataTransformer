<?php


namespace Gtt\Bundle\DataTransformerBundle\Tests\TestFieldSetBuilder;


use Gtt\Bundle\DataTransformerBundle\Fields\FieldConstraint\DependentFieldConstraint;
use Gtt\Bundle\DataTransformerBundle\Fields\FieldConstraint\FieldConstraint;
use Gtt\Bundle\DataTransformerBundle\Fields\FieldConstraint\FieldConstraintCondition;
use Gtt\Bundle\DataTransformerBundle\Fields\FieldSet;
use Gtt\Bundle\DataTransformerBundle\Fields\FieldSource\CustomFieldSource;
use Gtt\Bundle\DataTransformerBundle\Fields\FieldSource\FieldSource;
use Gtt\Bundle\DataTransformerBundle\Service\AbstractFieldSetBuilder;
use Gtt\Bundle\DataTransformerBundle\Tests\TestFieldSetBuilder\TestTransformers\TestTransformer;
use  Gtt\Bundle\DataTransformerBundle\Transformer\CardMaskToAsteriskTransformer;
use  Gtt\Bundle\DataTransformerBundle\Transformer\ImploderTransformer;
use  Gtt\Bundle\DataTransformerBundle\Transformer\StatusMapperTransformer;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Blank;
use Symfony\Component\Validator\Constraints\EqualTo;
use Symfony\Component\Validator\Constraints\NotBlank;

class TestFieldSetBuilder extends AbstractFieldSetBuilder
{
    /**
     * {@inheritdoc}
     */
    public function build(array $options = []): FieldSet
    {
        $this->configureFieldSetOptions($options);

        $this
            ->addField('client_card_number', new FieldSource('card_num'))
            ->addFieldTransformer([new CardMaskToAsteriskTransformer()])
            ->addFieldValidators(
                [
                    new DependentFieldConstraint(
                        NotBlank::class,
                        [],
                        [new FieldConstraintCondition('transaction_status', EqualTo::class, ['success']),]
                    ),
                    new DependentFieldConstraint(
                        Blank::class,
                        [],
                        [new FieldConstraintCondition('transaction_status', EqualTo::class, ['failed']),]
                    ),
                ]
            );

        $this
            ->addField('client_name', new FieldSource(['client_first_name', 'client_last_name'], TestCardFieldSetBuilder::class))
            ->addFieldTransformer([new ImploderTransformer()]);

        $this
            ->addField('merchant_alias', new FieldSource('mrch'))
            ->addFieldValidators([new FieldConstraint(EqualTo::class, 'merchant_alias'),]);

        $this->addField('transaction_id', new FieldSource(['transaction_id'], TestOtherInformationFieldSetBuilder::class));

        $this->addField('signature', new FieldSource('SIGN'))
             ->addFieldValidators(
                 [
                     new DependentFieldConstraint(
                         EqualTo::class,
                         'get_signature',
                         [new FieldConstraintCondition('transaction_status', EqualTo::class, ['success']),]
                     )]);
        $this->addField('client_card_cvv_number', new FieldSource(['client_card_cvv_number'], TestCardFieldSetBuilder::class));

        $this->addField('internal_transaction_status', new FieldSource('SUCCESS'));

        $this->addField('transaction_status', new FieldSource('SUCCESS'))
             ->addFieldTransformer([new StatusMapperTransformer(['TRUE'])]);

        $this->addField(
            'custom_field_source',
            new CustomFieldSource(
                [
                    'test'  => ['CARD_INFO', 'TestPS', 'holder_first_name'],
                    'test2' => ['CARD_INFO', 'TestPS', 'holder_first_name'],
                ]
            )
        )
             ->addFieldValidators([new FieldConstraint(EqualTo::class, 'custom_test_data')])
             ->addFieldTransformer([new TestTransformer()]);

        return $this->getFieldSet();
    }

    /**
     * {@inheritdoc}
     */
    public function setDefault(OptionsResolver $resolver)
    {
        $resolver->setDefault('merchant_alias', null);
        $resolver->setDefault('get_signature', null);
        $resolver->setDefault('custom_test_data', ['value' => ['test' => 'Dmitry', 'test2' => 'Dmitry']]);
    }
}