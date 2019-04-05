<?php


namespace  Gtt\Bundle\DataTransformerBundle\Transformer;


use InvalidArgumentException;
use Symfony\Component\Form\DataTransformerInterface;


/**
 * Class AmountToSmallCoinsTransformer
 */
class AmountToSmallCoinsTransformer implements DataTransformerInterface
{
    /**
     * Transform normal amount to small coins. For example, 105 USD to 10500 cents
     *
     * @param int|float|string|array $amount Normal amount. For example, USD (not cents).
     *
     * @return int Amount in small coins. For example, in cents.
     *
     * @throws InvalidArgumentException
     */
    public function transform($amount)
    {
        if ($amount === null || $amount === '') {
            return 0;
        }

        $amount = (array) $amount;

        return (int) round(current($amount) * 100);
    }

    /**
     * Transform small coins to normal amount. For example, 12550 cents to 125.5 USD
     *
     * @param int|float|string $smallCoinsAmount Amount in small coins. For example, in cents.
     *
     * @return float
     *
     * @throws InvalidArgumentException
     */
    public function reverseTransform($smallCoinsAmount)
    {
        if ($smallCoinsAmount === null || $smallCoinsAmount === '') {
            return 0.0;
        }

        if (!is_numeric($smallCoinsAmount)) {
            throw new InvalidArgumentException('Unsupported value for transform with type ' . gettype($smallCoinsAmount));
        }

        $result = (float) ($smallCoinsAmount / 100);

        return $result;
    }
}