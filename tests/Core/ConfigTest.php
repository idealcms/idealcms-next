<?php
namespace Ideal\Test;

use Ideal\Core\Config;
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    public function testImport(): void
    {
        $config = new Config();

        $config->import(['test' => 'value']);
        $this->assertStringContainsString('value', $config->test);

        $config->import(['test' => 'value2']);
        $this->assertStringContainsString('value2', $config->test);
    }
}
