<?php


namespace Gtt\Bundle\DataTransformerBundle\Tests\TestFieldSetBuilder;


use Gtt\Bundle\DataTransformerBundle\Fields\FieldSet;
use Gtt\Bundle\DataTransformerBundle\Fields\FieldSource\FieldSource;
use Gtt\Bundle\DataTransformerBundle\Service\AbstractFieldSetBuilder;
use  Gtt\Bundle\DataTransformerBundle\Transformer\CardMaskToAsteriskTransformer;

class TestCardFieldSetBuilder extends AbstractFieldSetBuilder
{
    /**
     * {@inheritdoc}
     */
    public function build(array $options = []): FieldSet
    {
        $this->addField(
            'client_first_name',
            new FieldSource(['CARD_INFO', 'TestPS', 'holder_first_name'])
        )->addFieldTransformer([new CardMaskToAsteriskTransformer('D')]);

        $this->addField(
            'client_last_name',
            new FieldSource(['CARD_INFO', 'TestPS', 'holder_second_name'])
        );

        $this->addField('client_card_cvv_number', new FieldSource(['CARD_INFO', 'TestPS', 'cvv']))
             ->addFieldTransformer([new CardMaskToAsteriskTransformer('1')]);

        return $this->getFieldSet();
    }
}