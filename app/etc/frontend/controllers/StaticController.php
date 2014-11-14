<?php

namespace App\Modules\Frontend\Controllers;

use App\Error;
use App\Extensions\Controller;
use App\Libraries\Email;
use Ice\Arr;
use Ice\Validation;

/**
 * Frontend static Controller
 *
 * @package     Ice/Base
 * @category    Controller
 * @version     1.0
 */
class StaticController extends Controller
{

    /**
     * Display contact form
     */
    public function contactAction()
    {
        $this->tag->setTitle(__('Contact'));
        $this->siteDesc = __('Contact');
    }

    /**
     * Validate _POST and send email
     *
     * @throws Error
     */
    public function postContactAction()
    {
        $validation = new Validation();

        $validation->rules([
            'fullName' => 'required',
            'email' => 'required|email',
            'repeatEmail' => 'same:email',
            'content' => 'required|length:10,5000',
        ]);

        $validation->setLabels([
            'fullName' => __('Full name'),
            'content' => __('Content'),
            'email' => __('Email'),
            'repeatEmail' => __('Repeat email')
        ]);

        $valid = $validation->validate($_POST);

        if (!$valid) {
            $this->view->setVar('errors', new Arr($validation->getMessages()));
            $this->flash->warning('<strong>' . __('Warning') . '!</strong> ' . __("Please correct the errors."));
        } else {
            // Prepare an email
            $email = new Email();
            $email->prepare(__('Contact'), $this->config->app->admin, 'email/contact', [
                'fullName' => $this->request->getPost('fullName'),
                'email' => $this->request->getPost('email'),
                'content' => $this->request->getPost('content'),
            ]);
            $email->addReplyTo($this->request->getPost('email'));

            // Try to send email
            if ($email->Send() === true) {
                $this->flash->notice('<strong>' . __('Success') . '!</strong> ' . __("Message was sent"));
                unset($_POST);
            } else {
                throw new Error($email->ErrorInfo);
            }
        }
    }
}
