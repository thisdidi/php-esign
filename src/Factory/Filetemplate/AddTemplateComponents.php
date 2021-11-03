<?php


namespace Conle\ESign\Factory\Filetemplate;

use Conle\ESign\Factory\Request\EsignRequest;
use Conle\ESign\Emun\HttpEmun;

/**
 * chao
 * Class AddTemplateComponents
 * @package Conle\ESign\Factory\Filetemplate
 */
class AddTemplateComponents extends EsignRequest implements \JsonSerializable
{
    private $templateId;
    private $structComponent;

    /**
     * AddTemplateComponents constructor.
     * @param $templateId
     * @param $structComponent
     */
    public function __construct($templateId, $structComponent)
    {
        $this->templateId = $templateId;
        $this->structComponent = $structComponent;
    }

    /**
     * @return mixed
     */
    public function getStructComponent()
    {
        return $this->structComponent;
    }

    /**
     * @param $structComponent
     */
    public function setStructComponent($structComponent)
    {
        $this->structComponent = $structComponent;
    }

    /**
     * @return mixed
     */
    public function getTemplateId()
    {
        return $this->templateId;
    }

    /**
     * @param $templateId
     */
    public function setTemplateId($templateId)
    {
        $this->templateId = $templateId;
    }


    function build()
    {
        $this->setUrl("/v1/docTemplates/" . $this->templateId . "/components");
        $this->setReqType(HttpEmun::POST);
    }

    /**
     * Specify data which should be serialized to JSON
     * @link https://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize()
    {
        $json = array();
        foreach ($this as $key => $value) {
            if ($value === null || $key == 'templateId') {
                continue;
            }
            $json[$key] = $value;
        }
        return $json;
    }
}
