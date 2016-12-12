<?php
namespace YarCode\TimeSlot;

use Carbon\Carbon;

/**
 * Class TimeSlot
 */
class TimeSlot
{
    /** @var Carbon */
    public $start;
    /** @var Carbon */
    public $end;
    /** @var array */
    private $data = [];

    /**
     * @param array $data
     */
    public function setData($data)
    {
        $this->data = $data;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Factory method. Creates time slot object from timestamps.
     *
     * @param integer $start
     * @param integer $end
     * @param array $data
     * @return TimeSlot
     */
    public static function createFromTimeStamp($start, $end, $data = [])
    {
        $start = Carbon::createFromTimestamp($start);
        $end = Carbon::createFromTimestamp($end);
        return static::createFromCarbon($start, $end, $data);
    }

    /**
     * Factory method. Creates time slot object from two DateTime objects.
     *
     * @param \DateTime $start
     * @param \DateTime $end
     * @param array $data
     * @return static
     */
    public static function createFromDateTime($start, $end, $data = [])
    {
        $start = Carbon::instance($start);
        $end = Carbon::instance($end);
        return static::createFromCarbon($start, $end, $data);
    }

    /**
     * Factory method. Creates time slot object from two Carbon objects.
     *
     * @param Carbon $start
     * @param Carbon $end
     * @param array $data
     * @return static
     */
    public static function createFromCarbon(Carbon $start, Carbon $end, $data = [])
    {
        $slot = new static();
        $slot->setData($data);

        $slot->start = clone $start;
        $slot->end = clone $end;

        return $slot;
    }

    /**
     * Returns merge of $source time slot array with $target time slot array.
     * When time slots intersect, time slots from $target will be taken.
     *
     * @param TimeSlot[] $source
     * @param TimeSlot[] $target
     * @param bool $cutIntersections
     * @return TimeSlot[]
     */
    public static function arrayMerge(array $source, array $target, $cutIntersections = true)
    {
        $prepend = $cutIntersections ? self::arraySubtract($source, $target) : $source;
        return array_values(array_merge($prepend, $target));
    }

    /**
     * Returns subtraction of $source time slots array with $target time slots array.
     *
     * @param TimeSlot[] $source
     * @param TimeSlot[] $target
     * @return TimeSlot[]
     */
    public static function arraySubtract($source, $target)
    {
        // removing intersections from source
        $source = array_filter($source, function ($v) use ($target) {
            foreach ($target as $item) {
                if ($item->intersectsWith($v))
                    return false;
            }
            return true;
        });
        return $source;
    }

    /**
     * Returns intersection of $source time slot array with $target slot array.
     *
     * @param TimeSlot[] $source
     * @param TimeSlot[] $target
     * @return TimeSlot[]
     */
    public static function arrayIntersect($source, $target)
    {
        // removing intersections from source
        $source = array_filter($source, function ($v) use ($target) {
            foreach ($target as $item) {
                if ($item->intersectsWith($v))
                    return true;
            }
            return false;
        });
        return $source;
    }

    /**
     * Checks if two slots are intersecting
     *
     * @param TimeSlot $compare
     * @return bool
     */
    public function intersectsWith(TimeSlot $compare)
    {
        $result = false;

        // comparing datetime objects
        if (
            ($compare->start <= $this->start and $compare->end > $this->start) // if intersecting through time slot start
            or
            ($compare->start < $this->end and $compare->end >= $this->end) // if intersecting through time slot end
            or
            ($compare->start <= $this->start and $compare->end >= $this->end) // if compare covers us
            or
            ($compare->start >= $this->start and $compare->end <= $this->end) // if we contain compare
        ) {
            $result = true;
        }

        return $result;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return array_merge($this->data, [
            'start' => $this->start->toIso8601String(),
            'end' => $this->end->toIso8601String(),
        ]);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        $start = clone $this->start;
        $start->setTimezone('UTC');
        return $start->toIso8601String();
    }
}