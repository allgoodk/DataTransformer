<?php


namespace Gtt\Bundle\DataTransformerBundle\Fields\FieldSource;

/**
 * Class CustomFieldSource
 */
class CustomFieldSource implements FieldSourceInterface
{
    /**
     * @var array
     */
    private $dataPath;

    /**
     * @var bool
     */
    private $isFieldSet = false;

    /**
     * CustomFieldSource constructor.
     *
     * @param array $dataPath
     */
    public function __construct(array $dataPath)
    {
        $this->dataPath = $dataPath;
    }

    /**
     * {@inheritdoc}
     */
    public function getDataPath(): array
    {
        return $this->dataPath;
    }

    /**
     * @return bool
     */
    public function isFieldSet(): bool
    {
        return $this->isFieldSet;
    }
}