<?php

//namespace NeoRest;

// TODO Any reason not to change $_data to $properties and to make it public?
//      This would allow statements like the following:
//      $name = $node->properties['name'];

class PropertyContainer
{
  var $_data;
  
  public function __set($k, $v)
  {
    if ($v===NULL && isset($this->_data[$k])) 
      unset($this->_data[$k]);
    else
      $this->_data[$k] = $v;
  }
  
  public function __get($k)
  {
    if (isset($this->_data[$k]))
      return $this->_data[$k];
    else
      return NULL;
  }
  
  public function setProperties($data)
  {
    $this->_data = $data;
  }
  
  public function getProperties()
  {
    return $this->_data;
  }
}
