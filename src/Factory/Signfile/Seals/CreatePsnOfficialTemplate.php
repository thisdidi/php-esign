<?php

namespace Conle\ESign\Factory\Signfile\Seals;

use Conle\ESign\Factory\Request\EsignRequest;
use Conle\ESign\Emun\HttpEmun;

/**
 * 创建个人印章
 * @author  Conle
 * @date  2021/10/20 16:01
 */
class CreatePsnOfficialTemplate extends EsignRequest implements \JsonSerializable
{
    private $accountId;
    private $color;
    private $type;
    private $height;
    private $width;

    /**
     * CreateOfficialTemplate constructor.
     * @param $accountId 用户id，该参数需放在请求地址里面
     * @param $color 印章颜色，RED-红色，BLUE-蓝色，BLACK-黑色
     * @param $type 模板类型 BORDERLESS 无边框矩形印章  TEMPLATE_OVAL 椭圆章
     */
    public function __construct($accountId, string $color = 'RED', string $type = 'RECTANGLE')
    {
        $this->accountId = $accountId;
        $this->color = $color;
        $this->type = $type;
    }

    function build()
    {
        $this->setUrl("/v1/accounts/" . $this->accountId . "/seals/personaltemplate");
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
            if ($value === null || $key == 'accountId') {
                continue;
            }
            $json[$key] = $value;
        }
        return $json;
    }
}
