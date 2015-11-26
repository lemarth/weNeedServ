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
        echo(login($_POST['idToken']));
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
            'name' => $arr['name'], 'email' => $arr['email']));
    }
    return json_encode(array('identified' => false));
    //echo json_encode($json);
    //$arr = array('test'=>'coucou');
    //echo json_encode($arr);
}



