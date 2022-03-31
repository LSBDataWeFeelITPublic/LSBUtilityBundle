<?php

namespace LSB\UtilityBundle\DTO\Request;

use LSB\UtilityBundle\Attribute\Resource;
use LSB\UtilityBundle\DTO\Model\DTOInterface;

class RequestData
{
    /**
     * @param DTOInterface|null $inputDTO
     * @param DTOInterface|null $outputDTO
     * @param array|null $outputData
     * @param Resource|null $resource
     * @param array $serializationGroups
     * @param int|null $responseStatusCode
     * @param object|null $entity
     */
    public function __construct(
        protected ?DTOInterface $inputDTO = null,
        protected ?DTOInterface $outputDTO = null,
        protected ?array        $outputData = null,
        protected ?Resource     $resource = null,
        protected array $serializationGroups = [],
        protected ?int $responseStatusCode = null,
        protected ?object $entity = null
    ) {
    }

    /**
     * @return DTOInterface|null
     */
    public function getInputDTO(): ?DTOInterface
    {
        return $this->inputDTO;
    }

    /**
     * @param DTOInterface|null $inputDTO
     * @return RequestData
     */
    public function setInputDTO(?DTOInterface $inputDTO): RequestData
    {
        $this->inputDTO = $inputDTO;
        return $this;
    }

    /**
     * @return DTOInterface|null
     */
    public function getOutputDTO(): ?DTOInterface
    {
        return $this->outputDTO;
    }

    /**
     * @param DTOInterface|null $outputDTO
     * @return RequestData
     */
    public function setOutputDTO(?DTOInterface $outputDTO): RequestData
    {
        $this->outputDTO = $outputDTO;
        return $this;
    }

    /**
     * @return array|null
     */
    public function getOutputData(): ?array
    {
        return $this->outputData;
    }

    /**
     * @param array|null $outputData
     * @return RequestData
     */
    public function setOutputData(?array $outputData): RequestData
    {
        $this->outputData = $outputData;
        return $this;
    }

    /**
     * @return Resource|null
     */
    public function getResource(): ?Resource
    {
        return $this->resource;
    }

    /**
     * @param Resource|null $resource
     * @return RequestData
     */
    public function setResource(?Resource $resource): RequestData
    {
        $this->resource = $resource;
        return $this;
    }

    /**
     * @return array
     */
    public function getSerializationGroups(): array
    {
        return $this->serializationGroups;
    }

    /**
     * @param array $serializationGroups
     * @return RequestData
     */
    public function setSerializationGroups(array $serializationGroups): RequestData
    {
        $this->serializationGroups = $serializationGroups;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getResponseStatusCode(): ?int
    {
        return $this->responseStatusCode;
    }

    /**
     * @param int|null $responseStatusCode
     * @return RequestData
     */
    public function setResponseStatusCode(?int $responseStatusCode): RequestData
    {
        $this->responseStatusCode = $responseStatusCode;
        return $this;
    }

    /**
     * @return object|null
     */
    public function getEntity(): ?object
    {
        return $this->entity;
    }

    /**
     * @param object|null $entity
     * @return RequestData
     */
    public function setEntity(?object $entity): RequestData
    {
        $this->entity = $entity;
        return $this;
    }
}