<?php

namespace Conle\ESign\Factory\Signfile\Seals;

use Conle\ESign\Factory\Request\EsignRequest;
use Conle\ESign\Emun\HttpEmun;

/**
 * 获取企业印章
 * @author  Conle
 * @date  2021/10/20 16:01
 */
class GetSeals extends EsignRequest implements \JsonSerializable
{
    private $accountId;
    private $offset;
    private $size;

    /**
     * CreateOfficialTemplate constructor.
     * @param $orgId 机构id，该参数需放在请求地址里面
     * @param $color 印章颜色，RED-红色，BLUE-蓝色，BLACK-黑色
     * @param $type 模板类型 TEMPLATE_ROUND 圆章  TEMPLATE_OVAL 椭圆章
     * @param $central 中心图案类型 STAR 圆形有五角星 NONE 圆形无五角星
     */
    public function __construct($accountId, int $offset = 1, int $size = 10)
    {
        $this->accountId = $accountId;
        $this->offset = $offset;
        $this->size = $size;
    }

    function build()
    {
        $this->setUrl("/v1/organizations/" . $this->accountId . "/seals?offset=" . $this->offset . "&size=" . $this->size);
        $this->setReqType(HttpEmun::GET);
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
            if ($value === null || $key == 'offset' || $key == 'accountId' || $key == 'size') {
                continue;
            }
            $json[$key] = $value;
        }
        return $json;
    }
}
