<?php
/*

All Example code is released under the GNU Affero General Public License.
See COPYRIGHT.txt and LICENSE.txt.

---------------------------------------------------------------------
Part of the OpenEnergyMonitor project:
http://openenergymonitor.org

*/
    
$mqtt_server = array (
    "user"=>"superuser",
    "password"=>"password",
    "host"=>"127.0.0.1",
    "port"=>1883
);

error_reporting(E_ALL);
ini_set('display_errors', 'on');
        
require("core.php");
$path = get_application_path();

$redis = new Redis();
$connected = $redis->connect("localhost");

$mysqli = @new mysqli("localhost","mysqluser","mysqlpassword","mymqttcloud");

require("user_model.php");
$user = new User($mysqli);

session_start();
$session = $user->status();
$userid = $session["userid"];

$q = "";
if (isset($_GET['q'])) $q = $_GET['q'];

$format = "html";
$content = "Sorry page not found";

switch ($q)
{   
    case "":
        $format = "html";
        $content = view("client.php",array('session'=>$session));
        break;
    
    case "device/list":
        $format = "json";        
        $content = json_decode($redis->get("devices:$userid"));
        break;
        
    case "mqtt":
        $format = "text";
        $topic = "user/$userid/".$_POST['topic'];
        $message = $_POST['message'];
        
        $mqtt_client = new Mosquitto\Client();    
        $mqtt_client->setCredentials($mqtt_server['user'],$mqtt_server['password']);
        $mqtt_client->connect($mqtt_server['host'], $mqtt_server['port'], 5);
        $mqtt_client->publish($topic,$message);
        
        $content = "ok";
        break;
        
    case "status":
        $format = "json";
        $content = $session;
        break;

    case "register":
        $format = "json";
        $content = $user->register(get('username'),get('password'));
        break;
                
    case "login":
        $format = "json";
        $content = $user->login(get('username'),get('password'));
        break;
        
    case "logout":
        $format = "text";
        $content = $user->logout();
        break;
}

switch ($format) 
{
    case "html":
        header('Content-Type: text/html');
        print view("theme/theme.php", array("content"=>$content));
        break;
    case "text":
        header('Content-Type: text/plain');
        print $content;
        break;
    case "json":
        header('Content-Type: application/json');
        print json_encode($content);
        break;
}
