<?php /*** @unfixed* */

namespace Generator\Admin\Module\Config;

use Core\DataType\PlainText;
use Generator\Admin\Module\Menu\ItemConfig;
use Hurah\Types\Type\Icon;
use Hurah\Types\Type\Path;

interface MenuConfigInterface {

    public function getIcon(): Icon;

    public function hasSubmenu(): bool;

    public function getTitle(): PlainText;

    /**
     * @return ItemConfig[]
     */
    public function getMenu(): array;

    public function location(): Path;
    /*
    public function getCustom(): string;
    public function getModule(): string;
    */
}
