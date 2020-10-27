<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Company Entity
 *
 * @property int $id
 * @property string $companyname
 * @property string $guarantorname
 * @property string $postcode
 * @property string $address1
 * @property string $address2
 * @property string $address3
 * @property string $guarantorphonenumber
 * @property string $cellphone
 * @property string $email
 * @property string $group_type
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime $modified
 *
 * @property \App\Model\Entity\User[] $users
 */
class Company extends Entity
{
    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * Note that when '*' is set to true, this allows all unspecified fields to
     * be mass assigned. For security purposes, it is advised to set '*' to false
     * (or remove it), and explicitly make individual fields accessible as needed.
     *
     * @var array
     */
    protected $_accessible = [
        'companyname' => true,
        'guarantorname' => true,
        'postcode' => true,
        'address1' => true,
        'address2' => true,
        'address3' => true,
        'guarantorphonenumber' => true,
        'cellphone' => true,
        'email' => true,
        'group_type' => true,
        'created' => true,
        'modified' => true,
        'users' => true,
    ];
}
