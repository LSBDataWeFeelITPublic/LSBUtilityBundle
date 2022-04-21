<?php

namespace LSB\UtilityBundle;

use LSB\UtilityBundle\DependencyInjection\Compiler\AddDataTransformerModulePass;
use LSB\UtilityBundle\DependencyInjection\Compiler\AddManagerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class LSBUtilityBundle extends Bundle
{
    public function build(ContainerBuilder $builder)
    {
        parent::build($builder);

        $builder
            ->addCompilerPass(new AddManagerPass())
            ->addCompilerPass(new AddDataTransformerModulePass())
        ;
    }
}
