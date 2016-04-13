<?php

/*
 * Copyright (c) 2016 Lp digital system
 *
 * This file is part of GoogleCustomSearchBundle.
 *
 * GoogleCustomSearchBundle is free bundle: you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as published
 * by the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * GoogleCustomSearchBundle is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with GoogleCustomSearchBundle. If not, see <http://www.gnu.org/licenses/>.
 */

namespace LpDigital\Bundle\GoogleCustomSearchBundle\Tests;

use org\bovigo\vfs\vfsStream;
use Symfony\Component\Security\Acl\Dbal\Schema;

use BackBee\Security\Token\BBUserToken;
use BackBee\Security\User;
use BackBee\Tests\Mock\MockBBApplication;

use LpDigital\Bundle\GoogleCustomSearchBundle\GoogleCustomSearch;

/**
 * Test case for GoogleCustomSearch bundle.
 *
 * @author Charles Rouillon <charles.rouilon@lp-digital.fr>
 */
class GcseTestCase extends \PHPUnit_Framework_TestCase
{

    /**
     * @var GoogleCustomSearch
     */
    protected $gcse;

    /**
     * Sets up the required fixtures.
     */
    public function setUp()
    {
        $mockConfig = [
            'ClassContent' => [],
            'Config' => [
                'bootstrap.yml' => file_get_contents(__DIR__ . '/Config/bootstrap.yml'),
                'bundles.yml' => file_get_contents(__DIR__ . './Config/bundles.yml'),
                'config.yml' => file_get_contents(__DIR__ . '/Config/config.yml'),
                'doctrine.yml' => file_get_contents(__DIR__ . '/Config/doctrine.yml'),
                'logging.yml' => file_get_contents(__DIR__ . '/Config/logging.yml'),
                'security.yml' => file_get_contents(__DIR__ . '/Config/security.yml'),
                'services.yml' => file_get_contents(__DIR__ . '/Config/services.yml'),
            ],
            'Ressources' => [],
            'cache' => [
                'Proxies' => [],
                'twig' => []
            ],
        ];
        vfsStream::umask(0000);
        vfsStream::setup('repositorydir', 0777, $mockConfig);

        $mockApp = new MockBBApplication(null, null, false, $mockConfig, __DIR__ . '/../vendor');
        $this->gcse = $mockApp->getBundle('gcse');
    }

    /**
     * Creates a user for the specified group and authenticates a BBUserToken with the newly created user.
     * Note that the token is setted into application security context.
     */
    protected function createAuthenticatedUser(array $roles = ['ROLE_API_USER'])
    {
        $user = new User();
        $user
                ->setEmail('admin@backbee.com')
                ->setLogin('admin')
                ->setPassword('pass')
                ->setApiKeyPrivate(uniqid('PRIVATE', true))
                ->setApiKeyPublic(uniqid('PUBLIC', true))
                ->setApiKeyEnabled(true)
        ;

        $token = new BBUserToken($roles);
        $token->setAuthenticated(true);
        $token
                ->setUser($user)
                ->setCreated(new \DateTime())
                ->setLifetime(300)
        ;

        $this->gcse->getApplication()->getSecurityContext()->setToken($token);
    }

    /**
     * Reset the ACL tables schema.
     */
    protected function resetAclSchema()
    {
        $conn = $this->gcse->getApplication()->getEntityManager()->getConnection();

        $tablesMapping = [
            'class_table_name' => 'acl_classes',
            'entry_table_name' => 'acl_entries',
            'oid_table_name' => 'acl_object_identities',
            'oid_ancestors_table_name' => 'acl_object_identity_ancestors',
            'sid_table_name' => 'acl_security_identities',
        ];

        foreach ($tablesMapping as $tableName) {
            $conn->executeQuery(sprintf('DROP TABLE IF EXISTS %s', $tableName));
        }

        $schema = new Schema($tablesMapping);

        $platform = $conn->getDatabasePlatform();

        foreach ($schema->toSql($platform) as $query) {
            $conn->executeQuery($query);
        }
    }
}
