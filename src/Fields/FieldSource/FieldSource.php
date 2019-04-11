<?php



namespace Gtt\Bundle\DataTransformerBundle\Fields\FieldSource;


/**
 * Class FieldSource
 */
class FieldSource implements FieldSourceInterface
{
    /**
     * FQCN of fieldset
     *
     * @var string
     */
    private $fieldSetClass;

    /**
     * @var bool
     */
    private $isFieldSet = false;


    /**
     * @var array
     */
    private $dataPath;

    /**
     * FieldSource constructor.
     *
     * @param mixed       $dataPath      Path in row data
     * @param string|null $fieldSetClass Children field's set class name
     */
    public function __construct($dataPath, string $fieldSetClass = null)
    {
        $this->dataPath = (array) $dataPath;

        if ($fieldSetClass !== null) {
            $this->setFieldSetClass($fieldSetClass);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getFieldSetClass(): string
    {
        return $this->fieldSetClass;
    }

    /**
     * @return array
     */
    public function getDataPath(): array
    {
        return $this->dataPath;
    }

    /**
     * {@inheritdoc}
     */
    public function isFieldSet(): bool
    {
        return $this->isFieldSet;
    }

    /**
     * @param string $fieldSetClass
     *
     * @return $this
     */
    private function setFieldSetClass(string $fieldSetClass): self
    {
        $this->fieldSetClass = $fieldSetClass;
        $this->isFieldSet    = true;

        return $this;
    }
}