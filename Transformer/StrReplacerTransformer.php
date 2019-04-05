<?php


declare(strict_types=1);

namespace  Gtt\Bundle\DataTransformerBundle\Transformer;

use Symfony\Component\Form\DataTransformerInterface;

/**
 * Class StrReplacerTransformer
 */
class StrReplacerTransformer implements DataTransformerInterface
{
    /**
     * Array of key-value pairs for replace. Example: ['from' => 'to', ...]
     *
     * @var array
     */
    private $replacePairs;

    /**
     * StrReplacerTransformer constructor.
     *
     * @param array $replacePairs
     */
    public function __construct(array $replacePairs)
    {
        $this->replacePairs = $replacePairs;
    }

    /**
     * {@inheritdoc}
     */
    public function transform($value)
    {
        $value = \is_array($value) ? current($value) : $value;

        return strtr($value, $this->replacePairs);
    }

    /**
     * {@inheritdoc}
     */
    public function reverseTransform($value)
    {
        // TODO: Implement reverseTransform() method.
    }
}