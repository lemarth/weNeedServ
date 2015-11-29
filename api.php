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

    case 'ajout_foyer':
        if (identify($_POST['id_google'])) {
            echo ajoutFoyer($_POST['nom'], $_POST['id']);
        }
        break;

    case 'repondre_invitation':
        if (identify($_POST['id_google'])) {
            echo repondreInvitation($_POST['id_invitation'], $_POST['reponse']);
        }
        break;

    case 'modifier_etat':
        if (identify($_POST['id_google'])) {
            echo modifierEtat($_POST['id_article'], $_POST['etat']);
        }
        break;

    case 'inviter':
        if (identify($_POST['id_google'])) {
            echo inviter_($_POST['adresse_invite'], $_POST['id_foyer']);
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
    return json_encode(select_invitations($id));
}

function ajoutArticle($id_foyer, $name_article, $quantite)
{
    $article = array($id_foyer, $name_article, $quantite);
    $arr = insert_article($article);
    return json_encode(array("id" => $arr));
}

function ajoutFoyer($nom, $id)
{
    $arr = insert_foyer($nom, $id);
    if ($arr == null) {
        return json_encode(array("success" => false));
    }
    return json_encode(array("success" => true));
}

function repondreInvitation($id_invitation, $reponse)
{
    $arr = update_etat_invitation($id_invitation, $reponse);
    if ($arr == null) {
        return json_encode(array("success" => false));
    }
    return json_encode(array("success" => $arr));
}

function modifierEtat($id_article, $etat)
{
    $arr = update_etat_article($id_article, $etat);
    if ($arr == null) {
        return json_encode(array("success" => false));
    }
    return json_encode(array("success" => $arr));
}

function inviter_($adresse_invite, $id_foyer)
{
    $arr = inviter($adresse_invite, $id_foyer);
    if ($arr == null) {
        return json_encode(array("success" => false));
    }
    return json_encode(array("success" => true));
}


