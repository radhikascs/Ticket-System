<?php
/**
*qdPM
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@qdPM.net so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade qdPM to newer
* versions in the future. If you wish to customize qdPM for your
* needs please refer to http://www.qdPM.net for more information.
*
* @copyright  Copyright (c) 2009  Sergey Kharchishin and Kym Romanets (http://www.qdpm.net)
* @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*/
?>
<?php

/**
 * ProjectsComments
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @package    sf_sandbox
 * @subpackage model
 * @author     Your name here
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
class ProjectsComments extends BaseProjectsComments
{
  public static function sendNotification($c,$comment,$sf_user)
  {
    $to = array();
    foreach(explode(',',$comment->getProjects()->getTeam()) as $v)
    {
      if($u = Doctrine_Core::getTable('Users')->find($v))
      {        
        $to[$u->getEmail()]=$u->getName();        
      }
    }
          
    $user = $sf_user->getAttribute('user');
    $from[$user->getEmail()] = $user->getName();
    $to[$comment->getProjects()->getUsers()->getEmail()] = $comment->getProjects()->getUsers()->getName();
    $to[$user->getEmail()] = $user->getName();
    
    $projects_comments = Doctrine_Core::getTable('ProjectsComments')
      ->createQuery()
      ->addWhere('projects_id=?',$comment->getProjectsId())      
      ->orderBy('created_at desc')
      ->execute();
      
    foreach($projects_comments as $v)
    {      
      $to[$v->getUsers()->getEmail()]=$v->getUsers()->getName();      
    }
    
    if(sfConfig::get('app_send_email_to_owner')=='off')
    {
      unset($to[$user->getEmail()]);             
    }
                 
    $subject = t::__('New Project Comment') . ': ' . $comment->getProjects()->getName() . ($comment->getProjects()->getProjectsStatusId()>0 ? ' [' . $comment->getProjects()->getProjectsStatus()->getName() . ']':'');
    $body  = $c->getComponent('projectsComments','emailBody',array('projects'=>$comment->getProjects()));
                
    Users::sendEmail($from,$to,$subject,$body,$sf_user);
  }
}
