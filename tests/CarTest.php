<?php

use PHPUnit\Framework\TestCase;

const MAX_INPUT_MILEAGE = 150000;
const MIN_INPUT_MILEAGE = 0;
const INC_MILEAGE = 1000;
const MAX_MILEAGE = 153000;

class CarTest extends TestCase
{
public $years = array(
array("modelyear" => 2003, "suffix1" => 15),
array("modelyear" => 2004, "suffix1" => 14),
array("modelyear" => 2005, "suffix1" => 13),
array("modelyear" => 2006, "suffix1" => 12),
array("modelyear" => 2007, "suffix1" => 11),
array("modelyear" => 2008, "suffix1" => 10),
array("modelyear" => 2009, "suffix1" => 9),
array("modelyear" => 2010, "suffix1" => 8),
array("modelyear" => 2011, "suffix1" => 7),
array("modelyear" => 2012, "suffix1" => 6),
array("modelyear" => 2013, "suffix1" => 5),
array("modelyear" => 2014, "suffix1" => 4),
array("modelyear" => 2015, "suffix1" => 3),
array("modelyear" => 2016, "suffix1" => 2),
array("modelyear" => 2017, "suffix1" => 1),
array("modelyear" => 2018, "suffix1" => 0),
array("modelyear" => 2019, "suffix1" => 0));

// mileage of the vehicle at the time the contract is rated
public $issue_mileage = array(
array("min" => 0, "max" => 12000, "suffix2" => "A"),
array("min" => 12001, "max" => 24000, "suffix2" => "A"),
array("min" => 24001, "max" => 36000, "suffix2" => "B"),
array("min" => 36001, "max" => 48000, "suffix2" => "C"),
array("min" => 48001, "max" => 60000, "suffix2" => "D"),
array("min" => 60001, "max" => 72000, "suffix2" => "E"),
array("min" => 72001, "max" => 84000, "suffix2" => "F"),
array("min" => 84001, "max" => 96000, "suffix2" => "G"),
array("min" => 96001, "max" => 108000, "suffix2" => "H"),
array("min" => 108001, "max" => 120000, "suffix2" => "I"),
array("min" => 120001, "max" => 132000, "suffix2" => "J"),
array("min" => 132001, "max" => 144000, "suffix2" => "K"),
array("min" => 144001, "max" => 150000, "suffix2" => "L")
);

public $base_warranty = array(
array("make" => "BMW", "term" => 36, "miles" => 48000),
array("make" => "Volkswagen", "term" => 72, "miles" => 100000)
);

public $coverage = array(
array("name" => "3 Months/3,000 Miles", "terms" => 3, "miles" => 3000),
array("name" => "6 Months/12,000 Miles", "terms" => 6, "miles" => 12000),
array("name" => "12 Months/24,000 Miles", "terms" => 12, "miles" => 24000),
array("name" => "24 Months/30,000 Miles", "terms" => 24, "miles" => 30000),
array("name" => "24 Months/36,000 Miles", "terms" => 24, "miles" => 36000),
array("name" => "36 Months/36,000 Miles", "terms" => 36, "miles" => 36000),
array("name" => "36 Months/45,000 Miles", "terms" => 36, "miles" => 45000),
array("name" => "36 Months/50,000 Miles", "terms" => 36, "miles" => 50000),
array("name" => "48 Months/50,000 Miles", "terms" => 48, "miles" => 50000),
array("name" => "48 Months/60,000 Miles", "terms" => 48, "miles" => 60000),
array("name" => "60 Months/72,000 Miles", "terms" => 60, "miles" => 72000),
array("name" => "60 Months/75,000 Miles", "terms" => 60, "miles" => 75000),
array("name" => "72 Months/100,000 Miles", "terms" => 72, "miles" => 100000),
array("name" => "84 Months/84,000 Miles", "terms" => 84, "miles" => 84000),
array("name" => "84 Months/96,000 Miles", "terms" => 84, "miles" => 96000),
array("name" => "100 Months/100,000 Miles", "terms" => 100, "miles" => 100000),
array("name" => "100 Months/120,000 Miles", "terms" => 100, "miles" => 120000),
array("name" => "120 Months/120,000 Miles", "terms" => 120, "miles" => 120000)
);

    protected $car;
    protected function setUp(): void {
        $this->car = new Car;
    }

    public function testCoverageArrayReturnedCorrectly(){
        $url = "src/devTest.json";
        $this->assertEquals($this->coverage, $this->car->getCoverageArray($url));
    }

    public function testCarMakeIsValid(){

        $make = "BMC";
        $valid_makes = array();

        $this->assertEquals(false, $this->car->validateMake($make, $this->base_warranty, $valid_makes));
        $this->assertEquals(2, count($valid_makes));

        $make = "BMW";
        $valid_makes = array();
        $this->assertEquals(true, $this->car->validateMake($make, $this->base_warranty, $valid_makes));

    }

    public function testCarYearIsValid(){

        $valid_years = array();
        $year = 2002;
        $this->assertEquals(false, $this->car->validateYear($year, $this->years, $valid_years));
        $this->assertEquals(17, count($valid_years));

        $valid_years = array();
        $year = 2018;
        $this->assertEquals(true, $this->car->validateYear($year, $this->years, $valid_years));

    }

    public function testCarMileageIsValid(){

        $mileage = 90000;
        $this->assertEquals(true, $this->car->validateMileage($mileage));

        $mileage = 90500;
        $this->assertEquals(false, $this->car->validateMileage($mileage));

    }

    public function testRetrievalOfBaseWarrantyInformation(){

        $this->car->make = "BMW" ;
        $this->assertEquals(true, $this->car->getBaseWarrantyInfo($this->base_warranty));
        $this->assertEquals(48000, $this->car->base_warranty_miles);
        $this->assertEquals(36, $this->car->base_warranty_term);

    }

    public function testConditionReturnsNewAndUsed(){

        $this->car->mileage = 52000 ;
        $this->car->base_warranty_miles= 48000;
        $this->assertEquals("USED", $this->car->getCondition());

        $this->car->mileage = 44000 ;
        $this->assertEquals("NEW", $this->car->getCondition());

    }

    public function testSuffix1returnsValidValue() {


        $this->car->year = 2019;
        $this->assertEquals("00", $this->car->getSuffix1($this->years));

        $this->car->year = 2012;
        $this->assertEquals("06", $this->car->getSuffix1($this->years));

        $this->car->year = 2007;
        $this->assertEquals("11", $this->car->getSuffix1($this->years));

    }

    public function testSuffix2returnsValidValue() {

        $this->car->mileage = 44000 ;
        $this->assertEquals("C", $this->car->getSuffix2($this->issue_mileage));

    }

    public function testCheckIfContractMilesExpiresBeforeBaseWarrantyMiles() {

        $this->car->errors = [];
        $this->car->mileage = 36000;
        $this->car->base_warranty_miles = 48000;
        $this->assertEquals(true, $this->car->checkContractMileage($this->coverage[7]));
        $this->assertEquals(0, count($this->car->errors));

        $this->car->mileage = 40000;
        $this->car->base_warranty_miles = 48000;
        $this->assertEquals(false, $this->car->checkContractMileage($this->coverage[0]));
        $this->assertEquals(1, count($this->car->errors));

    }

    public function testCheckIfContractTermExpiresBeforeBaseWarrantyTerm() {

        $this->car->errors = [];
        $this->car->year = 2018;
        $this->car->base_warranty_term= 36;
        $this->assertEquals(true, $this->car->checkContractTerms($this->coverage[8]));
        $this->assertEquals(0, count($this->car->errors));

        $this->car->year = 2017;
        $this->car->base_warranty_term= 36;
        $this->assertEquals(false, $this->car->checkContractTerms($this->coverage[0]));
        $this->assertEquals(1, count($this->car->errors));

    }

    public function testCheckIfCarWillBeTooOldBeforeContractExpires() {

        $this->car->errors = [];
        $this->car->year = 2018;
        $this->assertEquals(true, $this->car->checkAge($this->coverage[8]));
        $this->assertEquals(0, count($this->car->errors));

        $this->car->year = 2010;
        $this->assertEquals(false, $this->car->checkAge($this->coverage[8]));
        $this->assertEquals(1, count($this->car->errors));

    }

    public function testCheckIfCarWillHaveTooManyMilesBeforeContractExpires() {

        $this->car->errors = [];
        $this->car->year = 2018;
        $this->car->mileage = 1000;
        $this->assertEquals(true, $this->car->checkMileage($this->coverage[8]));
        $this->assertEquals(0, count($this->car->errors));

        $this->car->errors = [];
        $this->car->year = 2010;
        $this->car->mileage = 80000;
        $this->assertEquals(false, $this->car->checkMileage($this->coverage[17]));
        $this->assertEquals(1, count($this->car->errors));

    }

    public function testPrintedOutputIsValid(){

        $this->car->make = "BMW";
        $this->car->year ="2018";
        $this->car->mileage = "1000";
        $this->car->condition ="NEW";
        $this->car->coverage_name = "3 Months/3,000 Miles";
        $this->car->suffix1 = "00";
        $this->car->suffix2 = "A";
        $this->expectOutputString($this->car->getPrintValues());
        print 'BMW  2018  1000  NEW  "3 Months/3,000 Miles"  suffix1:00  suffix2:A  ';

    }

    public function testPrintedValidationOutputIsValidSuccess(){

        $this->car->errors = array();
        $this->expectOutputString($this->car->getPrintValidationResults());
        print 'RESULT:  SUCCESS';

    }

    public function testPrintedValidationOutputIsValidFailure(){

        $this->car->errors = array();
        array_push($this->car->errors,'Term expires before warranty');
        $this->expectOutputString($this->car->getPrintValidationResults());
        print "RESULT:  FAILURE array('Term expires before warranty')";

    }

}