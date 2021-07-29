<?php

class FinalResult {

    /** $f can be defined as this class private property and
     * passed trough the constructor instead into the method directly
     *
     private $f;

     function __construct($f)
        {
            $this->f = $f;
        }

     * This way file can be opened and checked inside constructor
     * and manipulated and closed in separate methods.
     * Also "__destructor" can be used to close the file for any case...
     **/

    /**
     * @param $f
     * @return array
     */
    function results($f) {

        try {

            $response = [
                "filename" => "",
                "document" => "",
                "failure_code" => "",
                "failure_message" => "",
                "records" => ""
            ];

            if (!file_exists($f) || !is_file($f)) {
                throw new Exception('The file does not exist.', 404);
                // Or whatever failure code is application using for such kind of error
            }

            if (($d = fopen($f, "r")) === false) {
                throw new Exception("Source file could not be open", 404);
                // Or whatever failure code is application using for such kind of error
            }

            $h = [];
            $rcs = [];
            $i = 0;

            while (!feof($d)) {

                $r = fgetcsv($d);

                if(!$r) throw new Exception("Error reading source file", 400);
                //or whatever failure code is using the application for such kind of error

                $r = array_map('trim', $r); //trim the array

                if($i === 0) {

                    $h = $r; // assign the first row to $h

                }elseif (count($r) == 16){

                    $r[8] = filter_var($r[8], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
                    $amt = !$r[8] || $r[8] == "0" ? 0 : (float)$r[8];

                    $r[6] = filter_var($r[6], FILTER_SANITIZE_NUMBER_INT);
                    $ban = !$r[6] ? "Bank account number missing" : (int)$r[6];

                    $r[2] = filter_var($r[2], FILTER_SANITIZE_NUMBER_INT);
                    $bac = !$r[2] ? "Bank branch code missing" : $r[2];

                    $r[10] = filter_var($r[10], FILTER_SANITIZE_STRING);
                    $r[11] = filter_var($r[11], FILTER_SANITIZE_STRING);
                    $e2e = !$r[10] && !$r[11] ? "End to end id missing" : $r[10] . $r[11];
                    //Is the application logic to have at least one end id or should be both present?
                    //If both end id are needed, the condition should be "!$r[10] || !$r[11]"

                    $r[7] = filter_var($r[7], FILTER_SANITIZE_STRING);
                    $r[0] = filter_var($r[0], FILTER_SANITIZE_NUMBER_INT);

                    $rcd = [
                        "amount" => [
                            "currency" => isset($h[0]) ? $h[0] : "", //check if isset
                            "subunits" => (int)($amt * 100)
                        ],

                        "bank_account_name" => str_replace(" ", "_", strtolower($r[7])),
                        "bank_account_number" => $ban,
                        "bank_branch_code" => $bac,
                        "bank_code" => $r[0],
                        "end_to_end_id" => $e2e,
                    ];

                    $rcs[] = $rcd;

                }

                $i++;
            }

            $rcs = array_filter($rcs);

            $response["filename"] = basename($f);
            $response["document"] = $d;
            $response["failure_code"] = isset($h[1]) ? $h[1] : ""; //check if isset
            $response["failure_message"] = isset($h[2]) ? $h[2] : ""; //check if isset
            $response["records"] = $rcs;

            if($d) {
                fclose($d); // Close the file
                $d = null;
            }

            return $response;

        }catch (Exception $e){
            $response["failure_code"] = $e->getCode();
            $response["failure_message"] = $e->getMessage();

            return $response;
        }
    }
}



?>
