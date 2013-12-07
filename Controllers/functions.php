<?php

// Signing function
include('aws_signed_request.php');

// Defines for IDs for finding goods
define("Access_Key_ID", "AKIAIMCFHBE6JELLM3JQ");
define("SECRET_KEY","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");

// Needed for the item search
define("Operation", "ItemSearch");
define("Version", "2011-08-01");

function FullItemSearch($SearchIndex, $Keywords)
{
    // Define Parameters we need
    $params['Operation']= Operation;
    $params['SearchIndex']=$SearchIndex;
    $params['Keywords']=$Keywords;

    // Since we have an attribute search...
    $params['ResponseGroup'] = "ItemAttributes,Images,OfferSummary";

    // Create a response with proper signatures using the other function, get from Amazon
    $response = file_get_contents(aws_signed_request('com', $params, Access_Key_ID, SECRET_KEY));
    $fullxml = simplexml_load_string($response);

    // Create arrays to make sure output goes through properly
    $output['Title'] = array();
    $output['Department'] = array();
    $output['Image'] = array();
    $output['Price'] = array();

    // Overall information
    $output['Results'] = (int)$fullxml->Items->TotalResults;
    $output['SearchIndex'] = $SearchIndex;
    $output['Keywords'] = $Keywords;

    foreach ($fullxml->Items->Item as $current)
    {
    	// Finding all the information we need
    	array_push($output['Title'], (string)$current->ItemAttributes->Title);
    	array_push($output['Department']), (string)$current->ItemAttributes->ProductGroup);
		array_push($output['Image']), (string)$current->LargeImage->URL);
		array_push($output['Price'], (float)$current->OfferSummary->LowestNewPrice->FormattedPrice);
    }

    return $output;
}

function DepartmentSearch($Keywords)
{
	// Define Parameters we need
	$params['Operation']=$Operation;
    $params['Keywords']=$Keywords;

    // Defined as part of just looking for department
	$params['SearchIndex'] = "All";
    $params['ResponseGroup'] = "ItemAttributes"

    // Create a response with proper signatures using the other function, get from Amazon
    $response = file_get_contents(aws_signed_request('com', $params, Access_Key_ID, SECRET_KEY));
    $fullxml = simplexml_load_string($response);

    // Create array for output
    $output['Department'] = array();

    // Overall information
    $output['Results'] = (int)$fullxml->Items->TotalResults;
    $output['SearchIndex'] = "All";
    $output['Keywords'] = $Keywords;

    foreach ($fullxml->Items->Item as $current)
    {
    	// Finding all the information we need
    	array_push($output['Department']), (string)$current->ItemAttributes->ProductGroup);
    }

    return $output;
}

function MultiNodeSearch($Keywords)
{
    // Define Parameters we need
    $params['Operation']= "BrowseNodeLookup";

    // Since we have an attribute search...
    $params['ResponseGroup'] = "ItemAttributes,Images,OfferSummary";

    // Create arrays to make sure output goes through properly
    $output['Title'] = array();
    $output['Department'] = array();
    $output['Image'] = array();
    $output['Price'] = array();
    $output['Numbers'] = array();
    $output['Results'] = array();
    $output['Keywords'] = array();

    foreach($Keywords as $Node)
    {
    	// Set the keyword to the next node
    	$params['Keywords']=$Node;

    	// Create a response with proper signatures using the other function, get from Amazon
    	$response = file_get_contents(aws_signed_request('com', $params, Access_Key_ID, SECRET_KEY));
    	$fullxml = simplexml_load_string($response);

    	foreach ($fullxml->Items->Item as $current)
    	{
    		// Finding all the information we need
    		array_push($output['Title'], (string)$current->ItemAttributes->Title);
    		array_push($output['Department']), (string)$current->ItemAttributes->ProductGroup);
			array_push($output['Image']), (string)$current->LargeImage->URL);
			array_push($output['Price'], (float)$current->OfferSummary->LowestNewPrice->FormattedPrice);
    	}

    	// Overall information
    	array_push($output['Numbers'], $counter);
    	array_push($output['Results'], (int)$fullxml->Items->TotalResults);
    	array_push($output['SearchIndex'], $SearchIndex);
    	array_push($output['Keywords'], $Keywords);
	}

    return $output;
}

function RedundantCheck($Keywords)
{
	// Length of total input and initialize output
	$length = count($Keywords);
	$output = array();

	// Double loop - compare every term to each other
	for ($i = 1; $i < $length; $i++)
	{
		// Boolean to see if a term is indeed redundant
		$redundant = 0;

		// We have j<i so that we don't redundantly check each pair twice
		for ($j = 0; $j < $i; $j++)
		{
			if((string)$Keywords[$i] == (string)$Keywords[$j])
			{
				$redundant = 1;
			}
		}

		// Slowly construct your output array
		if ($redundant == 0)
		{
			array_push($output, $Keywords[$i]);
		}
	}

	return $output;
}

?>