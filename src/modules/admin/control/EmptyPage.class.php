<?php
namespace Adianti\Base\Modules\Admin\Control;

use Adianti\Base\Lib\Control\TPage;
use Adianti\Base\Lib\Widget\Form\TLabel;

class EmptyPage extends TPage
{
    public function __construct()
    {
        parent::__construct();
        parent::add(new TLabel('<h3>Dvi</h3>'));
    }
}
