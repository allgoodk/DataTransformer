<?php


namespace Gtt\Bundle\DataTransformerBundle\Fields\FieldConstraint;

use Symfony\Component\Validator\Constraint;

/**
 * Interface FieldConstraintInterface
 */
interface FieldConstraintInterface
{

    /**
     * @param array $args
     *
     * @return Constraint
     */
    public function getConstraint(...$args): Constraint;

    /**
     * @return array
     */
    public function getConstraintValue(): array;

}