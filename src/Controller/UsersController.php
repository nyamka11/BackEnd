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
        $users = $this->paginate($this->Users);
        $this->set(compact('users'));
        $this->set('_serialize', ['users']);
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

    public function login()  {
        $res = array();
        if($this->request->is('post'))  {
            $user = $this->Auth->identify();
            if($user)  {
                $this->Auth->setUser($user);
                $res['status'] = 1;
                $res['msg'] = 'login successful';
            }
            else  {
                $res['status'] = 0;
                $res['msg'] = 'Your username or password is incorrect';
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

    public function register()  {
        $res = array();
        if($this->request->is('post'))  {
            $companyname = $this -> request -> getData(['companyname']);
            $guarantorname = $this -> request -> getData(['guarantorname']);
            $postcode = $this -> request -> getData(['postcode']);
            $address1 = $this -> request -> getData(['address1']);
            $address2 = $this -> request -> getData(['address2']);
            $address3 = $this -> request -> getData(['address3']);
            $guarantorphonenumber = $this -> request -> getData(['guarantorphonenumber']);
            $cellphone = $this -> request -> getData(['cellphone']);
            $myemail = trim($this -> request -> getData(['email']));

            $companyTable = tableRegistry::get('company');
            $company = $companyTable -> newEntity();
            $company->companyname = $companyname;
            $company->guarantorname = $guarantorname;
            $company->postcode = $postcode;
            $company->address1 = $address1;
            $company->address2 = $address2;
            $company->address3 = $address3;
            $company->guarantorphonenumber = $guarantorphonenumber;
            $company->cellphone = $cellphone;
            $company->email = $myemail;

            $result = $companyTable->save($company);
            $comId = $result->id;

            if($comId > 0)  {  //compnay register successful

            }
            else  {
                $res['status'] = 0;
                $res['msg'] = 'Company register failed, please try again.';
                $this->set(compact('res'));
                $this->set('_serialize', ['res']);
                exit();
            }

            //----- user -----
            $userTable = tableRegistry::get('Users');
            $user = $userTable -> newEntity();

            $hasher = new DefaultPasswordHasher();
            $mypass = '1200'; //password hiine
            $mytoken = Security::hash(Security::randomBytes(32));

            $user->email = $myemail;
            $user->username = $myemail;  // turzuur email hayagaar ni hiiw mail hayag ni dawhar orj bgaa
            $user->company_id = $comId;
            $user->password = $hasher->hash($mypass);
            $user->token = $mytoken;

            if($userTable->save($user))  { 
                $res['status'] = 1;
                $res['msg'] = 'User register successful, your confirmation email has been sent.';

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
                    'comId:'.$comId.' ------ '.$guarantorname.'<br/>Please confirm your email link below<br/>
                    <a href="http://localhost/backEnd/users/verification/'.$mytoken.'">Verification Email</a><br/>
                    Thank you for joining us'
                );
            }
            else  {
                $company = $companyTable->get($comId);
                $companyTable->delete($company);

                $res['status'] = 0;
                $res['msg'] = 'User register failed, please try again.';
            }
        }

        $this->set(compact('res'));
        $this->set('_serialize', ['res']);
    }

    public function verification($token)  {
        $userTable = tableRegistry::get('Users');
        $verify = $userTable -> find('all')->where(['token'=>$token])->first();
        $verify->verified = '1';
        $userTable->save($verify);
        $this->redirect('http://localhost:3000?verified=1');
    }

    /**
     * View method
     *
     * @param string|null $id User id.
     * @return \Cake\Network\Response|null
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */

    public function view($id = null)  {
        $user = $this->Users->get($id, [
            'contain' => []
        ]);

        $this->set('user', $user);
        $this->set('_serialize', ['user']);
    }

    /**
     * Add method
     *
     * @return \Cake\Network\Response|void Redirects on successful add, renders view otherwise.
     */
    public function add()  {
		$res = array();
        $user = $this->Users->newEntity();
        if ($this->request->is('post')) {
            $user = $this->Users->patchEntity($user, $this->request->data);
            if ($this->Users->save($user)) {
				// Remove flash and redirections
				$res['status'] = 1;
                $res['msg'] = 'The user has been saved.';
            } else {
				$res['status'] = 0;
                $res['msg'] = 'The user could not be saved. Please, try again.';
            }
        }
        $this->set(compact('res'));
        $this->set('_serialize', ['res']);
    }

    /**
     * Edit method
     *
     * @param string|null $id User id.
     * @return \Cake\Network\Response|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function edit($id = null)  {
        // $this->getEventManager()->off($this->Csrf);
        $user = $this->Users->get($id, [
            'contain' => []
        ]);

		$res = array();
        if ($this->request->is(['patch', 'post', 'put'])) {
            $user = $this->Users->patchEntity($user, $this->request->data);
            if ($this->Users->save($user)) {
				$res['status'] = 1;
				$res['msg'] = 'User updated successfully';
            } 
            else {
				$res['status'] = 0;
                $res['msg'] = 'The user could not be saved. Please, try again.';
            }
        }
        $this->set(compact('res'));
        $this->set('_serialize', ['res']);
    }

    /**
     * Delete method
     *
     * @param string|null $id User id.
     * @return \Cake\Network\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)  {
		$res = array();
        $this->request->allowMethod(['post', 'delete']);
        $user = $this->Users->get($id);
        if ($this->Users->delete($user)) {
			$res['status'] = 1;
            $res['msg'] = 'The user has been deleted.';
        } 
        else  {
			$res['status'] = 0;
            $res['msg'] = 'The user could not be deleted. Please, try again.';
        }

		$this->set(compact('res'));
        $this->set('_serialize', ['res']);
    }
}