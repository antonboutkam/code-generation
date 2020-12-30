<?php /*** @unfixed* */

namespace Generator\Generators\Admin\Public_html;

use Cli\Tools\VO\SystemBuildConfig;
use Generator\Generators\GeneratorInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

final class PublicStructure implements GeneratorInterface{

    function __construct(PublicStructureConfigInterface $oPublicStructureConfig, OutputInterface $output) {
        if (!isset($_ENV['SYSTEM_ROOT'])) {
            exit('To run this command the environment variable SYSTEM_ROOT must be set.');
        }
    }

    function generate() {
        $aDirectories = [
            $_ENV['SYSTEM_ROOT'] . '/admin_public_html/custom/' . $oSystemBuildVo->getSystemId(),
        ];

        foreach ($aDirectories as $sDirectory) {
            if (!is_dir($sDirectory)) {
                echo "Creating directory " . $sDirectory . PHP_EOL;
                mkdir($sDirectory, 0777, true);
            }
        }
    }
    /*

    */

    /**
     * @param SystemBuildConfig $oSystemBuildVo
     * @throws Throwable
     */
    /*
    public function create(SystemBuildConfig $oSystemBuildVo) {
        $this->makeAdminDirectories($oSystemBuildVo);
    }

    private function makeAdminDirectories(SystemBuildConfig $oSystemBuildVo): void {
        $aDirectories = [
            $_ENV['SYSTEM_ROOT'] . '/admin_public_html/custom/' . $oSystemBuildVo->getSystemId(),
        ];

        foreach ($aDirectories as $sDirectory) {
            if (!is_dir($sDirectory)) {
                echo "Creating directory " . $sDirectory . PHP_EOL;
                mkdir($sDirectory, 0777, true);
            }
        }
    }
    */
}

