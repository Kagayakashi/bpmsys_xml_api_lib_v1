# BPMSYS XML API Lib v1

## Project
I have worked on project for BPM/ERP system. This system have API.
This application integrates with that system using API to show information.

## Application
System have information about products. This application can scan Barcode on real product and find it in the system by special product number.
User can scan barcodes and if barcode exists in system, then user will see product data from it.

## Library
PHP lib to forming XML API request into system.

## How to
To start need to include start.php and use Execute class.

Execute->set_data($data);

Execute->send_request($data);

Examples of $data in the examples directory
