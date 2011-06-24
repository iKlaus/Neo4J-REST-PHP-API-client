<?php

//namespace NeoRest;

class TraversalDescription 
{
  const BREADTH_FIRST = 'breadth first';
  const DEPTH_FIRST = 'depth first';
  
  var $_neo_db;
  var $_traversalDescription;
  var $_order;
  var $_uniqueness;
  var $_relationships;
  var $_pruneEvaluator;
  var $_returnFilter;
  var $_data;
  var $_maxDepth;
  
  public function __construct($neo_db)
  {
    $this->_neo_db = $neo_db;
  }
      
  // Adds a relationship description.
  function relationships($type, $direction=NULL)
  {
    if ( $direction ) {
      $this->_relationships[] = array( 'type' => $type, 'direction' => $direction );
    } else {
      $this->_relationships[] = array( 'type' => $type );
    }
    
    $this->_traversalDescription['relationships'] = $this->_relationships;
  }
  
  function breadthFirst() {
    $this->_order = TraversalDescription::BREADTH_FIRST;
    $this->_traversalDescription['order'] = $this->_order;
  }
  
  function depthFirst() {
    $this->_order = TraversalDescription::DEPTH_FIRST;
    $this->_traversalDescription['order'] = $this->_order;
  }
  
  function prune($language, $body) {
    $this->_pruneEvaluator['language'] = $language;
    $this->_pruneEvaluator['body'] = $body;
    $this->_traversalDescription['prune evaluator'] = $this->_pruneEvaluator;
  }
  
  function returnFilter($language, $name) {
    $this->_returnFilter['language'] = $language;
    $this->_returnFilter['name'] = $name;
    $this->_traversalDescription['return filter'] = $this->_returnFilter;
  }
  
  function maxDepth($depth) {
    $this->_maxDepth = $depth;
    $this->_traversalDescription['max depth'] = $this->_maxDepth;
  }
  
  
  public function __invoke()
  {
    return $this->_traversalDescription;
  }
  
  public function traverse($node, $returnType) 
  {
    $this->_data = $this->_traversalDescription;
    $uri = $node->getUri().'/traverse'.'/'.$returnType;

// print_r($uri);
// print_r($traversalDescription);

    
    list($response, $http_code) = HTTPUtil::jsonPostRequest($uri, $this->_data);
    if ($http_code!=200) throw new HttpException($http_code);
    
    $objs = array();
    if ($returnType == TraversalType::NODE ) {
      $inflateClass = 'Node';
      $inflateFunc = 'inflateFromResponse';
    } elseif ($returnType == TraversalType::RELATIONSHIP) {
      $inflateClass = 'Relationship';
      $inflateFunc = 'inflateFromResponse';
    } else {
      $inflateClass = 'Path';
      $inflateFunc = 'inflateFromResponse';
    }
    
    foreach($response as $result)
    {
        $objs[] = $inflateClass::$inflateFunc($this->_neo_db, $result); 
//      $objs[] = $result;
    }
    
    return $objs;
  } 
  
  
}
