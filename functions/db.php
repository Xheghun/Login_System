<?php
/**
 * Created by PhpStorm.
 * User: xheghun
 * Date: 21/11/2018
 * Time: 08:57 PM
 */
$con = mysqli_connect("localhost","root","","login_standard");

function fetch_array($result) {
    global $con;
   return mysqli_fetch_array($result);
}

function escape($string) {
    global $con;
    return mysqli_real_escape_string($con,$string);
}

function query($sql) {
    global $con;
    return mysqli_query($con,$sql);
}

function confirm($result) {
    global $con;
    if (!$result) {
        die("Query Failed ".mysqli_error($con));
    }
}

function row_count($result) {
    return mysqli_num_rows($result);

}