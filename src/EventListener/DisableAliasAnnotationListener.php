<?php

declare(strict_types=1);

namespace App\EventListener;

use Doctrine\Common\Annotations\AnnotationReader;

final class DisableAliasAnnotationListener
{
    public function onKernelRequest(): void
    {
        AnnotationReader::addGlobalIgnoredName('alias');
    }
}
