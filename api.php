<?php 

require_once('fb_php/src/facebook.php');
require_once("db.php");
$db = new DB;

//$db->install_tables();

$facebook = new Facebook(array(
  'appId'  => 'YOUR_APP_ID',
  'secret' => 'YOUR_APP_SECRET',
));

$user = $facebook->getUser();

if ($user) {
  try {
    // Proceed knowing you have a logged in user who's authenticated.
    $user_info = $facebook->api('/me&fields=likes,name');
    
    //make sure they haven't been added yet
    $uid_result = $db->query("SELECT uid FROM user_likes WHERE uid = %d LIMIT 1", $user);
    
    $existing_user = mysql_fetch_assoc($uid_result);
    
      $count = 0;
    
    $count_2 = 0;
    
      foreach ($user_info['likes']['data'] as $like) {
        $common_likes_result = $db->query("SELECT * FROM user_likes WHERE like_id = %d AND uid != %d", $like['id'], $user);
        
        
        
        $reset = 1;
        $count_2 = 0;
        
        if ($uid_info) {
          unset($uid_info);
        }
        
          while ($common_like = mysql_fetch_assoc($common_likes_result)) {
          
            $count_3 = $count_2;
            $count_3 = strval($count_3);
            
            $user_info_return['likes'][$count]['like_id'] = $common_like['like_id'];
            $user_info_return['likes'][$count]['like_name'] = $common_like['like_name'];
            $user_info_return['likes'][$count]['like_category'] = $common_like['like_category'];
            
            $uid_info[] = array('id' => $common_like['uid'], 'name' => $common_like['user_name']);
            
            $count_2++;
            
            
          }
        
        if (count($uid_info) > 0) {
          $user_info_return['likes'][$count]['uids'] = $uid_info;
        }
        
        if (!$existing_user['uid']) {
          $db->query("INSERT INTO user_likes (like_id, like_name, user_name, like_category, uid) VALUES (%d, '%S', '%S', '%S', %d)", $like['id'], $like['name'], $user_info['name'], $like['category'], $user);
        }
        
        if ($user_info_return['likes'][$count]) {
          $count++;
        }
    }
  } catch (FacebookApiException $e) {
    error_log($e);
    $user = null;
  }
}

print json_encode($user_info_return);
