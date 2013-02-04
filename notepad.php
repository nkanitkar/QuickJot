<?php 
/**WRITTEN BY NISHANT KANITKAR**/
/**
    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.

**/
/**
**/

session_start(); 
date_default_timezone_set("America/New_York");

/*** DEFINE CONSTANTS ***/
$salt = "PUT_A_RANDOM_SALT_HERE";
$TABLE_NAMES['user'] = "users";
$TABLE_NAMES['email'] = "notepad";
$FILENAME = "notepad.php"
$WEBSITE = "blah.com";
/*** END DEFINE CONSTANTS **/

function connectDB()
{
	$username = "DATABASE_USERNAME";
	$password = "DATABASE_PASSWORD";
	$database = "DATABASE_NAME";
        $hostname = "DATABASE_HOST (localhost)";
        $dbh = mysql_connect($hostname, $username, $password)
        or die("Unable to connect to MySQL");
        //print "Connected to MySQL<br>";
        $selected = mysql_select_db($database,$dbh)
        or die("Could not select database");
        //print "connected to Database<br>";

}

function sendemail($to,$subject,$body, $headers){
	if(mail($to,$subject,$body, $headers)){
		return true;
	}
	else{return false;}
}

//Connect to Database
connectDB();



function sendNoteToDatabase($note)
{
	global $TABLE_NAMES;
	$note = mysql_real_escape_string($note);
	$query= "UPDATE {$TABLE_NAMES['user']}  SET `note`='$note' WHERE `user`='{$_SESSION['loggedInUser']}'";
	mysql_query($query) or die("The Following query failed:<br>$query");
	return true;
}

function sendNoteToDatabaseForUser($user, $note)
{
        global $TABLE_NAMES;
        $note = mysql_real_escape_string($_POST['ajax']);
        $user = mysql_real_escape_string($_POST['user']);
        $query= "UPDATE {$TABLE_NAMES['user']}  SET `note`='$note' WHERE `user`='$user'";
        mysql_query($query) or die("The Following query failed:<br>$query");
        return true;
}


function getNoteFromDatabase()
{
	global $TABLE_NAMES;
	$query = "SELECT `note`  FROM {$TABLE_NAMES['user']}  WHERE `user`='{$_SESSION['loggedInUser']}'";
//	echo $query;
	$result = mysql_query($query) or die("Query failed:<br>$query");
	$row = mysql_fetch_array($result,MYSQL_ASSOC);
	return $row['note'];
}

function getEmailFromDatabase($id)
{
	$id = mysql_real_escape_string($id);
        global $TABLE_NAMES;
        $query = "SELECT `body`  FROM {$TABLE_NAMES['email']}  WHERE `id`='$id'";
        $result = mysql_query($query) or die("Query failed:<br>$query");
        $row = mysql_fetch_array($result,MYSQL_ASSOC);
        return $row['body'];

}

function checkLogin()
{
        global $salt;
        global $TABLE_NAMES;
        $user = mysql_real_escape_string($_POST['ur123']);
        $query = "SELECT * FROM {$TABLE_NAMES['user']}  WHERE `user`='$user'";
        $result = mysql_query($query);

        if(mysql_num_rows($result)==0)
        {
                echo "login failed, no results";
                die();
                return false;
        }

        $row = mysql_fetch_array($result,MYSQL_ASSOC);
	//debugger
        //echo $row['user'],"<br>",$_POST['ur123'],"<br>","Post Password: {$_POST['ps123']}","<br>","row['pass']: ".$row['pass'],"<br>",SHA1("{$_POST['ps123']}$salt"),"<br>","{$_POST['ps123']}$salt","<br>";


        if($row['user']==$_POST['ur123'] && $row['pass']==SHA1("{$_POST['ps123']}$salt"))
        {
            $_SESSION['loggedIn']=true;
            $_SESSION['loggedInUser'] = $_POST['ur123'];
        }
        else
        {
                echo "Login failed";
                die();
        }
}

function logout()
{
        session_destroy();
	header("Location: $FILENAME"); //Hack to ensure behavior?
	die();
}

if(isset($_POST['ur123']))
{
        checkLogin();
}
if(isset($_POST['logout']))
{
      logout();
}


if(isset($_GET['display']))
{
	echo "<html><head><title>Display</title></head><body>";
	echo "<textarea cols=100 rows=40 readonly>".$_GET['display']."</textarea>";
	echo "<br> <br><a href=\"$FILENAME\">BACK</a></body></html>";
	die();
}

if(isset($_GET['displayid']))
{
	$text = getEmailFromDatabase($_GET['displayid']);
     	echo "<html><head><title>Display</title></head><body>";
        echo "<textarea cols=100 rows=40 readonly>".$text."</textarea>";
        echo "<br> <br><a href=\"FILENAME\">BACK</a></body></html>";
        die();
}

if(isset($_POST['ajax']) /*&& isset($_POST['user'])*/)
{
	global $TABLE_NAMES;
	sendNoteToDatabase($_POST['ajax']);
}

if(isset($_POST['notes']) )
{
	if($_POST['save'] || $_POST['submit'])
        {
	   sendNoteToDatabase(stripslashes($_POST['notes']));
        }

	if($_POST['submit'])
	{
	//SEND EMAIL!

	/************** EMAIL MAPPINGS ****************/
	$to = " ";
	if($_POST['bob'])
		$to .="bob@blah.com,";
	if($_POST['dev_log'])
		$to .="dev_log@blah.com,";
	/************** END EMAIL MAPPINGS ************/

	$to = substr($to, 0, -1);	//Remove Traiing Comma
	$date = date("M j, Y g:ia");
	$subject = "Notepad Message: {$_SESSION['loggedInUser']}  $date";
	$headers = "From: noreply@$WEBSITE";
 	$body = stripslashes($_POST['notes']);	
	if($to=="")
		$sent ="No Recipients!";
	else
	{ 
		$sent = sendemail($to,$subject,$body, $headers);
		if($sent===true)
		{
			//ADD TO DATABASE
			$subject = mysql_real_escape_string($subject);
			$to = mysql_real_escape_string($to);
			$body = mysql_real_escape_string($body);
			$query = "INSERT INTO {$TABLE_NAMES['email']} (`title`,`user`,`to`,`body`) VALUES ('$subject','{$_SESSION['loggedInUser']}', '$to', '$body');";
			mysql_query($query) or die("QUERY FAILED");
			
		}
	}
	if($sent===true)
		$sent ="Note Sent!<br>$to";
	}

	if($_POST['clear'])
	{
		sendNoteToDatabase("");
	}

}

?>
<html>
<head><title>NotePad Application</title>
<script type="text/javascript">
function get_html_translation_table (table, quote_style) {
    // http://kevin.vanzonneveld.net
    // +   original by: Philip Peterson
    // +    revised by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +   bugfixed by: noname
    // +   bugfixed by: Alex
    // +   bugfixed by: Marco
    // +   bugfixed by: madipta
    // +   improved by: KELAN
    // +   improved by: Brett Zamir (http://brett-zamir.me)
    // +   bugfixed by: Brett Zamir (http://brett-zamir.me)
    // +      input by: Frank Forte
    // +   bugfixed by: T.Wild
    // +      input by: Ratheous
    // %          note: It has been decided that we're not going to add global
    // %          note: dependencies to php.js, meaning the constants are not
    // %          note: real constants, but strings instead. Integers are also supported if someone
    // %          note: chooses to create the constants themselves.
    // *     example 1: get_html_translation_table('HTML_SPECIALCHARS');
    // *     returns 1: {'"': '&quot;', '&': '&amp;', '<': '&lt;', '>': '&gt;'}
    var entities = {},
        hash_map = {},
        decimal = 0,
        symbol = '';
    var constMappingTable = {},
        constMappingQuoteStyle = {};
    var useTable = {},
        useQuoteStyle = {};

    // Translate arguments
    constMappingTable[0] = 'HTML_SPECIALCHARS';
    constMappingTable[1] = 'HTML_ENTITIES';
    constMappingQuoteStyle[0] = 'ENT_NOQUOTES';
    constMappingQuoteStyle[2] = 'ENT_COMPAT';
    constMappingQuoteStyle[3] = 'ENT_QUOTES';

    useTable = !isNaN(table) ? constMappingTable[table] : table ? table.toUpperCase() : 'HTML_SPECIALCHARS';
    useQuoteStyle = !isNaN(quote_style) ? constMappingQuoteStyle[quote_style] : quote_style ? quote_style.toUpperCase() : 'ENT_COMPAT';

    if (useTable !== 'HTML_SPECIALCHARS' && useTable !== 'HTML_ENTITIES') {
        throw new Error("Table: " + useTable + ' not supported');
        // return false;
    }

    entities['38'] = '&amp;';
    if (useTable === 'HTML_ENTITIES') {
        entities['160'] = '&nbsp;';
        entities['161'] = '&iexcl;';
        entities['162'] = '&cent;';
        entities['163'] = '&pound;';
        entities['164'] = '&curren;';
        entities['165'] = '&yen;';
        entities['166'] = '&brvbar;';
        entities['167'] = '&sect;';
        entities['168'] = '&uml;';
        entities['169'] = '&copy;';
        entities['170'] = '&ordf;';
        entities['171'] = '&laquo;';
        entities['172'] = '&not;';
        entities['173'] = '&shy;';
        entities['174'] = '&reg;';
        entities['175'] = '&macr;';
        entities['176'] = '&deg;';
        entities['177'] = '&plusmn;';
        entities['178'] = '&sup2;';
        entities['179'] = '&sup3;';
        entities['180'] = '&acute;';
        entities['181'] = '&micro;';
        entities['182'] = '&para;';
        entities['183'] = '&middot;';
        entities['184'] = '&cedil;';
        entities['185'] = '&sup1;';
        entities['186'] = '&ordm;';
        entities['187'] = '&raquo;';
        entities['188'] = '&frac14;';
        entities['189'] = '&frac12;';
        entities['190'] = '&frac34;';
        entities['191'] = '&iquest;';
        entities['192'] = '&Agrave;';
        entities['193'] = '&Aacute;';
        entities['194'] = '&Acirc;';
        entities['195'] = '&Atilde;';
        entities['196'] = '&Auml;';
        entities['197'] = '&Aring;';
        entities['198'] = '&AElig;';
        entities['199'] = '&Ccedil;';
        entities['200'] = '&Egrave;';
        entities['201'] = '&Eacute;';
        entities['202'] = '&Ecirc;';
        entities['203'] = '&Euml;';
        entities['204'] = '&Igrave;';
        entities['205'] = '&Iacute;';
        entities['206'] = '&Icirc;';
        entities['207'] = '&Iuml;';
        entities['208'] = '&ETH;';
        entities['209'] = '&Ntilde;';
        entities['210'] = '&Ograve;';
        entities['211'] = '&Oacute;';
        entities['212'] = '&Ocirc;';
        entities['213'] = '&Otilde;';
        entities['214'] = '&Ouml;';
        entities['215'] = '&times;';
        entities['216'] = '&Oslash;';
        entities['217'] = '&Ugrave;';
        entities['218'] = '&Uacute;';
        entities['219'] = '&Ucirc;';
        entities['220'] = '&Uuml;';
        entities['221'] = '&Yacute;';
        entities['222'] = '&THORN;';
        entities['223'] = '&szlig;';
        entities['224'] = '&agrave;';
        entities['225'] = '&aacute;';
        entities['226'] = '&acirc;';
        entities['227'] = '&atilde;';
        entities['228'] = '&auml;';
        entities['229'] = '&aring;';
        entities['230'] = '&aelig;';
        entities['231'] = '&ccedil;';
        entities['232'] = '&egrave;';
        entities['233'] = '&eacute;';
        entities['234'] = '&ecirc;';
        entities['235'] = '&euml;';
        entities['236'] = '&igrave;';
        entities['237'] = '&iacute;';
        entities['238'] = '&icirc;';
        entities['239'] = '&iuml;';
        entities['240'] = '&eth;';
        entities['241'] = '&ntilde;';
        entities['242'] = '&ograve;';
        entities['243'] = '&oacute;';
        entities['244'] = '&ocirc;';
        entities['245'] = '&otilde;';
        entities['246'] = '&ouml;';
        entities['247'] = '&divide;';
        entities['248'] = '&oslash;';
        entities['249'] = '&ugrave;';
        entities['250'] = '&uacute;';
        entities['251'] = '&ucirc;';
        entities['252'] = '&uuml;';
        entities['253'] = '&yacute;';
        entities['254'] = '&thorn;';
        entities['255'] = '&yuml;';
    }

    if (useQuoteStyle !== 'ENT_NOQUOTES') {
        entities['34'] = '&quot;';
    }
    if (useQuoteStyle === 'ENT_QUOTES') {
        entities['39'] = '&#39;';
    }
    entities['60'] = '&lt;';
    entities['62'] = '&gt;';


    // ascii decimals to real symbols
    for (decimal in entities) {
        symbol = String.fromCharCode(decimal);
        hash_map[symbol] = entities[decimal];
    }

    return hash_map;
}

function htmlentities (string, quote_style) {
    // http://kevin.vanzonneveld.net
    // +   original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +    revised by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +   improved by: nobbler
    // +    tweaked by: Jack
    // +   bugfixed by: Onno Marsman
    // +    revised by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +    bugfixed by: Brett Zamir (http://brett-zamir.me)
    // +      input by: Ratheous
    // -    depends on: get_html_translation_table
    // *     example 1: htmlentities('Kevin & van Zonneveld');
    // *     returns 1: 'Kevin &amp; van Zonneveld'
    // *     example 2: htmlentities("foo'bar","ENT_QUOTES");
    // *     returns 2: 'foo&#039;bar'
    var hash_map = {},
        symbol = '',
        tmp_str = '',
        entity = '';
    tmp_str = string.toString();

    if (false === (hash_map = this.get_html_translation_table('HTML_ENTITIES', quote_style))) {
        return false;
    }
    hash_map["'"] = '&#039;';
    for (symbol in hash_map) {
        entity = hash_map[symbol];
        tmp_str = tmp_str.split(symbol).join(entity);
    }

    return tmp_str;
}


function submitForm()
{
    //document.forms["noteform"].submit();
	document.getElementById('save').click();
}

function submitFormAJAX()
{
	var xmlhttp;
	if (window.XMLHttpRequest)
	  {// code for IE7+, Firefox, Chrome, Opera, Safari
	  xmlhttp=new XMLHttpRequest();
	  }
	else
	  {// code for IE6, IE5
	  xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
	  }
	
	var notedata = htmlentities(document.getElementById('notes').value);
	var params = "ajax="+ notedata;
	xmlhttp.open("POST","<?php echo $FILENAME; ?>",true);
	xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	xmlhttp.setRequestHeader("Content-length", params.length);
	xmlhttp.setRequestHeader("Connection", "close");
	 xmlhttp.onreadystatechange=function()
	  {
	  if (xmlhttp.readyState==4 && xmlhttp.status==200)
	    {
	    //document.getElementById("myDiv").innerHTML=xmlhttp.responseText;
	    }
	  }

	xmlhttp.send(params);
}

</script>
</head>
<body>
<?php
if(isset($_SESSION['loggedIn']))
{
	//Display email form
		?><div style="clear: both; background-color: white; text-align: center; width: 80%; margin-left: auto; margin-right: auto;"> 
 
 
	<BIG><b><BIG>
	<?php if($sent){echo "$sent<br>"; $sent=false;}?>
Write Your Note:</BIG></big></b><br> 
    <form method="post" name="noteform" action ="<?php echo $FILENAME; ?>"> 
    <table style="margin:auto;"> 
    <tr> 
    <td><textarea name="notes" id="notes" onKeyUp="submitFormAJAX()" cols="70" rows="20" ><?php echo getNoteFromDatabase();?></textarea></td>
    </tr>
<?php /******************** FORM CHECKBOXES*******************************/?>
    <tr>
    <td><input type="submit" value="Save" name="save" id="save" />&nbsp;
    	<input type= "submit" value="Submit" name="submit" id="submit"/>
    	Dev_log:<input type="checkbox" name="dev_log" CHECKED >&nbsp;
	Bob:<input type="checkbox" name="bob" <?php f($_POST['bob']){echo "CHECKED ";}?>>&nbsp;
	
    </td>
    <td><input type="submit" value="Clear" name="clear" /></td>
    </tr>
 <?php /***************************** END FORM CHECKBOXES ************************/?>
    </form> 

	<form method="post" name="logout" action="<?php echo $FILENAME; ?>" >
	<tr>
	<td><input type="submit" name="logout" value="Logout"></td>
	</tr>
	</form>	
    </table> </div>
<div style="clear:both; background-color: white; text-align: center; width: 80%; margin: auto; ">
 <BIG><b><BIG>
Previous Sent Emails:</BIG></big></b><br>

<?php 
//Get latest Email sent and display
$query = "SELECT * FROM {$TABLE_NAMES['email']} WHERE user='{$_SESSION['loggedInUser']}' ORDER BY id DESC LIMIT 1";
$result = mysql_query($query); 
$row = mysql_fetch_array($result, MYSQL_ASSOC);
echo "<textarea overflow=auto cols=50 rows=5 readonly>".$row['body']."</textarea>";
?>

<table style="margin:auto;">
<?php 
//GET previous emails 
$query = "SELECT * FROM {$TABLE_NAMES['email']}  WHERE user='{$_SESSION['loggedInUser']}' ORDER BY timestamp DESC";
$result = mysql_query($query);
?>
<tr style="font-weight: bold;text-decoration: underline;">
<td>Title</td>
<td>&nbsp;</td>
<td>To</td>
<td>&nbsp;</td>
<td>Display Content</td>
</tr>
<?php
while($row = mysql_fetch_array($result,MYSQL_ASSOC)) {
?>
<tr>
<td><?php echo $row['title']; ?></td>
<td>&nbsp;</td>
<td><?php echo $row['to']; ?></td>
<td>&nbsp;</td>
<td><a href="<?php echo $FILENAME; ?>?displayid=<?php echo htmlentities($row['id']);?>">Content</a></td>
</tr>
<?php } ?>

</table>
</div>

<?php 
}else
{
	//Display Login form
	?>
			<div style="clear: both; background-color: white; text-align: center; width: 80%; margin-left: auto; margin: auto;"> 
 
 
	<BIG><b><BIG>LOGIN:</BIG></big></b><br> 
    <form method="post" name="loginform" action ="<?php echo $FILENAME; ?>"> 
    <table> 
    <tr> 
    <td>Username: </td> 
    <td><input type="text" id= "ur123" name ="ur123" /></td> 
    </tr> 
    
    <tr> 
    <td>Password: </td> 
    <td><input type="password" name="ps123"></td> 
    </tr> 
    
    <tr> 
    <td></td> 
    <td><input type= "submit" value="Submit" name="submit" /></td> 
    </tr> 
    </form> 
    </table> </div> 
	
<?php

}

?>

<table>
<tr style="font-weight: bold;text-decoration: underline;"><td>Features Implemented:</td></tr>
<tr><td>AutoSaves Content after changes, using AJAX on every KeyPress</td></tr>
<tr><td>Database Logins, Content stored on database</td></tr>
<tr><td>Email Tracking</td></tr>
<tr><td>Better Email Display</td></tr>
<tr><td>One notepad per user</td></tr>
<tr><td></td></tr>
</table>
</body>
</html>
