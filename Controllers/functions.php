<?php

// Signing function
include('aws_signed_request.php');

// Defines for IDs for finding goods
define("Access_Key_ID", "AKIAIMCFHBE6JELLM3JQ");
define("SECRET_KEY","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");

// Needed for the item search
define("Operation", "ItemSearch");
define("Version", "2011-08-01")

function ItemSearch($SearchIndex, $Keywords)
{
    // Define Parameters we need
    $params['Operation']=$Operation;
    $params['SearchIndex']=$SearchIndex;
    $params['Keywords']=$Keywords;

    // Since we have an attribute search...
    $params['ResponseGroup'] = "ItemAttributes";

    // Create a response with proper signatures using the other function, get from Amazon
    $response = file_get_contents(aws_signed_request('com', $params, Access_Key_ID, SECRET_KEY));
    $fullxml = simplexml_load_string($response);

    // Create arrays to make sure output goes through properly
    $output['Title'] = array();
    $output['Department'] = array();

    // Overall information
    $output['Results'] = (int)$fullxml->Items->TotalResults;

    foreach ($fullxml->Items->Item as $current)
    {
    	// Finding all the information we need
    	array_push($output['Title'], (string)$current->ItemAttributes->Title);
    	array_push($output['Department']), (string)$current->ItemAttributes->ProductGroup);
    }

    return $output;
}


?>