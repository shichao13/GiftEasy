<?php

// Header file to use if images are being displayed here.
//header('Content-Type: image/jpeg');

include('aws_signed_request.php');
include('functions.php');

// Code Begins!
session_start();

// Get data for our program
$UserData = GetGiftUserData();
$NodeData = LoadNodes();

$SearchIndex = "All";

if (isset($UserData['Traits']) && isset($UserData['Person']))
{
  $NodeOutput = array();
  foreach ($UserData['Traits'] as $trait)
  {
    $nodevec = PullNodeId($trait, $UserData['Person'], $NodeData);
    if (count($nodevec) != 0)
    {
      array_push($NodeOutput, MultiNodeSearch($nodevec));
    }
  }
}

if (isset($UserData['Keywords']))
{
  $KeyOutput = array();
  foreach ($UserData['Keywords'] as $word)
  {
    $safe = 1;
    $chars = str_split($word);

    $length = count($chars);

    for($i = 0; $i < $length; $i++)
    {
      $av = ord($chars[$i]);

      if (!($av == 32 || ($av > 47 && $av < 58) || ($av > 96 && $av < 123)))
      {
        print_r("ERROR: Nonstandard User Input Character");
        $safe = 0;
      }
    }

    if($safe == 1)
    {
      array_push($KeyOutput, FullItemSearch($SearchIndex, $word));
    }
  }
}

print_r($NodeOutput);

if (!(isset($KeyOutput) || isset($NodeOutput)))
{
  print_r("ERROR: User did not input keywords or traits");
}
else if (isset($KeyOutput) && isset($NodeOutput))
{
  // ToSession($NodeOutput);
  // $NodeItems = array($_SESSION['Title'], $_SESSION['Image'], $_SESSION['Price'], $_SESSION['Numbers']);
  $NodeItems = $NodeOutput;

  // ToSession($KeyOutput);
  // $KeyItems = array($_SESSION['Title'], $_SESSION['Image'], $_SESSION['Price']);
  $KeyItems = $KeyOutput;

  render("Pages/resultspage.html", ["KeyItems" => $KeyItems, "NodeItems" => $NodeItems, "pagetitle" => "Search Results"]);
}
else if (isset($KeyOutput))
{
  // ToSession($KeyOutput);
  // $KeyItems = array($_SESSION['Title'], $_SESSION['Image'], $_SESSION['Price']);
  $KeyItems = $KeyOutput;

  render("Pages/resultspage.html", ["KeyItems" => $KeyItems, "pagetitle" => "Search Results"]);
}
else
{
  // ToSession($NodeOutput);
  // $NodeItems = array($_SESSION['Title'], $_SESSION['Image'], $_SESSION['Price'], $_SESSION['Numbers']);
  $NodeItems = $NodeOutput;

  render("Pages/resultspage.html", ["NodeItems" => $NodeItems, "pagetitle" => "Search Results"]);
}


?>