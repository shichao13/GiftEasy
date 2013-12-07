<?php

// this generates a problem when we search for "Octopus", but "Octopu" is unique as well
if ($_SESSION['ClickedTitle'][strlen($_SESSION['ClickedTitle'])] == 's')
{
	$NewTitle = rtrim($_SESSION['ClickedTitle'], "s");
}

$query = sprintf("SELECT * FROM Learning WHERE title = %s", $NewTitle);

// possible error: have not yet linked the database. need mysql_connect
$result = mysql_query($query);

if (!$result)
{
	$update = sprintf("INSERT INTO Learning VALUES (%i, )")
}

?>