<?php

class Doctrine_Template_Listener_DmAkismet extends Doctrine_Record_Listener
{
  public function __construct($options = array())
  {
    $this->_options = $options;
  }
  
  protected function getAkismet($invoker)
  {
    $request = sfContext::getInstance()->getRequest();
    
    $api_key = sfConfig::get('app_akismet_api_key');
    
    if ( empty($api_key) ) return false;
    
  	$akismet = new Akismet(
       $request->getUriPrefix().$request->getRelativeUrlRoot(),
       $api_key
    );
    
    $data = $invoker->getAkismetData();
        
    // Set values
    if ( !empty($data['author_name']) ) $akismet->setCommentAuthor($data['author_name']); else return true;
    if ( !empty($data['author_email']) ) $akismet->setCommentAuthorEmail($data['author_email']);
    if ( !empty($data['author_url']) ) $akismet->setCommentAuthorURL($data['author_url']);
    if ( !empty($data['content']) ) $akismet->setCommentContent($data['content']); else return true;
    if ( !empty($data['permalink']) ) $akismet->setPermalink($data['permalink']);
    if ( !empty($data['referrer']) ) $akismet->setReferer($data['referrer']);
    if ( !empty($data['user_ip']) ) $akismet->setUserIp($data['user_ip']);
    
    return $akismet;
  }

  public function preSave(Doctrine_Event $event)
  {
  	$invoker = $event->getInvoker();
    $modified = $invoker->getModified();
    
    if ( $invoker->isNew() )
    {
    	// Check if the model is spam
    	$akismet = $this->getAkismet($invoker);
        
        if ( $akismet && ( is_bool($akismet) || $akismet->isCommentSpam() ) )
        {
        	$invoker->is_not_spam = false;
        	$invoker->is_active = false;
        }
        else
        {
        	$invoker->is_not_spam = true;
        }
    }
    else
    {
        if ( isset($modified['is_not_spam']) )
        {
        	$akismet = $this->getAkismet($invoker);
        	
        	if ( $akismet )
        	{
        	   if ( !$invoker->is_not_spam ) $akismet->submitSpam();
        	   else $akismet->submitHam();
        	}
        }
    }
  }
}