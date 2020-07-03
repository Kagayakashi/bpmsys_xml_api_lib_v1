<?php
/**
* @Initialization
* Lib Name: SimBASE API
* Description: Lib contain all sbapi requests for special interface creation.
* Version: 1.0 | 2019
* Author: Vyacheslav Odinokov
* Author contact: kagayakashi.vo@gmail.com
**/

include_once('core/request.php');
include_once('core/response.php');
include_once('core/execute.php');

$request = new Request();
$response = new Response();
$execute = new Execute( $request, $response );
