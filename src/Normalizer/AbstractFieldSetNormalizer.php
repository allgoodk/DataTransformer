<?php


namespace Gtt\Bundle\DataTransformerBundle\Normalizer;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Class AbstractFieldSetNormalizer
 */
abstract class AbstractFieldSetNormalizer implements NormalizerInterface
{
    /**
     * {@inheritdoc}
     */
    public abstract function normalize($object, $format = null, array $context = []);

    /**
     * {@inheritdoc}
     */
    public abstract function supportsNormalization($data, $format = null);

    /**
     * @param array $options
     *
     * @return AbstractFieldSetNormalizer
     */
    public abstract function setOptions(array $options = []);
}