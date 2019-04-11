<?php


namespace  Gtt\Bundle\DataTransformerBundle\Transformer;


use Gtt\Money\MiddleLevelApi\Transfer\TransactionDetailHelper;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

/**
 * Class FloatAmountToInternalStringFormatTransformer
 * Transform float amount to string in internal amount format
 *
 * @author Dmitry Gorbachev <dmitry.gorbachev@alpari.org>
 */
class FloatAmountToInternalStringFormatTransformer implements DataTransformerInterface
{
    /**
     * {@inheritdoc}
     *
     * @param float|null|mixed $value The value in the original representation
     *
     * @return string The value in the transformed representation
     *
     * @throws TransformationFailedException When the transformation fails.
     */
    public function transform($value)
    {
        if ($value === null) {
            $value = 0.0;
        }

        /* ToDo: Add working with integer */
        if (is_float($value)) {
            return TransactionDetailHelper::formatAmount($value);
        }

        $valueType = gettype($value);
        throw new TransformationFailedException("Unsupported value type to transform {$valueType}");
    }

    /**
     * {@inheritdoc}
     *
     * @param string|null|mixed $value The value in the transformed representation
     *
     * @return float The value in the original representation
     *
     * @throws TransformationFailedException When the transformation fails.
     */
    public function reverseTransform($value)
    {
        if ($value === null) {
            $value = TransactionDetailHelper::formatAmount(0.0);
        }

        if (is_string($value) && is_numeric($value)) {
            return (float) $value;
        }

        $valueType = gettype($value);
        throw new TransformationFailedException("Unsupported value type to transform {$valueType}");
    }
}