<?php

include('aws_signed_request.php');
include('functions.php')

// Code Begins!
session_start();

// Number of pages we are looking at from amazon
$j=2;

// Main function - looking for items
OldItemSearch($SearchIndex, $Keywords, $j);
$items = array($_SESSION['Title'], $_SESSION['Author'], $_SESSION['Price'], $_SESSION['Review']);
render("Pages/resultspage.html", ["items" => $items, "pagetitle" => "Search Results"]);
?>