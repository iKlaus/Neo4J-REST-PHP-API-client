<?php

//namespace NeoRest;

/**
 *  Very messy HTTP utility library
 */
class HTTPUtil 
{
  const GET = 'GET';
  const POST = 'POST';
  const PUT = 'PUT';
  const DELETE = 'DELETE';
  
  /**
   *  A general purpose HTTP request method
   */
  function request($url, $method='GET', $post_data='', $content_type='', $accept_type='')
  {
    // Uncomment for debugging
    //echo 'HTTP: ', $method, " : " ,$url , " : ", $post_data, "\n";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE); 
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION,1);

    //if ($method==self::POST){
    //  curl_setopt($ch, CURLOPT_POST, true); 
    //} else {
    //  curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    //}
    
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
  
    if ($post_data)
    {
      curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
      
      $headers = array(
            'Content-Length: ' . strlen($post_data),
            'Content-Type: '.$content_type,
            'Accept: '.$accept_type
            );
      curl_setopt($ch, CURLOPT_HTTPHEADER, $headers); 
    }

    // Batch jobs are overloading the local server so try twice, with a pause in the middle
    // TODO There must be a better way of handling this. What I've got below is an ugly hack.
    $count = 6;
    do {
      $count--;
      $response = curl_exec($ch);
      $error = curl_error($ch);
      if ($error != '') {
        echo "Curl got an error, sleeping for a moment before retrying: $count\n";
        sleep(10);
        $founderror = true;
      } else {
        $founderror = false;
      }
      
    } while ($count && $founderror);
  
    if ($error != '') {
      throw new CurlException($error);
    }

    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    curl_close($ch);

    return array($response, $http_code);
  }
  
  /**
   *  A HTTP request that returns json and optionally sends a json payload (post only)
   */
  function jsonRequest($url, $method, $data=NULL)
  {
    $json = json_encode($data);
// print_r($json);    
    $ret = self::request($url, $method, $json, 'application/json', 'application/json');
    $ret[0] = json_decode($ret[0], TRUE);
    return $ret;
  }
  
  function jsonPutRequest($url, $data)
  {
    return self::jsonRequest($url, self::PUT, $data);
  }
  
  function jsonPostRequest($url, $data)
  {
    return self::jsonRequest($url, self::POST, $data);
  }
  
  function jsonGetRequest($url)
  {
    return self::jsonRequest($url, self::GET);
  }
  
  function deleteRequest($url)
  {
    return self::request($url, self::DELETE);
  }
}
