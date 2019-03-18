<?php
$url = 'https://bank.niekvanleeuwen.nl/api/checksaldo.php';
$data = array('nuid' => 'B8C5E3K8', 'pin' => '1112', );

// use key 'http' even if you send the request to https://...
$options = array(
    'http' => array(
        'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
        'method'  => 'POST',
        'content' => http_build_query($data)
    )
);
$context  = stream_context_create($options);
$result = file_get_contents($url, false, $context);
$json_decoded = json_decode($result);

$status = $json_decoded->status;
if($status == 0){
  $saldo = $json_decoded->balance;
  echo($saldo);
}else{
  $error = $json_decoded->error;
  echo($error);
}
?>
