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
        $users = $this->Users->find('all');
        $this->set([
            'data'=>$users,
            'status' => 1,
            '_serialize' => ['users']
        ]);
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
        $email = $this->request->getData(['email']);
        $name = $this->request->getData(['name']);
        $phone = $this->request->getData(['phone']);

        $userTable = tableRegistry::get('Users');
        $user = $userTable -> newEntity();

        $user->company_id = $comId;
        $user->email = $email;
        $user->name = $name;
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