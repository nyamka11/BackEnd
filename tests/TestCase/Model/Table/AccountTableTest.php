<?php
namespace App\Test\TestCase\Model\Table;

use App\Model\Table\AccountTable;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\AccountTable Test Case
 */
class AccountTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\AccountTable
     */
    public $Account;

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'app.Account',
        'app.Users',
        'app.Coms',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $config = TableRegistry::getTableLocator()->exists('Account') ? [] : ['className' => AccountTable::class];
        $this->Account = TableRegistry::getTableLocator()->get('Account', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->Account);

        parent::tearDown();
    }

    /**
     * Test initialize method
     *
     * @return void
     */
    public function testInitialize()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test validationDefault method
     *
     * @return void
     */
    public function testValidationDefault()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test buildRules method
     *
     * @return void
     */
    public function testBuildRules()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
