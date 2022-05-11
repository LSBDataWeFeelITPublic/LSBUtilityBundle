<?php

namespace LSB\UtilityBundle\DataTransfer\Model\Output;

use LSB\UtilityBundle\DataTransfer\Model\BaseDTO;

abstract class BaseCollectionOutputDTO extends BaseDTO implements CollectionOutputDTOInterface
{
    /**
     * @var array
     */
    public array $items = [];

    public ?int $currentPageNumber = null;

    public ?int $totalItemCount = null;

    public ?int $page = null;

    public ?int $itemNumberPerPage = null;
}