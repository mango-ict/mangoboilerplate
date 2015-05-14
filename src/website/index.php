<?php

//
// Licensed to the Mango ICT software company under one
// or more contributor license agreements.  See the NOTICE file
// distributed with this work for additional information
// regarding copyright ownership.  The ASF licenses this file
// to you under the Apache License, Version 2.0 (the
// "License"); you may not use this file except in compliance
// with the License.  You may obtain a copy of the License at
//
// http://www.apache.org/licenses/LICENSE-2.0
//
// Unless required by applicable law or agreed to in writing,
// software distributed under the License is distributed on an
// "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY
//  KIND, either express or implied.  See the License for the
// specific language governing permissions and limitations
// under the License.
//

// Start a session
session_start();

// Truely start a session!
if( !isset($_SESSION['last_access']) || (time() - $_SESSION['last_access']) > 60 ) 
{
    $_SESSION['last_access'] = time();
}

// Show all errors
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Redirect to a HTTPS connection
if (isset($_SERVER['HTTPS']) == false) 
{
    $protocol = "https://";
    $address = $protocol.$_SERVER["SERVER_NAME"]."/";
    header("Location: " . $address);
    die;
}

// Get theme folder to use
$theme = "default";

// Parse the url
$url = parse_url($_SERVER['REQUEST_URI']);

// Get default HTML page
$page = file_get_contents("./theme/" . $theme . "/default.html");

// Go to default page when clicking on the home button.
if ($url["path"] == "/") 
{
    $url["path"] = "default";
}

// Check for a REST services request!
if (is_dir("./" . $url["path"]) && is_file("./" . $url["path"] . "/srv.php"))
{
    require_once("./" . $url["path"] . "/srv.php");
    $srv = new srv();
    $srv->execute();
} else {

    // Check if a page exists from the URL provided
    if (is_file("./theme/" . $theme . $url["path"] . ".html")) 
    {
        $page = file_get_contents("./theme/" . $theme . $url["path"] . ".html");
    }

    // Get page parts (seperate HTML files)
    $parts = array();
    if ($handle = opendir("./theme/" . $theme . "/parts"))
    {
        // This is the correct way to loop over the directory.
        while (false !== ($entry = readdir($handle))) {
            if($entry != "." && $entry != ".." && is_file("./theme/" . $theme . "/parts/" . $entry)) 
            {
                $parts[$entry] = file_get_contents("./theme/" . $theme . "/parts/" . $entry);
            }
        }
        closedir($handle);
    }

    // Interper template parts in the page. For now do this 30 times to go 30 layers deap.
    for($i = 0; $i < 30;$i++){
        foreach ($parts as $key => $value)
        {
            $page = str_replace("{{" . $key . "}}", $value, $page);
        }
    }

    // Get the consts from the template and replace them
    $const = file_get_contents("./theme/" . $theme . "/config/const.json");
    $const_arr = json_decode($const);
    foreach ($const_arr["const"] as $key => $value)
    {
        $page = str_replace("[" . $key . "]", $value, $page);
    }


    // Output HTML page
    header("Content-Type: text/html");
    echo $page;

}

