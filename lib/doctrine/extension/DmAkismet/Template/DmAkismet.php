<?php

class Doctrine_Template_DmAkismet extends Doctrine_Template
{
  public function __construct(array $options = array())
  {
  	$this->_options = array(
  	 'author_name' => 'author_name',
  	 'author_email' => 'author_email',
     'author_url' => 'author_url',
     'content' => 'content'
  	);
  	
    $this->_options = Doctrine_Lib::arrayDeepMerge($this->_options, $options);
  }
  
  public function setTableDefinition()
  {
    $this->hasColumn('is_not_spam', 'boolean', '25', array(
         'notnull' => '1',
         'default' => '1',
         ));
             
    $this->addListener(new Doctrine_Template_Listener_DmAkismet($this->_options));
  }
  
  /**
   * Override this method if you want to change the way to retrieve data used by Akismet
   * 
   * @return array
   */
  public function getAkismetData()
  {
  	$invoker = $this->getInvoker();
  	
  	$data = $invoker->toArray();
  	
  	$result = array();
    
    // Author name
    $result['author_name'] = 'unknown';
    foreach ( array($this->_options['author_name'], 'author_name', 'author', 'name', 'user_name', 'username', 'user') as $name )
        if ( !empty($data[$name]) && is_string($data[$name]) )
        {
            $result['author_name'] = $data[$name];
            break;
        }
    
    // Author email
    $data['author_email'] = '';
    foreach ( array($this->_options['author_email'], 'author_email', 'author_mail', 'email', 'mail') as $name )
        if ( !empty($data[$name]) && is_string($data[$name]) )
        {
            $result['author_email'] = $data[$name];
            break;
        }
    
    // Author url
    $data['author_url'] = '';
    foreach ( array($this->_options['author_url'], 'author_url', 'author_website', 'author_link', 'author_blog', 'url', 'website', 'link', 'blog') as $name )
        if ( !empty($data[$name]) && is_string($data[$name]) )
        {
            $result['author_url'] = $data[$name];
            break;
        }
    
    // Content
    $data['content'] = '';
    foreach ( array($this->_options['content'], 'content', 'body', 'data', 'description', 'text', 'comment', 'message') as $name )
        if ( !empty($data[$name]) && is_string($data[$name]) )
        {
            $result['content'] = $data[$name];
            break;
        }
  	
  	return $result;
  }
}