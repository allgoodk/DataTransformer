<?php


namespace Gtt\Bundle\DataTransformerBundle\Tests\TestFieldSetBuilder\TestTransformers;


use Symfony\Component\Form\DataTransformerInterface;

class TestTransformer implements DataTransformerInterface
{

    /**
     * {@inheritdoc}
     */
    public function transform($value)
    {
        foreach ($value as $key => $item){
            $value[$key] = array_flip($item);
        }
        return $value;
    }

    /**
     * {@inheritdoc}
     */
    public function reverseTransform($value)
    {
        // TODO: Implement reverseTransform() method.
    }
}