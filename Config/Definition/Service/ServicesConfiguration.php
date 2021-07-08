<?php
declare(strict_types=1);

namespace LSB\UtilityBundle\Config\Definition\Service;

/**
 * Class ServicesConfiguration
 * @package LSB\UtilityBundle\Config\Definition\Service
 */
class ServicesConfiguration
{
    /**
     * @var array
     */
    protected array $list = [];

    /**
     * @param string $interfaceClass
     * @param string $serviceClass
     * @return $this
     */
    public function add(string $interfaceClass, string $serviceClass): self
    {
        if (!array_key_exists($interfaceClass, $this->list)) {
            $this->list[$interfaceClass] = $serviceClass;
        }

        return $this;
    }

    /**
     * @param string $interfaceClass
     * @return $this
     */
    public function remove(string $interfaceClass): self
    {
        if (array_key_exists($interfaceClass, $this->list)) {
            unset($this->list[$interfaceClass]);
        }

        return $this;
    }

    /**
     * @return array
     */
    public function getList(): array
    {
        return $this->list;
    }

    /**
     * @param array $list
     * @return ServicesConfiguration
     */
    public function setList(array $list): ServicesConfiguration
    {
        $this->list = $list;
        return $this;
    }


}