<?php

include('aws_signed_request.php');

// IDs for pulling goods
define("Access_Key_ID", "AKIAIMCFHBE6JELLM3JQ");
define("SECRET_KEY","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"); 

function pulltitles($parsed_xml)
{
  // Initialize our output array
  $output = array();

  // Find output!
  foreach($parsed_xml->Items->Item as $current)
  {
      array_push($output, (string)$current->ItemAttributes->Title);
  }

// Return what we have
return $output;
}

function printtitles(&$relevancexml, $searcharray, $j)
{
  // How many have we currently found?
  $displayedinfo = 0;

  // How long is the array we are searching through?
  $length = count($searcharray);

  // Go through all of the text and determine if we have found matches
  for ($i = 0, $i<$j, $i++)
  {
    foreach($relevancexml[$i]->Items->Item as $current)
    {
      for($k = 0; $k<$length; $k++)
      {
        // The title we are comparing to the item that we have found
        $comparetitle = $searcharray[$k];

        // If they are the same, then...
        if($current->ItemAttributes->Title == $comparetitle)
        {
          // We have found an extra term!
          $displayedinfo++;

          // Push these arrays onto the superglobal for the next page
          array_push($_SESSION['Title'], (string)$current->$ItemAttributes->Title);
          array_push($_SESSION['Author'], (string)$current->$ItemAttributes->$Author); 
          array_push($_SESSION['Price'], (float)$current->Offers->Offer->Price->FormattedPrice); 
          array_push($_SESSION['Review'], $review);
        }
      }
    }
  }
  
  // Find out how many 
  $numberremain = 10 - $displayedinfo;
  nomatcheserror(&$relevancexml, $numberremain);
}

function nomatcheserror(&$relevancexml, $numberremain)
{
  $numOfItems = 0;
  foreach($parsed_xml->Items->Item as $counting)
    {
      $numOfItems = $numOfItems + 1;
    }
  if($numOfItems >0)
    {
      $numOfItems = 0;
      foreach ($parsed_xml->Items->Item as $current)
      {
        if($covered <= $numOfItems)
        {
          $covered++;
          array_push($_SESSION['Title'], (string)$current->$ItemAttributes->Title);
          array_push($_SESSION['Author'], (string)$current->$ItemAttributes->$Author); 
          array_push($_SESSION['Price'], (float)$current->Offers->Offer->Price->FormattedPrice); 
          array_push($_SESSION['Review'], $review);
        }
      } 
    }
}
  
//Set up the operation in the request
function ItemSearch($SearchIndex, $Keywords, $j)
{
  // Set parameters that will not change over time
  $Operation = "ItemSearch";
  $Version = "2011-08-01";
  $ResponseGroup = "ItemAttributes,Offers";

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

  // Define Parameters Matrix
  $params['Operation']=$Operation;
  $params['SearchIndex']=$SearchIndex;
  $params['Keywords']=$Keywords;

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
  for($i = 1; $i<=$j; $i++)
  {
    //Which Item Page are we on?
    $params['ItemPage']=$i;

    // If it isn't the first one, then we don't want sort to be in it
    if ($i != 1)
    {
      unset($params['Sort']);
    }

    // Create a response with proper signatures using the other function, get from Amazon
    $response = file_get_contents(aws_signed_request('com', $params, Access_Key_ID, SECRET_KEY));
    array_push($relevancexml, simplexml_load_string($response));

    // Create a second response with the sort on bestseller instead
    $params['Sort'] = $Sort;
    $responsetwo = file_get_contents(aws_signed_request('com', $params, Access_Key_ID, SECRET_KEY));
    array_push($bestsellxml, simplexml_load_string($responsetwo));

    // Start pulling titles into the total titles array
    $relevancearray = array_merge($relevancearray, pulltitles($parsed_xml));
    $bestsellarray = array_merge($bestsellarray, pulltitles($parsed_xmltwo));
  }
    
  printtitles(&$relevancexml, $bestsellarray, $j);

}

session_start();
$determine = 1;
$j=6;

// Defining the arrays of titles
$relevancearray = array();
$bestsellarray = array();

$relevancexml = array();
$bestsellxml = array();
ItemSearch($SearchIndex, $Keywords, $j, $determine);
?>