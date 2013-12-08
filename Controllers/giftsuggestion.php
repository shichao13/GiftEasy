<?php

include('aws_signed_request.php');
include('functions.php');

// Code Begins!
session_start();

$UserData = GetGiftUserData();




$output = MultiNodeSearch(array($Node));
print_r($output);

ToSession($output);
$items = array($_SESSION['Title'], $_SESSION['Author'], $_SESSION['Price'], $_SESSION['Review']);
render("Pages/resultspage.html", ["items" => $items, "pagetitle" => "Search Results"]);
?>