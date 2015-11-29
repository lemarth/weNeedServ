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

function identify($id)
{
    $conn = connect();
    $query_select = $conn->prepare("SELECT id FROM users WHERE id_google = ?");
    $query_select->execute(array($id));
    $conn = null;
    if ($query_select->rowCount() > 0)
        return true;
    return false;
}

function insert_user($user)
{
    $conn = connect();

    $query_select = $conn->prepare("SELECT id FROM users WHERE id_google = ?");
    $query_select->execute(array($user[0]));
    $id = $query_select->fetchAll()[0][0];
    if ($query_select->rowCount() == 0) {
        $query_insert = $conn->prepare("INSERT INTO users VALUES (DEFAULT , ?, ?, ?)");
        $query_insert->execute(array($user[0], $user[1], $user[2]));
        $id = $conn->lastInsertId();
    }
    $conn = null;
    return $id;
}

function insert_article($article)
{
    $conn = connect();

    $etat = "en attente";
    $query_insert = $conn->prepare("INSERT INTO articles VALUES (DEFAULT, ?, ?, ?, ?) ");
    $query_insert->execute(array($article[0], $article[2], $etat, $article[1]));
    $id = $conn->lastInsertId();

    $conn = null;
    return $id;
}

function insert_foyer($nom, $id)
{
    $conn = connect();


    $query_insert = $conn->prepare("INSERT INTO foyers VALUES (DEFAULT, ?)");
    $query_insert->execute(array($nom));
    $id_foyer = $conn->lastInsertId();

    $query = $conn->prepare("INSERT INTO users_foyers VALUES (DEFAULT, ?, ?, 'accepted')");
    $query->execute(array($id, $id_foyer));
    $id_users_foyers = $conn->lastInsertId();

    $conn = null;
    return $id_users_foyers;
}

function update_etat_article($id_article, $etat)
{
    $conn = connect();

    $query_update = $conn->prepare("UPDATE articles SET etat = ? WHERE id = ?");
    $query_update->execute(array($etat, $id_article));
    $query = $conn->prepare("SELECT * FROM articles WHERE id = ? AND etat <> ?");
    $query->execute(array($id_article, $etat));
    if ($query->rowCount() == 0) {
        $res = true;
    } else {
        $res = false;
    }
    $conn = null;
    return $res;
}

function inviter($adresse_invite, $id_foyer)
{
    $invite = select_user_by_mail($adresse_invite);
    if ($invite == null)
        return false;
    $conn = connect();

    $query = $conn->prepare("INSERT INTO users_foyers VALUES (DEFAULT , ?, ?, 'pending')");
    $query->execute(array($invite['id'], $id_foyer));

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

function select_invitations($id)
{
    $conn = connect();

    $query = $conn->prepare("SELECT uf.id, f.nom FROM users_foyers uf, foyers f WHERE uf.id_user = ?
                                                        AND uf.etat = 'pending' AND uf.id_foyer = f.id");
    $query->execute(array($id));
    $res = array();
    if ($query->rowCount() > 0) {
        $rows = $query->fetchAll();
        $i = 0;
        foreach ($rows as $row) {
            $res[$i] = array('id' => $row['id'], 'name' => $row['nom']);
            $i++;
        }
    }
    $conn = null;
    return $res;
}

function update_etat_invitation($id_invitation, $answer)
{

    $conn = connect();

    $query = $conn->prepare("UPDATE users_foyers SET etat = ? WHERE id = ?");
    $select_invitation = $conn->prepare("SELECT * FROM users_foyers WHERE id = ? AND etat = 'pending'");
    $query->execute(array($answer, $id_invitation));
    $select_invitation->execute(array($id_invitation));
    if ($select_invitation->rowCount() > 0) {
        $res = false;
    } else {
        $res = true;
    }
    $conn = null;
    return $res;
}

function select_foyers($id)
{
    $conn = connect();

    $select_id_nom = $conn->prepare("SELECT DISTINCT f.id, f.nom FROM users_foyers uf, foyers f WHERE uf.id_user = ?
                                      AND uf.etat ='accepted' AND f.id = uf.id_foyer");
    $select_id_nom->execute(array($id));
    $res = null;
    if ($select_id_nom->rowCount() > 0) {
        $res = array();
        $rows = $select_id_nom->fetchAll();
        $select_nom_users = $conn->prepare("SELECT u.nom FROM users u, users_foyers uf WHERE u.id = uf.id_user AND
                                              uf.id_foyer = ?");
        $select_articles = $conn->prepare("SELECT * FROM articles WHERE id_foyer = ?");
        $i = 0;
        foreach ($rows as $row) {
            $res[$i] = array('id' => $row[0], 'name' => $row[1], 'users' => array(), 'articles' => array());
            $select_nom_users->execute(array($row[0]));
            $rows2 = $select_nom_users->fetchAll();
            $j = 0;
            foreach ($rows2 as $row2) {
                $res[$i]['users'][$j] = $row2[0];
                $j++;
            }
            $select_articles->execute(array($row[0]));
            $rows2 = $select_articles->fetchAll();
            $j = 0;
            foreach ($rows2 as $row2) {
                $res[$i]['articles'][$j] = array('id' => $row2['id'], 'quantite' => $row2['quantite'], 'etat' => $row2['etat'],
                    'name' => $row2['name'], 'id_foyer' => $row2['id_foyer']);
                $j++;
            }
            $i++;
        }
    }
    $conn = null;
    return $res; //[[id,nom,[nom1,nom2]], id2, nom2,[nom3,nom4]]
}

function get_location_of_user($id, $lat, $long)
{
    $conn = connect();

    $query_select = $conn->prepare("SELECT lat, lon, date_pos FROM users WHERE id = ?");
    $query_select->execute(array($id));
    $row = $query_select->fetchAll()[0];
    $query_update = $conn->prepare("UPDATE users SET lat = ?, lon = ?, date_pos = now() WHERE id = ?");
    $query_update->execute(array($lat, $long, $id));
    $res = array("latitude" => $row[0], "longitude" => $row[1], "date" => $row[2]);
    $conn = null;
    return $res;
}