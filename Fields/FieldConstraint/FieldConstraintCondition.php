<?php


namespace Gtt\Bundle\DataTransformerBundle\Fields\FieldConstraint;

/**
 * Class FieldConstraintCondition
 */
class FieldConstraintCondition
{
    /**
     * @var string
     */
    private $fieldName;

    /**
     * @var string
     */
    private $constraintClass;

    /**
     * @var array
     */
    private $constraintValue;

    /**
     * FieldConstraintCondition constructor.
     *
     * @param string $fieldName
     * @param string $constraintClass
     * @param array  $constraintValue
     */
    public function __construct(string $fieldName, string $constraintClass, $constraintValue = [])
    {
        $this->fieldName       = $fieldName;
        $this->constraintClass = $constraintClass;
        $this->constraintValue = $constraintValue;
    }

    /**
     * @return string
     */
    public function getFieldName(): string
    {
        return $this->fieldName;
    }

    /**
     * @return string
     */
    public function getConstraintClass(): string
    {
        return $this->constraintClass;
    }

    /**
     * @return array
     */
    public function getConstraintValue(): array
    {
        return $this->constraintValue;
    }
}