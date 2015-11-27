<?php
/**
 * Created by PhpStorm.
 * User: romaingilles
 * Date: 24/11/15
 * Time: 01:28
 */
require_once('fonctionsDB.php');

switch ($_POST['action']) {
    case 'login':
        if ($_POST['idToken'] == '-1') {
            echo json_encode(array('identified' => true, 'id' => '3',
                'name' => 'Fabian Germeau', 'email' => 'germeau.fabian@gmail.com', 'id_google' => -1));
        }
        echo(login($_POST['idToken']));
        break;

    case 'get_foyers':
        if (identify($_POST['id_google'])) {
            echo getFoyers($_POST['id']);
        }
        break;

    case 'get_invitations':
        if (identify($_POST['id_google'])) {
            echo getInvitations($_POST['id']);
        }
        break;

    case 'ajout_article':
        if (identify($_POST['id_google'])) {
            echo ajoutArticle($_POST['id_foyer'], $_POST['name_article'], $_POST['quantite']);
        }
        break;

    default:
        echo "Tu es bien sur l'API de weNeed";
        break;
}


//functions

function login($idToken)
{
    $url = 'https://www.googleapis.com/oauth2/v3/tokeninfo?id_token=' . $idToken;
    $arr = json_decode(file_get_contents($url), true);
    if ($arr['aud'] == '715904460526-p13om3375npci6q0sd2hdbj37tq38oul.apps.googleusercontent.com') {
        $usr = array($arr['sub'], $arr['name'], $arr['email']);
        $id = insert_user($usr);
        return json_encode(array('identified' => true, 'id' => $id,
            'name' => $arr['name'], 'email' => $arr['email'], 'id_google' => $arr['sub']));
    }
    return json_encode(array('identified' => false));
    //echo json_encode($json);
    //$arr = array('test'=>'coucou');
    //echo json_encode($arr);
}

function getFoyers($id)
{
    $arr = select_foyers($id);
    if ($arr == null) {
        return json_encode(array("number" => 0));
    }
    return json_encode(array_merge(array("number" => sizeof($arr)), $arr));
}

function getInvitations($id)
{
    return select_invitations($id);
}
function ajout_article($id_foyer, $name_article, $quantite)
{
    $article = array($id_foyer, $name_article, $quantite);
    $arr = insert_article($article);
    if ($arr == null) {
        return json_encode(array("success" => false));
    }
    return json_encode(array("success" => true));
}


