<?php
namespace Adianti\Base\Modules\Common\Control;

use Adianti\Base\Lib\Database\TTransaction;
use Adianti\Base\Lib\Registry\TSession;
use Adianti\Base\Lib\Widget\Base\TElement;
use Adianti\Base\Lib\Widget\Dialog\TMessage;
use Adianti\Base\Modules\Admin\User\Model\SystemUser;
use Adianti\Base\Modules\Communication\Model\SystemMessage;
use DateTime;
use Exception;

/**
 * MessageList
 *
 * @version    1.0
 * @package    control
 * @author     Pablo Dall'Oglio
 * @copyright  Copyright (c) 2006 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    http://www.adianti.com.br/framework-license
 */
class MessageList extends TElement
{
    public function __construct($param)
    {
        parent::__construct('ul');
        
        try {
            TTransaction::open('communication');
            
            // load the messages to the logged user
            $system_messages = SystemMessage::where('checked', '=', 'N')->where('system_user_to_id', '=', TSession::getValue('userid'))->orderBy('id', 'desc')->load();
            
            if ($param['theme'] == 'theme2') {
                $this->class = 'dropdown-menu dropdown-messages';
                
                $a = new TElement('a');
                $a->{'class'} = "dropdown-toggle";
                $a->{'data-toggle'}="dropdown";
                $a->{'href'} = "#";
                
                $a->add(TElement::tag('i', '', array('class'=>"fa fa-envelope fa-fw")));
                $a->add(TElement::tag('span', count($system_messages), array('class'=>"badge badge-notify")));
                $a->add(TElement::tag('i', '', array('class'=>"fa fa-caret-down")));
                $a->show();
                
                TTransaction::open('permission');
                foreach ($system_messages as $system_message) {
                    $name    = SystemUser::find($system_message->system_user_id)->name;
                    $date    = $this->getShortPastTime($system_message->dt_message);
                    $subject = $system_message->subject;
                    
                    $li  = new TElement('li');
                    $a   = new TElement('a');
                    $div = new TElement('div');
                    
                    $a->href = urlRoute('/admin/system/message/form/view/key/'.$system_message->id.'/id/'.$system_message->id);
                    $a->{'generator'} = 'adianti';
                    $li->add($a);
                    $a->add($div);

                    $div->add(TElement::tag('strong', $name));
                    $div->add(TElement::tag('span', TElement::tag('em', $date), array('class' => 'pull-right text-muted')));
                    
                    $div2 = new TElement('div');
                    $div2->add($subject);
                    $a->add($div2);
                    
                    parent::add($li);
                    parent::add(TElement::tag('li', '', array('class' => 'divider')));
                }
                TTransaction::close();
                
                $li = new TElement('li');
                $a = new TElement('a');
                $li->add($a);
                $a->class='text-center';
                $a->href = urlRoute('/admin/system/message/list/filter/inbox');
                $a->{'generator'} = 'adianti';
                $a->add(TElement::tag('strong', 'Read messages'));
                $a->add($i = TElement::tag('i', '', array('class'=>'fa fa-inbox')));
                parent::add($li);
                
                parent::add(TElement::tag('li', '', array('class' => 'divider')));
                
                $li = new TElement('li');
                $a = new TElement('a');
                $li->add($a);
                $a->class='text-center';
                $a->href = urlRoute('/admin/system/message/form');
                $a->{'generator'} = 'adianti';
                $a->add(TElement::tag('strong', 'Send message'));
                $a->add($i = TElement::tag('i', '', array('class'=>'fa fa-envelope-o')));
                parent::add($li);
            } elseif ($param['theme'] == 'theme3') {
                $this->class = 'dropdown-menu';
                
                $a = new TElement('a');
                $a->{'class'} = "dropdown-toggle";
                $a->{'data-toggle'}="dropdown";
                $a->{'href'} = "#";
                
                $a->add(TElement::tag('i', '', array('class'=>"fa fa-envelope fa-fw")));
                $a->add(TElement::tag('span', count($system_messages), array('class'=>"label label-success")));
                $a->show();
                
                $li_master = new TElement('li');
                $ul_wrapper = new TElement('ul');
                $ul_wrapper->{'class'} = 'menu';
                $li_master->add($ul_wrapper);
                
                parent::add( TElement::tag('li', _t('Messages'), ['class'=>'header']));
                parent::add($li_master);
                
                TTransaction::open('permission');
                foreach ($system_messages as $system_message) {
                    $name    = SystemUser::find($system_message->system_user_id)->name;
                    $date    = $this->getShortPastTime($system_message->dt_message);
                    $subject = $system_message->subject;
                    
                    $li  = new TElement('li');
                    $a   = new TElement('a');
                    $div = new TElement('div');
                    
                    $a->href = urlRoute('/admin/system/message/form/view/key/'.$system_message->id.'/id/'.$system_message->id);
//                    $a->setProperty('generator', 'adianti');
                    $li->add($a);
                    
                    $div->{'class'} = 'pull-left';
                    $div->add(TElement::tag('i', '', array('class' => 'fa fa-user fa-2x')));
                    
                    $h4 = new TElement('h4');
                    $h4->add($name);
                    $h4->add(TElement::tag('small', TElement::tag('i', $date, array('class' => 'fa fa-clock-o'))));
                    
                    $a->add($div);
                    $a->add($h4);
                    $a->add(TElement::tag('p', $subject));
                    
                    $ul_wrapper->add($li);
                }
                
                TTransaction::close();
                
                parent::add(TElement::tag('li', TElement::tag('a', 'Read messages', array('href'=> urlRoute('/admin/system/message/list/filter/inbox'), 'generator' => 'adianti', ['class'=>'footer']))));
                parent::add(TElement::tag('li', TElement::tag('a', 'Send messages', array('href'=> urlRoute('/admin/system/message/form'), 'generator' => 'adianti'), array('class'=>'footer'))));
            } elseif ($param['theme'] == 'theme4') {
                $this->class = 'dropdown-menu';
                
                $a = new TElement('a');
                $a->{'class'} = "dropdown-toggle";
                $a->{'data-toggle'}="dropdown";
                $a->{'href'} = "#";
                
                $a->add(TElement::tag('i', 'email', array('class'=>"material-icons")));
                $a->add(TElement::tag('span', count($system_messages), array('class'=>"label-count")));
                $a->show();
                
                $li_master = new TElement('li');
                $ul_wrapper = new TElement('ul');
                $ul_wrapper->{'class'} = 'menu';
                $ul_wrapper->{'style'} = 'list-style:none';
                $li_master->{'class'} = 'body';
                $li_master->add($ul_wrapper);
                
                parent::add(TElement::tag('li', _t('Messages'), ['class'=>'header']));
                parent::add($li_master);
                
                TTransaction::open('permission');
                foreach ($system_messages as $system_message) {
                    $name    = SystemUser::find($system_message->system_user_id)->name;
                    $date    = $this->getShortPastTime($system_message->dt_message);
                    $subject = $system_message->subject;
                    
                    $li  = new TElement('li');
                    $a   = new TElement('a');
                    $div = new TElement('div');
                    $div2= new TElement('div');
                    
                    $a->href = urlRoute('/admin/system/message/form/view/key/'.$system_message->id.'/id/'.$system_message->id);
                    $a->class = 'waves-effect waves-block';
                    $a->generator = 'adianti';
                    $li->add($a);
                    
                    $div->{'class'} = 'icon-circle bg-light-green';
                    $div2->{'class'} = 'menu-info';
                    
                    $div->add(TElement::tag('i', '', array('class' => 'fa fa-user fa-2x')));
                    
                    $h4 = new TElement('h4');
                    $h4->add($name);
                    $h4->add($subject);
                    
                    $div2->add($h4);
                    $a->add($div);
                    $a->add($div2);
                    
                    $p = new TElement('p');
                    $p->add(TElement::tag('i', 'access_time', ['class' => 'material-icons']));
                    $p->add($date);
                    
                    $div2->add($p);
                    $ul_wrapper->add($li);
                }
                
                TTransaction::close();

                $route = urlRoute('/admin/system/message/list/filter/inbox/?generator=adianti');
                parent::add(TElement::tag('li', TElement::tag('a', _t('Read messages'), array('href'=> $route, array('class'=>'footer')))));
                parent::add(TElement::tag('li', TElement::tag('a', _t('Send message'), array('href'=> urlRoute('/admin/system/message/form/?generator=adianti'), array('class'=>'footer')))));
            }
            
            TTransaction::close();
        } catch (Exception $e) {
            new TMessage('error', $e->getMessage());
        }
    }
    
    public function getShortPastTime($from)
    {
        $to = date('Y-m-d H:i:s');
        $start_date = new DateTime($from);
        $since_start = $start_date->diff(new DateTime($to));
        if ($since_start->y > 0)
            return $since_start->y.' years ';
        if ($since_start->m > 0)
            return $since_start->m.' months ';
        if ($since_start->d > 0)
            return $since_start->d.' days ';
        if ($since_start->h > 0)
            return $since_start->h.' hours ';
        if ($since_start->i > 0)
            return $since_start->i.' minutes ';
        if ($since_start->s > 0)
            return $since_start->s.' seconds ';    
    }
}
