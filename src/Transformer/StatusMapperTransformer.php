<?php



namespace  Gtt\Bundle\DataTransformerBundle\Transformer;


use Symfony\Component\Form\DataTransformerInterface;

/**
 * Class StatusMapperTransformer
 */
class StatusMapperTransformer implements DataTransformerInterface
{

    /**
     * @var array
     */
    private $successStatusesList;

    /**
     * @var array
     */
    private $processingStatusesList;

    /**
     * StatusMapperTransformer constructor.
     *
     * @param array $successStatusesList
     * @param array $processingStatusesList
     */
    public function __construct(array $successStatusesList, array $processingStatusesList = [])
    {
        $this->successStatusesList    = $successStatusesList;
        $this->processingStatusesList = $processingStatusesList;
    }

    /**
     * {@inheritdoc}
     */
    public function transform($value)
    {
        $value = is_array($value) ? current($value) : $value;

        if (\in_array($value, $this->successStatusesList, true)) {
            return 'success';
        }

        if (\in_array($value, $this->processingStatusesList, true)) {
            return 'processing';
        }

        return 'failed';
    }

    /**
     *{@inheritdoc}
     */
    public function reverseTransform($value)
    {
        // TODO: Implement reverseTransform() method.
    }
}