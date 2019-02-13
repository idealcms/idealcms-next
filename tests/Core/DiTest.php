<?php
namespace Ideal\Test;

use PHPUnit\Framework\TestCase;
use Ideal\Core\Di;

class DiTest extends TestCase
{
    public function testCreateDice()
    {
        $dice = Di::getInstance();
        $this->assertInstanceOf('DI\Container', $dice);
    }
}
