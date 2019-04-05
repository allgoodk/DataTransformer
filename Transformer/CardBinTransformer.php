<?php


declare(strict_types=1);

namespace  Gtt\Bundle\DataTransformerBundle\Transformer;

use Symfony\Component\Form\DataTransformerInterface;

/**
 * Class CardBinTransformer
 */
class CardBinTransformer implements DataTransformerInterface
{
    /**
     *{@inheritdoc}
     */
    public function transform($value)
    {
        return array_map(function ($item) {
            return substr($item, 0, 6);
        }, $value);
    }

    /**
     *{@inheritdoc}
     */
    public function reverseTransform($value)
    {
        // This transformer don`t support reverseTransform() method
    }
}