<?php
require 'TwitterStreamApi.php';

$obj_tw_sapi = new TwitterStreamApi('TU7K5fBh3uQr84ysjSqw', 
	'rrVhCwSOSJBMD8Wd4ZJnBj0tDafgynXSLsWTNAx9R4', 
	'1918943665-AEsuvoCuwgp1abDvhH4L6q0UzR0SyYYskRzNbsC', 
	'Is0ZHMtRDumieshcT8M8Xx9iBzns5nHxi62uz3YoQ'
); 

while(true)
{
	$connection = $obj_tw_sapi->connect();

	if ($connection)
	{
		$keywords = array('mahinda');
		$data = 'track=' . rawurlencode(implode($keywords, ','));
		$request = $obj_tw_sapi->getRequest('statuses/filter', $data);
		
		fwrite($connection, $request);

		stream_set_blocking($connection, 0);
		
		while(!feof($connection))
		{
			$read   = array($connection);
			$write  = null;
			$except = null;

			$res = stream_select($read, $write, $except, 600, 0);
			if ( ($res == false) || ($res == 0) )
			{
				break;
			}

			$json = fgets($connection);

			if (strncmp($json, 'HTTP/1.1', 8) == 0)
			{
				$json = trim($json);
				if ($json != 'HTTP/1.1 200 OK')
				{
					break;
				}
			}

			if ( ($json !== false) && (strlen($json) > 0) )
			{
				$data = json_decode($json, true);
				
				if ($data)
				{
					print_r($data);
					//$this->process_tweet($data);
				}
			}
		}
	}
	
	fclose($connection);
	sleep(10);
}

?>