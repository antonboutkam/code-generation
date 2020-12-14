<?php/*** @unfixed**/

namespace Generator\Build\BuildStructure;

use Generator\IBuildFolderOwner;
use Generator\IPropelConfigBuildVo;
use Cli\Tools\VO\SystemBuildConfig;
use Core\InlineTemplate;
use Exception\LogicException;
use Hi\Helpers\DirectoryStructure;
use Throwable;
use Twig_Error_Loader;
use Twig_Error_Syntax;

final class BuildStructure
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
     * @throws Twig_Error_Loader
     * @throws Twig_Error_Syntax
     */
    public function create(SystemBuildConfig $oSystemBuildVo)
    {
        $this->makeBuildDirectories($oSystemBuildVo);
        $this->makeSchema($oSystemBuildVo);
        // $this->makeMigrateScript($oSystemBuildVo);

        if ($oSystemBuildVo->getHasApi()) {
            $this->makeApiXml($oSystemBuildVo);
        }

        $this->makePropel($oSystemBuildVo, 'live');
        $this->makePropel($oSystemBuildVo, 'test');
        $this->makePropel($oSystemBuildVo, 'dev');

        $this->makeCrudQueries($oSystemBuildVo);

        $this->makePhingFile($oSystemBuildVo, 'live');
        $this->makePhingFile($oSystemBuildVo, 'test');
    }

    /**
     * @param SystemBuildConfig $oSystemBuildVo
     * @param $sEnv
     * @throws Throwable
     * @throws Twig_Error_Loader
     * @throws Twig_Error_Syntax
     */
    private function makePhingFile(SystemBuildConfig $oSystemBuildVo, $sEnv)
    {
        $sToFile = $_ENV['SYSTEM_ROOT'] . '/build/source/build-' . $sEnv . '.' . $oSystemBuildVo->getServiceName() . '.xml';
        if (file_exists($sToFile)) {
            echo "Skip creating schema.xml file, it already exists" . PHP_EOL;
            echo "Not writing $sToFile " . PHP_EOL;
            return;
        }
        $sFromFile = $_ENV['SYSTEM_ROOT'] . '/build/_skel/phing-build-file.xml.twig';
        $sTemplate = file_get_contents($sFromFile);

        $sLinuxUser = ($sEnv === 'live') ? $oSystemBuildVo->getLinuxUserProduction() : $oSystemBuildVo->getLinuxUserTest();

        $aVars = [
            'env'           => $sEnv,
            'linux_user'    => $sLinuxUser,
            'system'        => $oSystemBuildVo,
            'mysql_pass'    => $oSystemBuildVo->generatePass($sEnv),
            'local_db_name' => $oSystemBuildVo->getDbName('dev'),
        ];
        $sParsedTemplate = InlineTemplate::parse($sTemplate, $aVars);
        echo "Generating build script $sToFile" . PHP_EOL;
        file_put_contents($sToFile, $sParsedTemplate);
    }

    /**
     * @param SystemBuildConfig $oSystemBuildVo
     * @throws Throwable
     * @throws Twig_Error_Loader
     * @throws Twig_Error_Syntax
     */
    private function makeCrudQueries(SystemBuildConfig $oSystemBuildVo)
    {

        $sFromFile = $_ENV['SYSTEM_ROOT'] . '/build/_skel/crud_queries/0_always_runs.php.twig';
        $sTemplate = file_get_contents($sFromFile);

        $aVars = [
            'config_dir'        => $oSystemBuildVo->getSystemId(),
            'service_name'      => $oSystemBuildVo->getServiceName(),
            'default_user_pass' => substr(md5(time()), 10, 8),
        ];
        echo "Generating initial data loader / generator." . PHP_EOL;
        $sParsedTemplate = InlineTemplate::parse($sTemplate, $aVars);
        $sBuildFile = $this->getBuildRoot($oSystemBuildVo) . '/crud_queries/0_always_runs.php';
        file_put_contents($sBuildFile, $sParsedTemplate);
    }

    /**
     * @param IPropelConfigBuildVo $oPropelConfigBuildVo
     * @param string $sEnv
     * @throws Throwable
     * @throws Twig_Error_Loader
     * @throws Twig_Error_Syntax
     */
    public function makePropel(IPropelConfigBuildVo $oPropelConfigBuildVo, string $sEnv): void
    {
        $oDirectoryStructure = new DirectoryStructure();

        $sSystemBaseDir = $_ENV['SYSTEM_ROOT'] . DIRECTORY_SEPARATOR . $oDirectoryStructure->getSystemDir();

        $sFromFile = $sSystemBaseDir . DIRECTORY_SEPARATOR . 'build' . DIRECTORY_SEPARATOR . '_skel' . DIRECTORY_SEPARATOR . 'propel.php.twig';

        $sTemplate = file_get_contents($sFromFile);
        $sFileSuffix = '';
        if (
            in_array($sEnv, [
            'dev',
            'live',
            'test',
            ])
        ) {
            $aArgs = [
                'password'     => $oPropelConfigBuildVo->getPassword($sEnv),
                'db_name'      => $oPropelConfigBuildVo->getDbName($sEnv),
                'db_user'      => $oPropelConfigBuildVo->getDbUser($sEnv),
                'db_server'    => $oPropelConfigBuildVo->getDbServer($sEnv),
                'build_dir'    => $oPropelConfigBuildVo->getBuildFolder(),
                'project_root' => $sSystemBaseDir,
            ];

            if (
                in_array($sEnv, [
                'live',
                'test',
                ])
            ) {
                $sFileSuffix = '-' . $sEnv;
            }
        } else {
            throw new LogicException("Env must be one of dev, test, live");
        }

        $sPropelContents = InlineTemplate::parse($sTemplate, $aArgs);
        $sBuildFile = $this->getBuildRoot($oPropelConfigBuildVo) . '/propel' . $sFileSuffix . '.php';
        file_put_contents($sBuildFile, $sPropelContents);
    }

    private function getBuildRoot(IBuildFolderOwner $oBuildFolderOwner): string
    {
        $oDirectoryStructure = new DirectoryStructure();
        return $_ENV['SYSTEM_ROOT'] . DIRECTORY_SEPARATOR . $oDirectoryStructure->getSystemDir() . DIRECTORY_SEPARATOR . 'build' . DIRECTORY_SEPARATOR . 'database' . DIRECTORY_SEPARATOR . $oBuildFolderOwner->getSystemId();
    }

    private function makeBuildDirectories(SystemBuildConfig $oSystemBuildVo): void
    {

        $aDirectories = [
            $this->getBuildRoot($oSystemBuildVo),
            $this->getBuildRoot($oSystemBuildVo) . '/crud_queries/',
            $this->getBuildRoot($oSystemBuildVo) . '/generated-schema/',
            $this->getBuildRoot($oSystemBuildVo) . '/migrations/',
        ];

        foreach ($aDirectories as $sDirectory) {
            if (!is_dir($sDirectory)) {
                echo "Creating directory " . $sDirectory . PHP_EOL;
                mkdir($sDirectory, 0777, true);
            }
        }
    }

    private function makeMigrateScript(SystemBuildConfig $oSystemBuildVo)
    {
        $sFromFile = $_ENV['SYSTEM_ROOT'] . '/build/_skel/migrate.sh';
        $sToFile = $this->getBuildRoot($oSystemBuildVo) . '/migrate.sh';
        echo "Copy $sFromFile to $sToFile" . PHP_EOL;
        file_put_contents($sToFile, file_get_contents($sFromFile));
    }

    /**
     * @param SystemBuildConfig $oSystemBuildVo
     * @throws Throwable
     * @throws Twig_Error_Loader
     * @throws Twig_Error_Syntax
     */
    private function makeApiXml(SystemBuildConfig $oSystemBuildVo): void
    {
        $sSkelSchema = file_get_contents($_ENV['SYSTEM_ROOT'] . '/build/_skel/api.xml.twig');
        $sSchema = InlineTemplate::parse($sSkelSchema, ['system' => $oSystemBuildVo]);
        $sSchemaFile = $this->getBuildRoot($oSystemBuildVo) . '/api.xml';
        echo "Creating api xml file: $sSchemaFile." . PHP_EOL;
        file_put_contents($sSchemaFile, $sSchema);
    }

    /**
     * @param SystemBuildConfig $oSystemBuildVo
     * @throws Throwable
     * @throws Twig_Error_Loader
     * @throws Twig_Error_Syntax
     */
    private function makeSchema(SystemBuildConfig $oSystemBuildVo): void
    {
        $sSkelSchema = file_get_contents($_ENV['SYSTEM_ROOT'] . '/build/_skel/schema.xml.twig');
        $sSchema = InlineTemplate::parse($sSkelSchema, ['system' => $oSystemBuildVo]);
        $sSchemaFile = $this->getBuildRoot($oSystemBuildVo) . '/schema.xml';
        echo "Creating schema file: $sSchemaFile." . PHP_EOL;
        file_put_contents($sSchemaFile, $sSchema);
    }
}