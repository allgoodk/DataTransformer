<?php



namespace Gtt\Bundle\DataTransformerBundle\Fields\FieldConstraint;


use RuntimeException;
use Symfony\Component\Validator\Constraint;

/**
 * Class FieldConstraint
 */
class FieldConstraint implements FieldConstraintInterface
{
    /**
     * @var Constraint
     */
    private $constraint;

    /**
     * @var array
     */
    private $constraintValue;

    /**
     * FieldConstraint constructor.
     *
     * @param string      $constraint
     * @param mixed|array $constraintValue
     */
    public function __construct(string $constraint, $constraintValue = [])
    {
        $this->constraint      = $constraint;
        $this->constraintValue = (array) $constraintValue;
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
}