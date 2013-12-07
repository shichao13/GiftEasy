<?php

/*
 *  File Description: Functions we made in order to make the output more accessible
 *                    without having to deal with all the nasty amazon api.
 *                    Such time. Much sadface.
 */

// Defines for IDs for finding goods
define("Access_Key_ID", "AKIAIMCFHBE6JELLM3JQ");
define("SECRET_KEY","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");

// Needed for the item search
define("Operation", "ItemSearch");
define("Version", "2011-08-01");

/*
 * PrintCompareResults: Given two xml files, it will print the common items between them.
 */
function PrintCompareResults($parsed_xml, $parsed_xmltwo, $SearchIndex)
{
  // Start the HTML file to format the results
  print_r("<table>");

  // Make sure that each of the xml files have items in them
  $numOfItems = 0;
  $secOfItems = 0;

  // The loops for this making sure (relevance)
  foreach($parsed_xml->Items->Item as $counting)
  {
       $numOfItems = $numOfItems + 1;
  }
  
  // Similar loop for best selling category
  foreach($parsed_xmltwo->Items->Item as $counting)
  {
       $secOfItems = $secOfItems + 1;
  }

  if($numOfItems>0 && $secOfItems>0)
  {
  	// Print out the total number of items that we are printing - used for bugtesting
  	// Remove in final implementation
    print_r($numOfItems." ".$secOfItems);

    // Compare the two of them - if they are the same, output
    foreach($parsed_xmltwo->Items->Item as $compare)
    {
      foreach($parsed_xml->Items->Item as $current)
      {
      	// both are simple_xml format, so we don't need to recast
        if ($current->ItemAttributes->Title == $compare->ItemAttributes->Title)
        {
          // HTML formatting!
          print_r("<td><font size='-1'><b>".$current->ItemAttributes->Title."</b>");
          
          // Make sure that a title exists
          if (isset($current->ItemAttributes->Title)) 
          {
            print_r("<br>Title: ".$current->ItemAttributes->Title);
          } 

          // Make sure that an author exists (it won't for most things)
          elseif(isset($current->ItemAttributes->Author)) 
          {
            print_r("<br>Author: ".$current->ItemAttributes->Author);
          } 

          // Use offers to grab price. This may not work if you have not grabbed offers
          elseif(isset($current->Offers->Offer->Price->FormattedPrice))
          {
            print_r("<br>Price:".$current->Offers->Offer->Price->FormattedPrice);
          }
          
          // Otherwise, we didn't find anything for this item
          else
          {
            print_r("<center>Error: Unknown XML Format</center>");
          }
        }
      }
    }
  }
  else
  {
     print_r("Error: There are no items that match the search results!");
     print_r("<br>".$numOfItems);
     print_r("<br>".$secOfItems);
  }
}  

// Good 'ol pset7 has this great render function
function render($template, $values = [])
      {
          // if template exists, render it
          if (file_exists("../$template"))
          {
              // extract variables into local scope
              extract($values);

              // render header
              require("../Pages/header.html");

              // render template
              require("../$template");

              // render footer
              require("../Pages/footer.html");
          }

          // else err
          else
          {
              trigger_error("Invalid template: $template", E_USER_ERROR);
          }
      }

function pulltitles($ourxml)
{
  // Initialize our output array
  $output = array();

  // Find output!
  foreach($ourxml->Items->Item as $current)
  {
      array_push($output, (string)$current->ItemAttributes->Title);
  }

// Return what we have
return $output;
}

// Give all of our stuff to session!
function printtitles($relevancexml, $searcharray, $j)
{
  // How many have we currently found?
  $displayedinfo = 0;

  // How long is the array we are searching through?
  $length = count($searcharray);

  // Go through all of the text and determine if we have found matches
  for ($i = 0; $i<$j; $i++)
  {
    foreach($relevancexml[$i]->Items->Item as $current)
    {
      for($k = 0; $k<$length; $k++)
      {
        // The title we are comparing to the item that we have found
        $comparetitle = $searcharray[$k];

        // If they are the same, then...
        if((string)$current->ItemAttributes->Title == $comparetitle)
        {
          // We have found an extra term!
          $displayedinfo++;

          // Push these arrays onto the superglobal for the next page
          array_push($_SESSION['Title'], (string)$current->ItemAttributes->Title);
          array_push($_SESSION['Author'], (string)$current->ItemAttributes->Author); 
          array_push($_SESSION['Price'], (float)$current->Offers->Offer->Price); 
          array_push($_SESSION['Review'], $review);
        }
      }
    }
  }
  

  // Maximum number of pages
  $maximumpage = 10;

  // Find out how many 
  $numberremain = $maximumpage - $displayedinfo;
  nomatcheserror($relevancexml, $numberremain);
}

// What happens when there are no matches
function nomatcheserror($relevancexml, $numberremain)
{
  // How many items are in our xml? If there are 0, something happened
  $numOfItems = 0;

  // The loop
  foreach($relevancexml[0]->Items->Item as $counting)
  {
    $numOfItems = $numOfItems + 1;
  }

  // Assuming that something bad happened:
  if($numOfItems == 0)
  {
    print_r("ERROR HAPPENED. NUMBER OF XML ITEMS IN RELEVANCE IS 0");
  }
  else
  {
    // This now represents how many items we have outputted onto the page
    $numOfItems = 0;

    // For each of the most relevant ones, we put onto the output
    foreach ($relevancexml[0]->Items->Item as $current)
    {
      // If we haven't outputted too many
      if($numOfItems <= $numberremain)
      {
        // +1!
        $numOfItems++;

        print_r($current->Offers);
        // Push onto our superglobal for other pages to use
        array_push($_SESSION['Title'], (string)$current->ItemAttributes->Title);
        array_push($_SESSION['Author'], (string)$current->ItemAttributes->ListPrice); 
        array_push($_SESSION['Price'], (float)$current->OfferSummary->LowestNewPrice->FormattedPrice); 
        array_push($_SESSION['Review'], $review);
      }
    } 
  }
}
  
//Set up the operation in the request
function OldItemSearch($SearchIndex, $Keywords, $j)
{
  // Set parameters that will not change over time
  $Operation = "ItemSearch";
  $Version = "2011-08-01";
  $ResponseGroup = "ItemAttributes";

  // From the input, which age group are we looking at?
  if ($_POST["age"] == 1)
  {
    $SearchIndex = "Baby";
    $Sort = "salesrank";
  }
  else if ($_POST["age"] == 2)
  {
    $SearchIndex = "Toys";
    $Sort = "salesrank";
  }
  else
  {
    $SearchIndex = "All";
  }

  // Other user input not related to age
  $Keywords = $_POST["keywords"];

  // Defining the arrays of titles (just the strings)
  $relevancearray = array();
  $bestsellarray = array();

  // The two arrays that will be full of XML documents
  $relevancexml = array();
  $bestsellxml = array();

  // Define Parameters Matrix
  $params['Operation']=$Operation;
  $params['SearchIndex']=$SearchIndex;
  $params['Keywords']=$Keywords;
  $params['ResponseGroup']= $ResponseGroup;

  // Make userinfo into a superglobal
  $_SESSION['AmazonCategory'] = $SearchIndex;
  $_SESSION['Keywords'] = $Keywords;
  $_SESSION['Gender'] = $_POST['gender'];

  // Array of results
  $_SESSION['Title'] = array();
  $_SESSION['Author'] = array();
  $_SESSION['Price'] = array();
  $_SESSION['Review'] = array();

  // Begin iterating over number of pages
  for($i = 0; $i < $j; $i++)
  {
    //Which Item Page are we on?
    $params['ItemPage']=$i+1;

    // If it isn't the first one, then we don't want sort to be in it
    if ($i != 0)
    {
      unset($params['Sort']);
    }

    // Create a response with proper signatures using the other function, get from Amazon
    $response = file_get_contents(aws_signed_request('com', $params, Access_Key_ID, SECRET_KEY));
    array_push($relevancexml, simplexml_load_string($response));
    
    // Bugtesting Print Function
    //print_r($relevancexml[$i]->Items->Item->OfferSummary->LowestNewPrice->FormattedPrice);
    $test = array("Hello", "No");
    print_r((int)$test);
    print_r("<br>MICHAELMAHELLO<br>");

    // Create a second response with the sort on bestseller instead
    $params['Sort'] = $Sort;
    $responsetwo = file_get_contents(aws_signed_request('com', $params, Access_Key_ID, SECRET_KEY));
    array_push($bestsellxml, simplexml_load_string($responsetwo));

    // Start pulling titles into the total titles array
    $relevancearray = array_merge($relevancearray, pulltitles($relevancexml[$i]));
    $bestsellarray = array_merge($bestsellarray, pulltitles($bestsellxml[$i]));
  }
    
  printtitles($relevancexml, $bestsellarray, $j);

}

/*
 * FullItemSearch: Will output the amazon title, department, image, price arrays for use
 */
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

/*
 * FullParse: Given a FullItemSearch output, this will output a vector without words
 */
function FullParse($FullItemOutput, $RestrictedWords = array(), $RestrictedDepartment = array(), 
	                                $RequiredWords = array(), $RequiredDepartment = array(), 
	                                $Minprice = 0.00, $Maxprice = PHP_INT_MAX)
{
	// Create arrays to make sure output goes through properly
    $output['Title'] = array();
    $output['Department'] = array();
    $output['Image'] = array();
    $output['Price'] = array();

    // Overall information
    $output['Results'] = $FullItemOutput['Results'];
    $output['SearchIndex'] = $FullItemOutput['SearchIndex'];
    $output['Keywords'] = $FullItemOutput['Keywords'];

    // What is the length of the current outputarray?
    $length = count($FullItemOutput['Title']);
	
	// Iterate and add selectively into the vector that we want
	for($i = 0; $i < $length; $i++)
	{
		// Boolean to see if anything bad happened
		$AddToOutput = 1;

		// Did any faulty words happen?
		foreach($RestrictedWords as $badword)
		{
			if($FullItemOutput['Title'][$i] == $badword)
			{
				$AddToOutput = 0;
			}
		}

		// Did any bad departments happen?
		foreach($RestrictedDepartment as $baddep)
		{
			if($FullItemOutput['Department'] == $baddep)
			{
				$AddToOutput = 0;
			}
		}

		// Casting an array as an int return 0 if it is empty (sorry, bad practice D:)
		if((int)$RequiredWords)
		{
			foreach($RequiredWords as $word)
			{
				if($FullItemOutput['Title'] == $word)
				{
					$AddToOutput = 0;
				}
			}
		}

		// More sketchiness with casting arrays as ints
		if((int)$RequiredDepartment)
		{
			foreach($RequiredDepartment as $dep)
			{
				if($FullItemOutput['Department'] == $dep)
				{
					$AddToOutput = 0;
				}
			}
		}

		// Make sure price is within the range we want
		if ($FullItemOutput['Price'] < $Minprice || $FullItemOutput['Price'] > $Maxprice)
		{
			$AddToOutput = 0;
		}

		// Add to output if we have no problems!
		if ($AddToOutput == 1)
		{
			array_push($output['Title'], $FullItemOutput['Title'][$i]);
    		array_push($output['Department']), $FullItemOutput['Department'][$i]);
			array_push($output['Image']), $FullItemOutput['Image'][$i]);
			array_push($output['Price'], $FullItemOutput['Price'][$i]);
    }
		}
	}
}
/*
 * DepartmentSearch: If we are trying to find items in a department, only pulls this information. Fast function.
 */
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

/*
 * DepartmentSummary: Takes a department output file and then finds the number of times each deparment
 *                    pops up, sorts by most number of times and returns
 */
function DepartmentSummary($DepOutput)
{
	// How many total terms do we have?
	$length = count($DepOutput['Department']);

	// Make an unsorted array of departments and how often they appear
	$Temp['Department'] = array();
	$Temp['NumTimes'] = array();
}

function MultiNodeSearch($Id)
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
    $output['NodeId'] = array();

    foreach($NodeId as $Node)
    {
    	// Counter for number of items
    	$counter = 0;

    	// Set the keyword to the next node
    	$params['NodeId']=$Node;

    	// Create a response with proper signatures using the other function, get from Amazon
    	$response = file_get_contents(aws_signed_request('com', $params, Access_Key_ID, SECRET_KEY));
    	$fullxml = simplexml_load_string($response);

    	foreach ($fullxml->Items->Item as $current)
    	{
    		// Increment the Counter
    		$counter++;

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
    	array_push($output['NodeId'], $Node);
	}

    return $output;
}

// Searches by node ID with keywords, only allows for one keyword
function KeyNodeSearch($Node, $Keyword)
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

    // Set the specifics of the keynode search
    $params['NodeId']=$Node;
    $params['Keywords']=$Keyword;

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
    $output['Keyword'] = $Keyword;
    $output['Results'] = (int)$fullxml->Items->TotalResults;
    $output['NodeId'] = $Node;

	}

    return $output;
}

// Makes sure that an array doesn't have two of the same word
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

function ToSession($output)
{
  $_SESSION['Title'] = $output['Title'];
  $_SESSION['Price'] = $output['Price'];
  $_SESSION['Image'] = $output['Image'];
}

?>