<?php

require_once dirname(__FILE__) . "/API/Autoloader.php";
/*
 * Initialize the Mollie API library with your API key.
 *
 * See: https://www.mollie.nl/beheer/account/profielen/
 */
$mollie = new Mollie_API_Client;
$mollie->setApiKey("test_9ubnfG9dPCRyyvHmiaPbs5Wpeg8La4");