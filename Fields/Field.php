<?php



namespace Gtt\Bundle\DataTransformerBundle\Fields;

use Gtt\Bundle\DataTransformerBundle\Fields\FieldConstraint\FieldConstraint;
use Gtt\Bundle\DataTransformerBundle\Fields\FieldSource\CustomFieldSource;
use Gtt\Bundle\DataTransformerBundle\Fields\FieldConstraint\FieldConstraintInterface;
use Gtt\Bundle\DataTransformerBundle\Fields\FieldSource\FieldSourceInterface;
use Symfony\Component\Form\DataTransformerInterface;

/**
 * Class Field
 */
class Field
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var FieldSourceInterface
     */
    private $fieldSource;

    /**
     * @var DataTransformerInterface[]
     */
    private $transformers = [];

    /**
     * @var FieldConstraint[]
     */
    private $validators = [];

    /**
     * @var array
     */
    private $value = [];

    /**
     * @var bool
     */
    private $hasDefaultValue;

    /**
     * @var bool
     */
    private $validated = false;

    /**
     * @var bool
     */
    private $transformed = false;

    /**
     * NewField constructor.
     *
     * @param string               $name
     * @param FieldSourceInterface $fieldSource
     * @param mixed                $defaultValue
     */
    public function __construct(string $name, FieldSourceInterface $fieldSource, $defaultValue = null)
    {
        $this->name = $name;
        $this->setDefaultValue($defaultValue);
        $this->setFieldSource($fieldSource);
    }

    /**
     * @return array
     */
    public function getValue(): array
    {
        return $this->value;
    }

    /**
     * @param mixed $value
     *
     * @return Field
     */
    public function setValue($value)
    {
        if ($value === [] && $this->hasDefaultValue === true) {
            return $this;
        }

        if ($this->value === [] && $this->fieldSource instanceof CustomFieldSource) {
            $this->value = [$value];

            return $this;
        }

        $this->value = \is_array($value) ? $value : [$value];

        return $this;
    }

    /**
     * @return bool
     */
    public function isValidated(): bool
    {
        return $this->validated;
    }

    /**
     * @param bool $validated
     */
    public function setValidated(bool $validated)
    {
        $this->validated = $validated;
    }

    /**
     * @return bool
     */
    public function isTransformed(): bool
    {
        return $this->transformed;
    }

    /**
     * @param bool $transformed
     */
    public function setTransformed(bool $transformed)
    {
        $this->transformed = $transformed;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name)
    {
        $this->name = $name;
    }

    /**
     * @return FieldSourceInterface
     */
    public function getFieldSource(): FieldSourceInterface
    {
        return $this->fieldSource;
    }

    /**
     * @param FieldSourceInterface $fieldSource
     */
    public function setFieldSource(FieldSourceInterface $fieldSource)
    {
        $this->fieldSource = $fieldSource;
    }

    /**
     * @return array
     */
    public function getTransformers(): array
    {
        return $this->transformers;
    }

    /**
     * @param array $transformers
     */
    public function setTransformers(array $transformers)
    {
        $this->transformers = $transformers;
    }

    /**
     * @return FieldConstraintInterface[]
     */
    public function getValidators(): array
    {
        return $this->validators;
    }

    /**
     * @param FieldConstraintInterface[] $validators
     */
    public function setValidators(array $validators)
    {
        $this->validators = $validators;
    }

    /**
     * @param DataTransformerInterface $transformer
     *
     * @return $this
     */
    public function addTransformer(DataTransformerInterface $transformer): self
    {
        $this->transformers[] = $transformer;

        return $this;
    }

    /**
     * @param FieldConstraint $validator
     */
    public function addValidator($validator)
    {
        $this->validators[] = $validator;
    }

    /**
     * Sets default value
     *
     * @param mixed $defaultValue
     */
    private function setDefaultValue($defaultValue)
    {
        if ($defaultValue === null) {
            return;
        }

        $this->hasDefaultValue = true;

        $this->setValue($defaultValue);
    }
}