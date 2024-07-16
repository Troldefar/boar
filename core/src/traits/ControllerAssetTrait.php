<?php

namespace app\core\src\traits;

use \app\core\src\File;
use \app\core\src\View;

trait ControllerAssetTrait {

    private const VALID_ASSET_TYPES = ['js', 'css', 'meta'];
    private const VALID_ASSET_LOCATION = ['header', 'footer'];
    private const FORBIDDEN_ASSET = 'Forbidden asset type was provided';

    protected function getClientAssets() {
        return $this->clientAssets;
    }

    public function checkAssetLocation(string $type): void {
        if (in_array($type, $this::VALID_ASSET_LOCATION)) return;

        throw new \app\core\src\exceptions\ForbiddenException($this::FORBIDDEN_ASSET);
    }

    public function checkAssetType(string $type): void {
        if (in_array($type, $this::VALID_ASSET_TYPES)) return;

        throw new \app\core\src\exceptions\ForbiddenException($this::FORBIDDEN_ASSET);
    }    

    public function addScript(string|array $src) {
        if (is_string($src)) $src = (array)$src;

        array_map(function($file) {
            return app()->getParentController()->upsertData(File::JS_EXTENSION, File::buildScript($file));
        }, $src);
    }

    public function addStylesheet(string $src) {
        if (is_string($src)) $src = (array)$src;

        array_map(function($file) {
            return app()->getParentController()->upsertData(File::CSS_EXTENSION, File::buildStylesheet($file));
        }, $src);
    }

    public function getView(): string {
        return $this->view ?? View::INVALID_VIEW;
    }

    public function setView(string $view, string $dir = ''): void {
        $this->view = app()->getView()->getTemplatePath($view, $dir);
    }

    public function setLayout(string $layout): void {
        $this->layout = $layout;
    }

    public function getLayout(): string {
        return $this->layout;
    }

    public function setClientLayoutStructure(string $layout, string $view, array $data = []) {
        $this->setLayout($layout);
        $this->setFrontendTemplateAndData($view, [...$data]);
    }

    public function setFrontendTemplateAndData(string $templateFile, array $data = []): self {
        $this->setData($data);
        $this->setView($templateFile);
        return $this;
    }

}