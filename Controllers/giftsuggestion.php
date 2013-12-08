<?php

include('aws_signed_request.php');
include('functions.php');

// Code Begins!
session_start();

$UserData = GetGiftUserData();

$SearchIndex = "All";

if (isset($UserData['Traits']) && isset($UserData['Person']))
{
  $NodeOutput = array();
  foreach ($UserData['Traits'] as $trait)
  {
    $nodevec = PullNodeId($trait, $UserData['Person']);
    if ($nodevec != 400)
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