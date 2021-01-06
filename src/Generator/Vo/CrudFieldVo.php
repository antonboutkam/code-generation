<?php

namespace Generator\Vo;

class CrudFieldVo {
    private $model_object;
    private $query_object;
    private $crud_folder_path;
    private $create_edit_button;
    private $create_delete_button;
    private $edit_url;
    private $namespace;
    private $classname;
    private $fieldname;
    private $fieldlabel;
    private $getter;
    private $datatype;
    private $genericType;
    private $crudinterface;
    private $fieldicon;
    private $fieldplaceholder;

    public function __construct(array $aArguments) {
        $this->model_object = $aArguments['model_object'] ?? null;
        $this->crud_folder_path = $aArguments['crud_folder_path'] ?? null;
        $this->create_edit_button = $aArguments['create_edit_button'] ?? null;
        $this->create_delete_button = $aArguments['create_delete_button'] ?? null;
        $this->edit_url = $aArguments['edit_url'] ?? null;

        $sNamespaceReverseSlashes = str_replace('/Field', '', $this->getCrudFolderPath());
        $this->namespace = str_replace('/', '\\', $sNamespaceReverseSlashes);

        $this->query_object = $aArguments['model_object'] . 'Query';
    }

    /**
     * @return mixed
     */
    public function getFieldicon() {
        return $this->fieldicon;
    }

    /**
     * @param mixed $fieldicon
     */
    public function setFieldicon($fieldicon) {
        $this->fieldicon = $fieldicon;
    }

    /**
     * @return mixed
     */
    public function getFieldplaceholder() {
        return $this->fieldplaceholder;
    }

    /**
     * @param mixed $fieldplaceholder
     */
    public function setFieldplaceholder($fieldplaceholder) {
        $this->fieldplaceholder = $fieldplaceholder;
    }

    public function setClassName($classname) {
        $this->classname = $classname;
    }

    public function getClassName() {
        return $this->classname;
    }

    /**
     * @return mixed
     */
    public function getCrudinterface() {
        return $this->crudinterface;
    }

    /**
     * @param mixed $crudinterface
     */
    public function setCrudinterface($crudinterface) {
        $this->crudinterface = $crudinterface;
    }

    /**
     * @return mixed
     */
    public function getFieldlabel() {
        return $this->fieldlabel;
    }

    /**
     * @param mixed $fieldlabel
     */
    public function setFieldlabel($fieldlabel) {
        $this->fieldlabel = $fieldlabel;
    }

    /**
     * @return mixed
     */
    public function getDataType(): string {
        return $this->datatype;
    }

    /**
     * @param mixed $datatype
     */
    public function setDatatype($datatype) {
        $this->datatype = $datatype;
    }

    /**
     * @return mixed
     */
    public function getGenericType() {
        return $this->genericType;
    }

    /**
     * @param mixed $genericType
     */
    public function setGenericType($genericType) {
        $this->genericType = $genericType;
    }

    /**
     * @return mixed
     */
    public function getGetter() {
        return $this->getter;
    }

    /**
     * @param mixed $getter
     */
    public function setGetter($getter) {
        $this->getter = $getter;
    }

    /**
     * @return mixed
     */
    public function getFieldname() {
        return $this->fieldname;
    }

    /**
     * @param mixed $fieldname
     */
    public function setFieldname($fieldname) {
        $this->fieldname = $fieldname;
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
    public function getCrudFolderPath() {
        return $this->crud_folder_path;
    }

    /**
     * @return mixed
     */
    public function getCreateEditButton() {
        return $this->create_edit_button;
    }

    /**
     * @return mixed
     */
    public function getCreateDeleteButton() {
        return $this->create_delete_button;
    }

    /**
     * @return mixed
     */
    public function getEditUrl() {
        return $this->edit_url;
    }

    /**
     * @return string
     */
    public function getQueryObject(): string {
        return $this->query_object;
    }

    /**
     * @return mixed
     */
    public function getNamespace() {
        return $this->namespace;
    }
}
