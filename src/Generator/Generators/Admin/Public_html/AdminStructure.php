<?php/*** @unfixed**/

namespace Generator\Admin\Public_html\AdminStructure {

    use Cli\Tools\VO\SystemBuildConfig;
    use Throwable;

    final class AdminStructure
    {
        public function __construct()
        {
            if (!isset($_ENV['SYSTEM_ROOT'])) {
                exit('To run this command the environment variable SYSTEM_ROOT must be set.');
            }
        }

        /**
         * @param SystemBuildConfig $oSystemBuildVo
         * @throws Throwable
         */
        public function create(SystemBuildConfig $oSystemBuildVo)
        {
            $this->makeAdminDirectories($oSystemBuildVo);
        }

        private function makeAdminDirectories(SystemBuildConfig $oSystemBuildVo): void
        {
            $aDirectories = [
                $_ENV['SYSTEM_ROOT'] . '/admin_public_html/custom/' . $oSystemBuildVo->getSystemId()
            ];

            foreach ($aDirectories as $sDirectory) {
                if (!is_dir($sDirectory)) {
                    echo "Creating directory " . $sDirectory . PHP_EOL;
                    mkdir($sDirectory, 0777, true);
                }
            }
        }
    }
}