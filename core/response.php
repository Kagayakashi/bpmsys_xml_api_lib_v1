<?php
/**
* @RESPONSE
* Lib Name: SimBASE API
* Description: Lib contain all sbapi requests for special interface creation.
* Version: 1.0
* Author: Vyacheslav Odinokov
* Author contact: kagayakashi.vo@gmail.com
**/

Class Response
{	
	public function __construct()
	{
		
	}
	
	public function check_error( $result )
	{
		$result = simplexml_load_string( $result );
		$error = $result->{'header'}->error;
		
		if( $error['id'] == '0' ){ return; }
		
		die( 'Error code: '.$error['id'].'; Error text: '.$error['text'] );
	}
	
	public function get_response( $result )
	{
		$result = simplexml_load_string( $result );
		$response = $result->{'body'}->{'response'}->{'value'};

		foreach( $response as $empl){
			echo $empl . '<br/>';
		}
		
		return $response;
	}
}