<?php
class FunctionalTests extends \PHPUnit\Framework\TestCase
{
    /**
     * @param $versionPath
     * @param string $arguments
     * @return string
     */
    private function generateAnalyseCommand($versionPath, $arguments = '')
    {
        $baseDir = BASE_DIR;
        $command = "php {$baseDir}/bin/patch-helper.php analyse $arguments {$baseDir}{$versionPath}";
        echo PHP_EOL . "Generated command: $command" . PHP_EOL;
        return $command;
    }

    /**
     * @group v22
     */
    public function testMagentoTwoTwo()
    {
        $this->assertFileExists(BASE_DIR . '/dev/instances/magento22/app/etc/env.php', "Magento 2.2 is not installed");

        exec($this->generateAnalyseCommand('/dev/instances/magento22', '--sort-by-type --vendor-namespaces Ampersand'), $output, $return);
        $this->assertEquals(0, $return, "The return code of the command was not zero");

        $lastLine = array_pop($output);
        $this->assertStringStartsWith('You should review the above', $lastLine);

        $output = implode(PHP_EOL, $output);

        $this->assertEquals(\file_get_contents(BASE_DIR . '/dev/phpunit/functional/expected_output/magento22.out.txt'), $output);
    }

    /**
     * @link https://github.com/AmpersandHQ/ampersand-magento2-upgrade-patch-helper/issues/9
     * @depends testMagentoTwoTwo
     * @group v22
     */
    public function testVirtualTypesNoException()
    {
        copy(
            BASE_DIR . '/dev/phpunit/functional/resources/reflection-exception.diff',
            BASE_DIR . '/dev/instances/magento22/vendor.patch'
        );
        $this->assertFileEquals(
            BASE_DIR . '/dev/phpunit/functional/resources/reflection-exception.diff',
            BASE_DIR . '/dev/instances/magento22/vendor.patch',
            "vendor.patch did not update for this test"
        );

        exec($this->generateAnalyseCommand('/dev/instances/magento22'), $output, $return);
        $this->assertEquals(0, $return, "The return code of the command was not zero");
    }

    /**
     * @group v23
     */
    public function testMagentoTwoThree()
    {
        $this->assertFileExists(BASE_DIR . '/dev/instances/magento23/app/etc/env.php', "Magento 2.3 is not installed");

        exec($this->generateAnalyseCommand('/dev/instances/magento23', '--sort-by-type --vendor-namespaces Ampersand'), $output, $return);
        $this->assertEquals(0, $return, "The return code of the command was not zero");

        $lastLine = array_pop($output);
        $this->assertStringStartsWith('You should review the above', $lastLine);

        $output = implode(PHP_EOL, $output);

        $this->assertEquals(\file_get_contents(BASE_DIR . '/dev/phpunit/functional/expected_output/magento23.out.txt'), $output);
    }

    /**
     * @group v23
     */
    public function testMagentoTwoThreeShowCustomModules()
    {
        $this->assertFileExists(BASE_DIR . '/dev/instances/magento23/app/etc/env.php', "Magento 2.3 is not installed");

        exec($this->generateAnalyseCommand('/dev/instances/magento23', '--sort-by-type --vendor-namespaces Ampersand,Amazon'), $output, $return);
        $this->assertEquals(0, $return, "The return code of the command was not zero");

        $lastLine = array_pop($output);
        $this->assertStringStartsWith('You should review the above', $lastLine);

        $output = implode(PHP_EOL, $output);

        $this->assertEquals(\file_get_contents(BASE_DIR . '/dev/phpunit/functional/expected_output/magento23VendorNamespaces.out.txt'), $output);
    }

    /**
     * @link https://github.com/AmpersandHQ/ampersand-magento2-upgrade-patch-helper/pull/27
     * @depends testMagentoTwoThree
     * @group v23
     */
    public function testAutoApplyPatches()
    {
        copy(
            BASE_DIR . '/dev/phpunit/functional/resources/template-change.diff',
            BASE_DIR . '/dev/instances/magento23/vendor.patch'
        );
        $this->assertFileEquals(
            BASE_DIR . '/dev/phpunit/functional/resources/template-change.diff',
            BASE_DIR . '/dev/instances/magento23/vendor.patch',
            "vendor.patch did not update for this test"
        );

        exec($this->generateAnalyseCommand('/dev/instances/magento23', '--auto-theme-update 5'), $output, $return);

        exec($this->generateAnalyseCommand('/dev/instances/magento23'), $output, $return);

        $this->assertEquals(0, $return);
        $this->assertFileEquals(
            BASE_DIR . '/dev/phpunit/functional/expected_output/auto-apply-patch.txt',
            BASE_DIR . '/dev/instances/magento23/app/design/frontend/Ampersand/theme/Magento_Bundle/templates/js/components.phtml',
            "This file did not get auto patched properly"
        );
    }

    /**
     * @link https://github.com/AmpersandHQ/ampersand-magento2-upgrade-patch-helper/issues/9
     * @depends testMagentoTwoThree
     * @group v23
     */
    public function testUnifiedDiffIsProvided()
    {
        copy(
            BASE_DIR . '/dev/phpunit/functional/resources/not-a-unified-diff.txt',
            BASE_DIR . '/dev/instances/magento23/vendor.patch'
        );
        $this->assertFileEquals(
            BASE_DIR . '/dev/phpunit/functional/resources/not-a-unified-diff.txt',
            BASE_DIR . '/dev/instances/magento23/vendor.patch',
            "vendor.patch did not update for this test"
        );

        exec($this->generateAnalyseCommand('/dev/instances/magento23'), $output, $return);
        $this->assertEquals(1, $return);
    }
}
