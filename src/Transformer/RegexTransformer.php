<?php


namespace  Gtt\Bundle\DataTransformerBundle\Transformer;

use Symfony\Component\Form\DataTransformerInterface;

/**
 * Class RegexTransformer
 */
class RegexTransformer implements DataTransformerInterface
{
    /**
     * @var string
     */
    private $regexPattern;

    /**
     * RegexTransformer constructor.
     *
     * @param string $regexPattern
     */
    public function __construct($regexPattern = '/./')
    {
        $this->regexPattern = $regexPattern;
    }

    /**
     *{@inheritdoc}
     */
    public function transform($value)
    {
        if (false === preg_match($this->regexPattern, $value, $matched)) {
            return $value;
        }

        return array_pop($matched);
    }

    /**
     *{@inheritdoc}
     */
    public function reverseTransform($value)
    {
        //Not supported
    }
}
