<?php
/**
 * @author Alexey Samoylov <alexey.samoylov@gmail.com>
 */

class TimeSlotTest extends PHPUnit_Framework_TestCase
{
    public function testCreateFromCarbon()
    {
        $s1 = \Carbon\Carbon::now('UTC');
        $e1 = clone $s1;
        $e1->modify('+1 hour');

        $t1 = \YarCode\TimeSlot\TimeSlot::createFromCarbon($s1, $e1);
    }
}