<?php



namespace  Gtt\Bundle\DataTransformerBundle\Transformer;


use Symfony\Component\Form\DataTransformerInterface;

/**
 * Class ImploderTransformer
 */
class ImploderTransformer implements DataTransformerInterface
{
    /**
     * @var string
     */
    private $delimiter;

    /**
     * ImploderTransformer constructor.
     *
     * @param string $delimiter
     */
    public function __construct(string $delimiter = ' ')
    {
        $this->delimiter = $delimiter;
    }

    /**
     *{@inheritdoc}
     */
    public function transform($value)
    {
        return implode($this->delimiter, $value);
    }

    /**
     *{@inheritdoc}
     */
    public function reverseTransform($value)
    {
        // TODO: Implement reverseTransform() method.
    }
}