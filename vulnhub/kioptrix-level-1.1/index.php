<?php
        mysql_connect("localhost", "john", "hiroshima") or die(mysql_error());
        //print "Connected to MySQL<br />";
        mysql_select_db("webapp");

        if ($_POST['uname'] != ""){
                $username = $_POST['uname'];
                $password = $_POST['psw'];
                $query = "SELECT * FROM users WHERE username = '$username' AND password='$password'";
                //print $query."<br>";
                $result = mysql_query($query);

                $row = mysql_fetch_array($result);
                //print "ID: ".$row['id']."<br />";
        }

?>
<html>
<body>
<?php
if ($row['id']==""){
?>
<form method="post" name="frmLogin" id="frmLogin" action="index.php">
        <table width="300" border="1" align="center" cellpadding="2" cellspacing="2">
                <tr>
                        <td colspan='2' align='center'>
                        <b>Remote System Administration Login</b>
                        </td>
                </tr>
                <tr>
                        <td width="150">Username</td>
                        <td><input name="uname" type="text"></td>
                </tr>
                <tr>
                        <td width="150">Password</td>
                        <td>
                        <input name="psw" type="password">
                        </td>
                </tr>
                <tr>
                        <td colspan="2" align="center">
                        <input type="submit" name="btnLogin" value="Login">
                        </td>
                </tr>
        </table>
</form>
<?php
        } //END of login form
?>

<!-- Start of HTML when logged in as Administator -->
<?php
        if ($row['id']==1){
?>
        <form name="ping" action="pingit.php" method="post" target="_blank">
                <table width='600' border='1'>
                <tr valign='middle'>
                        <td colspan='2' align='center'>
                        <b>Welcome to the Basic Administrative Web Console<br></b>
                        </td>
                </tr>
                <tr valign='middle'>
                        <td align='center'>
                                Ping a Machine on the Network:
                        </td>
                                <td align='center'>
                                <input type="text" name="ip" size="30">
                                <input type="submit" value="submit" name="submit">
                        </td>
                        </td>
                </tr>
        </table>
        </form>


<?php
}
?>
</body>
</html>