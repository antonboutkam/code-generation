<?php /*** @unfixed* */

namespace Generator\Generators\Admin\Module\Config;

use Generator\Generators\BaseGeneratorConfig;
use Generator\Generators\InputInterface;
use Helper\Schema\Table;
use Hurah\Types\Type\Path;
use Hurah\Types\Type\PhpNamespace as PhpNamespaceType;
use Symfony\Component\Console\Output\OutputInterface;

interface ConfigConfigInterface {

    public function getNamespaceName(): PhpNamespaceType;
    public function location(): Path;
    public function getCustom(): string;
    public function getModule(): string;
}
