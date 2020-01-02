<?php
namespace Ideal\Test;

use PHPUnit\Framework\TestCase;
use Ideal\Core\Di;

class DiTest extends TestCase
{
    public function testCreateDice(): void
    {
        $dice = Di::getInstance();
        $this->assertInstanceOf('Ideal\Core\Di', $dice);
    }
}
