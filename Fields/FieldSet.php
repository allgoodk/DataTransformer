<?php



namespace Gtt\Bundle\DataTransformerBundle\Fields;


use Gtt\Bundle\DataTransformerBundle\Fields\FieldConstraint\DependentFieldConstraint;
use Gtt\Bundle\DataTransformerBundle\Fields\FieldConstraint\FieldConstraintCondition;
use Gtt\Bundle\DataTransformerBundle\Fields\FieldSource\CustomFieldSource;
use Gtt\Bundle\DataTransformerBundle\Fields\FieldSource\FieldSource;
use Gtt\Bundle\DataTransformerBundle\Fields\FieldSource\FieldSourceInterface;
use Gtt\Bundle\DataTransformerBundle\Normalizer\AbstractFieldSetNormalizer;
use RuntimeException;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\ValidatorInterface;


/**
 * Class FieldSet
 */
class FieldSet
{
    /**
     * @var Field[]
     */
    private $fields;

    /**
     * @var AbstractFieldSetNormalizer
     */
    private $normalizer;

    /**
     * @var array
     */
    private $callbacks = [];
    /**
     * @var FieldSet[]
     */
    private $fieldSets = [];

    /**
     * @var array
     */
    private $additionalData = [];

    /**
     * @var bool
     */
    private $processed;

    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * @var array
     */
    private $options;

    /**
     * @var array
     */
    private $normalizerContext;

    /**
     * @var array
     */
    private $errors = [];

    /**
     * @var bool
     */
    private $hasErrors = false;

    /**
     * NewFieldSet constructor.
     */
    public function __construct()
    {
        $this->validator = Validation::createValidator();
    }

    /**
     * @param mixed $data
     *
     * @return FieldSet
     */
    public function process($data): FieldSet
    {
        if ($this->processed) {
            return $this;
        }

        if ($this->normalizer) {
            $data = $this->normalizer
                ->setOptions($this->options)
                ->normalize($data, null, $this->normalizerContext);
        }

        foreach ($this->fields as $field) {
            $this->mapDataOnField($field, $data);
        }

        $this->validateFields($data);

        foreach ($this->fields as $field) {
            $this->transform($field);
        }

        $this->setProcessed();

        return $this;
    }

    /**
     * @param AbstractFieldSetNormalizer $normalizer
     * @param array                      $context
     */
    public function setNormalizer($normalizer, array $context = [])
    {
        $this->normalizer        = $normalizer;
        $this->normalizerContext = $context;
    }

    /**
     * @param FieldSet $fieldSet
     * @param string   $type
     *
     * @return $this
     */
    public function addFieldSet(FieldSet $fieldSet, string $type): self
    {
        $this->fieldSets[$type] = $fieldSet;

        return $this;
    }

    /**
     * @param string               $name
     * @param FieldSourceInterface $source
     * @param mixed                $defaultValue
     *
     * @return FieldSet
     */
    public function addField($name, FieldSourceInterface $source, $defaultValue = null): FieldSet
    {
        $field = new Field(
            $name,
            $source,
            $defaultValue
        );

        $this->fields[] = $field;

        return $this;
    }

    /**
     * @param string $fieldSetType
     *
     * @return FieldSet | null
     */
    public function getFieldSetByType(string $fieldSetType)
    {
        if (empty($this->fieldSets[$fieldSetType])) {
            return null;
        }

        return $this->fieldSets[$fieldSetType];
    }

    /**
     * @param string $additionalDataName
     * @param mixed  $data
     *
     * @return FieldSet
     */
    public function addData($additionalDataName, $data): FieldSet
    {
        $this->additionalData[$additionalDataName] = $data;

        return $this;
    }

    /**
     * @return array
     */
    public function getProcessedData(): array
    {
        $result = [];
        foreach ($this->fields as $field) {
            $result[$field->getName()] = $field->getValue();
        }

        return $result;
    }

    /**
     * @param string   $callbackName
     * @param callable $callable
     *
     * @return $this
     */
    public function addCallback($callbackName, $callable): self
    {
        $this->callbacks[$callbackName] = $callable;

        return $this;
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        $result = [];
        foreach ($this->getProcessedData() as $dataSetName => $dataSetValue) {
            $result[$dataSetName] = \count($dataSetValue) === 1 && !$this->isValueAssocArray($dataSetValue)
                ? current($dataSetValue)
                : $dataSetValue;
        }

        return $result;
    }

    /**
     * Returns last inserted field
     *
     * @return Field|bool
     */
    public function getLastInsertedFields()
    {
        return end($this->fields);
    }

    /**
     * @param array $options
     *
     * @return void
     */
    public function configureOptions(array $options)
    {
        $this->options = $options;
    }

    /**
     * @param Field $field
     * @param array $data
     *
     * @return void
     */
    private function mapDataOnField(Field $field, $data)
    {
        $result = [];
        $source = $field->getFieldSource();

        if ($source instanceof CustomFieldSource) {
            foreach ($source->getDataPath() as $key => $dataPath) {
                $result[$key] = $this->loadDataFromSource($data, (array) $dataPath);
            }

            $field->setValue($result);

            return;
        }


        if ($source instanceof FieldSource && $source->isFieldSet() === false) {
            $result = $this->loadDataFromSource($data, $source->getDataPath());
        } else {
            $fieldSet      = $this->getFieldSetByType($source->getFieldSetClass());
            $processedData = $fieldSet->process($data)->getProcessedData();

            foreach ($processedData as $fieldName => $processedField) {
                if (\in_array($fieldName, $source->getDataPath(), true)) {
                    $result = array_merge($result, $processedField);
                }
            }
        }

        $field->setValue($result);
    }

    /**
     * @param Field $field
     */
    private function transform(Field $field)
    {
        if ($field->isTransformed()) {
            return;
        }

        foreach ($field->getTransformers() as $dataTransformer) {
            $transformedValue = $dataTransformer->transform($field->getValue());

            $field->setValue($transformedValue);
        }

        $field->setTransformed(true);
    }

    /**
     * Get values from raw data, by array of keys
     *
     * @example Value 'value' from $array['key1' => ['key2' => 'value']]
     *          can be reached by array of keys like ['key1', 'key2]
     *
     * @param array $data
     * @param array $source
     *
     * @return mixed
     */
    private function loadDataFromSource(array $data, array $source)
    {
        return array_reduce($source, function ($data, $sourceName) {
            if (isset($data[$sourceName])) {
                $data = $data[$sourceName];

                return $data;
            }
        }, $data);
    }

    /**
     * @return $this
     */
    private function setProcessed(): self
    {
        $this->processed = true;

        return $this;
    }

    /**
     * @param string $optionName
     * @param mixed  $data
     *
     * @return mixed
     */
    private function resolveOption(string $optionName, $data)
    {
        $option = $this->options[$optionName];

        if (\is_callable($option)) {
            $option = $option($data);
        }

        return $option;
    }

    /**
     * Sets validation errors
     *
     * @param string              $fieldName Destination field name
     * @param ConstraintViolation $error     Validation field error
     * @param string              $payload   Error description (for example source data path)
     */
    private function setErrors(string $fieldName, ConstraintViolation $error, string $payload)
    {
        $this->hasErrors            = true;
        $this->errors[$fieldName][] = "{$error->getMessage()} {$payload}";
    }

    /**
     * Return string of error on each fields
     *
     * @return string
     */
    private function getErrorMessages(): string
    {
        $errorMessage = '';
        foreach ($this->errors as $fieldName => $message) {
            $messageString = array_reduce($message, function ($acc, $string) {
                return $acc .= ' ' . $string;
            }, '');
            $errorMessage  .= "{$fieldName}: {$messageString}; ";
        }

        return $errorMessage;
    }

    /**
     * @param array $data
     */
    private function validateFields(array $data)
    {
        foreach ($this->fields as $field) {
            $this->validate($field, $data);
        }

        if ($this->hasErrors) {
            throw new RuntimeException($this->getErrorMessages());
        }
    }

    /**
     * @param array $value
     *
     * @return bool
     */
    private function isValueAssocArray(array $value): bool
    {
        if ([] === $value) {
            return false;
        }

        return array_keys($value) !== range(0, \count($value) - 1);
    }

    /**
     * @param Field $field
     * @param mixed $data
     */
    private function validate(Field $field, $data)
    {
        if ($field->isValidated()) {
            return;
        }

        foreach ($field->getValidators() as $constraintContainer) {
            $dependentError = null;
            if ($constraintContainer instanceof DependentFieldConstraint) {
                foreach ($constraintContainer->getConstraintConditionList() as $fieldConstraint) {
                    $dependentError = $this->processDependentFieldConstraints($data, $fieldConstraint);
                }
            }

            if ($dependentError !== null && $dependentError->count()) {
                continue;
            }

            $args = [];
            foreach ($constraintContainer->getConstraintValue() as $value) {
                $args[] = $this->resolveOption($value, $data);
            }
            $constraint = $constraintContainer->getConstraint(...$args);
            $errors     = $this->validator->validate(current($field->getValue()), $constraint);

            if ($errors->count()) {
                foreach ($errors as $error) {
                    $visualizedDataPath = json_encode($field->getFieldSource()->getDataPath());
                    $this->setErrors($field->getName(), $error, "Data path: {$visualizedDataPath}");
                }
            }

        }

        $field->setValidated(true);
    }

    /**
     * @param string $fieldName
     *
     * @return Field
     */
    private function getFieldByName(string $fieldName): Field
    {
        $resultField = null;
        foreach ($this->fields as $field) {
            if ($field->getName() === $fieldName) {
                $resultField = $field;
                break;
            }
        }

        if ($resultField === null) {
            throw new RuntimeException("Field with name $fieldName not found");
        }

        return $resultField;
    }

    /**
     * @param array                    $data
     * @param FieldConstraintCondition $fieldConstraint
     *
     * @return ConstraintViolationListInterface
     */
    private function processDependentFieldConstraints(
        array $data,
        FieldConstraintCondition $fieldConstraint
    ): ConstraintViolationListInterface
    {
        $dependentField = $this->getFieldByName($fieldConstraint->getFieldName());

        $this->validate($dependentField, $data);
        $this->transform($dependentField);

        $dependentConstraintClass = $fieldConstraint->getConstraintClass();
        $dependentConstraintValue = $fieldConstraint->getConstraintValue();

        $dependentConstraint = new $dependentConstraintClass(...$dependentConstraintValue);
        $dependentError      = $this->validator->validate(current($dependentField->getValue()), $dependentConstraint);

        return $dependentError;
    }
}