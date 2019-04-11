<?php


namespace Gtt\Bundle\DataTransformerBundle\Fields\FieldSource;

/**
 * Interface FieldSourceInterface
 */
interface FieldSourceInterface
{
    /**
     * @return array
     */
    public function getDataPath(): array;

    /**
     * @return bool
     */
    public function isFieldSet(): bool;
}