<?php

namespace Generator\Vo;

use Cli\Tools\CommandUtils;
use Crud\Field;
use ReflectionClass;

class CrudManager {
    private $crud_fields_created;
    private $crud_folder_path;
    private $model_object;
    private $overview_title;
    private $new_title;
    private $edit_title;
    private $overview_url;
    private $edit_url;
    private $namespace;
    private $bare_manager_name;
    private $base_model_object;
    private $fields_as_array;
    private $fields_as_array_edit;
    private $fill_vo;
    private $expose_api;
    private $expose_api_sort_desc;

    public function __construct(array $aArguments) {
        $this->crud_fields_created = $aArguments['crud_fields_created'];
        $this->crud_folder_path = $aArguments['crud_folder_path'];
        $this->model_object = $aArguments['model_object'];
        $this->overview_title = $aArguments['overview_title'];
        $this->new_title = $aArguments['new_title'];
        $this->edit_title = $aArguments['edit_title'];
        $this->overview_url = $aArguments['overview_url'];
        $this->edit_url = $aArguments['edit_url'];

        $this->expose_api = $aArguments['expose_api'] == 'yes';
        $this->expose_api_sort_desc = $aArguments['expose_api_sort_desc'];

        $this->namespace = str_replace('/', '\\', $aArguments['crud_folder_path']);
        $this->bare_manager_name = array_reverse(explode('/', $aArguments['crud_folder_path']))[0];
        $this->base_model_object = basename(str_replace('\\', '/', $aArguments['model_object']));
        $this->fields_as_array = self::genFieldsAsArray($aArguments['crud_folder_path']);
        $this->fields_as_array_edit = self::genFieldsAsArrayEdit($aArguments['crud_folder_path']);

        $this->fill_vo = self::genFillVo($aArguments['crud_folder_path']);
    }

    public function getExposeApi(): bool {
        return $this->expose_api;
    }

    public function getExposeApiShortDesc(): string {
        return $this->expose_api_sort_desc;
    }

    /**
     * @return mixed
     */
    public function getModelObject() {
        return $this->model_object;
    }

    /**
     * @return mixed
     */
    public function getOverviewTitle() {
        return $this->overview_title;
    }

    /**
     * @return mixed
     */
    public function getNewTitle() {
        return $this->new_title;
    }

    /**
     * @return mixed
     */
    public function getEditTitle() {
        return $this->edit_title;
    }

    /**
     * @return mixed
     */
    public function getOverviewUrl() {
        return $this->overview_url;
    }

    /**
     * @return mixed
     */
    public function getEditUrl() {
        return $this->edit_url;
    }

    /**
     * @return mixed
     */
    public function getNamespace() {
        return $this->namespace;
    }

    /**
     * @return mixed
     */
    public function getBareManagerName() {
        return $this->bare_manager_name;
    }

    /**
     * @return string
     */
    public function getBaseModelObject(): string {
        return $this->base_model_object;
    }

    public function getFieldsAsArrayEdit() {
    }

    /**
     * @return string
     */
    public function getFieldsAsArray(): string {
        return $this->fields_as_array;
    }

    /**
     * @return string
     */
    public function getFillVo(): string {
        return $this->fill_vo;
    }

    private function genFieldsAsArrayEdit($sDir): string {
        $sLookInDir = CommandUtils::getRoot() . '/classes/' . $sDir;
        $aFields = glob($sLookInDir . '/*');

        $aOut = [];
        foreach ($aFields as $sField) {
            if (in_array($sField, [
                'Delete',
                'Edit',
            ])) {
                continue;
            }
            $aOut[] = "'" . str_replace('.php', '', basename($sField)) . "'";
        }
        return '[' . PHP_EOL . join(', ' . PHP_EOL, $aOut) . PHP_EOL . ']';
    }

    private function genFieldsAsArray($sDir): string {

        $sLookInDir = CommandUtils::getRoot() . '/classes/' . $sDir;
        $aFields = glob($sLookInDir . '/*');

        $aOut = [];
        foreach ($aFields as $sField) {
            $aOut[] = "'" . str_replace('.php', '', basename($sField)) . "'";
        }
        return '[' . PHP_EOL . join(', ' . PHP_EOL, $aOut) . PHP_EOL . ']';
    }

    private function genFillVo($sDir): string {

        $sLookInDir = CommandUtils::getRoot() . '/classes/' . $sDir . '/Field';
        $aFields = glob($sLookInDir . '/*');

        $aOut = [];
        foreach ($aFields as $sFieldFileName) {
            require_once $sFieldFileName;

            $sFqnBasic = str_replace(CommandUtils::getRoot() . '/classes/', '', $sFieldFileName);
            $oFieldClassFqn = '\\' . str_replace('/', '\\', $sFqnBasic);
            $oFieldClassFqn = str_replace('.php', '', basename($oFieldClassFqn));
            $oField = new $oFieldClassFqn();

            if ($oField instanceof Field) {
                $reflect = new ReflectionClass($oField);
                $sShort = $reflect->getShortName();

                if (in_array($sShort, [
                    'Delete',
                    'Edit',
                ])) {
                    continue;
                }
                $under_scored_name = self::fromCamelCase($sShort);
                $aOut[] = "if(isset(\$aData['" . $under_scored_name . "'])){ \$oModel->set$sShort( \$aData['" . $under_scored_name . "'] ); }";
            }
        }
        $aOut[] = "return \$oModel;";
        return join(PHP_EOL, $aOut) . PHP_EOL;
    }

    private static function fromCamelCase($input) {
        preg_match_all('!([A-Z][A-Z0-9]*(?=$|[A-Z][a-z0-9])|[A-Za-z][a-z0-9]+)!', $input, $matches);
        $ret = $matches[0];
        foreach ($ret as &$match) {
            $match = $match == strtoupper($match) ? strtolower($match) : lcfirst($match);
        }
        return implode('_', $ret);
    }
}
