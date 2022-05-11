<?php

namespace LSB\UtilityBundle\DataTransfer\Request;

use LSB\UtilityBundle\Attribute\Resource;
use LSB\UtilityBundle\DataTransfer\Model\DTOInterface;

class RequestData
{
    /**
     * @param DTOInterface|null $inputDTO
     * @param DTOInterface|null $outputDTO
     * @param array|null $outputData
     * @param Resource|null $resource
     * @param array $serializationGroups
     * @param int|null $responseStatusCode
     * @param object|null $object
     * @param bool $isGranted
     * @param bool $isObjectFetched
     * @param null $responseContent
     */
    public function __construct(
        protected ?DTOInterface $inputDTO = null,
        protected ?DTOInterface $outputDTO = null,
        protected ?array        $outputData = null,
        protected ?Resource     $resource = null,
        protected array         $serializationGroups = [],
        protected ?int          $responseStatusCode = null,
        protected ?object       $object = null,
        protected bool          $isGranted = false,
        protected bool          $isObjectFetched = false,
        protected               $responseContent = null
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
    public function getObject(): ?object
    {
        return $this->object;
    }

    /**
     * @param object|null $object
     * @return RequestData
     */
    public function setObject(?object $object): RequestData
    {
        $this->object = $object;

        if ($object) {
            $this->isObjectFetched = true;
        }

        return $this;
    }

    /**
     * @return bool
     */
    public function isGranted(): bool
    {
        return $this->isGranted;
    }

    /**
     * @param bool $isGranted
     * @return RequestData
     */
    public function setIsGranted(bool $isGranted): RequestData
    {
        $this->isGranted = $isGranted;
        return $this;
    }

    /**
     * @return bool
     */
    public function isObjectFetched(): bool
    {
        return $this->isObjectFetched;
    }

    /**
     * @param bool $isObjectFetched
     * @return RequestData
     */
    public function setIsObjectFetched(bool $isObjectFetched): RequestData
    {
        $this->isObjectFetched = $isObjectFetched;
        return $this;
    }

    /**
     * @return null
     */
    public function getResponseContent()
    {
        return $this->responseContent;
    }

    /**
     * @param null $responseContent
     * @return RequestData
     */
    public function setResponseContent($responseContent)
    {
        $this->responseContent = $responseContent;
        return $this;
    }
}