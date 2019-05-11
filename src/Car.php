<?php

class Car
{
    public $make;
    public $year;
    public $mileage;
    public $coverage_name;
    public $coverage_terms;
    public $coverage_miles;
    public $condition;
    public $suffix1;
    public $suffix2;

    public $base_warranty_term;
    public $base_warranty_miles;

    public $validation_result;
    public $errors;

    //=====================
    // public functions
    //=====================

    // Retrieve json file by simulating API call
    public function getCoverageArray($url)
    {
        $results = $this->apiCall($url);
        return $results;
    }

    // Validate make input from the command line
    public function validateMake($make, $base_warranty, &$valid_makes)
    {
        foreach ($base_warranty as $item) {
            if ($item["make"] == $make) {
                return true;
            }
            array_push($valid_makes,$item["make"]);
        }
        return false;
    }

    // Validate year input from the command line
    public function validateYear($year, $years, &$valid_years)
    {
        foreach ($years as $item) {
            if ($item["modelyear"] == $year) {
                return true;
            }
            array_push($valid_years,$item["modelyear"]);
        }
        return false;
    }

    // Validate mileage input from the command line
    public function validateMileage($mileage)
    {
        return (($mileage>MIN_INPUT_MILEAGE) &&
                ($mileage<MAX_INPUT_MILEAGE) &&
                ($mileage%INC_MILEAGE==0)) ? true : false;
    }

    // Get basic info from the command line
    public function getCarInputValues($base_warranty, $years)
    {
        return $this->getInputValues($base_warranty, $years);
    }

    // Set base warranty info for the car
    public function getBaseWarrantyInfo($base_warranty)
    {
        foreach ($base_warranty as $item) {
            if ($item["make"] != $this->make)
                continue;
            else {
                $this->base_warranty_miles = $item["miles"];
                $this->base_warranty_term = $item["term"];
                return true;
            }
        }
    }

    // Checks the age condition of the car
    public function getCondition()
    {
        return ($this->mileage < $this->base_warranty_miles) ? "NEW" : "USED";
    }

    // Sets the correct suffix1 for the car
    public function getSuffix1($years)
    {
        foreach ($years as $item) {
            if ($item["modelyear"]== $this->year) {
                $formatted_suffix1 = sprintf("%02d", $item["suffix1"]);
                return $formatted_suffix1;
            }
        }
        array_push($this->errors, "Car model year is not on our list");
        return "--";
    }

    // Sets the correct suffix2 for the car
    public function getSuffix2($issue_mileage)
    {
        foreach ($issue_mileage as $item) {
            if ($this->mileage > $item["max"])
                continue;
            else
                return $item["suffix2"];
        }
        array_push($this->errors, "Car mileage is not on our list");
        return "-";
    }

    // Checks if the miles of the coverage contract expire before the base warranty miles of the
    // vehicle has expired.
    public function checkContractMileage($coverage)
    {
        // get number miles left on warranty
        $num_miles_left_on_base_warranty = $this->base_warranty_miles - $this->mileage;
        // if this is negative, adjust it to 0, to show we have no miles left under the base warranty
        if ($num_miles_left_on_base_warranty < 0)
            $num_miles_left_on_base_warranty = 0;
        if ($coverage['miles'] <= $num_miles_left_on_base_warranty) {
            array_push($this->errors, "Miles expires before warranty");
            return false;
        }
        return true;
    }

    // Checks if the term of the coverage contract expire before the base warranty term of the
    // vehicle has expired.
    public function checkContractTerms($coverage)
    {
        $current_year = date("Y");
        // get car's age in months
        $car_age = ($current_year - $this->year)*12;
        // get number months left on warranty
        $num_months_left_on_base_warranty = $this->base_warranty_term - $car_age;
        // if this is negative, adjust it to 0, to show we have no time left on the base warranty
        if ($num_months_left_on_base_warranty < 0)
            $num_months_left_on_base_warranty = 0;
        if ($coverage['terms'] <= $num_months_left_on_base_warranty) {
            array_push($this->errors, "Term expires before warranty");
            return false;
        }
        return true;
    }

    // Checks if car will be too old before contract expires
    public function checkAge($coverage)
    {
        $current_year = date("Y");

        // Get future age of car, in months, at the end of the term
        $future_age = (12*($current_year - $this->year)) + $coverage['terms'];

        // if future age is > 147 months, this car will be too old
        if ($future_age > 147){
            array_push($this->errors, "Vehicle age will be too old before contract ends");
            return false;
        }
        return true;
    }

    // Checks if car will have too many miles before contract expires
    public function checkMileage($coverage)
    {
        $current_year = date("Y");
        $current_month = date("m");

        // first get car current average miles per month
        if ($this->year < $current_year)
            $average_monthly_miles = $this->mileage / (12*($current_year - $this->year));
        else
            $average_monthly_miles = $this->mileage / $current_month;

        // next get mileage expected during the term
        $miles_expected_during_contract = $average_monthly_miles * $coverage["terms"];

        // finally get total miles expected
        $total_expected_miles = $miles_expected_during_contract + $this->mileage;

        // if total expected miles is > MAX_MILEAGE miles, this car will be too high mileage
        if ($total_expected_miles > MAX_MILEAGE) {
            array_push($this->errors, "Vehicle mileage will be too high before contract ends");
            return false;
        }
        return true;
    }

    // Returns values to print of general car information,
    public function getPrintValues()
    {
        $string_to_output =
            $this->make.'  '.$this->year.'  '.$this->mileage.'  '.$this->condition.'  "'.$this->coverage_name.'"  suffix1:'.$this->suffix1.'  suffix2:'.$this->suffix2.'  ';
        return $string_to_output;
    }

    // Returns values to print of validation results
    public function getPrintValidationResults()
    {
        $num_errors = count($this->errors);
        if ($num_errors == 0)
            $string_to_output = "RESULT:  SUCCESS";
        else {
            $string_to_output = "RESULT:  FAILURE array('";
            $cnt=0;
            foreach($this->errors as $value) {
                $string_to_output = $string_to_output.((++$cnt == $num_errors) ? "$value')" : "$value', '");
            }
        }
        return $string_to_output;
    }

    //=====================
    // private functions
    //=====================

    // simulate API call to get json file.
    private function apiCall($url)
    {
        $strJson = file_get_contents($url);  //implemented with file_get_contents since file is in same directory
        $result = json_decode($strJson, true);
        return $result;
    }

    // Retrieve car's make from the command line
    private function getCarMake($base_warranty)
    {
        $valid_input = false;
        $valid_makes = array();
        while (!$valid_input) {
            echo "Enter car make: ";
            $make = rtrim(fgets(STDIN), "\r\n");
            $valid_input = $this->validateMake($make, $base_warranty, $valid_makes);
            if (!$valid_input) {
                echo "Invalid car make input, select from [ ";
                while (count($valid_makes) > 0) {
                    echo array_shift($valid_makes) . " ";
                }
                echo "]. Please try again\n";
            }
        }
        return $make;
    }

    // Retrieve car's year from the command line
    private function getCarYear($years)
    {
        $valid_input = false;
        $valid_years = array();
        while (!$valid_input) {
            echo "Enter car year: ";
            $year = rtrim(fgets(STDIN), "\r\n");
            $valid_input = $this->validateYear($year, $years, $valid_years);
            if (!$valid_input){
                echo "Invalid car year input, select from [ ";
                while (count($valid_years) > 0) {
                    echo array_shift($valid_years) . " ";
                }
                echo "]. Please try again\n";
            }
        }
        return $year;
    }

    // Retrieve car's issue mileage from the command line
    private function getCarMileage()
    {
        $valid_input = false;
        while (!$valid_input) {
            echo "Enter car issue mileage: ";
            $mileage = rtrim(fgets(STDIN), "\r\n");
            $valid_input = $this->validateMileage($mileage);
            if (!$valid_input)
                echo "Invalid car issue mileage input, select from [ ".MIN_INPUT_MILEAGE."-".MAX_INPUT_MILEAGE." ] in ".INC_MILEAGE." mile increments.";
            echo "Please try again\n";
        }
        return $mileage;
    }

    // Retrieve car make, year, and issue mileage from the command line
    private function getInputValues($base_warranty, $years)
    {
        $this->make = $this->getCarMake($base_warranty);
        $this->year = $this->getCarYear($years);
        $this->mileage = $this->getCarMileage();
        return true;
    }
}