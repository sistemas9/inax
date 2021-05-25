<?php
// print_r('expression');exit();
class Token{
	public $rutaToken;
	public function getToken(){
		// $urlTokenProd = 'http://intranet/SolutionTokenProd/api/SolutionToken';
		// $urlToken = 'http://intranet/SolutionToken/api/SolutionToken';

		// $urlTokenProd = 'https://solutiontinax.azurewebsites.net/SolutionToken/api/SolutionToken';
		// $urlToken = 'https://solutiontinax.azurewebsites.net/SolutionToken/api/SolutionToken';

		// (DYNAMICS365==RUTAPRODUCCION)?$rutaToken="$urlTokenProd":$rutaToken="$urlToken";
		// $curl = curl_init();
		// curl_setopt_array($curl, array(
		//   CURLOPT_URL => $rutaToken,
		//   CURLOPT_RETURNTRANSFER => true,
		//   CURLOPT_ENCODING => "",
		//   CURLOPT_MAXREDIRS => 10,
		//   CURLOPT_TIMEOUT => 30,
		//   CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		//   CURLOPT_CUSTOMREQUEST => "GET",
		//   CURLOPT_POSTFIELDS => "",
		//   CURLOPT_USERPWD => 'atp\\administrador:Avance04',
		//   CURLOPT_HTTPAUTH => CURLAUTH_NTLM
		// ));

		// $response = curl_exec($curl);
		// $err = curl_error($curl);

		// curl_close($curl);

		// if ($err) {
		//   $result = "cURL Error #:" . $err;
		// } else {
		//   $result = $response;
		// }

		$result = array(array('Token' => 'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsIng1dCI6Im5iQ3dXMTF3M1hrQi14VWFYd0tSU0xqTUhHUSIsImtpZCI6Im5iQ3dXMTF3M1hrQi14VWFYd0tSU0xqTUhHUSJ9.eyJhdWQiOiJodHRwczovL3Rlcy1heXQuc2FuZGJveC5vcGVyYXRpb25zLmR5bmFtaWNzLmNvbSIsImlzcyI6Imh0dHBzOi8vc3RzLndpbmRvd3MubmV0Lzk3ZWY4M2JlLTc1ZmUtNGRlMi05M2NkLTIxZGY5NzhiMjRjMS8iLCJpYXQiOjE1NDg3MjMzMTEsIm5iZiI6MTU0ODcyMzMxMSwiZXhwIjoxNTQ4NzI3MjExLCJhaW8iOiI0MkpnWU5oaWJSQnJ1SnZYUGNCcjhtbTF2a01zQUE9PSIsImFwcGlkIjoiMGRjODc0MTktZGUyOS00M2FjLWFmMjAtMjdmZjRiMDg4YjZlIiwiYXBwaWRhY3IiOiIxIiwiaWRwIjoiaHR0cHM6Ly9zdHMud2luZG93cy5uZXQvOTdlZjgzYmUtNzVmZS00ZGUyLTkzY2QtMjFkZjk3OGIyNGMxLyIsIm9pZCI6ImU1ZTRjYTMyLTQ3ZDEtNGQyYi04MzZlLTU0M2FjMzIzZWQ2ZiIsInN1YiI6ImU1ZTRjYTMyLTQ3ZDEtNGQyYi04MzZlLTU0M2FjMzIzZWQ2ZiIsInRpZCI6Ijk3ZWY4M2JlLTc1ZmUtNGRlMi05M2NkLTIxZGY5NzhiMjRjMSIsInV0aSI6ImZ2XzQ4RWZuVDB1ZmFHR0E4dTRCQUEiLCJ2ZXIiOiIxLjAifQ.tgIgrAdhXCzM96SFc-KbIroy1bOC9Hvk5tWv1_Ua8RiXD7CghQRJWPR5zznYVV9EDuqBSeJJ_rIu5V0xWXgzm-Cig7J5bFeQGcl53wUHDQcYaHIPmNrqHSE8oL3NbBZeBc5KbUG-gef6RWnoEOhaDm6l8-2HSDMTp4_7xOM5n2ZLMBDWCYn6euuJ4mPy8TF95yxcR5U231Q1HLAk_uOPlOK6KfuFVcuiKpkWoqawNq-_booJPTgdXJeMDOOUl3ygDzbCL-QrxbeIGj0LpbOGjIxY50uyNuYPqelnxSPbLmvg98h_E3sjVWAnnVMXcZY74m6YVJELaXIULhobhsyyrA'));

		return json_decode($result);
	}
}