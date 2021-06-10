<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace CM\Payments\Test\Integration;

use Magento\Framework\Filesystem\DirectoryList;
use Magento\TestFramework\ObjectManager;
use PHPUnit\Framework\TestCase;

class IntegrationTestCase extends TestCase
{
    /**
     * @var ObjectManager
     */
    protected $objectManager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->objectManager = ObjectManager::getInstance();
    }
    /**
     * Load a custom fixture in the Test/Fixtures folder, and make it think it's inside the
     * `dev/test/integration/testsuite` folder so it can rely on other fixtures.
     *
     * @param $path
     * @throws \Exception
     */
    public function loadFixture($path)
    {
        $cwd = getcwd();

        $fullPath = __DIR__ . '/../Fixtures/' . $path;
        if (!file_exists($fullPath)) {
            throw new \Exception('The path "' . $fullPath . '" does not exists');
        }

        chdir($this->getRootDirectory() . '/dev/tests/integration/testsuite/');
        require $fullPath;
        chdir($cwd);
    }

    protected function getRootDirectory()
    {
        static $path;

        if (!$path) {
            $directoryList = $this->objectManager->get(DirectoryList::class);
            $path = $directoryList->getRoot();
        }

        return $path;
    }
}
