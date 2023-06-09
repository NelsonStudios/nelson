<?php
/**
 * @author Aitoc Team
 * @copyright Copyright (c) 2021 Aitoc (https://www.aitoc.com)
 * @package Aitoc_DimensionalShipping
 */


namespace Aitoc\DimensionalShipping\Model\Convertor;

/*
 * This file is part of the Convertor package.
 *
 * (c) Oliver Folkerd <oliver.folkerd@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Magento\Framework\Exception\CouldNotSave\Exception;

class Convertor
{
    private $value = null; //value to convert
    private $baseUnit = false; //base unit of value

    //array to hold unit conversion functions
    private $units = [];

    /**
     * Construt Object
     *
     * @param    number $value -  a numeric value to base conversions on
     * @param    string $unit  (optional) - the unit symbol for the start value
     *
     * @return    an instance of the Convertor object
     */
    public function __construct($value, $unit)
    {

        //create units array
        $this->defineUnits();

        //unit optional
        if (!is_null($value)) {
            //set from unit
            $this->from($value, $unit);
        }

    }

    /**
     * setup units conversion array
     */
    public function defineUnits()
    {

        $this->units = [
            ///////Units Of Length///////
            "m"     => ["base" => "m", "conversion" => 1], //meter - base unit for distance
            "km"    => ["base" => "m", "conversion" => 1000], //kilometer
            "dm"    => ["base" => "m", "conversion" => 0.1], //decimeter
            "cm"    => ["base" => "m", "conversion" => 0.01], //centimeter
            "mm"    => ["base" => "m", "conversion" => 0.001], //milimeter
            "μm"    => ["base" => "m", "conversion" => 0.000001], //micrometer
            "nm"    => ["base" => "m", "conversion" => 0.000000001], //nanometer
            "pm"    => ["base" => "m", "conversion" => 0.000000000001], //picometer
            "in"    => ["base" => "m", "conversion" => 0.0254], //inch
            "ft"    => ["base" => "m", "conversion" => 0.3048], //foot
            "yd"    => ["base" => "m", "conversion" => 0.9144], //yard
            "mi"    => ["base" => "m", "conversion" => 1609.344], //mile
            "h"     => ["base" => "m", "conversion" => 0.1016], //hand
            "ly"    => ["base" => "m", "conversion" => 9460730472580800], //lightyear
            "au"    => ["base" => "m", "conversion" => 149597870700], //astronomical unit
            "pc"    => ["base" => "m", "conversion" => 30856775814913672.789139379577965], //parsec


            ///////Units Of Area///////
            "m2"    => ["base" => "m2", "conversion" => 1], //meter square - base unit for area
            "km2"   => ["base" => "m2", "conversion" => 1000000], //kilometer square
            "cm2"   => ["base" => "m2", "conversion" => 0.0001], //centimeter square
            "mm2"   => ["base" => "m2", "conversion" => 0.000001], //milimeter square
            "ft2"   => ["base" => "m2", "conversion" => 0.092903], //foot square
            "mi2"   => ["base" => "m2", "conversion" => 2589988.11], //mile square
            "ac"    => ["base" => "m2", "conversion" => 4046.86], //acre
            "ha"    => ["base" => "m2", "conversion" => 10000], //hectare

            ///////Units Of Volume///////
            "l"     => ["base" => "l", "conversion" => 1], //litre - base unit for volume
            "ml"    => ["base" => "l", "conversion" => 0.001], //mililitre
            "m3"    => ["base" => "l", "conversion" => 1], //meters cubed
            "pt"    => ["base" => "l", "conversion" => 0.56826125], //pint
            "gal"   => ["base" => "l", "conversion" => 4.405], //gallon

            ///////Units Of Weight///////
            "kg"    => ["base" => "kg", "conversion" => 1], //kilogram - base unit for weight
            "g"     => ["base" => "kg", "conversion" => 0.001], //gram
            "mg"    => ["base" => "kg", "conversion" => 0.000001], //miligram
            "N"     => ["base" => "kg", "conversion" => 9.80665002863885], //Newton (based on earth gravity)
            "st"    => ["base" => "kg", "conversion" => 6.35029], //stone
            "lb"    => ["base" => "kg", "conversion" => 0.453592], //pound
            "oz"    => ["base" => "kg", "conversion" => 0.0283495], //ounce
            "t"     => ["base" => "kg", "conversion" => 1000], //metric tonne
            "ukt"   => ["base" => "kg", "conversion" => 1016.047], //UK Long Ton
            "ust"   => ["base" => "kg", "conversion" => 907.1847], //US short Ton

            //////Units Of Speed///////
            "mps"   => ["base" => "mps", "conversion" => 1], //meter per seond - base unit for speed
            "kph"   => ["base" => "mps", "conversion" => 0.44704], //kilometer per hour
            "mph"   => ["base" => "mps", "conversion" => 0.277778], //kilometer per hour

            ///////Units Of Rotation///////
            "deg"   => ["base" => "deg", "conversion" => 1], //degrees - base unit for rotation
            "rad"   => ["base" => "deg", "conversion" => 57.2958], //radian

            ///////Units Of Temperature///////
            "k"     => ["base" => "k", "conversion" => 1], //kelvin - base unit for distance
            "c"     => [
                "base"       => "k",
                "conversion" => function ($val, $tofrom) {
                    return $tofrom ? $val - 273.15 : $val + 273.15;
                }
            ], //celsius
            "f"     => [
                "base"       => "k",
                "conversion" => function ($val, $tofrom) {
                    return $tofrom ? ($val * 9 / 5 - 459.67) : (($val + 459.67) * 5 / 9);
                }
            ], //Fahrenheit

            ///////Units Of Pressure///////
            "pa"    => ["base" => "Pa", "conversion" => 1], //Pascal - base unit for Pressure
            "kpa"   => ["base" => "Pa", "conversion" => 1000], //kilopascal
            "mpa"   => ["base" => "Pa", "conversion" => 1000000], //megapascal
            "bar"   => ["base" => "Pa", "conversion" => 100000], //bar
            "mbar"  => ["base" => "Pa", "conversion" => 100], //milibar
            "psi"   => ["base" => "Pa", "conversion" => 6894.76], //pound-force per square inch

            ///////Units Of Time///////
            "s"     => ["base" => "s", "conversion" => 1], //second - base unit for time
            "year"  => ["base" => "s", "conversion" => 31536000], //year - standard year
            "month" => ["base" => "s", "conversion" => 18748800], //month - 31 days
            "week"  => ["base" => "s", "conversion" => 604800], //week
            "day"   => ["base" => "s", "conversion" => 86400], //day
            "hr"    => ["base" => "s", "conversion" => 3600], //hour
            "min"   => ["base" => "s", "conversion" => 30], //minute
            "ms"    => ["base" => "s", "conversion" => 0.001], //milisecond
            "μs"    => ["base" => "s", "conversion" => 0.000001], //microsecond
            "ns"    => ["base" => "s", "conversion" => 0.000000001], //nanosecond

            ///////Units Of Power///////ß
            "j"     => ["base" => "j", "conversion" => 1], //joule - base unit for energy
            "kj"    => ["base" => "j", "conversion" => 1000], //kilojoule
            "mj"    => ["base" => "j", "conversion" => 1000000], //megajoule
            "cal"   => ["base" => "j", "conversion" => 4184], //calorie
            "Nm"    => ["base" => "j", "conversion" => 1], //newton meter
            "ftlb"  => ["base" => "j", "conversion" => 1.35582], //foot pound
            "whr"   => ["base" => "j", "conversion" => 3600], //watt hour
            "kwhr"  => ["base" => "j", "conversion" => 3600000], //kilowatt hour
            "mwhr"  => ["base" => "j", "conversion" => 3600000000], //megawatt hour
            "mev"   => ["base" => "j", "conversion" => 0.00000000000000016], //mega electron volt
        ];
    }

    /**
     * Set from conversion value / unit
     *
     * @param    number $value -  a numeric value to base conversions on
     * @param    string $unit  (optional) - the unit symbol for the start value
     *
     * @return   none
     */
    public function from($value, $unit)
    {

        //check if value has been set
        if (is_null($value)) {
            throw new \Exception("Value Not Set");
        }

        if ($unit) {
            //check that unit exists
            if (array_key_exists($unit, $this->units)) {
                $unitLookup = $this->units[$unit];

                //convert unit to base unit for this unit type
                $this->baseUnit = $unitLookup["base"];
                $this->value    = $this->convertToBase($value, $unitLookup);
            } else {
                throw new \Exception("Unit Does Not Exist");
            }
        } else {
            $this->value = $value;
        }
    }

    /**
     * Convert from value to its base unit
     *
     * @param    number $value     - from value
     * @param    array  $unitArray - unit array from object units array
     *
     * @return   number - converted value
     */
    private function convertToBase($value, $unitArray)
    {

        if (is_callable($unitArray["conversion"])) {
            // if unit has a conversion function, run value through it
            return $unitArray["conversion"]($value, false);
        } else {
            return $value * $unitArray["conversion"];
        }
    }

    /**
     * Convert from value to new unit
     *
     * @param    string[] $unit     -  the unit symbol (or array of symblos) for the conversion unit
     * @param    int      $decimals (optional, default-null) - the decimal precision of the conversion result
     * @param    boolean  $round    (optional, default-true) - round or floor the conversion result
     *
     * @return   none
     */
    public function to($unit, $decimals = null, $round = true)
    {
        //check if from value is set
        if (is_null($this->value)) {
            throw new \Exception("From Value Not Set");
        }

        //check if to unit is set
        if (!$unit) {
            throw new \Exception("Unit Not Set");
        }

        //if unit is array, itterate through each unit
        if (is_array($unit)) {
            return $this->toMany($unit, $decimals, $round);
        } else {
            //check unit symbol exists
            if (array_key_exists($unit, $this->units)) {
                $unitLookup = $this->units[$unit];

                $result = 0;

                //if from unit not provided, asume base unit of to unit type
                if ($this->baseUnit) {
                    if ($unitLookup["base"] != $this->baseUnit) {
                        throw new \Exception("Cannot Convert Between Units of Different Types");
                    }
                } else {
                    $this->baseUnit = $unitLookup["base"];
                }

                //calculate converted value
                if (is_callable($unitLookup["conversion"])) {
                    // if unit has a conversion function, run value through it
                    $result = $unitLookup["conversion"]($this->value, true);
                } else {
                    $result = $this->value / $unitLookup["conversion"];
                }

                //result precision and rounding
                if (!is_null($decimals)) {
                    if ($round) {
                        //round to the specifidd number of decimals
                        $result = round($result, $decimals);
                    } else {
                        //truncate to the nearest number of decimals
                        $shifter = $decimals ? pow(10, $decimals) : 1;
                        $result  = floor($result * $shifter) / $shifter;
                    }
                }

                return $result;
            } else {
                throw new \Exception("Unit Does Not Exist");
            }
        }
    }

    /**
     * Itterate through multiple unit conversions
     *
     * @param    string[] $unit     -  the array of symblos for the conversion units
     * @param    int      $decimals (optional, default-null) - the decimal precision of the conversion result
     * @param    boolean  $round    (optional, default-true) - round or floor the conversion result
     *
     * @return   array - results of the coversions
     */
    private function toMany($unitList = [], $decimals = null, $round = true)
    {
        $resultList = [];
        foreach ($unitList as $key) {
            //convert units for each element in the array
            $resultList[$key] = $this->to($key, $decimals, $round);
        }

        return $resultList;
    }

    /**
     * Add Conversion Unit
     *
     * @param    string $unit  - the symbol for the new unit
     * @param    string $base  - the symbol for the base unit of this unit
     * @param           number /function() - the conversion ration or conversion function from this unit to its base unit
     *
     * @return   boolean - true - if successfull
     */
    public function addUnit($unit, $base, $conversion)
    {

        //check that the new unit does not ealread exist
        if (array_key_exists($unit, $this->units)) {
            throw new \Exception("Unit Already Exists");
        } else {
            //make sure the base unit for the new unit exists or that the new unit is a base unit itself
            if (!array_key_exists($base, $this->units) && $base != $unit) {
                throw new \Exception("Base Unit Does Not Exist");
            } else {
                //add unit to units array
                $this->units[$unit] = ["base" => $base, "conversion" => $conversion];

                return true;
            }
        }

    }

    /**
     * Remove Conversion Unit
     *
     * @param    string $unit - the symbol for the unit to be removed
     *
     * @return   boolean - true - if successfull
     */
    public function removeUnit($unit)
    {
        //check unit exists
        if (array_key_exists($unit, $this->units)) {
            //if unit is base unit remove all dependant units
            if ($this->units[$unit]["base"] == $unit) {
                foreach ($this->units as $key => $values) {
                    if ($values["base"] == $unit) {
                        unset($this->units[$key]);
                    }
                }
            } else {
                //remove unit
                unset($this->units[$unit]);
            }

            return true;
        } else {
            throw new \Exception("Unit Does Not Exist");
        }
    }

    /**
     * List all available conversion units for given unit
     *
     * @param    string $unit - the symbol to search for available conversion units
     *
     * @return   array - list of all available conversion units
     */
    public function getUnits($unit)
    {
        //check that unit exists
        if (array_key_exists($unit, $this->units)) {
            $baseUnit = $this->units[$unit]["base"];
            $unitList = [];
            //find all units that are linked to the base unit
            foreach ($this->units as $key => $values) {
                if ($values["base"] == $baseUnit) {
                    array_push($unitList, $key);
                }
            }

            return $unitList;
        } else {
            throw new \Exception("Unit Does Not Exist");
        }
    }
}
