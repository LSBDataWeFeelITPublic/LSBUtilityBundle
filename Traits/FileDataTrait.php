<?php
declare(strict_types=1);

namespace LSB\UtilityBundle\Traits;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Trait FileDataTrait
 * @package LSB\UtiltyBundle\Entity
 */
trait FileDataTrait
{

    /**
     * @var string|null
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\Length(max="255")
     */
    protected ?string $fileName;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\Length(max="255")
     */
    protected ?string $originalFileName;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\Length(max="255")
     */
    protected ?string $extension;

    /**
     * @var int
     * @ORM\Column(type="integer", nullable=true)
     */
    protected int $size;

    /**
     * @deprecated
     * @var null
     */
    protected $uploadedFileEmpty = null;

    /**
     * @return string|null
     */
    public function getName() {
        if ($this->originalFileName && $this->fileName) {
            return sprintf('%s (%s, %s b)', $this->originalFileName, $this->fileName, $this->size);
        }

        return null;
    }

    /**
     * @return string|null
     */
    public function getFileName(): ?string
    {
        return $this->fileName;
    }

    /**
     * @param string|null $fileName
     * @return $this
     */
    public function setFileName(?string $fileName): self
    {
        $this->fileName = $fileName;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getOriginalFileName(): ?string
    {
        return $this->originalFileName;
    }

    /**
     * @param string|null $originalFileName
     * @return $this
     */
    public function setOriginalFileName(?string $originalFileName): self
    {
        $this->originalFileName = $originalFileName;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getExtension(): ?string
    {
        return $this->extension;
    }

    /**
     * @param string|null $extension
     * @return $this
     */
    public function setExtension(?string $extension): self
    {
        $this->extension = $extension;
        return $this;
    }

    /**
     * @return int
     */
    public function getSize(): int
    {
        return $this->size;
    }

    /**
     * @param int $size
     * @return $this
     */
    public function setSize(int $size): self
    {
        $this->size = $size;
        return $this;
    }

    /**
     * @return null
     */
    public function getUploadedFileEmpty()
    {
        return $this->uploadedFileEmpty;
    }

    /**
     * @param null $uploadedFileEmpty
     * @return $this
     */
    public function setUploadedFileEmpty($uploadedFileEmpty)
    {
        $this->uploadedFileEmpty = $uploadedFileEmpty;
        return $this;
    }

}