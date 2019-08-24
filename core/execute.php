<?php
/**
* @EXECUTE
* Lib Name: SimBASE API
* Description: Lib contain all sbapi requests for special interface creation.
* Version: 1.0
* Author: Vyacheslav Odinokov
* Author contact: kagayakashi.vo@gmail.com
**/

Class Execute
{
	private $request;
	private $response;

	public function __construct( Request $request, Response $response )
	{
		$this->request = $request;
		$this->response = $response;
	}

	public function set_data( $data )
	{
		/*
			Get sbapi message type from data array,
			Check, is it correct,
			Set message type,
			Set data array.
		*/
		$msg_type = $data['msg_type'];
		$msg_types = array( 3000, 3010, 3020, 3030, 3100, 3110, 3120, 3130, 4000, 5000, 9000, 9010 );

		if( in_array( $msg_type, $msg_types ) ){ $this->request->set_msg_type( $msg_type ); }
		else{ exit( 'Message type is incorect => data_array["msg_type"]' ); }

		$this->request->set_data( $data );
	}

	public function send_request()
	{
		/*
			Send request
			Check for errors

			Return result
		*/
		$result = $this->request->send_request();
		$this->response->check_error( $result );
		//$this->response->get_response( $result );

		return $result;
	}


}
