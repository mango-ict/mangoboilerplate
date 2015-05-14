<?php

    class db{

        var $host = "";
        var $usr = "";
        var $pasw = "";
        var $db = "";
        var $tb = "";
        var $insertid = "";
        var $link = "";
        var $result = "";

   		var $error_log = "";
		
		function strip_html_tags( $text )
		{
			// PHP's strip_tags() function will remove tags, but it
			// doesn't remove scripts, styles, and other unwanted
			// invisible text between tags.  Also, as a prelude to
			// tokenizing the text, we need to insure that when
			// block-level tags (such as <p> or <div>) are removed,
			// neighboring words aren't joined.
			$text = preg_replace(
				array(
					// Remove invisible content
					'@<head[^>]*?>.*?</head>@siu',
					'@<style[^>]*?>.*?</style>@siu',
					'@<script[^>]*?.*?</script>@siu',
					'@<object[^>]*?.*?</object>@siu',
					'@<embed[^>]*?.*?</embed>@siu',
					'@<applet[^>]*?.*?</applet>@siu',
					'@<noframes[^>]*?.*?</noframes>@siu',
					'@<noscript[^>]*?.*?</noscript>@siu',
					'@<noembed[^>]*?.*?</noembed>@siu',
		
					// Add line breaks before & after blocks
					'@<((br)|(hr))@iu',
					'@</?((address)|(blockquote)|(center)|(del))@iu',
					'@</?((div)|(h[1-9])|(ins)|(isindex)|(p)|(pre))@iu',
					'@</?((dir)|(dl)|(dt)|(dd)|(li)|(menu)|(ol)|(ul))@iu',
					'@</?((table)|(th)|(td)|(caption))@iu',
					'@</?((form)|(button)|(fieldset)|(legend)|(input))@iu',
					'@</?((label)|(select)|(optgroup)|(option)|(textarea))@iu',
					'@</?((frameset)|(frame)|(iframe))@iu',
				),
				array(
					' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ',
					"\n\$0", "\n\$0", "\n\$0", "\n\$0", "\n\$0", "\n\$0",
					"\n\$0", "\n\$0",
				),
				$text );
		
			// Remove all remaining tags and comments and return.
			return strip_tags( $text );
		}
		
		function db_sanitize($name,$method = "POST",$classname = "",$striptags = true,$mysql_compatible = true,$expected = "string",$value_expectations = array()){
		
			// We pakken de waarde als deze bestaat in de lijst met variabelen.
			if ($method == "POST"){
				if(isset($_POST[$name])){
					$tmp = $_POST[$name];
					$this->error_log .= "POST variable '$name' IS set.";
				} else {
					$this->error_log .= "POST variable '$name' not set.";
					return false;
				}
			}
			if ($method == "GET"){
				if(isset($_GET[$name])){
					$tmp = $_GET[$name];
					$this->error_log .= "GET variable '$name' IS set.";
				} else {
					$this->error_log .= "GET variable '$name' not set.";
					return false;
				}
			}
			if ($method == "COOKIE"){
				if(isset($_COOKIE[$name])){
					$tmp = $_COOKIE[$name];
					$this->error_log .= "COOKIE variable '$name' IS set.";
				} else {
					$this->error_log .= "COOKIE variable '$name' not set.";
					return false;
				}
			}
			if ($method == "SESSION"){
				if(isset($_SESSION[$name])){
					$tmp = $_SESSION[$name];
					$this->error_log .= "SESSION variable '$name' IS set.";
				} else {
					$this->error_log .= "SESSION variable '$name' not set.";
					return false;
				}
			}
			if ($method == "VALUE"){
				$tmp = $name;
			}
			
			// We strippen alle tags.
			if($striptags == true){
				$tmp = $this->strip_html_tags($tmp);
				$this->error_log .= "We stripped the tags of $name.";
			}
			
			// Als het mysql compatbile moet zijn dan het volgende:
			if($mysql_compatible == true){
				$tmp = $this->db_addslashes($tmp,$this->link);
                $tmp = mysql_real_escape_string($tmp, $this->link);
			}
			
			// We bekijken of de variable de te verwachte type is.
			switch($expected){
				case  "string":
					if (is_string($tmp)){
						$this->error_log .= "$name is a string, as expected.";
					} else {
						$this->error_log .= "$name is NOT a string, as expected.";
						return false;
					}
					break;
				case  "array":
					if (is_array($tmp)){
						$this->error_log .= "$name is an array, as expected.";
					} else {
						$this->error_log .= "$name is NOT an array, as expected.";
						return false;
					}
					break;
				case  "boolean":
					if (is_bool($tmp)){
						$this->error_log .= "$name is a boolean, as expected.";
					} else {
						$this->error_log .= "$name is NOT a boolean, as expected.";
						return false;
					}
					break;
				case  "numeric":
					if (is_numeric($tmp)){
						$this->error_log .= "$name is numeric, as expected.";
					} else {
						$this->error_log .= "$name is NOT numeric, as expected.";
						return false;
					}
					break;
				case  "int":
					if (is_int($tmp)){
						$this->error_log .= "$name is an integer, as expected.";
					} else {
						$this->error_log .= "$name is NOT an integer, as expected.";
						return false;
					}
					break;
				case  "float":
					if (is_float($tmp)){
						$this->error_log .= "$name is a float (decimal in VB), as expected.";
					} else {
						$this->error_log .= "$name is NOT a float (decimal in VB), as expected.";
						return false;
					}
					break;
				case "object":
					if (is_a($tmp,$classname)){
						$this->error_log .= "$name is an object of $classname, as expected.";
					} else {
						$this->error_log .= "$name is NOT an object of $classname, as expected.";
						return false;
					}
					break;
				case "nan":
					if (is_nan($tmp)){
						$this->error_log .= "$name is not a number, as expected.";
					} else {
						$this->error_log .= "$name is NOT not a number, as expected.";
						return false;
					}
					break;
				default:
					if (is_string($tmp)){
						$this->error_log .= "$name is a string, as expected.";
					} else {
						$this->error_log .= "$name is NOT a string, as expected.";
						return false;
					}
					break;
			}
		
			// Als de waarde valt in de te verwachte waarde dan is het goed en sturen we het door.
			if(count($value_expectations) > 0){
				
				$found_value_expect = false;
				while(list($k,$v) = each($value_expectations)){
					if($tmp == $v){
						$found_value_expect = true;
					}
				}
				if($found_value_expect == true){
				} else {
					return false;
				}
				
			}
			
			return $tmp;
		
		}
        
        function db_create_password(){
            return substr(hash('sha512',rand()),0,8);
        }
        
		function db_addslashes($var){
			return addslashes($var);
		}
		
        function db_connection_check(){
        	/* Open connection */
        	$link = mysql_connect($this->host, $this->usr, $this->pasw);
			if (!$link) {
			    return false;
			}
			$db_selected = mysql_select_db($this->db, $link);
			if (!$db_selected) {
			    return false;
			}
            mysql_close($link);
            return true;
        }

        function db_open_connection(){
        	/* Open connection */
        	$this->link = mysql_connect($this->host, $this->usr, $this->pasw) or die("Could not connect : " . mysql_error());
            mysql_select_db($this->db) or die("Could not select database");
        }

        function db_sort($result,$field){
        	/* Speciaal sorteren van de gegevens */
			reset($result);
			$new_k = array();
			$new_sort_k = array();
			while(list($kk,$vv) = each($result)){
				$new_sortnr = "0000000000000";
				$tmp_sortnr = $vv[$field];
				$c_length = strlen($tmp_sortnr);
				$cc_length = strlen($new_sortnr);
				$ccc_length = $cc_length - $c_length;
				$str = substr($new_sortnr,0,$ccc_length);
				$str .= $tmp_sortnr;
				$result[$kk][$field] = $str;
				$new_k[$str] = $kk;
				$new_sort_k[] = $str;
			}

			/*  */
			sort($new_sort_k,SORT_NUMERIC);
			$new_prods_records = array();
			while(list($kk,$vv) = each($new_sort_k)){
				$k_special = $new_k[$vv];
				$new_prods_records[] = $result[$k_special];
			}
			$result = "";
			$result = $new_prods_records;
			reset($result);
			$this->result = $result;
			return $result;
        }

        function db_close_connection(){
        	/* Closing connection */
            mysql_close($this->link);
        }

        function db_create_fieldtable($css_name = "",$submit_reset = true,$submit = "verzenden",$reset = "beginwaarden",$dont_show = array()){

        	$fields = $this->db_field_list();

        	$res = "";

        	if(is_array($fields)){

        		$res .= "<table class=\"dbInsert$css_name\">\n";


        		while(list($k,$v) = each($fields)){

        			$name = $v[0];
        			$showname = ucfirst(htmlentities(str_replace("_"," ",$v[0])));
        			$type = $v[1];

        			if(strtolower($name) == "id" || isset($dont_show[$name])){
        			} else {

	        			// Default.
	        			$field_insert = "<input type=\"text\" class=\"dbTdField_".$name."_"."$css_name\" />";
	        			if(strtolower($type) == "int"){
	        				$field_insert = "<input type=\"text\" class=\"dbTdField_".$name."_"."$css_name\" />";
	        			}
	        			if(strtolower($type) == "string"){
	        				$field_insert = "<input type=\"text\" class=\"dbTdField_".$name."_"."$css_name\" />";
	        			}
	        			if(strtolower($type) == "blob"){
	        				$field_insert = "<textarea class=\"dbTdField_".$name."_"."$css_name\"></textarea>";
	        			}

	        			$res .= "<tr class=\"dbTr$css_name\">\n";

	        			$res .= "<td class=\"dbTdFieldName$css_name\"><span class=\"dbTdField_ShowName_".$name."_"."$css_name\"\">$showname</span></td><td class=\"dbTdFieldInsert$css_name\">$field_insert</td>\n";

        				$res .= "</tr>\n";
        			}
        		}


        		$res .= "<tr class=\"dbTr$css_name\">\n";

        		if($submit_reset == true){
        			$res .= "<td class=\"dbTdSubmitResetFront$css_name\"></td><td class=\"dbTdSubmitResetBack$css_name\"><input type=\"submit\" value=\"$submit\" class=\"dbTdSubmit"."$css_name\" />&nbsp;<input type=\"reset\" value=\"$reset\" class=\"dbTdReset"."$css_name\" /></td>\n";
        		}

        		$res .= "</tr>\n";

        		$res .= "</table>";

        	}


        	return $res;
        }

        function db_delete($sql){

            /* Performing SQL query */
            $query = "DELETE FROM `$this->tb` WHERE $sql ; ";
            $result = mysql_query($query, $this->link) or die("Query failed : " . mysql_error() . " query: " . $query);

            return $result;
        }

        function db_copy($tbfields,$field,$value){

            /* Performing SQL query */
            $query = "INSERT INTO `$this->tb` (".$tbfields.") SELECT $tbfields FROM `$this->tb` WHERE `".$field."` = '".$value."';";
            $result = mysql_query($query, $this->link) or die("Query failed : " . mysql_error() . " query: " . $query);

            return $result;

        }

        function db_field_list(){

			$fields = mysql_list_fields($this->db, $this->tb, $this->link);
			$this->columns = mysql_num_fields($fields);

			$fieldsres = array();
			if (!$fields) {
				print "DB Error, could not list fields\n";
		        print 'MySQL Error: ' . mysql_error();
		        exit;
			} else {

				for ($i = 0; $i < $this->columns; $i++) {
					$type  = mysql_field_type($fields, $i);
					$name  = mysql_field_name($fields, $i);
					$len  = mysql_field_len($fields, $i);
					$flags = mysql_field_flags($fields, $i);
					$fieldsres[] = array( 0 => $name, 1 => $type, 2 => $len, 3 => $flags);
				}

				return $fieldsres;

			}
		}


        function db_tables(){

        	/* Performing MySQL Action*/
            $result = mysql_list_tables($this->db);

            if (!$result) {
                print "DB Error, could not list tables\n";
                print 'MySQL Error: ' . mysql_error();
                exit;
            }

            $t = 'Tables_in_'.$this->db;
            $tables = array();
            while ($line = mysql_fetch_array($result, MYSQL_ASSOC)) {

                $tables[] = $line[$t];
            }

            mysql_free_result($result);

            return $tables;
        }

        function db_findall($sql = "", $type = MYSQL_ASSOC, $fields = "*"){

            /* Performing SQL query */
            $query = "SELECT $fields FROM $this->tb $sql";
			//$result = mysql_query($query, $this->link) or trigger_error("Query failed : " . $query, E_USER_ERROR);
            $result = mysql_query($query, $this->link) or die(" HERE !!! Query failed : " . mysql_error() . " query: " . $query);

            /* Printing results in HTML */
            $records = array();
            while ($line = mysql_fetch_array($result, $type)) {
                $records[] = $line;
            }

            /* Free resultset */
            mysql_free_result($result);

            /* Internal records */
			$this->result = $records;

            return $records;
        }

        function db_findall_row_count(){

            /* Performing SQL query */
            $query = "SELECT * FROM $this->tb";
            $result = mysql_query($query, $this->link) or die("Query failed : " . mysql_error() . " query: " . $query);

            /* Printing results in HTML */
            $count = mysql_num_rows($result);

            /* Internal records */
			$this->result = $count;

            return $count;

        }

        function db_insert($fields,$vals){

            /* Performing SQL query */
            $query = "INSERT INTO `$this->tb` ($fields) VALUES ($vals); ";
            $result = mysql_query($query, $this->link) or die("Query failed : " . mysql_error() . " query: " . $query);

            $this->insertid = mysql_insert_id();

            return $result;
        }

        function db_search($sql,$limit = "10000"){

            /* Performing SQL query */
            $query = "SELECT * FROM `$this->tb` WHERE $sql LIMIT $limit;";
            $result = mysql_query($query, $this->link) or die("Query failed : " . mysql_error() . " query: " . $query);

            /* Printing results in HTML */
            $records = array();
            while ($line = mysql_fetch_array($result, MYSQL_ASSOC)) {
                $records[] = $line;
            }

            /* Free resultset */
            mysql_free_result($result);

            /* Internal records */
			$this->result = $records;

            return $records;
        }

        function db_search_extended($sql,$fields,$limit = "10000"){

            /* Performing SQL query */
            $query = "SELECT $fields FROM `$this->tb` WHERE $sql LIMIT $limit;";
            $result = mysql_query($query, $this->link) or die("Query failed : " . mysql_error() . " query: " . $query);

            /* Printing results in HTML */
            $records = array();
            while ($line = mysql_fetch_array($result, MYSQL_ASSOC)) {
                $records[] = $line;
            }

            /* Free resultset */
            mysql_free_result($result);

            /* Internal records */
			$this->result = $records;

            return $records;
        }
        
        function db_search_row_count($sql,$limit = "10000"){

            /* Performing SQL query */
            $query = "SELECT * FROM `$this->tb` WHERE $sql LIMIT $limit;";
            $result = mysql_query($query, $this->link) or die("Query failed : " . mysql_error() . " query: " . $query);

            $count = mysql_num_rows($result);

            /* Internal records */
			$this->result = $count;

            return $count;
        }

		function db_raw_search($sql,$limit = "10000"){

		    /* Performing SQL query */
		    $query = "$sql";
		    $result = mysql_query($query, $this->link) or die("Query failed : " . mysql_error() . " query: " . $query);

		    /* Printing results in HTML */
		    while ($line = mysql_fetch_array($result, MYSQL_ASSOC)) {
		        $records[] = $line;
		    }

			$this->rows = mysql_num_rows($result);

		    /* Free resultset */
		    mysql_free_result($result);

            /* Internal records */
			$this->result = $records;

		    return $records;
		}

        function db_query($sql){

            /* Performing SQL query */
            $query = "$sql";
            $result = mysql_query($query, $this->link) or die("Query failed : " . mysql_error() . " query: " . $query);

            return $result;
        }

        function db_createtable($sql){

            /* Performing SQL query */
            $query = "CREATE TABLE `$this->tb` ( $sql ) TYPE=MyISAM AUTO_INCREMENT=1 ;";

            $result = mysql_query($query, $this->link) or die("Query failed : " . mysql_error() . " query: " . $query);


            return $result;
        }

        function db_droptable(){

            /* Performing SQL query */
            $query = "DROP TABLE IF EXISTS `$this->tb`;";
            $result = mysql_query($query, $this->link) or die("Query failed : " . mysql_error() . " query: " . $query);

            return $result;
        }

        function db_dropfield($field){
        	 /* Performing SQL query */
            $query = "ALTER TABLE `$this->tb` DROP `$field`;";

            $result = mysql_query($query, $this->link) or die("Query failed : " . mysql_error() . " query: " . $query);

            return $result;
        }


        function db_addafterfield($field,$type,$afterfield){

            /* Performing SQL query */
            $query = "ALTER TABLE `$this->tb` ADD `$field` $type AFTER `$afterfield` ;";

            $result = mysql_query($query, $this->link) or die("Query failed : " . mysql_error() . " query: " . $query);


            return $result;
        }

        function db_altercolomn($fname,$nname,$sql){

            /* Performing SQL query */
            $query = "ALTER TABLE `$this->tb` CHANGE `$fname` `$nname` $sql ";
            $result = mysql_query($query, $this->link) or die("Query failed : " . mysql_error() . " query: " . $query);

            return $result;
        }


        function db_update($sql,$fields){

            /* Performing SQL query */
            $query = "UPDATE `$this->tb` SET $sql WHERE $fields LIMIT 1; ";
            $result = mysql_query($query, $this->link) or die("Query failed : " . mysql_error() . " query: " . $query);

            return $result;
        }

        function db_lastid(){
            return $this->insertid;
        }

        function db_get_lastid(){
        	return mysql_insert_id($this->link);
        }

        function db_autoincrementval(){
        	$qry=mysql_query("SHOW TABLE STATUS WHERE name='".$this->tb."'",$this->link);
			$row=mysql_fetch_array($qry);
			return $row[10];
        }

   }