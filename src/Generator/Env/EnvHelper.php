<?php
namespace Generator\Env;

use Hurah\Types\Type\SystemId;
use Generator\IEnvConfig;

class EnvHelper {

    static function getDbPass(string $sSomeSaltyString): string
    {
        return substr(sha1($sSomeSaltyString . time() . 'xx'), 5, 16);
    }
    static function getDbHost(){
        if(file_exists('.env'))
        {
            $aEnv = parse_ini_file('.env');
            if(isset($aEnv['DATABASE_IP']))
            {
                return $aEnv['DATABASE_IP'];
            }
        }
        return '127.0.0.1';
    }
    static function getDbUser(string $sUserName)
    {
        $sUserName = (string) $sUserName;
        return str_replace('.', '_', $sUserName);
    }
}
