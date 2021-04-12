<?php
declare(strict_types=1);

namespace LSB\UtilityBundle\Interfaces;

use Doctrine\Common\Collections\Collection;
use LSB\ProductBundle\Entity\Category;
use LSB\ProductBundle\Entity\CategoryInterface;

/**
 * Interface NestedTreeInterface
 * @package LSB\UtilityBundle\Interfaces
 */
interface NestedTreeInterface extends IdInterface
{
    /**
     * @return int|null
     */
    public function getLft(): ?int;
    /**
     * @param int|null $lft
     * @return $this
     */
    public function setLft(?int $lft): self;

    /**
     * @return int|null
     */
    public function getLvl(): ?int;

    /**
     * @param int|null $lvl
     * @return $this
     */
    public function setLvl(?int $lvl): self;

    /**
     * @return int|null
     */
    public function getRgt(): ?int;

    /**
     * @param int|null $rgt
     * @return $this
     */
    public function setRgt(?int $rgt): self;

    /**
     * @return CategoryInterface|null
     */
    public function getRoot(): ?CategoryInterface;

    /**
     * @param CategoryInterface|null $root
     * @return $this
     */
    public function setRoot(?CategoryInterface $root): self;

    /**
     * @return CategoryInterface|null
     */
    public function getParent(): ?CategoryInterface;

    /**
     * @param CategoryInterface|null $parent
     * @return $this
     */
    public function setParent(?CategoryInterface $parent): self;

    /**
     * @return Collection
     */
    public function getChildren(): Collection;

    /**
     * @param CategoryInterface $children
     *
     * @return $this
     */
    public function addChildren(CategoryInterface $children): self;

    /**
     * @param CategoryInterface $children
     *
     * @return $this
     */
    public function removeChildren(CategoryInterface $children): self;

    /**
     * @param Collection $children
     * @return $this
     */
    public function setChildren(Collection $children): self;
}