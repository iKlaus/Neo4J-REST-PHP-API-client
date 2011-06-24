<?php

//namespace NeoRest;

class Node extends PropertyContainer
{
  var $_neo_db;
  var $_id;
  var $_is_new;
  var $_pathFinderData;
  
  public function __construct($neo_db)
  {
    $this->_neo_db = $neo_db;
    $this->_is_new = TRUE;
  }
  
  public function delete()
  {
    if (!$this->_is_new) 
    {
      list($response, $http_code) = HTTPUtil::deleteRequest($this->getUri());
      
      if ($http_code!=204) throw new HttpException("http code: " . $http_code . ", response: " . print_r($response, true));
      
      $this->_id = NULL;
      $this->_id_new = TRUE;
    }
  }
  
  public function save()
  {
    if ($this->_is_new) {
      list($response, $http_code) = HTTPUtil::jsonPostRequest($this->getUri(), $this->_data);
      if ($http_code!=201) throw new HttpException("http code: " . $http_code . ", response: " . print_r($response, true));
    } else {
      list($response, $http_code) = HTTPUtil::jsonPutRequest($this->getUri().'/properties', $this->_data);
      if ($http_code!=204) throw new HttpException("http code: " . $http_code . ", response: " . print_r($response, true));
    }

    if ($this->_is_new) 
    {
      $this->_id = end(explode("/", $response['self']));
      $this->_is_new=FALSE;
    }
  }
  
  public function getId()
  {
    return $this->_id;
  }
  
  public function isSaved()
  {
    return !$this->_is_new;
  }
  
  public function getRelationships($direction=Relationship::DIRECTION_BOTH, $types=NULL)
  {
    $uri = $this->getUri().'/relationships';
    
    switch($direction)
    {
      case Relationship::DIRECTION_IN:
        $uri .= '/' . DIRECTION::INCOMING;
        break;
      case Relationship::DIRECTION_OUT:
        $uri .= '/' . DIRECTION::OUTGOING;
        break;
      default:
        $uri .= '/' . DIRECTION::BOTH;
    }
    
    if ($types)
    {
      if (is_array($types)) $types = implode("&", $types);
      
      $uri .= '/'.$types;
    }
    
    list($response, $http_code) = HTTPUtil::jsonGetRequest($uri);
    
    $relationships = array();
    
    foreach($response as $result)
    {
      $relationships[] = Relationship::inflateFromResponse($this->_neo_db, $result);
    }
    
    return $relationships;
  }
  
  public function createRelationshipTo($node, $type)
  {
    $relationship = new Relationship($this->_neo_db, $this, $node, $type);
    return $relationship;
  }
  
  public function getUri()
  {
    $uri = $this->_neo_db->getBaseUri().'node';
  
    if (!$this->_is_new) $uri .= '/'.$this->getId();
  
    return $uri;
  }
  
  public static function inflateFromResponse($neo_db, $response)
  {
    $node = new Node($neo_db);
    $node->_is_new = FALSE;
    $node->_id = end(explode("/", $response['self']));
    $node->setProperties($response['data']);

    return $node;
  }

// curl -H Accept:application/json -H Content-Type:application/json -d
// '{ "to": "http://localhost:9999/node/3" }'
// -X POST http://localhost:9999/node/1/pathfinder
// TODO Add handling for relationships
// TODO Add algorithm parameter
  public function findPaths(Node $toNode, $maxDepth=null, RelationshipDescription $relationships=null, $singlePath=null)
  {
    
    $this->_pathFinderData['to'] =  $this->_neo_db->getBaseUri().'node'.'/'.$toNode->getId();
    if ($maxDepth) $this->_pathFinderData['max depth'] = $maxDepth;
    if ($singlePath) $this->_pathFinderData['single path'] = $singlePath;
    if ($relationships) $this->_pathFinderData['relationships'] = $relationships->get();
    
    list($response, $http_code) = HTTPUtil::jsonPostRequest($this->getUri().'/pathfinder', $this->_pathFinderData);
    
    if ($http_code==404) throw new NotFoundException;
    if ($http_code!=200) throw new HttpException("http code: " . $http_code . ", response: " . print_r($response, true));
    
    $paths = array();
    foreach($response as $result)
    {
        $paths[] = Path::inflateFromResponse($this->_neo_db, $result);  
    }
    
    if (empty($paths)) {
      throw new NotFoundException();
    }
    
    return $paths;
  } 

  // Convenience method just returns the first path
  public function findPath(Node $toNode, $maxDepth=null, RelationshipDescription $relationships=null)
  {
    $paths = $this->findPaths($toNode, $maxDepth, $relationships, 'true');
    return $paths[0];
  }
  
  
}
