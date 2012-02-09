<?php

/**
 *  Very messy HTTP utility library
 */
class HTTPUtil 
{
  const GET = 'GET';
  const POST = 'POST';
  const PUT = 'PUT';
  const DELETE = 'DELETE';
  
  const DEBUGGING = true;
  
  /**
   *  A general purpose HTTP request method
   *
   * @param  string $url
   * @param  string $method
   * @param  mixed  $post_data
   * @param  string $content_type
   * @param  string $accept_type
   * @return array
   * @throws CurlException 
   */
  public static function request($url, $method = 'GET', $post_data = '', $content_type = '', $accept_type = '')
  {
    $headers = array(
      'Accept: '.$accept_type
    );
    $ch = curl_init();
    
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
  
    if ($post_data)
    {
      curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
      
      $headers[] = 'Content-Length: '.strlen($post_data);
      $headers[] = 'Content-Type: '.$content_type;
    }
    
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    
    // if debugging is enabled every request gets logged to disk
    if (true === self::DEBUGGING) {
      $logfile = fopen('request.log.csv', 'a');
      $fields = array(
        date('Y-m-d H:i:s'),
        $method,
        $url,
        $post_data,
        serialize($headers)
      );
      
      fputcsv($logfile, $fields, ';');
      fclose($logfile);
    }

    $response = curl_exec($ch);
    $error = curl_error($ch);
      
    if ($error != '') {
      throw new CurlException($error);
    }
    
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    curl_close($ch);

    return array($response, $http_code);
  }
  
  /**
   *  A HTTP request that returns json and optionally sends a json payload (post only)
   *
   * @param  string $url
   * @param  string $method
   * @param  mixed  $data
   * @return array 
   */
  public static function jsonRequest($url, $method, $data = null)
  {
    $json = json_encode($data);
    $ret = self::request($url, $method, $json, 'application/json', 'application/json');
    $ret[0] = json_decode($ret[0], true);
    
    return $ret;
  }
  
  /**
   * Executes a PUT request for the given url and data
   * 
   * @param  string $url
   * @param  mixed  $data
   * @return array
   */
  public static function jsonPutRequest($url, $data)
  {
    return self::jsonRequest($url, self::PUT, $data);
  }
  
  /**
   * Executes a POST request for the given url and data
   * 
   * @param  string $url
   * @param  mixed  $data
   * @return array
   */
  public static function jsonPostRequest($url, $data)
  {
    return self::jsonRequest($url, self::POST, $data);
  }
  
  /**
   * Executes a GET request for the given url
   * 
   * @param  string $url
   * @return array
   */
  public static function jsonGetRequest($url)
  {
    return self::jsonRequest($url, self::GET);
  }
  
  /**
   * Executes a DELETE request for the given url
   * 
   * @param  string $url
   * @return array
   */
  function deleteRequest($url)
  {
    return self::request($url, self::DELETE);
  }
}
