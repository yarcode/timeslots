<?php
namespace YarCode\TimeSlot;

/**
 * Class TimeSlotGroup
 * @package common\reservation
 */
class TimeSlotGroup implements \Iterator, \ArrayAccess, \Countable
{
    /** @var array */
    public $data = [];
    /** @var int */
    private $position = 0;
    /** @var TimeSlot[] */
    private $slots = [];

    /**
     * Makes time slots grid with the specified duration
     *
     * @param \DateTime $periodStart
     * @param \DateTime $periodEnd
     * @param int $slotDuration duration in minutes
     * @return static
     */
    public static function makeGrid(\DateTime $periodStart, \DateTime $periodEnd, $slotDuration)
    {
        $interval = new \DateInterval('PT'. $slotDuration .'M');
        $range = new \DatePeriod($periodStart, $interval, $periodEnd);

        $result = new static();

        foreach($range as $start) {
            /** @var \DateTime $end */
            $end = clone $start;
            $end->add($interval);
            $result[] = TimeSlot::createFromDateTime($start, $end);
        }

        return $result;
    }

    /**
     * Makes time slots grid with the specified duration, start is aligned every $alignment minutes
     *
     * @param \DateTime $periodStart
     * @param \DateTime $periodEnd
     * @param int $slotDuration duration in minutes
     * @param int $alignment alignment in minutes
     * @return TimeSlotGroup
     */
    public static function makeAlignedGrid(\DateTime $periodStart, \DateTime $periodEnd, $slotDuration, $alignment)
    {
        $alignmentInterval = new \DateInterval('PT'. $alignment .'M');
        $durationInterval = new \DateInterval('PT'. $slotDuration .'M');
        $range = new \DatePeriod($periodStart, $alignmentInterval, $periodEnd);

        $result = new static();

        foreach($range as $start) {
            /** @var \DateTime $end */
            $end = clone $start;
            $end->add($durationInterval);
            $result[] = TimeSlot::createFromDateTime($start, $end);
        }

        return $result;
    }

    /**
     * @param TimeSlot[] $data
     * @return TimeSlotGroup
     */
    public static function createFromArray($data)
    {
        $result = new static;
        $result->slots = $data;
        return $result;
    }

    /**
     * Makes intersection of events array with $target event group.
     *
     * @param TimeSlotGroup|TimeSlot[] $target
     */
    public function intersect($target)
    {
        $this->slots = TimeSlot::arrayIntersect($this->slots, $target);
    }

    /**
     * Filter slots by specified callback function
     * @param $callback
     */
    public function filter($callback)
    {
        $this->slots = array_filter($this->slots, $callback);
    }

    /**
     * Makes subtraction of events array with $target event group.
     *
     * @param TimeSlotGroup $target
     */
    public function subtract($target)
    {
        $this->slots = TimeSlot::arraySubtract($this->slots, $target);
    }

    /**
     * @param $callback
     * @return $this
     */
    public function sort($callback)
    {
        usort($this->slots, $callback);
        return $this;
    }

    /**
     * Makes merge of $source event array with $target event array.
     * When events intersect, event from $target will be taken.
     *
     * @param TimeSlotGroup $target
     * @param bool $cutIntersections
     */
    public function mergeWith(TimeSlotGroup $target, $cutIntersections = true)
    {
        $this->slots = TimeSlot::arrayMerge($this->slots, $target->slots, $cutIntersections);
    }

    public function removeDuplicates()
    {
        $this->slots = array_unique($this->slots);
    }

    /**
     * @return string
     */
    public function toJSON()
    {
        return json_encode($this->toArray());
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $result = [];
        foreach ($this->slots as $event) {
            $result[] = array_merge($this->data, $event->toArray());
        }
        return $result;
    }

    /**
     * @return TimeSlot[]
     */
    public function getSlots()
    {
        return $this->slots;
    }

    // Iterator interface implementation

    public function rewind()
    {
        $this->position = 0;
    }

    public function current()
    {
        return $this->slots[$this->position];
    }

    public function key()
    {
        return $this->position;
    }

    public function next()
    {
        ++$this->position;
    }

    public function valid()
    {
        return isset($this->slots[$this->position]);
    }

    // ArrayAccess interface implementation

    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->slots[] = $value;
        } else {
            $this->slots[$offset] = $value;
        }
    }

    public function offsetExists($offset)
    {
        return isset($this->slots[$offset]);
    }

    public function offsetUnset($offset)
    {
        unset($this->slots[$offset]);
    }

    public function offsetGet($offset)
    {
        return isset($this->slots[$offset]) ? $this->slots[$offset] : null;
    }

    // Countable implementation

    public function count()
    {
        return count($this->slots);
    }
}