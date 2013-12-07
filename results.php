<?php

	function render($template, $values = [])
	    {
	        // if template exists, render it
	        if (file_exists("../$template"))
	        {
	            // extract variables into local scope
	            extract($values);

	            // render header
	            require("../header.php");

	            // render template
	            require("../$template");

	            // render footer
	            require("../footer.php");
	        }

	        // else err
	        else
	        {
	            trigger_error("Invalid template: $template", E_USER_ERROR);
	        }
	    }

	$items = array($_SESSION['Title'], $_SESSION['Author'], $_SESSION['Price'], $_SESSION['Review']);
	render("resultspage.html", ["items" => $items, "pagetitle" => "Search Results"]);
    

?>

