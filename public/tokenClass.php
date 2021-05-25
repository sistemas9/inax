<?php
$curl = curl_init();
curl_setopt_array($curl, array(
  CURLOPT_URL => "https://solutiontinax.azurewebsites.net/SolutionToken/api/SolutionToken",
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => "",
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 30,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => "GET",
   CURLOPT_POSTFIELDS => "",
  CURLOPT_USERPWD => 'atp\\administrador:Avance04',
  CURLOPT_HTTPAUTH => CURLAUTH_NTLM
));

 $response = curl_exec($curl);
 $err = curl_error($curl);

 curl_close($curl);

 if ($err) {
   $result = "cURL Error #:" . $err;
 } else {
   $result = $response;
 }
 $file = fopen('var/www/html/inaxL3/public/token.json', 'w');
 fwrite($file, $result);
 fclose($file);
 print_r($result."FATAL");
 exit();