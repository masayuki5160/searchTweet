<?php
 
require_once(__DIR__.'/twitteroauth/twitteroauth.php');
 
//Twitterで検索するワード
$key = "名古屋";
 
//オプション設定
$options = array('q'=>$key,'count'=>'100','lang'=>'ja');
 
//twitterAppsで取得
$consumerKey = '';
$consumerSecret = '';
$accessToken = '';
$accessTokenSecret = '';
    
$twObj = new TwitterOAuth(
    $consumerKey,
    $consumerSecret,
    $accessToken,
    $accessTokenSecret
);
$json = $twObj->OAuthRequest(
    'https://api.twitter.com/1.1/search/tweets.json',
    'GET',
    $options
);
$jset = json_decode($json, true);
 
$tmpHtml = "";
 
foreach ($jset['statuses'] as $result){
    $id = $result['id'];
    $name = $result['in_reply_to_screen_name'];
    $uri = "http://twitter.com/".$name;
    $link = $result['user']['profile_image_url'];
    $content = $result['text'];
    $updated = $result['created_at'];
    $jptime = strtotime($updated);
    $timestamp = $jptime + 9 * 60 * 60;
    $timestamp = date("Y-m-d H:i:s",$timestamp);

    // 転置インデックス作成処理の実施(indexer.phpへPOST)
    $result = file_get_contents(
      'http://localhost/indexer.php',
      false,
      stream_context_create(
        array(
          'http' => array(
            'method' => 'POST',
            'header' => implode(
              "\r\n",
              array(
                'Content-Type: application/x-www-form-urlencoded'
              )
            ),
            'content' => http_build_query(
              array(
                'test1' => 'test1',
                'doc' => $content
              )
            )
          )
        )
      )
    );
}
 
?>