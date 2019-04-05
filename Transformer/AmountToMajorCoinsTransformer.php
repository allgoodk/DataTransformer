<?php


namespace  Gtt\Bundle\DataTransformerBundle\Transformer;


use Symfony\Component\Form\DataTransformerInterface;

/**
 * Class AmountToMajorCoinsTransformer
 */
class AmountToMajorCoinsTransformer implements DataTransformerInterface
{

    /**
     * {@inheritdoc}
     */
    public function transform($value)
    {
        $value = (array) $value;

        return (float) (current($value) / 100);
    }

    /**
     * {@inheritdoc}
     */
    public function reverseTransform($amount)
    {
        if ($amount === null || $amount === '') {
            return 0;
        }

        $amount = (array) $amount;

        return (int) round(current($amount) * 100);
    }
}