<?php
namespace App\Controller;

use App\Controller\AppController;
use Cake\Mailer\Email;
use Cake\Auth\DefaultPasswordHasher;
use Cake\Utility\Security;
use Cake\ORM\TableRegistry;
use Illuminate\Http\Request;
use Cake\Network\Exception\UnauthorizedException;

class UsersController extends AppController  {

    public function index()  {
        // $data = $this->paginate($this->Users);
        // $this->set(compact('data'));
        // $this->set('_serialize', ['data']);

        // $res = array();
        // $result = $this->Users->find('all'); 
        // $res['data'] = $result;
        // $res['total'] = 5;

        // $this->set(compact('res'));
        // $this->set('_serialize', ['res']);

        $users = $this->Users->find('all');
        $this->set([
            'data'=>$users,
            'status' => 'ok',
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

    public function login()  {
        $res = array();
        if($this->request->is('post'))  {
            $user = $this->Auth->identify();
            if($user)  {
                $this->Auth->setUser($user);
                $res['status'] = 1;
                $res['msg'] = 'login successful';
                $res['data'] = $user;
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

    public function register()  {
        $res = array();
        if($this->request->is('post'))  {
            $res = array();
            $jsonData = $this->request->input('json_decode');
            $companyname = $jsonData->companyName;
            $guarantorname = $jsonData->guarantorName;
            $postcode = $jsonData->postCode;
            $address1 = $jsonData->address1;
            $address2 = $jsonData->address2;
            $address3 = $jsonData->address3;
            $guarantorphonenumber = $jsonData->guarantorPhoneNumber;
            $cellphone = $jsonData->cellPhone;
            $myemail = $jsonData->email;

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
            $company->created = date('Y-m-d H:i:s');
            $result = $companyTable->save($company);

            if(!$result)  {  //compnay register successful
                $res['status'] = 0;
                $res['msg'] = 'Company register failed, please try again.';
                $this->set(compact('res'));
                return;
            }
            $comId = (int) $company['id'];  //登録した会社IDです。

            //** ----------------- User start---------- */

            $userTable = tableRegistry::get('Users');
            $user = $userTable -> newEntity();

            $hasher = new DefaultPasswordHasher();
            $mypass = '1200'; //password hiine
            $mytoken = Security::hash(Security::randomBytes(32));

            $user->email = $myemail;
            $user->name = $guarantorname;
            $user->username = $myemail;  // turzuur email hayagaar ni hiiw mail hayag ni dawhar orj bgaa
            $user->company_id = $comId;
            $user->password = $hasher->hash($mypass);
            $user->token = $mytoken;
            $user->phone = $cellphone;
            $user->level = "admin";
            $user->created = time();

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
                    <a href="http://localhost:8765/users/verification/'.$mytoken.'">Verification Email</a><br/>
                    Thank you for joining us'
                );
            }
            else  {
                $company = $companyTable->get($comId);
                $companyTable->delete($company);

                $res['status'] = 0;
                $res['msg'] = 'User register failed, please try again.';
            }
            
            //** ----------------- User end---------- */
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
        $username = $this->request->getData(['username']);
        $email = $this->request->getData(['email']);
        $name = $this->request->getData(['name']);
        $phone = $this->request->getData(['phone']);
        $comId = $this->request->getData(['comId']);
        $authorId = $this->request->getData(['authorId']);

        $userTable = tableRegistry::get('Users');
        $user = $userTable -> newEntity();

        $hasher = new DefaultPasswordHasher();
        $mypass = '1200'; //password hiine
        $mytoken = Security::hash(Security::randomBytes(32));

        $res = array();
        $user->email = $email;
        $user->name = $name;
        $user->username = $username;  // turzuur email hayagaar ni hiiw mail hayag ni dawhar orj bgaa
        $user->phone = $phone;
        $user->password = $hasher->hash($mypass);
        $user->token = $mytoken;
        $user->company_id = $comId;
        $user->author_id = $authorId;

        if($userTable->save($user))  {
            $res['status'] = 1;
            $res['msg'] = 'User register successful, your confirmation email has been sent.';
        }
        else  {
            $res['status'] = 0;
            $res['msg'] = 'User register failed, please try again.';
        }

        $this->set(compact('res'));
    }

    public function edit($id = null)  {
        $usersTable = TableRegistry::get('Users');
        $user = $usersTable->get($id);
        $user->name = $this->request->getData(["name"]);
        $user->username = $this->request->getData(["username"]);
        $user->phone = $this->request->getData(["phone"]);
        $user->email = $this->request->getData(["email"]);

        if ($this->Users->save($user))  {
            $res['status'] = 1;
            $res['msg'] = 'User edit successful';
        }
        else  {
            $res['status'] = 0;
            $res['msg'] = 'User edit failed, please try again.';
        }

        $this->set(compact('res'));
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