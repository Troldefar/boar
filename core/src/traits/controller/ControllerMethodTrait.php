<?php

namespace app\core\src\traits\controller;

use \app\models\FileModel;
use \app\core\src\File;
use \app\core\src\miscellaneous\Hash;

trait ControllerMethodTrait {

    private const ACTION_NOT_FOUND = 'Action was not found';

    public function denyGETRequest() {
        if ($this->request->isGet()) 
            $this->response->methodNotAllowed();
    }

    public function denyPOSTRequest() {
        if ($this->request->isPost()) 
            $this->response->methodNotAllowed(); 
    }

    public function checkAction() {
        if (isset($this->requestBody->body->action)) return;
            throw new \app\core\src\exceptions\NotFoundException(self::ACTION_NOT_FOUND);
    }

    public function determineClientResponseMethod(mixed $dispatchedHTTPMethodResult): string {
        if (is_array($dispatchedHTTPMethodResult)) $dispatchedHTTPMethodResult = $dispatchedHTTPMethodResult['message'] ?? '';

        $backendMessageContainsErrorInString = is_int(strpos(strtolower($dispatchedHTTPMethodResult) ?? '', 'error'));

        return $backendMessageContainsErrorInString ? 'dataConflict' : 'ok';
    }

    public function edit() {
        $this->denyGETRequest();
        $this->dispatchMethodOnEntity();
    }

    public function view() {
        $this->denyPOSTRequest();
        $this->dispatchMethodOnEntity();
    }

    public function delete() {
        $this->denyGETRequest();
        $this->dispatchMethodOnEntity('delete');
    }

    private function dispatchMethodOnEntity(string $method = '') {
        $cEntity = $this->returnValidEntityIfExists();

        $request = $this->requestBody->body;
        $response = $cEntity->dispatchHTTPMethod($request->action ?? $method, $request);

        $this->response->{$this->determineClientResponseMethod(dispatchedHTTPMethodResult: $response)}($response ?? '');
    }

    public function appendFilesToRequestBody() {
        $customFileType = $this->requestBody->body->fileType ?? 'DefaultFile';

        foreach ($this->requestBody->files as $newFile) {
            $file = new File($newFile);
            
            if (empty($file->getName())) continue;
            $destination = $file->moveFile();

            $cFile = new FileModel();
            $cFile->setData([
                'Name' => $file->getName(),
                'Path' => $destination,
                'Hash' => Hash::file($destination),
                'Type' => $customFileType
            ]);

            $cFile->save();
            $this->requestBody->body->$customFileType = $cFile;
        }
    }

    public function dispatchMethodAction() {
        $cEntity = $this->returnValidEntityIfExists();
        
        $this->appendFilesToRequestBody();
        $request = $this->requestBody->body;
        return $cEntity->dispatchHTTPMethod($request->action, $request);
    }

}