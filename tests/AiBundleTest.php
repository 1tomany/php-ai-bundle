<?php

namespace OneToMany\AiBundle\Tests;

use OneToMany\AiBundle\AiBundle;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group('UnitTests')]
final class AiBundleTest extends TestCase
{
    public function testExtensionAlias(): void
    {
        $this->assertSame('onetomany_ai', new AiBundle()->getContainerExtension()?->getAlias());
    }
}
