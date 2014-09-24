<?php
  $DisplayAllEnvironmentVariables = false;
  $DisplayAllGetPostData          = false;

  $Regexp = "/^[a-zA-Z0-9 ._-]+$/";

  $MyIP = $_SERVER['REMOTE_ADDR'];

  unset($Args);
  getAllArgs();

  if($Args['logout'] == 1)
  {
    $out = setcookie("authenticated", "1", time()-86400*365);
    $authenticated = 0;
    unset($_COOKIE['authenticated']);
  }
  $authenticated = 0;
  if(isset($_COOKIE['authenticated']))
  {
    $authenticated = 1;
  }
  if($Args['authenticating'] == 1)
  {
    $safeArg = escapeshellcmd($Args['username']);
    # Note:  the application "form" source code is on cm815.
    $auth = exec("./form $safeArg");
    if($auth)
    {
      if(setcookie("authenticated", "1", time()+86400*365))
      {
        $authenticated = 1;
      }
    }
  }
?>

<html xml:lang="en">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" />
  <meta name="viewport" content="width=400, initial-scale=1">
  <title>
    <?php
      if($Args['list'] != "")
      {
        print $Args['list'];
      }
      else
      {
        print "Lists";
      }
    ?>
  </title>
</head>
<body bgcolor="#111111" text="#ffffff" vlink="#AAAAff" link="#AAAAff" onLoad="focus();add.item.focus()"> 

<?php
  if($DisplayAllGetPostData)
  {
    displayAllGetPostData();
  }
  if($DisplayAllEnvironmentVariables)
  {
    displayAllEnvironmentVariables();
  }
  if(!$authenticated)
  {
    ?>
    <form style="{ MARGIN-LEFT: 0pt; MARGIN-RIGHT: 0pt; MARGIN-TOP: 0pt; MARGIN-BOTTOM: 0pt; }" method="POST" action="index.php">
              <input type=hidden name=ln value=14>
              <input type=hidden name=authenticating value=1>
              <input type=password name=username value="">
              <input type="submit" value="Login">
    </form>
    <?php
  }
  else
  {
    if($Args['createnewlist'] == 1) # Create a new list
    {
      ?>
        <form style="{ MARGIN-LEFT: 0pt; MARGIN-RIGHT: 0pt; MARGIN-TOP: 0pt; MARGIN-BOTTOM: 0pt; }" method="POST" action="index.php">
          <input type=hidden name=ln value=15>
          <input type=text name=newlistname value="">
          <input type="submit" value="Create">
        </form>
      <?php

      print "<form style=\"{ MARGIN-LEFT: 0pt; MARGIN-RIGHT: 0pt; MARGIN-TOP: 0pt; MARGIN-BOTTOM: 0pt; }\" method=POST action=index.php>\n";
      print "<a href=\"index.php?ln=1-" . getmypid() . "\">Back</a>\n";
      print "</form>\n";
    }
    else if($Args['list'] != "")
    {
      // show choices at the top for long lists
      if($Args['editask'] == "" && $Args['list'] === "Shopping Prices")
      {
        print "<a href=\"index.php?ln=3-" . getmypid() . "\">Home</a>\n";
        print "| <a href=\"index.php?ln=4-" . getmypid() . "&list=" . $Args['list'] . "\">Refresh</a>";
        $dir = opendir(getcwd());
        while(false !== ($fname = readdir($dir)))
        {
          if($fname != "index.php" && $fname != "form" && $fname != "." && $fname != "..")
          {
            $lists[] = $fname;
          }
        }
        closedir($dir);
        sort($lists);

        print "  <select onchange=\"if (this.selectedIndex > 0) document.location.href='index.php?ln=5&list=' + this.value;\">\n";
        print "<option></option>\n";
        foreach ($lists as $list)
        {
          if($list == $Args['list'])
          {
            print "<option value=\"$list\" selected>$list</option>\n";
          }
          else
          {
            print "<option value=\"$list\">$list</option>\n";
          }
        }
        print "  </select><p>\n";
      }

      procList();

      if($Args['editask'] != "")
      {
        print "<form style=\"{ MARGIN-LEFT: 0pt; MARGIN-RIGHT: 0pt; MARGIN-TOP: 0pt; MARGIN-BOTTOM: 0pt; }\" method=POST action=index.php>\n";
        print "<a href=\"index.php?ln=2-" . getmypid() . "&list=" .  $Args['list'] . "\">Back</a>";
        print "</form>\n";
      }
      else
      {
        # links
        print "<form style=\"{ MARGIN-LEFT: 0pt; MARGIN-RIGHT: 0pt; MARGIN-TOP: 0pt; MARGIN-BOTTOM: 0pt; }\" method=POST action=index.php>\n";
        print "<a href=\"index.php?ln=3-" . getmypid() . "\">Home</a>\n";
        print "| <a href=\"index.php?ln=4-" . getmypid() . "&list=" . $Args['list'] . "\">Refresh</a>";
        $dir = opendir(getcwd());
        if(empty($lists)) { // may have been done above
          while(false !== ($fname = readdir($dir)))
          {
            if($fname != "index.php" && $fname != "form" && $fname != "." && $fname != "..")
            {
              $lists[] = $fname;
            }
          }
          closedir($dir);
          sort($lists);
        }

        print "  <select onchange=\"if (this.selectedIndex > 0) document.location.href='index.php?ln=5&list=' + this.value;\">\n";
        print "<option></option>\n";
        foreach ($lists as $list)
        {
          if($list == $Args['list'])
          {
            print "<option value=\"$list\" selected>$list</option>\n";
          }
          else
          {
            print "<option value=\"$list\">$list</option>\n";
          }
        }
        print "  </select>\n";

        print "</form>\n";
      }
    }
    else
    {
      if($Args['rmlist'] != "")
      {
        rmlist($Args['rmlist']);
      }
      if($Args['newlistname'] != "")
      {
        $newListName = $Args['newlistname'];
        $newListFspec = getcwd() . "/" . $newListName;
        if(!preg_match($Regexp, $newListName))
        {
          print "ERROR:  list can only contain:  \"$Regexp\".<p>\n";
        }
        else if(file_exists($newListFspec))
        {
          print "ERROR:  list $newListName already exists.<p>\n";
        }
        else
        {
          $ofp=fopen($newListFspec, 'w') or die("failed to open for writing<br>\n");
          fclose($ofp);

          if(!(file_exists($newListFspec)))
          {
            print "ERROR:  failed to create...<p>\n";
          }
        }
      }
      displayLists();

      print "&nbsp;<br>\n";

      # links
      print "<form style=\"{ MARGIN-LEFT: 0pt; MARGIN-RIGHT: 0pt; MARGIN-TOP: 0pt; MARGIN-BOTTOM: 0pt; }\" method=POST action=index.php>\n";
      print "<a href=\"index.php?ln=6&createnewlist=1\">Create New List</a>\n";
      print "| <a href=\"index.php?ln=7&logout=1\">Logout</a>\n";
      print "</form>\n";
    }
  }
?>

</body>
</html>

<?php

function disableMagicQuotes()
{
  global $_GET;
  global $_POST;
  global $_COOKIE;
  global $_REQUEST;
  global $Separator;

  // Disable magic-quotes (auto-translation of single-quote, double-quote,
  // backslash and NULL are automatically preceeded by a backslash).  This is
  // obsolete past 5.3.0 but is still set on our server.  External (does not
  // appear to work):  "/adp/lib/*php.ini*" and change:  magic_quotes_gpc = Off
  if(get_magic_quotes_gpc())
  {
    $Process = array(&$_GET, &$_POST, &$_COOKIE, &$_REQUEST);
    while (list($Key, $Val) = each($Process))
    {
      foreach ($Val as $K => $V)
      {
        unset($Process[$Key][$K]);
        if (is_array($V))
        {
          $Process[$Key][stripslashes($K)] = $V;
          $Process[] = &$Process[$Key][stripslashes($K)];
        }
        else
        {
          $Process[$Key][stripslashes($K)] = stripslashes($V);
        }
      }
    }
    unset($Process);
  }
  return;
}

function displayAllGetPostData()
{
  global $Args;

  print "args:<br>\n";
  print "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<table>\n";
  foreach ($Args as $key => $value)
  {
    print "  <tr>\n";
    print "    <td>'$key'</td>\n";
    print "    <td>'$value'</td>\n";
    print "  </tr>\n";
  }
  print "</table>\n";
}

function displayAllEnvironmentVariables()
{
  // only via http
  echo "HTTP vars:<br>\n";
  foreach ($HTTP_ENV_VARS as $Key => $Value)
  {
    echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'$Key' = '$Value'<br>\n";
  }
  echo "_SERVER vars:<br>\n";
  foreach ($_SERVER as $Key => $Value)
  {
    echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'$Key' = '$Value'<br>\n";
  }
}

function displayLists()
{
  $dir = opendir(getcwd());
  while(false !== ($fname = readdir($dir)))
  {
    if($fname != "index.php" && $fname != "form" && $fname != "." && $fname != "..")
    {
      $lists[] = $fname;
    }
  }
  closedir($dir);

  sort($lists);
  $i = 1;
  # The form is to keep not-so-smart phones from "condensing" the webpage
  //print "<form style=\"{ MARGIN-LEFT: 0pt; MARGIN-RIGHT: 0pt; MARGIN-TOP: 0pt; MARGIN-BOTTOM: 0pt; }\" method=POST action=index.php>\n";
  //print "  <input type=hidden name=ln value=19>\n";
  //print "  <input type=submit value=x><br>\n";
  //print "  <input type=hidden name=rml value=1>\n";
  //print "  <input type=hidden name=number value=" . count($lists) . ">\n";
  for($cntr=0; $cntr<count($lists); $cntr++)
  {
    print "<font face=courier>\n";
    //print "<input type=checkbox name=rml$cntr value=\"" . htmlentities($lists[$cntr]) . "\">\n";

    print "<a href=\"index.php?ln=8&rmlist=$lists[$cntr]\" style='text-decoration: none;'>x</a>\n";

    $format = "%0" . strlen(count($lists)) . "d\n";
    printf($format, $cntr+1);

    print "</font>\n";
    print " <a href=\"index.php?ln=9&list=$lists[$cntr]\" style='text-decoration: none;'>$lists[$cntr]</a>\n";
    $i++;

    print "<br>\n";
  }
  //print "  <input type=submit value=x><br>\n";
  //print "</form>\n";
}

function procList()
{
  global $Args;
  global $Regexp;

  $list = $Args['list'];

  $ifp = fopen(getcwd() . "/$list", 'r') or die("failed to open $list for reading<br>\n");
  while(!feof($ifp))
  {
    $fileLine = rtrim(fgets($ifp));
    if($fileLine == "")
    {
      break;
    }
    $listItems[] = $fileLine;
  }
  fclose($ifp);

  if($Args['add'] != "")
  {
    $newListItem = $Args['item'];
    $pos = $Args['pos'];
    $error = 0;
    if($newListItem == "")
    {
      $error = 1;
      print "ERROR:  Cannot add blank item<p>\n";
    }
    foreach ($listItems as $listItem)
    {
      if($newListItem == $listItem)
      {
        $error = 1;
        print "ERROR:  Cannot add duplicate entry<p>\n";
      }
    }
    if($error == 0)
    {
      if(count($listItems) == 0)
      {
        $listItems[] = $newListItem;
      }
      else if($pos == "first")
      {
        array_unshift($listItems, $newListItem);
      }
      else if($pos == "last")
      {
        array_push($listItems, $newListItem);
      }
      else
      {
        $pos--;
        for($cntr=0; $cntr<$pos; $cntr++)
        {
          $listItemsNew[] = $listItems[$cntr];
        }
        $listItemsNew[] = $newListItem;
        for($cntr=$pos; $cntr<count($listItems); $cntr++)
        {
          $listItemsNew[] = $listItems[$cntr];
        }
        $listItems = $listItemsNew;
      }
      if($list === "Shopping Prices")
      {
        natcasesort($listItems);
      }
      $ofp = fopen(getcwd() . "/$list", 'w') or die("failed to open $list for writing<br>\n");
      foreach ($listItems as $listItem)
      {
        fwrite($ofp, $listItem . "\n") or die("Failed to write to $list<br>\n");
      }
      fclose($ofp);
    }
  }
  if($Args['rm'] != "")
  {
    $rmListItem = rawurldecode($Args['rm']);

    for($cntr=0; $cntr<count($listItems); $cntr++)
    {
      if($listItems[$cntr] == $rmListItem)
      {
        unset($listItems[$cntr]);
        break;
      }
    }

    $ofp = fopen(getcwd() . "/$list", 'w') or die("failed to open $list for writing<br>\n");
    foreach ($listItems as $listItem)
    {
      fwrite($ofp, $listItem . "\n") or die("Failed to write to $list<br>\n");
    }
    fclose($ofp);
  }
  if($Args['rmm'] != "")
  {
    $number = $Args['number'];

    # cycle all "delete" items
    for($ncntr=0; $ncntr<$number; $ncntr++)
    {
      if(isset($Args["rmm" . $ncntr]))
      {
        $rmListItem = rawurldecode($Args["rmm" . $ncntr]);

        for($cntr=0; $cntr<count($listItems); $cntr++)
        {
          if($listItems[$cntr] == $rmListItem)
          {
            unset($listItems[$cntr]);
            break;
          }
        }

        $ofp = fopen(getcwd() . "/$list", 'w') or die("failed to open $list for writing<br>\n");
        foreach ($listItems as $listItem)
        {
          fwrite($ofp, $listItem . "\n") or die("Failed to write to $list<br>\n");
        }
        fclose($ofp);

        # Since unset messes up the indexes we need to re-load the array for the next delete operation
        unset($listItems);
        $ifp = fopen(getcwd() . "/$list", 'r') or die("failed to open $list for reading<br>\n");
        while(!feof($ifp))
        {
          $fileLine = rtrim(fgets($ifp));
          if($fileLine == "")
          {
            break;
          }
          $listItems[] = $fileLine;
        }
        fclose($ifp);
      }
    }
  }
  if($Args['up'] != "")
  {
    $listItemUp = rawurldecode($Args['up']);

    # Detect "section of interest"
    $pos = -1;
    for($cntr=0; $cntr<count($listItems); $cntr++)
    {
      if($listItems[$cntr] == $listItemUp)
      {
        $pos = $cntr;
        break;
      }
    }
    if($pos == -1)
    {
      die("ERROR:  item '$listItemUp' was removed during this operation<br>\n");
    }
    # copy everything before the "section of interest"
    for($cntr=0; $cntr<$pos-1; $cntr++)
    {
      $listItemsNew[] = $listItems[$cntr];
    }
    $listItemsNew[] = $listItems[$pos]; # copy the item in question
    $listItemsNew[] = $listItems[$pos-1]; # copy the item before the item in question
    # copy everything after the "section of interest
    for($cntr=$pos+1; $cntr<count($listItems); $cntr++)
    {
      $listItemsNew[] = $listItems[$cntr];
    }

    $ofp = fopen(getcwd() . "/$list", 'w') or die("failed to open $list for writing<br>\n");
    foreach ($listItemsNew as $listItem)
    {
      fwrite($ofp, $listItem . "\n") or die("Failed to write to $list<br>\n");
    }
    fclose($ofp);
  }
  if($Args['dn'] != "")
  {
    $listItemDn = rawurldecode($Args['dn']);

    # Detect "section of interest"
    $pos = -1;
    for($cntr=0; $cntr<count($listItems); $cntr++)
    {
      if($listItems[$cntr] == $listItemDn)
      {
        $pos = $cntr;
        break;
      }
    }
    if($pos == -1)
    {
      die("ERROR:  item '$listItemDn' was removed during this operation<br>\n");
    }
    # copy everything before the "section of interest"
    for($cntr=0; $cntr<$pos; $cntr++)
    {
      $listItemsNew[] = $listItems[$cntr];
    }
    $listItemsNew[] = $listItems[$pos+1]; # copy the item in question
    $listItemsNew[] = $listItems[$pos]; # copy the item before the item in question
    # copy everything after the "section of interest
    for($cntr=$pos+2; $cntr<count($listItems); $cntr++)
    {
      $listItemsNew[] = $listItems[$cntr];
    }

    $ofp = fopen(getcwd() . "/$list", 'w') or die("failed to open $list for writing<br>\n");
    foreach ($listItemsNew as $listItem)
    {
      fwrite($ofp, $listItem . "\n") or die("Failed to write to $list<br>\n");
    }
    fclose($ofp);
  }
  if($Args['edit'] != "")
  {
    $editfrom = $Args['editfrom'];
    $editto = $Args['editto'];
    # make sure there are no duplicates
    foreach ($listItems as $listItem)
    {
      if($listItem == $editto)
      {
        print "ERROR:  Cannot add duplicate entry<p>\n";
        return;
      }
    }
    for($cntr=0; $cntr<count($listItems); $cntr++)
    {
      if($listItems[$cntr] == $editfrom)
      {
        $listItems[$cntr] = $editto;
        break;
      }
    }
    if($list === "Shopping Prices")
    {
      natcasesort($listItems);
    }
    $ofp = fopen(getcwd() . "/$list", 'w') or die("failed to open $list for writing<br>\n");
    foreach ($listItems as $listItem)
    {
      fwrite($ofp, $listItem . "\n") or die("Failed to write to $list<br>\n");
    }
    fclose($ofp);
  }

  if($Args['editask'] != "")
  {
    $editfrom = rawurldecode($Args['editfrom']);
    print "<form style=\"{ MARGIN-LEFT: 0pt; MARGIN-RIGHT: 0pt; MARGIN-TOP: 0pt; MARGIN-BOTTOM: 0pt; }\" method=POST action=index.php>\n";
    print "  <input type=hidden name=ln value=16>\n";
    print "  <input type=hidden name=list value=\"$list\">\n";
    print "  <input type=hidden name=editfrom value=\"" . htmlentities($editfrom) . "\">\n";
    print "  <input type=hidden name=edit value=1>\n";
    print "  <input type=text size=45 name=editto value=\"" . htmlentities($editfrom) . "\">\n";
    print "  <input type=submit name=\"Submit\">\n";
    print "</form>\n";
    return;
  }

  unset($listItems);
  $ifp = fopen(getcwd() . "/$list", 'r') or die("failed to open $list for reading<br>\n");
  while(!feof($ifp))
  {
    $fileLine = rtrim(fgets($ifp));
    if($fileLine == "")
    {
      break;
    }
    $listItems[] = $fileLine;
  }
  fclose($ifp);

  $hidden = "";
  if($list === "Shopping Prices")
  {
    $hidden = " hidden";
  }

  if(count($listItems) > 10)
  {
  ?>
    <form style="{ MARGIN-LEFT: 0pt; MARGIN-RIGHT: 0pt; MARGIN-TOP: 0pt; MARGIN-BOTTOM: 0pt; }" name="add" method="POST" action="index.php">
      <input type=hidden name=ln value=18>
      <input type=hidden name=list value="<?php echo $list?>">
      <input type=hidden name=add value=1>
      <input type=text size=45 name=item value="">
      <select<?php echo $hidden; ?>  name=pos>
        <option>first</option>
        <?php
          for($cntr=1,$cntr2=2; $cntr<count($listItems); $cntr++,$cntr2++)
          {
            print "<option>$cntr2</option>\n";
          }
        ?>
        <option selected>last</option>
      </select>
      <input type="submit" value="Add">
    </form>
  <?php
  }

  print "<form style=\"{ MARGIN-LEFT: 0pt; MARGIN-RIGHT: 0pt; MARGIN-TOP: 0pt; MARGIN-BOTTOM: 0pt; }\" method=POST action=index.php>\n";
  print "  <input type=hidden name=ln value=17>\n";
  print "  <input type=submit value=x><br>\n";
  print "  <input type=hidden name=list value=\"$list\">\n";
  print "  <input type=hidden name=rmm value=1>\n";
  print "  <input type=hidden name=number value=" . count($listItems) . ">\n";
  $showTotal = 0;
  $totalMoney = 0.0;
  $priorListItem = "";
  for($cntr=0; $cntr<count($listItems); $cntr++)
  {
    $listItem = $listItems[$cntr];
    if(substr($listItem, 0, 6) == "----- ")
    {
      $totalMoney = 0.0;
    }
    print "<font face=courier>\n";
    print "<input type=checkbox name=rmm$cntr value=\"" . htmlentities($listItem) . "\">\n";

    print "<a href=\"index.php?ln=10&list=$list&rm=" . rawurlencode($listItem) . "\" style='text-decoration: none;'>x</a>\n";

    //$format = sprintf("%%0%dd\n", strlen(count($listItems)));
    $format = "%0" . strlen(count($listItems)) . "d\n";
    printf($format, $cntr+1);

    if($list !== "Shopping Prices")
    {
      if($cntr != 0)
      {
        print "<a href=\"index.php?ln=11-" . getmypid() . "&list=$list&up=" . rawurlencode($listItem) . "\" style='text-decoration: none;'>^</a>\n";
      }
      else
      {
        print "-\n";
      }
      if($cntr != count($listItems) - 1)
      {
        print "<a href=\"index.php?ln=12-" . getmypid() . "&list=$list&dn=" . rawurlencode($listItem) . "\" style='text-decoration: none;'>v</a>\n";
      }
      else
      {
        print "-\n";
      }
    }
    print "</font>\n";
    print "<a href=\"index.php?ln=13&list=$list&editask=1&editfrom=" . rawurlencode($listItem) . "\" style='text-decoration: none;'>$listItem</a>\n";

    if(strpos($list, "Mileage")) {
      $wordsPrior = preg_split("/\s+/", $priorListItem);
      if(is_numeric($wordsPrior[0])) {
        $wordsCur = preg_split("/\s+/", $listItem);
        $miles = $wordsCur[0] - $wordsPrior[0];
        print "($miles" . " mi";
        $mpg = $miles / $wordsCur[3];
        printf(", %.2f mpg", $mpg);
        $cost = $wordsCur[2] * $wordsCur[3];
        $cpm = $cost / $miles;
        printf(", $%.2f/mi", $cpm);
        $days = (strtotime($wordsCur[1]) - strtotime($wordsPrior[1])) / 60 / 60 / 24;
        printf(", %.2f mi/day", $miles/$days);
        print ")\n";
      }
    }

    # get shopping data
    if($list == "Shopping List")
    {
      if(file_exists("Shopping Prices"))
      {
        # extract & remove quantity if it exists
        preg_match('#^[0-9]+ #', $listItem, $matches);
        $quantity = 1;
        if($matches[0] != "")
        {
          $quantity = rtrim($matches[0]);
          $listItem = substr($listItem, strlen($quantity)+1);
        }

        # retrieve prices
        $ifp=fopen(getcwd() . "/Shopping Prices", 'r') or die ("Failed to open 'Shopping Prices' for reading<p>\n");
        $fileContents = file("Shopping Prices");
        foreach ($fileContents as $fileLine)
        {
          $prefix = substr($fileLine, 0, strlen($listItem));

          $prefixLc = strtolower($prefix);
          $listItemLc = strtolower($listItem);
          if($prefixLc == $listItemLc)
          {
            $fileLine = substr($fileLine, strlen($listItem)+1);

            # find the price
            preg_match('#[0-9]*\.[0-9]{0,2}#', $fileLine, $matches);
            if($matches[0] != "")
            {
              $price = $matches[0];

              $vendor = rtrim(substr($fileLine, 0, strpos($fileLine, $price)));
              print "$vendor\n";

              $price *= $quantity;
              $listItem .= "\$$price";
            }
          }
        }
      }
    }

    # calculate/print the actual dollar ammount (if any)
    preg_match('#(-[\$])([0-9]*\.?[0-9]{0,2})#', $listItem, $matches); # payments
    if($matches[0] != "")
    {
      $totalWithoutDollarSign = substr($matches[0], 2);
      if($totalWithoutDollarSign != "")
      {
        $totalMoney -= $totalWithoutDollarSign;
        print "&nbsp;x<font color=#FF8040>($matches[0])</font> <font color=#777788>$$totalMoney</font>\n";
        $showTotal = 1;
      }
    }
    else
    {
      preg_match('#([\$])([0-9]*\.?[0-9]{0,2})#', $listItem, $matches); # expenses
      if($matches[0] != "")
      {
        $totalWithoutDollarSign = substr($matches[0], 1);
        if($totalWithoutDollarSign != "")
        {
          $totalMoney += $totalWithoutDollarSign;
          print "&nbsp;<font color=#FF8040>($matches[0])</font> <font color=#777799>$$totalMoney</font>\n";
          $showTotal = 1;
        }
      }
    }

    print "<br>\n";
    $priorListItem = $listItem;
  }
  if($showTotal)
  {
    if($totalMoney < 0)
    {
      $totalMoney *= -1;
      print "<font color=#FF8040>(total = -$$totalMoney)</font><br>\n";
    }
    else
    {
      print "<font color=#FF8040>(total = $$totalMoney)</font><br>\n";
    }
  }
  print "  <input type=submit value=x><br>\n";
  print "</form>\n";

  ?>
    <form style="{ MARGIN-LEFT: 0pt; MARGIN-RIGHT: 0pt; MARGIN-TOP: 0pt; MARGIN-BOTTOM: 0pt; }" name="add" method="POST" action="index.php">
      <input type=hidden name=ln value=18>
      <input type=hidden name=list value="<?php echo $list?>">
      <input type=hidden name=add value=1>
      <input type=text size=45 name=item value="">
      <select<?php echo $hidden; ?>  name=pos>
        <option>first</option>
        <?php
          for($cntr=1,$cntr2=2; $cntr<count($listItems); $cntr++,$cntr2++)
          {
            print "<option>$cntr2</option>\n";
          }
        ?>
        <option selected>last</option>
      </select>
      <input type="submit" value="Add">
    </form>
  <?php
}

# Note that stripslashes are needed here since apostrophes make it through the magic quotes disabling code somehow.
function getAllArgs()
{
  global $Args;
  global $_GET;
  global $_POST;

  // get args into one associative array
  foreach ($_GET as $Key => $Value)
  {
    $Args[$Key] = stripslashes($Value);
  }
  foreach ($_POST as $Key => $Value)
  {
    $Args[$Key] = stripslashes($Value);
  }
  ksort($Args);
}

# Delete an empty list
function rmlist($list)
{
  $ifp=fopen(getcwd() . "/$list", 'r') or die ("Failed to open $list for reading<p>\n");
  $numFound=0;
  while(!feof($ifp))
  {
    $fileLine = rtrim(fgets($ifp));
    if($fileLine != "")
    {
      $numFound++;
    }
  }
  fclose($ifp);
  if($numFound != 0)
  {
    print "List '$list' not empty.  Delete list only works on empty lists.<p>\n";
  }
  else
  {
    unlink($list);
  }
  fclose($ifp);
}

?>
