<?php
namespace Adianti\Base\Lib\Base;

use Adianti\Base\Lib\Control\TPage;

/**
 * Standard page controller for listings
 *
 * @version    5.5
 * @package    base
 * @author     Pablo Dall'Oglio
 * @copyright  Copyright (c) 2006 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    http://www.adianti.com.br/framework-license
 */
abstract class TStandardList extends TPage
{
    protected $form;
    protected $datagrid;
    protected $pageNavigation;
    
    use AdiantiStandardListTrait;
}
