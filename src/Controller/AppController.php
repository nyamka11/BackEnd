<?php
/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link      https://cakephp.org CakePHP(tm) Project
 * @since     0.2.9
 * @license   https://opensource.org/licenses/mit-license.php MIT License
 */
namespace App\Controller;

use Cake\Controller\Controller;
use Cake\Event\Event;

/**
 * Application Controller
 *
 * Add your application-wide methods in the class below, your controllers
 * will inherit them.
 *
 * @link https://book.cakephp.org/3/en/controllers.html#the-app-controller
 */
class AppController extends Controller
{

    /**
     * Initialization hook method.
     *
     * Use this method to add common initialization code like loading components.
     *
     * e.g. `$this->loadComponent('Security');`
     *
     * @return void
     */
    public function initialize()
    {
        parent::initialize();

        $this->loadComponent('RequestHandler', [
            'enableBeforeRedirect' => false,
        ]);
        $this->loadComponent('Flash');
        
        $this->loadComponent('Auth',  [
            'authenticate' => [
                'Form'=> [
                    'fields' => ['username'=>'username', 'password'=>'password'],
                    'scope' => ['verified'=>'1'],
                    'userModel' => 'Users'
                ]
            ],
            'storage'=>'Session'
            ]
        );

        /*
         * Enable the following component for recommended CakePHP security settings.
         * see https://book.cakephp.org/3/en/controllers/components/security.html
         */
        //$this->loadComponent('Security');
    }

    public function beforeFilter(event $event) {
        $this->Auth->allow([
            'verification', 
            'register', 
            'logout', 
            'forgotpassword',
            'resetpassword',
            'index',
            'view',
            'edit',
            'delete',
            'add'
        ]);

        if ($this->request->is('options')) {
            $this->setCorsHeaders();
            return $this->response;
        }
    }
    
    private function setCorsHeaders() {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: POST, GET, PUT, PATCH, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: *');
        header('Content-Range: users 0-24/319');
        header('X-Total-Count: 30');
        header('Access-Control-Expose-Headers: Content-Range');

        // if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
        //     exit(0);
        // }
    }

    public function beforeRender(Event $event)  {
        $this->setCorsHeaders();

        $this->RequestHandler->renderAs($this, 'json');
        $this->response->type('application/json');
        $this->set('_serialize', true);
    }
}
