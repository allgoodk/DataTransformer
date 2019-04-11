<?php


namespace Gtt\Bundle\DataTransformerBundle\Tests\TestFieldSetBuilder;


use Gtt\Bundle\DataTransformerBundle\Fields\FieldSet;
use Gtt\Bundle\DataTransformerBundle\Fields\FieldSource\FieldSource;
use Gtt\Bundle\DataTransformerBundle\Service\AbstractFieldSetBuilder;

class TestOtherInformationFieldSetBuilder extends AbstractFieldSetBuilder
{

    /**
     * @param array $options
     *
     * @return FieldSet
     */
    public function build(array $options = []): FieldSet
    {
        $this->addField('transaction_id', new FieldSource(['OTHER_INFO', 'TRANSAC_ID']));

        return $this->getFieldSet();
    }
}