<?php

class FinalResult_OLD {
    function results($f) {
        $d = fopen($f, "r"); //catch error here
        $h = fgetcsv($d);
        //$d is used twice to get csv.
        // While loop can have counter and set first row as $h
        $rcs = [];
        while(!feof($d)) {
            $r = fgetcsv($d);
            if(count($r) == 16) {
                //trim the $r
                $amt = !$r[8] || $r[8] == "0" ? 0 : (float) $r[8];
                $ban = !$r[6] ? "Bank account number missing" : (int) $r[6];
                $bac = !$r[2] ? "Bank branch code missing" : $r[2];
                $e2e = !$r[10] && !$r[11] ? "End to end id missing" : $r[10] . $r[11];
                //Is the logic to have at least one end id or should be both present?
                //If both are needed, it should be "!trim($r[10]) || !trim($r[11])"
                $rcd = [
                    "amount" => [
                        "currency" => $h[0],
                        "subunits" => (int) ($amt * 100)
                    ],
                    "bank_account_name" => str_replace(" ", "_", strtolower($r[7])),
                    "bank_account_number" => $ban,
                    "bank_branch_code" => $bac,
                    "bank_code" => $r[0],
                    "end_to_end_id" => $e2e,
                ];
                $rcs[] = $rcd;
            }
        }
        $rcs = array_filter($rcs);
        return [
            "filename" => basename($f),
            "document" => $d,
            "failure_code" => $h[1],
            "failure_message" => $h[2],
            "records" => $rcs
        ];
    }
}



?>
