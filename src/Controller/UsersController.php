<?php
namespace App\Controller;

use App\Controller\AppController;

use Cake\Mailer\Email;
use Cake\Auth\DefaultPasswordHasher;
use Cake\Utility\Security;
use Cake\ORM\TableRegistry;
use Illuminate\Http\Request;
use Cake\Network\Exception\UnauthorizedException;

/**
 * Users Controller
 *
 * @property \App\Model\Table\UsersTable $Users
 */



class UsersController extends AppController  {  

    /**
     * Index method
     *
     * @return \Cake\Network\Response|null
     */
    public function index()  {
        $users = $this->Users->find('all');
        $this->set([
            'data'=>$users,
            'status' => 1,
            '_serialize' => ['users']
        ]);
    }

    public function forgotpassword()  {
        $res = array();
        if($this->request->getData('post'));
        $myemail = $this->request->getData('email');
        $mytoken = Security::hash(Security::randomBytes(25));

        $userTable = TableRegistry::get('Users');
        $user = $userTable->find('all')->where(['email'=>$myemail])->first();
        $user->password = '';
        $user->token = $mytoken;

        if($userTable->save($user))  {
            $res['status'] = 1;
            $res['msg'] = 'Reset password link has been to your email('.$myemail.'), please open your indox';

            Email::configTransport('mailtrap', [
                'host' => 'smtp.mailtrap.io',
                'port' => 2525,
                'username' => '507e5493f6ad0c',
                'password' => '855f891440d4c8',
                'className' => 'Smtp'
            ]);

            $email = new Email('default');
            $email -> transport('mailtrap');
            $email -> emailFormat('html');
            $email -> from('unyamka@gmail.com', 'U.N');
            $email -> subject('Please confirm your email to activation your account');
            $email -> to($myemail);
            $email -> send(
                'Hello '.$myemail.'<br/>Please click link below to reset your password<br/>
                <a href="http://localhost:3000/resetpassword?mt='.$mytoken.'">Reset Password</a><br/>'
            );
        }
        else {
            $res['status'] = 0;
            $res['msg'] = '('.$myemail.') ない';
        }

        $this->set(compact('res'));
        $this->set('_serialize', ['res']);
    }

    public function resetpassword()  {
        $res = array();
        if($this->request->is('post'))  {
            $hasher = new DefaultPasswordHasher();
            $mypass = $hasher->hash($this->request->getData('password'));
            $token = $this->request->getData('token');

            $userTable = TableRegistry::get('Users');
            $user = $userTable->find('all')->where(['token'=>$token])->first();
            $user->password =$mypass;
            if($userTable->save($user))  {
                $res['status'] = 1;
            }
        }

        $this->set(compact('res'));
        $this->set('_serialize', ['res']);
    }

    public function view($id = null)  {
        $user = $this->Users->get($id, [
            'contain' => []
        ]);

        $this->set([
            'data'=>$user,
            'status' => 1,
            '_serialize' => ['user']
        ]);
    }

    public function add()  {
        $comId = $this->request->getData(['comId']);
        $username = $this->request->getData(['username']);
        $email = $this->request->getData(['email']);
        $name = $this->request->getData(['name']);
        $phone = $this->request->getData(['phone']);

        $userTable = tableRegistry::get('Users');
        $user = $userTable -> newEntity();

        $user->company_id = $comId;
        $user->email = $email;
        $user->name = $name;
        $user->username = $username;  // turzuur email hayagaar ni hiiw mail hayag ni dawhar orj bgaa
        $user->phone = $phone;

        if($userTable->save($user))  {
            $message = 'Saved';
        } 
        else  {
            $message = 'Error';
        }

        $this->set([
            'message' => $message,
            'data' => $user,
            '_serialize' => ['message', 'data']
        ]);
    }

    public function edit($id = null)  {
        $usersTable = TableRegistry::get('Users');
        $user = $usersTable->get($id);
        $user->name = $this->request->getData(["name"]);
        $user->username = $this->request->getData(["username"]);
        $user->phone = $this->request->getData(["phone"]);
        $user->email = $this->request->getData(["email"]);
        $user->modified = date('Y-m-d H:i:s');

        if ($this->Users->save($user))  {
            $message = 'Edited';
        }
        else  {
            $message = 'Error';
        }

        $this->set([
            'messagedd' => $message,
            '_serialize' => ['message']
        ]);
    }

    public function delete($id = null)  {
        $this->request->allowMethod(['delete']);
        $user = $this->Users->get($id);
        $message = 'The user has been deleted';
        if (!$this->Users->delete($user))  {
            $message = 'The user could not be deleted. Please, try again';
        }

        $this->set([
            'message' => $message,
            '_serialize' => ['message']
        ]);
    }
}