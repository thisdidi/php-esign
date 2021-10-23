<?php

namespace Conle\ESign\Factory\Signfile\Seals;

use Conle\ESign\Factory\Request\EsignRequest;
use Conle\ESign\Emun\HttpEmun;

/**
 * 创建企业印章
 * @author  Conle
 * @date  2021/10/20 16:01
 */
class CreateOfficialTemplate extends EsignRequest implements \JsonSerializable
{
    private $orgId;
    private $color;
    private $type;
    private $central;
    private $htext;//横向文，可设置0-8个字，企业名称超出25个字后，不支持设置横向文
    private $qtext;//下弦文，可设置0-20个字，企业企业名称超出25个字后，不支持设置下弦文
    private $height;
    private $width;

    /**
     * CreateOfficialTemplate constructor.
     * @param $orgId 机构id，该参数需放在请求地址里面
     * @param $color 印章颜色，RED-红色，BLUE-蓝色，BLACK-黑色
     * @param $type 模板类型 TEMPLATE_ROUND 圆章  TEMPLATE_OVAL 椭圆章
     * @param $central 中心图案类型 STAR 圆形有五角星 NONE 圆形无五角星
     */
    public function __construct($orgId, string $color = 'RED', string $type = 'TEMPLATE_ROUND', string $central = 'STAR')
    {
        $this->orgId = $orgId;
        $this->color = $color;
        $this->type = $type;
        $this->central = $central;
    }

    /**
     * @return 机构id，该参数需放在请求地址里面
     */
    public function getOrgId()
    {
        return $this->orgId;
    }

    /**
     * @param $orgId
     * @return $this
     */
    public function setOrgId($orgId)
    {
        $this->orgId = $orgId;
        return $this;
    }

    /**
     * @return 印章颜色，RED-红色，BLUE-蓝色，BLACK-黑色|string
     */
    public function getColor()
    {
        return $this->color;
    }

    /**
     * @param $color
     * @return $this
     */
    public function setColor($color)
    {
        $this->color = $color;
        return $this;
    }

    /**
     * @return 模板类型|string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param $type
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return 中心图案类型|string
     */
    public function getCentral()
    {
        return $this->central;
    }

    /**
     * @param $central
     * @return $this
     */
    public function setCentral($central)
    {
        $this->central = $central;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getHtext()
    {
        return $this->htext;
    }

    /**
     * @param $htext
     * @return $this
     */
    public function setHtext($htext)
    {
        $this->htext = $htext;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getQtext()
    {
        return $this->qtext;
    }

    /**
     * @param $qtext
     * @return $this
     */
    public function setQtext($qtext)
    {
        $this->qtext = $qtext;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * @param $height
     * @return $this
     */
    public function setHeight($height)
    {
        $this->height = $height;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * @param $width
     * @return $this
     */
    public function setWidth($width)
    {
        $this->width = $width;
        return $this;
    }


    function build()
    {
        $this->setUrl("/v1/organizations/" . $this->orgId . "/seals/officialtemplate");
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
            if ($value === null || $key == 'orgId') {
                continue;
            }
            $json[$key] = $value;
        }
        return $json;
    }
}
