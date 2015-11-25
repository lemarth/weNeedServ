<?php
/**
 * Created by PhpStorm.
 * User: Romain
 * Date: 24/11/2015
 * Time: 02:01
 */

function connect()
{
    return new PDO('mysql:host=mysql-weneed.alwaysdata.net;dbname=weneed_db', 'weneed_usr', 'grosmotdepassesecurise', array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));

}

function insert_user($user)
{
    $conn = connect();

    $query_select = $conn->prepare("SELECT id FROM users WHERE id_google = ?");
    $query_select->execute(array($user[0]));
    if ($query_select->rowCount() == 0) {
        $query_insert = $conn->prepare("INSERT into users VALUES (DEFAULT , ?, ?, ?)");
        $query_insert->execute(array($user[0], $user[1], $user[2]));
    }
    $conn = null;
}

function inviter($adresse_invite, $id_foyer)
{
    $invite = select_user_by_mail($adresse_invite);
    if ($invite == null)
        return false;
    $conn = connect();

    $query = $conn->prepare("INSERT INTO users_foyers VALUES (DEFAULT , ?, ?, 'pending')");
    $query->execute(array($invite['id_google'], $id_foyer));

    $conn = null;
    return true;
}

function select_user_by_mail($mail)
{
    $conn = connect();

    $query = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $query->execute(array($mail));
    $res = null;
    if ($query->rowCount() > 0)
        $res = $query->fetchAll()[0];
    $conn = null;
    return $res;
}