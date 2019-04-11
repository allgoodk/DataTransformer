<?php



namespace Gtt\Bundle\DataTransformerBundle\Service;

use Gtt\Bundle\DataTransformerBundle\Fields\FieldConstraint\FieldConstraint;
use Gtt\Bundle\DataTransformerBundle\Fields\FieldSet;
use Gtt\Bundle\DataTransformerBundle\Fields\FieldSource\FieldSource;
use Gtt\Bundle\DataTransformerBundle\Fields\FieldSource\FieldSourceInterface;
use InvalidArgumentException;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Class FieldSetBuilder
 */
abstract class AbstractFieldSetBuilder
{
    /**
     * @var FieldSet
     */
    private $fieldSet;

    /**
     * @var OptionsResolver
     */
    protected $resolver;

    /**
     * AbstractFieldSetBuilder constructor.
     */
    public function __construct()
    {
        $this->fieldSet = new FieldSet();
        $this->resolver = new OptionsResolver();
    }

    /**
     * @param array $options
     *
     * @return FieldSet
     */
    public abstract function build(array $options = []): FieldSet;


    /**
     * @param string $name
     * @param mixed  $source
     * @param mixed  $defaultValue
     *
     * @return $this
     */
    public function addField(string $name, FieldSourceInterface $source, $defaultValue = null): self
    {
        $this->fieldSet->addField(
            $name,
            $source,
            $defaultValue
        );

        if ($source->isFieldSet() && !$this->fieldSet->getFieldSetByType($source->getFieldSetClass())) {
            $fieldSetClassName = $source->getFieldSetClass();
            if (!class_exists($fieldSetClassName)) {
                throw new InvalidArgumentException('Fieldset ' . $fieldSetClassName . 'doesn\'t exist');
            }

            /** @var AbstractFieldSetBuilder $innerFieldSetBuilder */
            $innerFieldSetBuilder = new $fieldSetClassName();

            $this->fieldSet->addFieldSet($innerFieldSetBuilder->build(), $fieldSetClassName);
        }

        return $this;
    }

    /**
     * @param DataTransformerInterface[] $transformers
     *
     * @return $this
     */
    public function addFieldTransformer(array $transformers): self
    {
        $currentField = $this->fieldSet->getLastInsertedFields();

        foreach ($transformers as $transformer) {
            $currentField->addTransformer($transformer);
        }

        return $this;
    }

    /**
     * @param FieldConstraint[] $validators
     *
     * @return $this
     */
    public function addFieldValidators(array $validators): self
    {
        $currentField = $this->fieldSet->getLastInsertedFields();

        foreach ($validators as $validator) {
            $currentField->addValidator($validator);
        }

        return $this;
    }

    /**
     * @return FieldSet
     */
    public function getFieldSet(): FieldSet
    {
        return $this->fieldSet;
    }

    /**
     * Configure options for fieldsets
     *
     * @param array $options
     *
     * @return void
     */
    public function configureFieldSetOptions(array $options)
    {
        $this->setDefault($this->resolver);
        $this->fieldSet->configureOptions($this->resolver->resolve($options));
    }

    /**
     * Special empty method for builders without default options
     *
     * @param OptionsResolver $optionResolver
     */
    public function setDefault(OptionsResolver $optionResolver)
    {
    }

    /**
     * Setting up normalizer for input data
     *
     * @param NormalizerInterface $normalizer
     * @param array               $context
     *
     * @return void
     */
    protected function setNormalizer(NormalizerInterface $normalizer, $context = [])
    {
        $this->fieldSet->setNormalizer($normalizer, $context);
    }
}