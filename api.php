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
        if (login($_POST['idToken']))
            echo json_encode(array('identified' => true));
        else
            echo json_encode(array('identified' => false));
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
        insert_user($usr);
        return true;
    }
    return false;
    //echo json_encode($json);
    //$arr = array('test'=>'coucou');
    //echo json_encode($arr);
}



