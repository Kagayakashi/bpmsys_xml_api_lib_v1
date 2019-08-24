<?php
/**
* @REQUEST
* Lib Name: SimBASE API
* Description: Lib contain all sbapi requests for special interface creation.
* Version: 1.0
* Author: Vyacheslav Odinokov
* Author contact: kagayakashi.vo@gmail.com
**/

Class Request
{
	private $api_cfg;
	private $msg_type;
	private $data;
	
	public function __construct()
	{
		/* Include api config */
		$this->api_cfg = parse_ini_file('sbapi_lib/config/api.ini');
	}
	
	public function set_data( $data )
	{
		$this->data = $data;
	}
	
	public function set_msg_type( $msg_type )
	{
		$this->msg_type = $msg_type;
	}
	
	public function send_request()
	{
		/* Send request cross CURL */
		$error = false;
		/* Get xml for request */
		$xml = $this->get_xml();
		$header = array(
			"Content-type: text/xml",
			"Content-length: ".strlen( $xml ),
			"Connection: close",
		);
		
		$ch = curl_init(); 
		curl_setopt( $ch, CURLOPT_URL, $this->api_cfg['api_url'] );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt( $ch, CURLOPT_TIMEOUT, 10 );
		curl_setopt( $ch, CURLOPT_POST, true );
		curl_setopt( $ch, CURLOPT_POSTFIELDS, $xml );
		curl_setopt( $ch, CURLOPT_HTTPHEADER, $header );
		curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
		
		/*
			IF there is error, return 404,
			Else, return result.
		*/
		if( curl_errno( $ch ) ){
			curl_close( $ch );
			return 404;
		}
		else{ 
			$result = curl_exec( $ch );
			curl_close( $ch );
			return $result;
		}
	}
	
	private function get_xml()
	{
		/*
			Form base XML structure.
			
			Get api parametrs from api config.
			Get XML body.
		*/
		$msg_id = time();
		$msg_created = ''.date('Y-m-d').'T'.date('H:m:s').'Z';
		
		$xml  = '<?xml version="1.0" encoding="UTF-8"?>';
		$xml .= '<sbapi>';
		$xml .= '<header>';
		$xml .= '<interface id="'.hexdec( $this->api_cfg['api_iid'] ).'" version="'.$this->api_cfg['api_ver'].'"></interface>';
		$xml .= '<message id="'.$msg_id.'" type="'.$this->msg_type.'" created="'.$msg_created.'"></message>';
		$xml .= '<error id="0"></error>';
		$xml .= '<auth pwd="'.$this->api_cfg['api_hash'].'">';
		$xml .= base64_encode( '<authdata msg_id="'.$msg_id.'" user="'.$this->api_cfg['api_usr'].'" password="'.$this->api_cfg['api_pwd'].'" msg_type="'.$this->msg_type.'" user_ip="'.$_SERVER['REMOTE_ADDR'].'"/>' );
		$xml .= '</auth>';
		$xml .= '</header>';
		$xml .= $this->get_xml_body();
		$xml .= '</sbapi>';
		
		return $xml;
	}
	
	private function get_xml_body()
	{
		/* Form XML body per message type using data array */
		switch ( $this->msg_type )
		{
			case 3000:
				$body = $this->xml_start_process();
				break;
			case 3010:
				$body = $this->xml_set_object_state();
				break;
			case 3020:
				$body = $this->xml_get_objects();
				break;
			case 3030:
				$body = $this->xml_edit_objects();
				break;
			case 3100:
				$body = $this->xml_get_dicts();
				break;
			case 3110:
				$body = $this->xml_add_dict_elems();
				break;
			case 3120:
				$body = $this->xml_edit_dict();
				break;
			case 3130:
				$body = $this->xml_set_dict_status();
				break;
			case 4000:
				$body = $this->xml_get_metric();
				break;
			case 5000:
				$body = $this->xml_exec_function();
				break;
			case 9000:
				$body = $this->xml_echo_test();
				break;
			case 9010:
				$body = $this->xml_add_events();
				break;
				
		}
		
		return $body;
	}
	
	private function xml_start_process()
	{
		$body  = '<body>';
		$body .= '<object process="'.$this->data['object']['process'].'" group="'.$this->data['object']['group'].'">';
		
		foreach( $this->data['fields'] as $key => $val )
		{
			$type = '';
			if( !empty( $val['type'] ) ){ $type = ' type="'.$val['type'].'"'; }
			if( $key <= 100 ){ $body .= '<field name="'.$val['name'].'"'.$type.'>'.$val['value'].'</field>'; }
			elseif( $key >= 101 ){ $body .= '<file name="'.$val['name'].'">'.$val['value'].'</field>'; }
		}
		
		$body .= '</object>';
		$body .= '</body>';
		
		return $body;
	}
	
	private function xml_set_object_state()
	{
		$body  = '<body>';
		$body .= '<object id="'.$this->data['object']['id'].'" old="'.$this->data['object']['old'].'" new="'.$this->data['object']['new'].'"></object>';
		$body .= '</body>';
		
		return $body;
	}
	
	private function xml_get_objects()
	{
		$body  = '<body>';
		$body .= '<search>';
		
		foreach( $this->data['search'] as $key => $val )
		{
			$oper = '';
			if( !empty( $val['value'] ) ){ $oper = 'value="'.$val['value'].'"'; }
			if( $key <= 100 ){ $body .= '<field name="'.$val['name'].'" operator="'.$val['operator'].'"'.$oper.'></field>'; }
			elseif( $key >= 101 ){ $body .= '<file name="'.$val['name'].'" operator="'.$val['operator'].'"></file>'; }
		}
		
		$body .= '</search>';
		$body .= '<data limit="'.$this->data['limit'].'" total="'.$this->data['total'].'">';
		
		foreach( $this->data['data'] as $key => $val )
		{	
			if( $key <= 100 ){ $body .= '<field name="'.$val['name'].'" sort="'.$val['sort'].'"></field>'; }
			elseif( $key >= 101 ){ $body .= '<file name="'.$val['name'].'" sort="'.$val['size'].'"></field>'; }
		}
		
		$body .= '</data>';
		$body .= '</body>';
		
		return $body;
	}
	
	private function xml_edit_objects()
	{
		$body  = '<body>';
		$body .= '<object id="'.$this->data['object']['id'].'">';
		
		foreach( $this->data['fields'] as $key => $val )
		{
			$type = '';
			$field = '';
			if( !empty( $val['type'] ) ){ $type = ' type="'.$val['type'].'"'; }
			if( !empty( $val['field'] ) ){ $type = ' field="'.$val['field'].'"'; }
			if( $key <= 100 ){ $body .= '<field name="'.$val['name'].'"'.$type.'>'.$val['value'].'</field>'; }
			elseif( $key >= 101 ){ $body .= '<file name="'.$val['name'].'"'.$field.'>'.$val['value'].'</field>'; }
		}
		
		$body .= '</object>';
		$body .= '</body>';
		
		return $body;
	}
	
	private function xml_get_dicts()
	{
		$body  = '<body>';
		$body .= '<search>';
		
		foreach( $this->data['search'] as $key => $val )
		{
			if( $key <= 100 ){ $body .= '<field name="'.$val['name'].'" operator="'.$val['operator'].'" value="'.$val['value'].'"></field>'; }
			elseif( $key >= 101 ){ $body .= '<file name="'.$val['name'].'" operator="'.$val['operator'].'"></file>'; }
		}
		
		$body .= '</search>';
		$body .= '<data limit="'.$this->data['limit'].'" total="'.$this->data['total'].'" picture="'.$this->data['picture'].'">';
		
		foreach( $this->data['data'] as $key => $val )
		{	
			if( $key <= 100 ){ $body .= '<field name="'.$val['name'].'" sort="'.$val['sort'].'"></field>'; }
			elseif( $key >= 101 ){ $body .= '<file name="'.$val['name'].'" sort="'.$val['size'].'"></field>'; }
		}
		
		$body .= '</data>';
		$body .= '</body>';
		
		return $body;
	}
	
	private function xml_add_dict_elems()
	{
		$body = '<body>';
		$body .= '<element dict_id="'.$this->data['element']['dict_id'].'">';
		
		foreach( $this->data['fields'] as $key => $val )
		{
			$type = '';
			$lang = '';
			if( !empty( $val['type'] ) ){ $type = ' type="'.$val['type'].'"'; }
			if( !empty( $val['lang'] ) ){ $type = ' lang="'.$val['lang'].'"'; }
			$body .= '<field name="'.$val['name'].'"'.$type.$lang.'>'.$val['value'].'</field>';
		}
		
		$body .= '</element>';
		$body .= '</body>';
		
		return $body;
	}
	
	private function xml_edit_dict()
	{
		$body  = '<body>';
		$dict_id = '';
		if( $this->data['element']['type'] == 'value' ){ $dict_id = 'dict_id="'.$this->data['element']['dict_id'].'" '; }
		$body .= '<element '.$dict_id.'id="'.$this->data['element']['id'].'" type="'.$this->data['element']['type'].'">';
		
		foreach( $this->data['fields'] as $key => $val )
		{
			$lang = '';
			if( !empty( $val['lang'] ) ){ $lang = ' lang="'.$val['lang'].'"'; }
			$body .= '<field name="'.$val['name'].'"'.$lang.'>'.$val['value'].'</field>';
		}
		
		$body .= '</element>';
		$body .= '</body>';
		
		return $body;
	}
	
	private function xml_set_dict_status()
	{
		$dict_id = '';
		if( $this->data['element']['type'] == 'value' ){ $dict_id = ' dict_id="'.$this->data['element']['dict_id'].'"'; }
		$body  = '<body>';
		$body  = '';
		$body .= '<element id="'.$this->data['element']['id'].'" type="'.$this->data['element']['type'].'" new="'.$this->data['element']['new'].'"'.$dict_id.'></element>';
		$body .= '</body>';
		
		return $body;
	}
	
	private function xml_get_metric()
	{
		$body  = '<body>';
		$body .= '<search>';
		
		foreach( $this->data['search'] as $key => $val )
		{
			$body .= '<field name="'.$val['name'].'" operator="'.$val['operator'].'" value="'.$val['value'].'"></field>';
		}
		
		$body .= '</search>';
		$body .= '<data limit="'.$this->data['limit'].'" total="'.$this->data['total'].'">';
		
		foreach( $this->data['data'] as $key => $val )
		{	
			$body .= '<field name="'.$val['name'].'" sort="'.$val['sort'].'"></field>';
		}
		
		$body .= '</data>';
		$body .= '</body>';
		
		return $body;
	}
	
	private function xml_exec_function()
	{
		$body  = '<body>';
		$body .= '<function name="'.$this->data['function']['name'].'">';
		
		foreach( $this->data['args'] as $key => $val )
		{	
			$body .= '<arg name="'.$val['name'].'">'.$val['value'].'</arg>';
		}
		
		$body .= '</function>';
		$body .= '</body>';
		
		return $body;
	}
	
	private function xml_echo_test()
	{
		$body  = '<body>';
		$body .= '<echo>Hello World!</echo>';
		$body .= '</body>';
		
		return $body;
	}
	
	private function xml_add_events()
	{
		$body  = '<body>';
		
		foreach( $this->data['events'] as $key => $val )
		{
			$category = '';
			if( !empty( $val['category'] ) ){ $category = ' category="'.$val['category'].'"'; }
			$body .= '<event'.$category.'>'.$val['value'].'</event>';
		}
		
		$body .= '</body>';
		
		return $body;
	}
}