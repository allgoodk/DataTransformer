<?php


namespace  Gtt\Bundle\DataTransformerBundle\Transformer;


use Gtt\Bundle\CatsClient\Enum\Currency as CurrencyEnum;
use Symfony\Component\Form\DataTransformerInterface;

/**
 * Class CurrencyIdToIsoCodeTransformer
 */
class CurrencyIdToIsoCodeTransformer implements DataTransformerInterface
{

    /**
     * {@inheritdoc}
     * */
    public function transform($value)
    {
        if (empty($value)) {
            return $value;
        }

        return CurrencyEnum::getIsoCodeById(current($value));
    }

    /**
     * {@inheritdoc}
     */
    public function reverseTransform($value)
    {
        if (empty($value)) {
            return $value;
        }

        return CurrencyEnum::getIdByIsoCode($value);
    }
}