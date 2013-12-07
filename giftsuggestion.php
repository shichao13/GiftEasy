<?php

include('aws_signed_request.php');

//Enter your IDs
define("Access_Key_ID", "AKIAIMCFHBE6JELLM3JQ");
define("SECRET_KEY","PGKAUCkEraUBFxrNFoBTf5dhE8LSFNEm+Pq1oxAd");

//mySQL table is called 'Search Results'
//INSERT INTO 'Search Results' VALUES (title, author, price, reviews)
  
function compareprintSearchResults($parsed_xml, $parsed_xmltwo, $SearchIndex)
{
  $failcount = 0;
  print_r("<table>");
  $numOfItems = 0;
  $secOfItems = 0;
  foreach($parsed_xml->Items->Item as $counting)
          {
            $numOfItems = $numOfItems + 1;
          }
  foreach($parsed_xmltwo->Items->Item as $counting)
          {
            $secOfItems = $secOfItems + 1;
          }

  if($numOfItems>0 && $secOfItems>0)
  {
    print_r($numOfItems." ".$secOfItems);
    foreach($parsed_xmltwo->Items->Item as $compare)
    {
      foreach($parsed_xml->Items->Item as $current)
      {
        if ($current->ItemAttributes->Title == $compare->ItemAttributes->Title)
        {
          print_r("<td><font size='-1'><b>".$current->ItemAttributes->Title."</b>");
          if (isset($current->ItemAttributes->Title)) 
          {
            print_r("<br>Title: ".$current->ItemAttributes->Title);
          } 
          elseif(isset($current->ItemAttributes->Author)) 
          {
            print_r("<br>Author: ".$current->ItemAttributes->Author);
          } 
          elseif(isset($current->Offers->Offer->Price->FormattedPrice))
          {
            print_r("<br>Price:".$current->Offers->Offer->Price->FormattedPrice);
          }
          else
          {
            print_r("<center>No matches found.</center>");
          }
        }
        else
        {
          //print_r("\n Relevance and Bestselling do not match for item ".$current->ItemAttributes->Title."!");
          //print_r($failcount);
          //$failcount = $failcount + 1;
        }
      }
    }
  }
  else
  {
     print_r("There are no items that match the search results!");
     print_r("\n".$numOfItems);
     print_r("\n".$secOfItems);
  }
}  

function pulltitles($parsed_xml)
{
  $i=0;
  foreach($parsed_xml->Items->Item as $current)
  {
    if($i == 0)
    {
      $output[] = (string)$current->ItemAttributes->Title;
      $i++;
    }
    else
    {
      array_push($output, (string)$current->ItemAttributes->Title);
  }
}
  //print_r("Hi.");
  //print_r($output);
return $output;
}

function printtitles($parsed_xml, $searcharray, $i, $j)
{
  $displayedinfo = 0;
  print_r("<table>");
  foreach($parsed_xml->Items->Item as $current)
  {
    $length = count($searcharray);
    for($j = 0; $j<$length; $j++)
    {
      $comparetitle = $searcharray[$j];
      if($current->ItemAttributes->Title == $comparetitle)
      {
        $displayedinfo++;
        /*print_r("<td><fontsize='-1'><b>".$current->ItemAttributes->Title."</b>");
                if (isset($current->ItemAttributes->Title)) 
                {
                    print_r("<br>Title: ".$current->ItemAttributes->Title);
                } 
                elseif(isset($current->ItemAttributes->Author)) 
                {
                    print_r("<br>Author: ".$current->ItemAttributes->Author);
                } 
                elseif(isset($current->Offers->Offer->Price->FormattedPrice))
                {
                print_r("<br>Price:".$current->Offers->Offer->Price->FormattedPrice);
                }
        */
        /*
        // old query stuff
        $review = 0.00;
        $query = sprintf("INSERT INTO Results VALUES (%i, %s, %s, %.2f, %.2f)", 
        $displayedinfo, 
        (string)$current->$ItemAttributes->Title, 
        (string)$current->$ItemAttributes->$Author, 
        (float)$current->Offers->Offer->Price->FormattedPrice, 
        $review);

        $result = mysql_query($query);
        if (!result) 
        {
            $message = 'Invalid query: '. mysql_error() . "<br>";
            print_r($message);
        }
        */
        array_push($_SESSION['Title'], (string)$current->$ItemAttributes->Title);
        array_push($_SESSION['Author'], (string)$current->$ItemAttributes->$Author); 
        array_push($_SESSION['Price'], (float)$current->Offers->Offer->Price->FormattedPrice); 
        array_push($_SESSION['Review'], $review);
      }
    }
  }
  
  $numberremain = 10 - $displayedinfo;
  nomatcheserror($parsed_xml, $numberremain, $displayedinfo);
}
function nomatcheserror($parsed_xml, $numberremain, $displayedinfo)
{
    print_r("No Matches Between Bestselling and Relevance were Found<br>");
    $Operation = "ItemSearch";
$Version = "2011-08-01";
$ResponseGroup = "ItemAttributes,Offers";
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

/*
$Time = date("c");
$Sig = base64_encode(hash_hmac('sha256', $Operation.$Time, SECRET_KEY, true));
$Sig = str_replace('+','%2B',$Sig);
$Sig = str_replace('=','%3D',$Sig);
*/
  //Define the request
  /*$request=
     "http://webservices.amazon.com/onca/xml"
   . "?Service=AWSECommerceService"
   . "&AWSAccessKeyId=" . Access_Key_ID
   . "&Operation=" . $Operation
   . "&Version=" . $Version
   . "&SearchIndex=" . $SearchIndex
   . "&Keywords=" . $Keywords
   . "&Signature=" . $Sig
   . "&Timestamp=" . $Time
   . "&ResponseGroup=" . $ResponseGroup;
 */

// Define Parameters Matrix
$params['Operation']=$Operation;
$params['SearchIndex']=$SearchIndex;
$params['Keywords']=$Keywords;
  //$params['ResponseGroup']=$ResponseGroup;  
  $response = file_get_contents(aws_signed_request('com', $params, Access_Key_ID, SECRET_KEY));
  //$response = file_get_contents($request);
  $parsed_xml = simplexml_load_string($response);
  
  print_r("<table>");
  $numOfItems = 0;
  $covered = 0;
  foreach($parsed_xml->Items->Item as $counting)
          {
            $numOfItems = $numOfItems + 1;
          }
  if($numOfItems >0)
  {
     foreach($parsed_xml->Items->Item as $current)
             {
               if($covered <= $numberremain)
               {
               $covered++;
               /*
               print_r("<td><font size='-1'><b>".$current->ItemAttributes->Title."</b>");
          if (isset($current->ItemAttributes->Title)) 
          {
            print_r("<br>Title: ".$current->ItemAttributes->Title);
          } 
          elseif(isset($current->ItemAttributes->Author)) 
          {
            print_r("<br>Author: ".$current->ItemAttributes->Author);
          } 
          elseif(isset($current->Offers->Offer->Price->FormattedPrice))
          {
            print_r("<br>Price:".$current->Offers->Offer->Price->FormattedPrice);
          }
          */
          /*
          // old query stuff
          $review = 0.00;
          $displayedinfo++;
          $query = sprintf("INSERT INTO Results VALUES (%i, %s, %s, %.2f, %.2f)", 
          $displayedinfo, 
          (string)$current->$ItemAttributes->Title, 
          (string)$current->$ItemAttributes->$Author, 
          (float)$current->Offers->Offer->Price->FormattedPrice, 
          $review);

          $result = mysql_query($query);
          if (!result) 
          {
              $message = 'Invalid query: '. mysql_error() . "<br>";
              print_r($message);
          }
          */

          array_push($_SESSION['Title'], (string)$current->$ItemAttributes->Title);
          array_push($_SESSION['Author'], (string)$current->$ItemAttributes->$Author); 
          array_push($_SESSION['Price'], (float)$current->Offers->Offer->Price->FormattedPrice); 
          array_push($_SESSION['Review'], $review);

             }
             } 
  }
}
  
//Set up the operation in the request
function ItemSearch($SearchIndex, $Keywords, $j, $determine){
$Operation = "ItemSearch";
$Version = "2011-08-01";
$ResponseGroup = "ItemAttributes,Offers";
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
$Keywords = $_POST["keywords"];

// Define Parameters Matrix
$params['Operation']=$Operation;
$params['SearchIndex']=$SearchIndex;
$params['Keywords']=$Keywords;

// Make userinfo into a superglobal
$_SESSION['AmazonCategory'] = $SearchIndex;
$_SESSION['Keywords'] = $Keywords;
$_SESSION['Gender'] = $_POST['gender'];

//array of results
$_SESSION['Title'] = array();
$_SESSION['Author'] = array();
$_SESSION['Price'] = array();
$_SESSION['Review'] = array();

for($i = 1; $i<=$j; $i++)
{
//Set the values for some of the parameters

/*
$Time = date("c");
$Sig = base64_encode(hash_hmac('sha256', $Operation.$Time, SECRET_KEY, true));
$Sig = str_replace('+','%2B',$Sig);
$Sig = str_replace('=','%3D',$Sig);
*/
  //Define the request
  /*$request=
     "http://webservices.amazon.com/onca/xml"
   . "?Service=AWSECommerceService"
   . "&AWSAccessKeyId=" . Access_Key_ID
   . "&Operation=" . $Operation
   . "&Version=" . $Version
   . "&SearchIndex=" . $SearchIndex
   . "&Keywords=" . $Keywords
   . "&Signature=" . $Sig
   . "&Timestamp=" . $Time
   . "&ResponseGroup=" . $ResponseGroup;
 */


  //$params['ResponseGroup']=$ResponseGroup;
$params['ItemPage']=$i;
  if ($i != 1)
  {
    unset($params['Sort']);
  }
  //Catch the response in the $response object
  $response = file_get_contents(aws_signed_request('com', $params, Access_Key_ID, SECRET_KEY));
  //$response = file_get_contents($request);
  $parsed_xml = simplexml_load_string($response);
$params['Sort']=$Sort;
$responsetwo = file_get_contents(aws_signed_request('com', $params, Access_Key_ID, SECRET_KEY));
$parsed_xmltwo = simplexml_load_string($responsetwo);

if($determine == 1)
{
if ($i == 1)
{
  // print_r($parsed_xmltwo);
      $relevancearray = pulltitles($parsed_xml);
      $bestsellarray = pulltitles($parsed_xmltwo);
}
else
{
    $relevancearray = array_merge($relevancearray, pulltitles($parsed_xml));
    $bestsellarray = array_merge($bestsellarray, pulltitles($parsed_xmltwo));
    if ($i == $j-1)
    {
      $determine = 0;
    }
}
}
else
{
  printtitles($parsed_xml, $bestsellarray, $i, $j);
//compareprintSearchResults($parsed_xml, $parsed_xmltwo, $SearchIndex);
}
}
}

session_start();
$Keywords = $_POST["keywords"];
$determine = 1;
$j=6;
ItemSearch($SearchIndex, $Keywords, $j, $determine);
?>