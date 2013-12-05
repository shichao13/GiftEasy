<?php

include('aws_signed_request.php');

//Enter your IDs
define("Access_Key_ID", "AKIAIMCFHBE6JELLM3JQ");
define("SECRET_KEY","PGKAUCkEraUBFxrNFoBTf5dhE8LSFNEm+Pq1oxAd");

function printSearchResults($parsed_xml, $SearchIndex){
  print("<table>");
  if($numOfItems>0){
  foreach($parsed_xml->Items->Item as $current){
    print("<td><font size='-1'><b>".$current->ItemAttributes->Title."</b>");
    if (isset($current->ItemAttributes->Title)) {
    print("<br>Title: ".$current->ItemAttributes->Title);
  } elseif(isset($current->ItemAttributes->Author)) {
    print("<br>Author: ".$current->ItemAttributes->Author);
  } elseif
   (isset($current->Offers->Offer->Price->FormattedPrice)){
    print("<br>Price:
    ".$current->Offers->Offer->Price->FormattedPrice);
  }else{
  print("<center>No matches found.</center>");
   }
  }
 }
}  

//Set up the operation in the request
function ItemSearch($SearchIndex, $Keywords){

//Set the values for some of the parameters
$Operation = "ItemSearch";
$Version = "2011-08-01";
$ResponseGroup = "ItemAttributes,Offers";
$SearchIndex = "Baby";
$Keywords = $_POST["keywords"];
$Time = date("c");
$Sig = base64_encode(hash_hmac('sha256', $Operation.$Time, SECRET_KEY, true));
$Sig = str_replace('+','%2B',$Sig);
$Sig = str_replace('=','%3D',$Sig);

  //Define the request
$request=
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

//Catch the response in the $response object
$response = file_get_contents($request);
$parsed_xml = simplexml_load_string($response);
printSearchResults($parsed_xml, $SearchIndex);
}

ItemSearch($SearchIndex, $Keywords);
?>