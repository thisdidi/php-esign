<?php

namespace Conle\ESign\File;

use Conle\ESign\Core\AbstractAPI;
use Conle\ESign\Exceptions\HttpException;
use Conle\ESign\Support\Collection;

class File extends AbstractAPI
{
    /**
     * @param $templateId
     * @param $name
     * @param $simpleFormFields
     * @return Collection|null
     * @throws HttpException
     */
    public function createByTemplateId($templateId, $name, $simpleFormFields)
    {
        $url = '/v1/files/createByTemplate';
        $params = [
            'name' => $name,
            'templateId' => $templateId,
            'simpleFormFields' => $simpleFormFields,
        ];
        return $this->parseJSON('json', [$url, $params]);
    }

}