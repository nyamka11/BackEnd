<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Company Model
 *
 * @property \App\Model\Table\UsersTable&\Cake\ORM\Association\HasMany $Users
 *
 * @method \App\Model\Entity\Company get($primaryKey, $options = [])
 * @method \App\Model\Entity\Company newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\Company[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Company|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Company saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Company patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\Company[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\Company findOrCreate($search, callable $callback = null, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class CompanyTable extends Table
{
    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config)
    {
        parent::initialize($config);

        $this->setTable('company');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        // $this->hasMany('Users', [
        //     'foreignKey' => 'company_id',
        // ]);
    }

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator)
    {
        $validator
            ->integer('id')
            ->allowEmptyString('id', null, 'create');

        $validator
            ->scalar('companyname')
            ->maxLength('companyname', 255)
            ->requirePresence('companyname', 'create')
            ->notEmptyString('companyname');

        $validator
            ->scalar('guarantorname')
            ->maxLength('guarantorname', 255)
            ->requirePresence('guarantorname', 'create')
            ->notEmptyString('guarantorname');

        $validator
            ->scalar('postcode')
            ->maxLength('postcode', 255)
            ->requirePresence('postcode', 'create')
            ->notEmptyString('postcode');

        $validator
            ->scalar('address1')
            ->maxLength('address1', 255)
            ->requirePresence('address1', 'create')
            ->notEmptyString('address1');

        $validator
            ->scalar('address2')
            ->maxLength('address2', 255)
            ->requirePresence('address2', 'create')
            ->notEmptyString('address2');

        $validator
            ->scalar('address3')
            ->maxLength('address3', 255)
            ->requirePresence('address3', 'create')
            ->notEmptyString('address3');

        $validator
            ->scalar('guarantorphonenumber')
            ->maxLength('guarantorphonenumber', 255)
            ->requirePresence('guarantorphonenumber', 'create')
            ->notEmptyString('guarantorphonenumber');

        $validator
            ->scalar('cellphone')
            ->maxLength('cellphone', 255)
            ->requirePresence('cellphone', 'create')
            ->notEmptyString('cellphone');

        $validator
            ->email('email')
            ->requirePresence('email', 'create')
            ->notEmptyString('email');

        $validator
            ->scalar('group_type')
            ->requirePresence('group_type', 'create')
            ->notEmptyString('group_type');

        return $validator;
    }

    /**
     * Returns a rules checker object that will be used for validating
     * application integrity.
     *
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules)
    {
        $rules->add($rules->isUnique(['email']));

        return $rules;
    }
}
