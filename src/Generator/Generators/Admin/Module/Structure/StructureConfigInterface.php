<?php /*** @unfixed* */

namespace Generator\Admin\Module\Structure;

use Core\DataType\Path;


interface StructureConfigInterface {

    public function getInstallRoot():Path;
    public function getModuleModels():array;

}
