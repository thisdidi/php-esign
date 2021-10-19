<?php


namespace Conle\ESign\Factory\Signfile\Signflows;

use Conle\ESign\Factory\Request\EsignRequest;

/**
 * 轩辕API签署流程开启
 * @author  澄泓
 * @date  2020/11/25 11:02
 */
class StartSignFlow extends EsignRequest implements \JsonSerializable
{
    private $flowId;

    /**
     * StartSignFlow constructor.
     * @param $flowId
     */
    public function __construct($flowId)
    {
        $this->flowId = $flowId;
    }

    /**
     * @return mixed
     */
    public function getFlowId()
    {
        return $this->flowId;
    }

    /**
     * @param mixed $flowId
     * @return StartSignFlow
     */
    public function setFlowId($flowId)
    {
        $this->flowId = $flowId;
        return $this;
    }

    function build()
    {
        $this->setUrl("/v1/signflows/".$this->flowId."/start");
        $this->setReqType(\HttpEmun::PUT);
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
            if($value===null||$key=='flowId') {
                continue;
            }
            $json[$key] = $value;
        }
        return $json;
    }
}