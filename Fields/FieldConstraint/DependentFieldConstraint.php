<?php


namespace Gtt\Bundle\DataTransformerBundle\Fields\FieldConstraint;


use RuntimeException;
use Symfony\Component\Validator\Constraint;

/**
 * Class DependentFieldConstraint
 */
class DependentFieldConstraint implements FieldConstraintInterface
{
    /**
     * @var array
     */
    private $constraintValue;

    /**
     * @var Constraint
     */
    private $constraint;

    /**
     * @var array
     */
    private $constraintConditionList;

    /**
     * DependentFieldConstraint constructor.
     *
     * @param string                     $constraint
     * @param array                      $constraintValue
     * @param FieldConstraintCondition[] $constraintConditionList
     */
    public function __construct(string $constraint, $constraintValue = [], $constraintConditionList = null)
    {
        $this->constraint              = $constraint;
        $this->constraintValue         = (array) $constraintValue;
        $this->constraintConditionList = $constraintConditionList;
    }

    /**
     * {@inheritdoc}
     */
    public function getConstraint(...$args): Constraint
    {
        if (!$this->constraint) {
            throw new RuntimeException('Constraint not set');
        }

        return new $this->constraint(...$args);
    }

    /**
     * {@inheritdoc}
     */
    public function getConstraintValue(): array
    {
        return $this->constraintValue;
    }

    /**
     * @return FieldConstraintCondition[]
     */
    public function getConstraintConditionList(): array
    {
        return $this->constraintConditionList;
    }
}