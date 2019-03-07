<?php
/*
Thanks to https://github.com/projectbank/API
*/

// This functions returns a big random number
// which is made by using the time. This big
// number is used to seed the random function of php.
function make_seed(){
  list($usec, $sec) = explode(' ', microtime());
  return $sec + $usec * 1000000;
}
// Seed the rand function
srand(make_seed());
// according to the IBAN specification
// a special rotational algorithm is
// used to generate numerical values
// specific to a given string (str).
// this is used for generating and validating IBAN numbers
// full spec can be found here https://nl.wikipedia.org/wiki/International_Bank_Account_Number#Valideren
function ibanUpRot($str){
    $ret = "";
    for($i=0; $i<strlen($str); $i++){
        $ret .= ord($str[$i]) - 64 + 9;
    }
    return $ret;
}
// Functions for calculating the controlNumber
// It takes a number with the length of 20.
// With this it calculates the following:
// 98 - input % 97.
function calcControl($input){
    $ls = (int)substr($input, 0, 10) % 97 * pow(10, 10);
    $rs = (int)substr($input, 10, 20);
    $total = $ls + $rs;
    return 98 - $total % 97;
}
// Generates a random IBAN with give countryCode and bankCode
// coutnryCode should be of length 2 and bankCode should be length 4,
// functions will return NULL if this is not the case.
// It uses random numbers to then calculate a valid IBAN and returns this.
function ibanGenerator($countryCode, $bankCode){
    if(strlen($countryCode) != 2){
        echo "Failed to generate IBAN with invalid country prefix length (should be 2, got " . strlen(countryCode) . ")";
        return null;
    }
    if(strlen($bankCode) != 4){
        echo "Failed to generate IBAN with invalid bankcode prefix length (should be 4, got " . strlen(countryCode) . ")";
        return null;
    }
    $countryCode = strtoupper($countryCode);
    $bankCode = strtoupper($bankCode);
    $randomBankPassNumber = (string)rand(100000, 999999);
    $countryPrefixControlVersion = ibanUpRot($countryCode);
    $bankCodeControlVersion = ibanUpRot($bankCode);
    $controlNumber = calcControl($bankCodeControlVersion . $randomBankPassNumber . $countryPrefixControlVersion . '00');
    if(strlen($controlNumber) < 2){
        $controlNumber = "0" + $controlNumber;
    }
    return $countryCode . $controlNumber . $bankCode . $randomBankPassNumber;
}
?>
