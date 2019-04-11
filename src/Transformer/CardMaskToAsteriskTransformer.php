<?php


namespace  Gtt\Bundle\DataTransformerBundle\Transformer;


use Symfony\Component\Form\DataTransformerInterface;


/**
 * Transformer for transform card mask from 123456xxxxxx4321 to 123456******4321
 */
class CardMaskToAsteriskTransformer implements DataTransformerInterface
{
    /**
     * Given symbol will replaced to '*' symbol. For example, 'x' -> '*'
     *
     * @var string
     */
    private $sourceSymbolToReplace;

    /**
     * PaytureCardMaskToAsteriskTransformer constructor.
     *
     * @param string $sourceSymbolToReplace Given symbol will replaced to '*' symbol. For example, 'x' -> '*'
     */
    public function __construct($sourceSymbolToReplace = 'x')
    {
        $this->sourceSymbolToReplace = $sourceSymbolToReplace;
    }

    /** {@inheritdoc} */
    public function transform($transformedValue)
    {
        $transformedValue = \is_array($transformedValue) ? array_shift($transformedValue) : $transformedValue;

        return str_replace($this->sourceSymbolToReplace, '*', $transformedValue);
    }

    /** {@inheritdoc} */
    public function reverseTransform($valueToTransform)
    {
        return $valueToTransform === null ? null : str_replace('*', $this->sourceSymbolToReplace, $valueToTransform);
    }
}