<?php


declare(strict_types=1);

namespace  Gtt\Bundle\DataTransformerBundle\Transformer;

use Closure;
use Symfony\Component\Form\DataTransformerInterface;

/**
 * Class CallbackTransformer
 */
class CallbackTransformer implements DataTransformerInterface
{
    /**
     * @var callable
     */
    private $callback;

    /**
     * @var callable
     */
    private $reverseCallback;

    /**
     * CallbackTransformer constructor.
     *
     * @param callable $callback
     * @param callable $reverseCallback
     */
    public function __construct(callable $callback, callable $reverseCallback = null)
    {
        $this->callback        = $callback;
        $this->reverseCallback = $reverseCallback;
    }

    /**
     *{@inheritdoc}
     */
    public function transform($value)
    {
        $callback = $this->callback;

        return $callback($value);
    }

    /**
     *{@inheritdoc}
     */
    public function reverseTransform($value)
    {
        if ($this->reverseCallback === null) {
            return $value;
        }

        return array_map($this->reverseCallback, $value);
    }
}