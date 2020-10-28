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
 * Company Controller
 *
 * @property \App\Model\Table\CompanyTable $Company
 *
 * @method \App\Model\Entity\Company[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class CompanyController extends AppController
{
    /**
     * Index method
     *
     * @return \Cake\Http\Response|null
     */
    public function index()
    {
        $company = $this->paginate($this->Company);

        $this->set(compact('company'));
    }

    /**
     * View method
     *
     * @param string|null $id Company id.
     * @return \Cake\Http\Response|null
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $company = $this->Company->get($id, [
            'contain' => ['Users'],
        ]);

        $this->set('company', $company);
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null Redirects on successful add, renders view otherwise.
     */
    public function add()  {  //Register
        $res = array();
        $jsonData = $this->request->input('json_decode');
        $companyname = $jsonData->companyname;
        $guarantorname = $jsonData->guarantorname;
        $postcode = $jsonData->postcode;
        $address1 = $jsonData->address1;
        $address2 = $jsonData->address2;
        $address3 = $jsonData->address3;
        $guarantorphonenumber = $jsonData->guarantorphonenumber;
        $cellphone = $jsonData->cellphone;
        $cellphone = $jsonData->cellphone;
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

        //** ----------------- User start------------------------ */
        $userTable = tableRegistry::get('Users');
        $user = $userTable -> newEntity();
        $user->email = $myemail;
        $user->name = $guarantorname;
        $user->company_id = $comId;
        $user->phone = $cellphone;

        if(!$userTable->save($user))  {
            $company = $companyTable->get($comId);
            $companyTable->delete($company);

            $res['status'] = 0;
            $res['msg'] = 'User register failed, please try again.';
            $this->set(compact('res'));
            return;
        }

        $userId = (int) $user['id'];

        // //** ----------------- Account start------------------------ */
        $accountTable = tableRegistry::get('account');
        $account = $accountTable -> newEntity();

        $hasher = new DefaultPasswordHasher();
        $mypass = '1200'; //password hiine
        $mytoken = Security::hash(Security::randomBytes(32));

        $account->user_id = $userId;
        $account->com_id = $comId;
        $account->username = $myemail;  // turzuur email hayagaar ni hiiw mail hayag ni dawhar orj bgaa
        $account->password = $hasher->hash($mypass);
        $account->token = $mytoken;
        $account->verified = 0;
        $account->created = date('Y-m-d H:i:s');

        if($accountTable->save($account))  { 
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
                <a href="http://localhost/backEnd/account/verification/'.$mytoken.'">Verification Email</a><br/>
                Thank you for joining us'
            );

            $res['status'] = 1;
            $res['msg'] = 'User register successful, your confirmation email has been sent.';
        }
        else  {
            $company = $companyTable->get($comId);
            $companyTable->delete($company);

            $user = $userTable->get($userId);
            $userTable->delete($user);
        }
        // ** ----------------- User account---------- */
        $this->set(compact('res'));
    }

    /**
     * Edit method
     *
     * @param string|null $id Company id.
     * @return \Cake\Http\Response|null Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $company = $this->Company->get($id, [
            'contain' => [],
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $company = $this->Company->patchEntity($company, $this->request->getData());
            if ($this->Company->save($company)) {
                $this->Flash->success(__('The company has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The company could not be saved. Please, try again.'));
        }
        $this->set(compact('company'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Company id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $company = $this->Company->get($id);
        if ($this->Company->delete($company)) {
            $this->Flash->success(__('The company has been deleted.'));
        } else {
            $this->Flash->error(__('The company could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
