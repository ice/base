<?php

namespace App\Modules\Frontend\Controllers;

use App\Extensions\Controller;

/**
 * Frontend language controller
 *
 * @package     Ice/Base
 * @category    Controller
 * @version     1.0
 */
class LangController extends Controller
{

    /**
     * Set a language
     */
    public function setAction()
    {
        $params = $this->router->getParams();

        if ($lang = $params["param"]) {
            // Store lang in session and cookie
            $this->session->set('lang', $lang);
            $this->cookies->set('lang', $lang, time() + 365 * 86400);
        }
        // Go to the last place
        $referer = $this->request->getServer("HTTP_REFERER");
        if (strpos($referer, $this->request->getServer("HTTP_HOST") . "/") === false) {
            return $this->response->setHeader("Location", $referer);
        } else {
            $this->response->redirect();
        }
    }
}
