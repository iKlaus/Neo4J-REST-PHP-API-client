<?php 

//namespace NeoRest;

class GraphDatabaseService
{
  var $base_uri;
  
  public function __construct($base_uri)
  {
    $this->base_uri = $base_uri;
  }
  
  public function getNodeByUri($uri)
  {
    list($response, $http_code) = HTTPUtil::jsonGetRequest($uri);
  
    switch ($http_code)
    {
      case 200:
        break;
      case 404:
        throw new NotFoundException();
        break;
      default:
        throw new HttpException("http code: " . $http_code . ", response: " . print_r($response, true));
        break;
    }
    return Node::inflateFromResponse($this, $response);
  }
  
  public function getNodeById($node_id)
  {
    $uri = $this->base_uri.'node/'.$node_id;
    
    return $this->getNodeByUri($uri);
  }
  
  public function getRelationshipById($relationship_id)
  {
    $uri = $this->base_uri.'relationship/'.$relationship_id;
    
    return $this->getRelationshipByUri($uri);
  }

  public function getRelationshipByUri($uri)
  {
    list($response, $http_code) = HTTPUtil::jsonGetRequest($uri);
  
    switch ($http_code)
    {
      case 200:
        return Relationship::inflateFromResponse($this, $response);
      case 404:
        throw new NotFoundException();
      default:
        throw new HttpException("http code: " . $http_code . ", response: " . print_r($response, true));
    }
  }
    
  public function createNode()
  {
    return new Node($this);
  }
  
  public function getBaseUri()
  {
    return $this->base_uri;
  }
  
}
