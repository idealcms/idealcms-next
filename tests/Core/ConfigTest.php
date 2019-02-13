<?php
namespace Ideal\Test;

use Ideal\Core\Config;
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    public function testImport()
    {
        $config = new Config();

        $config->import(['test' => 'value']);
        $this->assertContains('value', $config->test);

        $config->import(['test' => 'value2']);
        $this->assertContains('value2', $config->test);
    }
}
