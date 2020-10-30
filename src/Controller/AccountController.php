<?php
namespace App\Controller;

use App\Controller\AppController;
use Cake\Datasource\ConnectionManager;
use Cake\ORM\Query;

use Cake\Mailer\Email;
use Cake\Auth\DefaultPasswordHasher;
use Cake\Utility\Security;
use Cake\ORM\TableRegistry;
use Illuminate\Http\Request;
use Cake\Network\Exception\UnauthorizedException;

class AccountController extends AppController  {
    public function index()  {
        $this->paginate = [
            'contain' => ['Users', 'Coms'],
        ];
        $account = $this->paginate($this->Account);

        $this->set(compact('account'));
    }

    public function view($id = null)  {
        $account = $this->Account->get($id, [
            'contain' => ['Users', 'Coms'],
        ]);

        $this->set('account', $account);
    }

    public function verification($token)  {
        $verify = $this->Account -> find('all')->where(['token'=>$token])->first();
        $verify->verified = 1;
        $this->Account->save($verify);
        $this->redirect('http://localhost:3000');
    }

    public function login()  {
        $res = array();
        if($this->request->is('post'))  {
            $account = $this->Auth->identify();
            if($account)  {
                $this->Auth->setUser($account);
                $res['status'] = 1;
                $res['msg'] = 'login successful';
                $res['data'] = $account;
            }
            else  {
                $res['status'] = 0;
                $res['msg'] = 'Your username or password is incorrect';
                $res['data'] = NULL;
            }
        }
        $this->set(compact('res'));
        $this->set('_serialize', ['res']);
    }

    public function logout()  {
        $res = array();
        if($this->Auth->logout())  {
            $res['status'] = 1;
            $res['msg'] = 'OK';
        }

        $this->set(compact('res'));
        $this->set('_serialize', ['res']);
    }

    public function forgotpassword()  {
        $res = array();
        if($this->request->is('post'))  {
            $myemail = $this->request->getData('email');
            $mytoken = Security::hash(Security::randomBytes(25));

            $connection = ConnectionManager::get('default');
            $results = $connection->execute('SELECT * FROM Users WHERE email = :email', ['email' => $myemail])
            ->fetchAll('assoc');

            $userId =  (int) $results[0]['id'];
            $result = $connection->update('Account', ['password' => '','token'=> $mytoken], ['user_id' => $userId]);

            if($result)  {
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

            $userTable = TableRegistry::get('Account');
            $user = $userTable->find('all')->where(['token'=>$token])->first();
            $user->password =$mypass;
            if($userTable->save($user))  {
                $res['status'] = 1;
            }
        }

        $this->set(compact('res'));
        $this->set('_serialize', ['res']);
    }

    public function add()  {
        $account = $this->Account->newEntity();
        if ($this->request->is('post')) {
            $account = $this->Account->patchEntity($account, $this->request->getData());
            if ($this->Account->save($account)) {
                $this->Flash->success(__('The account has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The account could not be saved. Please, try again.'));
        }
        $users = $this->Account->Users->find('list', ['limit' => 200]);
        $coms = $this->Account->Coms->find('list', ['limit' => 200]);
        $this->set(compact('account', 'users', 'coms'));
    }

    public function edit($id = null)
    {
        $account = $this->Account->get($id, [
            'contain' => [],
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $account = $this->Account->patchEntity($account, $this->request->getData());
            if ($this->Account->save($account)) {
                $this->Flash->success(__('The account has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The account could not be saved. Please, try again.'));
        }
        $users = $this->Account->Users->find('list', ['limit' => 200]);
        $coms = $this->Account->Coms->find('list', ['limit' => 200]);
        $this->set(compact('account', 'users', 'coms'));
    }

    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $account = $this->Account->get($id);
        if ($this->Account->delete($account)) {
            $this->Flash->success(__('The account has been deleted.'));
        } else {
            $this->Flash->error(__('The account could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
