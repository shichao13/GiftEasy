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

// Good 'ol pset7 has this render function
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
          array_push($_SESSION['Price'], (string)$current->Offers->Offer->Price); 
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
        array_push($_SESSION['Price'], (string)$current->OfferSummary->LowestNewPrice->FormattedPrice); 
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
    //$test = array("Hello", "No");
    //print_r((int)$test);
    //print_r("<br>MICHAELMAHELLO<br>");

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

    // Since we have an attribute search...require
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
    	array_push($output['Department'], (string)$current->ItemAttributes->ProductGroup);
		  array_push($output['Image'], (string)$current->SmallImage->URL);
		  array_push($output['Price'], (string)$current->OfferSummary->LowestNewPrice->FormattedPrice);
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
    	array_push($output['Department'], $FullItemOutput['Department'][$i]);
			array_push($output['Image'], $FullItemOutput['Image'][$i]);
			array_push($output['Price'], $FullItemOutput['Price'][$i]);
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
    $params['ResponseGroup'] = "ItemAttributes";

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
    	array_push($output['Department'], (string)$current->ItemAttributes->ProductGroup);
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

function MultiNodeSearch($Ids)
{
    // Create arrays to make sure output goes through properly
    $output['Title'] = array();
    $output['Department'] = array();
    $output['Image'] = array();
    $output['Price'] = array();
    $output['Numbers'] = array();
    $output['Results'] = array();
    $output['NodeId'] = array();

    foreach($Ids as $Node)
    {
    	// Counter for number of items
    	$counter = 0;

    	// Set the keyword to the next node
    	$params['BrowseNodeId']=$Node;

      // Since we have an attribute search...
      $params['ResponseGroup'] = "TopSellers";
      $params['Operation'] = "BrowseNodeLookup";

      // make sure that ItemId doesn't exist, but this error checks
      if (isset($params['ItemId']))
      {
        unset($params['ItemId']);
      }

    	// Create a response with proper signatures using the other function, get from Amazon
      $request = aws_signed_request('com', $params, Access_Key_ID, SECRET_KEY);
    	$response = file_get_contents($request);
      $fullxml = simplexml_load_string($response);

      // For the for loop, we want to use ItemLookup instead
      $params['Operation'] = "ItemLookup";

      // Also do not want to generate an error with browse node ID
      if(isset($params['BrowseNodeId']))
      {
        unset($params['BrowseNodeId']);
      }

    	foreach ($fullxml->BrowseNodes->BrowseNode->TopSellers->TopSeller as $returnresult)
    	{
    		// Increment the Counter
    		$counter++;

        // Find the titles we want
        $title = (string)$returnresult->Title;
        array_push($output['Title'], $title);

        // Get a list of ItemId's togehter
        if ($counter == 1)
        {
          $ItemId = (string)$returnresult->ASIN;
        }
        else
        {
          $ItemId = $ItemId . "," . (string)$returnresult->ASIN;
        }
      }

        // extra parameters for this request
        $params['ItemId'] = $ItemId;
        $params['ResponseGroup'] = "Images,OfferSummary";

        // Get another request!
        $request = aws_signed_request('com', $params, Access_Key_ID, SECRET_KEY);
        $response = file_get_contents($request);
        $fullxml = simplexml_load_string($response);

        // Convert to current for similar syntax across this file
        foreach($fullxml->Items->Item as $current)
        {
    		  // Finding all the information we need
    		  array_push($output['Department'], (string)$current->ItemAttributes->ProductGroup);
			    array_push($output['Image'], (string)$current->SmallImage->URL);
			    array_push($output['Price'], (string)$current->OfferSummary->LowestNewPrice->FormattedPrice);
    	  }

    	// Overall information
    	array_push($output['Numbers'], $counter);
    	array_push($output['Results'], (string)$fullxml->Items->TotalResults);
    	array_push($output['NodeId'], $Node);
	}

    return $output;
}

// Publishes a full output file to session. Probably useless... but it was fun.
function ToSession($output)
{
  $_SESSION['Title'] = $output['Title'];
  $_SESSION['Price'] = $output['Price'];
  $_SESSION['Image'] = $output['Image'];

  if (isset($output['Numbers']))
  {
    $_SESSION['Numbers'] = $output['Numbers'];
  }

  if (isset($output['Keywords']))
  {
    $_SESSION['Keywords'] = $output['Keywords'];
  }

  if (isset($output['NodeId']))
  {
    $_SESSION['NodeId'] = $output['NodeId'];
  }
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
    $params['BrowseNodeId']=$Node;
    $params['Keywords']=$Keyword;

    // Create a response with proper signatures using the other function, get from Amazon
    $response = file_get_contents(aws_signed_request('com', $params, Access_Key_ID, SECRET_KEY));
    $fullxml = simplexml_load_string($response);

   	foreach ($fullxml->Items->Item as $current)
   	{
   		// Finding all the information we need
   		array_push($output['Title'], (string)$current->ItemAttributes->Title);
   		array_push($output['Department'], (string)$current->ItemAttributes->ProductGroup);
		  array_push($output['Image'], (string)$current->SmallImage->URL);
		  array_push($output['Price'], (string)$current->OfferSummary->LowestNewPrice->FormattedPrice);
   	}

   	// Overall information
    $output['Keyword'] = $Keyword;
    $output['Results'] = (int)$fullxml->Items->TotalResults;
    $output['NodeId'] = $Node;


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

// Only works for giftsuggestion.php! Uses post
function GetGiftUserData()
{
  // Make sure that a form exists
  if(!isset($_POST))
  {
    print_r("ERROR: GetGiftUserData function is missing a form for input");
  }

  // Initialize the traits array
  $output['Traits'] = array();

  // Find out if the user is a boy, girl, man, or woman for later
  if($_POST['gender'] == "M")
  {
    if($_POST['age'] < 3)
    {
      $output['Person'] = 1;
      $_SESSION['Person'] = "Boy";
    }
    else
    {
      $output['Person'] = 3;
      $_SESSION['Person'] = "Man";
    }

    if($_POST['relationship'] == 'Parent')
    {
      array_push($output['Traits'], 'Father');
    }
  }
  else if ($_POST['gender'] == "F")
  {
    if($_POST['age'] < 3)
    {
      $output['Person'] = 2;
      $_SESSION['Person'] = "Girl";
    }
    else
    {
      $output['Person'] = 4;
      $_SESSION['Person'] = "Man";
    }

    if($_POST['relationship'] == 'Girlfriend')
    {
      array_push($output['Traits'], 'Girlfriend');
    }
    else if($_POST['relationship'] == 'Parent')
    {
      array_push($output['Traits'], 'Mother');
    }
  }
  else
  {
    // there is an error!
    $output['Error'] = array("Person");
  }

  // Get the keywords if they exist
  if (isset($_POST['keywords']))
  {
    // Explode the string into words
    $output['Keywords'] = explode(",", $_POST['keywords']);
  }
  else
  {
    // Make sure the error is set.... sanitizing our own data. :(
    if(!isset($output['Error']))
    {
      $output['Error'] = array();
    }

    $output['Error'] = array("Keywords");
  }

  // Get the relationship if they exist
  if (isset($_POST['relationship']))
  {
    $output['Relation'] = $_POST['relationship'];   
  }
  else
  {
    // Make sure the error is set.... sanitizing our own data. :(
    if(!isset($output['Error']))
    {
      $output['Error'] = array();
    }
  
    array_push($output['Error'], "Relation");
  }

  // Get the traits from our input
  if (isset($_POST['traits']))
  {
    array_push($output['Traits'], $_POST['traits']);
  }

  return $output;
}

// Pulls Node ID array from mySQL, or return array with 400 in it
function PullNodeId($trait, $person, $NodeMap)
{
  $length = count($NodeMap['Name']);
  $index = 400;
  if ($trait == 'Trendy' || $trait == 'DIY' || $trait == 'Professional')
  {
    for ($i = 0; $i < $length; $i++)
    {
      if ((string)$NodeMap['Name'][$i] == (string)$trait.$person)
      {
        $index = $i;
      }
    }
  }
  else
  {
    for ($i = 0; $i < $length; $i++)
    {
      if ((string)$NodeMap['Name'][$i] == $trait)
      {
        $index = $i;
      }
    }
  }

  $output = array();

  // Filling the output array.
  if ($NodeMap['First'][$index] != 0)
  {
    array_push($output, $NodeMap['First'][$index]);
  }
  
  if ($NodeMap['Second'][$index] != 0)
  {
    array_push($output, $NodeMap['Second'][$index]);
  }
  
  if ($NodeMap['Third'][$index] != 0)
  {
    array_push($output, $NodeMap['Third'][$index]);
  }
  
  if ($NodeMap['Fourth'][$index] != 0)
  {
    array_push($output, $NodeMap['Fourth'][$index]);
  }
  
  return $output;
  // In case we want to use mySQL later
  /*
  $query = sprintf("SELECT * FROM nodes WHERE description = %s", 
                    $trait.$person);
  $output = mysql_query($query);
  if($output == FALSE)
  {
    print_r("ERROR: Incorrect Query");
  }
  else
  {
    $length = count($output);
    $nodeid = array();
    for ($i = 1; $i<$length; $i++)
    {
      array_push($nodeid, $output[$i]);
    }
  }
  if(count($output) == 0)
  {
    $error = array(400);
    return $error;
  }
  else
  {
    return $output;
  }
  */
}

function CondenseOutput($KeyOutput)
{
  // Max number of items on our page
  $maxitems = 15;

  // Find out how many terms we have in total
  $NumKey = count($KeyOutput);

  // Create arrays to keep track of how many we've used
  $NumItem = array();
  $UsedItem = array();

  // Our output array!
  $output['Title'] = array();
  $output['Image'] = array();
  $output['Price'] = array();

  // Populate the arrays for total and how much we've used
  for ($i = 0; $i < $NumKey; $i++)
  {
    array_push($NumItem, count($KeyOutput[$i]['Title']));
    array_push($UsedItem, 0);
  }

  // Display 10 things. It doesn't matter how long it takes to get there
  $counter = 0;
  $infinite = 0;
  while (($counter < $maxitems) && ($infinite < 30))
  {
    // Random integer that represents one of our words
    $rint = rand(0, $NumKey);
    $infinite++;
    
    if ($NumItem[$rint] != $UsedItem[$rint])
    {

      // Create a new output matrix by pushing things onto it
      array_push($output['Title'], $KeyOutput[$rint]['Title'][$UsedItem[$rint]]);
      array_push($output['Image'], $KeyOutput[$rint]['Image'][$UsedItem[$rint]]);
      array_push($output['Price'], $KeyOutput[$rint]['Price'][$UsedItem[$rint]]);

      // We have used up an item and the counter
      $UsedItem[$rint]++;
      $counter++;
    }
  }

  return $output;
}

/**
 * Loading in Node Information
 */
function LoadNodes($filename='../Controllers/OnlineFilter.csv', $delimiter=',', $reverse=1)
{
    if(!file_exists($filename) || !is_readable($filename))
    {
      print_r("ERROR: File is not readable");
      $error = 400;
      return $error;
    }

    $header = NULL;
    $nodes = array();
    if (($handle = fopen($filename, 'r')) !== FALSE)
    {
        while (($row = fgetcsv($handle, 1000, $delimiter)) !== FALSE)
        {
            if(!$header)
                $header = $row;
            else
                $nodes[] = array_combine($header, $row);
        }
        fclose($handle);
    }

    // Do we need to reverse the structure of this?
    if ($reverse == 1)
    {
      // Figure out how long we should be iterating over
      $length = count($nodes);

      // Set up all of the output arrays that we need
      $output['Name'] = array();
      $output['First'] = array();
      $output['Second'] = array();
      $output['Third'] = array();
      $output['Fourth'] = array();

      // Iterate over the array
      for ($i = 0; $i < $length; $i++)
      {
        // Pushing all of the current values in the inner dimension to the outer
        array_push($output['Name'], $nodes[$i]['description']);
        array_push($output['First'], $nodes[$i]['n1']);
        array_push($output['Second'], $nodes[$i]['n2']);
        array_push($output['Third'], $nodes[$i]['n3']);
        array_push($output['Fourth'], $nodes[$i]['n4']);
      }

      return $output;

    }
    else
    {
      return $nodes;
    }
}


?>