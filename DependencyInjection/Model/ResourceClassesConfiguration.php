<?php
declare(strict_types=1);

namespace LSB\UtilityBundle\DependencyInjection\Model;

use Symfony\Component\DependencyInjection\Definition;

/**
 * Class ResourceClassesConfiguration
 * @package LSB\UtilityBundle\DependencyInjection\Model
 */
class ResourceClassesConfiguration
{
    protected ?string $entityClass = null;

    protected ?string $translationEntityClass = null;

    protected ?string $factoryClass = null;

    protected ?Definition $factoryDefinition = null;

    protected ?string $managerClass = null;

    protected ?Definition $managerDefinition = null;

    protected ?string $formClass = null;

    protected ?Definition $formDefinition = null;

    protected ?string $translationFormClass = null;

    protected ?Definition $translationFormDefinition = null;

    protected ?string $repositoryClass = null;

    protected ?Definition $repositoryDefinition = null;

    protected ?string $voterSubjectClass = null;

    /**
     * ResourceClassesConfiguration constructor.
     * @param string|null $entityClass
     * @param string|null $translationEntityClass
     * @param string|null $factoryClass
     * @param string|null $managerClass
     * @param string|null $repositoryClass
     * @param string|null $formClass
     * @param string|null $translationFormClass
     * @param string|null $voterSubjectClass
     */
    public function __construct(
        ?string $entityClass = null,
        ?string $translationEntityClass = null,
        ?string $factoryClass = null,
        ?string $managerClass = null,
        ?string $repositoryClass = null,
        ?string $formClass = null,
        ?string $translationFormClass = null,
        ?string $voterSubjectClass = null

    ) {
        $this->entityClass = $entityClass;
        $this->translationEntityClass = $translationEntityClass;
        $this->factoryClass = $factoryClass;
        $this->managerClass = $managerClass;
        $this->formClass = $formClass;
        $this->translationFormClass = $translationFormClass;
        $this->repositoryClass = $repositoryClass;
        $this->voterSubjectClass = $voterSubjectClass;
    }

    /**
     * @return string|null
     */
    public function getEntityClass(): ?string
    {
        return $this->entityClass;
    }

    /**
     * @param string|null $entityClass
     * @return ResourceClassesConfiguration
     */
    public function setEntityClass(?string $entityClass): ResourceClassesConfiguration
    {
        $this->entityClass = $entityClass;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getTranslationEntityClass(): ?string
    {
        return $this->translationEntityClass;
    }

    /**
     * @param string|null $translationEntityClass
     * @return ResourceClassesConfiguration
     */
    public function setTranslationEntityClass(?string $translationEntityClass): ResourceClassesConfiguration
    {
        $this->translationEntityClass = $translationEntityClass;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getFactoryClass(): ?string
    {
        return $this->factoryClass;
    }

    /**
     * @param string|null $factoryClass
     * @return ResourceClassesConfiguration
     */
    public function setFactoryClass(?string $factoryClass): ResourceClassesConfiguration
    {
        $this->factoryClass = $factoryClass;
        return $this;
    }

    /**
     * @return Definition|null
     */
    public function getFactoryDefinition(): ?Definition
    {
        return $this->factoryDefinition;
    }

    /**
     * @param Definition|null $factoryDefinition
     * @return ResourceClassesConfiguration
     */
    public function setFactoryDefinition(?Definition $factoryDefinition): ResourceClassesConfiguration
    {
        $this->factoryDefinition = $factoryDefinition;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getManagerClass(): ?string
    {
        return $this->managerClass;
    }

    /**
     * @param string|null $managerClass
     * @return ResourceClassesConfiguration
     */
    public function setManagerClass(?string $managerClass): ResourceClassesConfiguration
    {
        $this->managerClass = $managerClass;
        return $this;
    }

    /**
     * @return Definition|null
     */
    public function getManagerDefinition(): ?Definition
    {
        return $this->managerDefinition;
    }

    /**
     * @param Definition|null $managerDefinition
     * @return ResourceClassesConfiguration
     */
    public function setManagerDefinition(?Definition $managerDefinition): ResourceClassesConfiguration
    {
        $this->managerDefinition = $managerDefinition;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getFormClass(): ?string
    {
        return $this->formClass;
    }

    /**
     * @param string|null $formClass
     * @return ResourceClassesConfiguration
     */
    public function setFormClass(?string $formClass): ResourceClassesConfiguration
    {
        $this->formClass = $formClass;
        return $this;
    }

    /**
     * @return Definition|null
     */
    public function getFormDefinition(): ?Definition
    {
        return $this->formDefinition;
    }

    /**
     * @param Definition|null $formDefinition
     * @return ResourceClassesConfiguration
     */
    public function setFormDefinition(?Definition $formDefinition): ResourceClassesConfiguration
    {
        $this->formDefinition = $formDefinition;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getTranslationFormClass(): ?string
    {
        return $this->translationFormClass;
    }

    /**
     * @param string|null $translationFormClass
     * @return ResourceClassesConfiguration
     */
    public function setTranslationFormClass(?string $translationFormClass): ResourceClassesConfiguration
    {
        $this->translationFormClass = $translationFormClass;
        return $this;
    }

    /**
     * @return Definition|null
     */
    public function getTranslationFormDefinition(): ?Definition
    {
        return $this->translationFormDefinition;
    }

    /**
     * @param Definition|null $translationFormDefinition
     * @return ResourceClassesConfiguration
     */
    public function setTranslationFormDefinition(?Definition $translationFormDefinition): ResourceClassesConfiguration
    {
        $this->translationFormDefinition = $translationFormDefinition;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getRepositoryClass(): ?string
    {
        return $this->repositoryClass;
    }

    /**
     * @param string|null $repositoryClass
     * @return ResourceClassesConfiguration
     */
    public function setRepositoryClass(?string $repositoryClass): ResourceClassesConfiguration
    {
        $this->repositoryClass = $repositoryClass;
        return $this;
    }

    /**
     * @return Definition|null
     */
    public function getRepositoryDefinition(): ?Definition
    {
        return $this->repositoryDefinition;
    }

    /**
     * @param Definition|null $repositoryDefinition
     * @return ResourceClassesConfiguration
     */
    public function setRepositoryDefinition(?Definition $repositoryDefinition): ResourceClassesConfiguration
    {
        $this->repositoryDefinition = $repositoryDefinition;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getVoterSubjectClass(): ?string
    {
        return $this->voterSubjectClass;
    }

    /**
     * @param string|null $voterSubjectClass
     * @return ResourceClassesConfiguration
     */
    public function setVoterSubjectClass(?string $voterSubjectClass): ResourceClassesConfiguration
    {
        $this->voterSubjectClass = $voterSubjectClass;
        return $this;
    }
}