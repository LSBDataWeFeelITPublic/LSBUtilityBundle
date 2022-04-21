<?php

namespace LSB\UtilityBundle\DTO\Model\Input;

use LSB\UtilityBundle\DTO\Model\BaseDTO;

abstract class BaseCollectionInputDTO extends BaseDTO implements CollectionInputDTOInterface
{
    protected $limit = 10;

    protected $page = 1;

    protected $sort;

    protected $order = 'ASC';

    /**
     * @return int
     */
    public function getLimit(): int
    {
        return $this->limit;
    }

    /**
     * @param int $limit
     * @return BaseCollectionInputDTO
     */
    public function setLimit(int $limit): BaseCollectionInputDTO
    {
        $this->limit = $limit;
        return $this;
    }

    /**
     * @return int
     */
    public function getPage(): int
    {
        return $this->page;
    }

    /**
     * @param int $page
     * @return BaseCollectionInputDTO
     */
    public function setPage(int $page): BaseCollectionInputDTO
    {
        $this->page = $page;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getSort()
    {
        return $this->sort;
    }

    /**
     * @param mixed $sort
     * @return BaseCollectionInputDTO
     */
    public function setSort($sort)
    {
        $this->sort = $sort;
        return $this;
    }

    /**
     * @return string
     */
    public function getOrder(): string
    {
        return $this->order;
    }

    /**
     * @param string $order
     * @return BaseCollectionInputDTO
     */
    public function setOrder(string $order): BaseCollectionInputDTO
    {
        $this->order = $order;
        return $this;
    }


}