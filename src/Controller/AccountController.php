<?php
namespace App\Controller;

use App\Controller\AppController;

/**
 * Account Controller
 *
 * @property \App\Model\Table\AccountTable $Account
 *
 * @method \App\Model\Entity\Account[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class AccountController extends AppController  {
    /**
     * Index method
     *
     * @return \Cake\Http\Response|null
     */

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

        // $accountTable = $this->Account->find('all');
        // $accountTable->select(['account.com_id','account.username','account.token']);
        // $accountTable->select(['user.id','user.email','user.name','user.phone']);
        // $accountTable->join([
        //     'table' => 'users',
        //     'alias' => 'user',
        //     'type' => 'INNER',
        //     'conditions' => 'user.id = account.user_id AND account.com_id=317',
        // ]);

        // $this->set(compact('accountTable'));


        // return;
        // $res = array();
        // if($this->request->getData('post'))
        $myemail = $this->request->getData('email');
        // $mytoken = Security::hash(Security::randomBytes(25));

        // $accountTable = TableRegistry::get('Account');
        // $account = $accountTable->find('all')->where(['email'=>$myemail])->first();
        // $account->password = '';
        // $account->token = $mytoken;

        
            $accountTable = $this->Account->find('all');
            $accountTable->select(['account.com_id','account.username','account.token']);
            $accountTable->select(['user.id','user.email','user.name','user.phone']);
            $accountTable->join([
                'table' => 'users',
                'alias' => 'user',
                'type' => 'INNER',
                'conditions' => "user.id = account.user_id AND user.email='".$myemail."'"
            ]);
            $accountTable->first();

            $accountTable->password = '';
            // $accountTable->token = $mytoken;

            $this->Account->save($accountTable);
            $this->set(compact('accountTable'));
            return;


        if($accountTable->save($account))  {
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
