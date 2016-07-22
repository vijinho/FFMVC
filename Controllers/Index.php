<?php

namespace FFMVC\Controllers;

use FFMVC\Helpers as Helpers;

/**
 * Index Controller Class.
 *
 * @author Vijay Mahrra <vijay@yoyo.org>
 * @copyright (c) Copyright 2015 Vijay Mahrra
 * @license GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */
class Index extends Base
{
    // render a php template .phtml view from ui/
    public function index($f3, $params)
    {
        $notifications = Helpers\Notifications::instance();
        $notifications->add('Welcome!', 'success');

        echo \View::instance()->render('www/index/index.phtml');
    }
}
