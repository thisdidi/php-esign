<?php
namespace Conle\ESign\Factory\Signfile\Pdfverify;
use Conle\ESign\Factory\Request\EsignRequest;

/**
 * 轩辕API文件验签
 * @author  澄泓
 * @date  2020/11/24 14:43
 */
class PdfVerify extends EsignRequest implements \JsonSerializable
{
    private $fileId;
    private $flowId;

    /**
     * PdfVerify constructor.
     * @param $fileId
     */
    public function __construct($fileId)
    {
        $this->fileId = $fileId;
    }

    /**
     * @return mixed
     */
    public function getFileId()
    {
        return $this->fileId;
    }

    /**
     * @param mixed $fileId
     * @return PdfVerify
     */
    public function setFileId($fileId)
    {
        $this->fileId = $fileId;
        return $this;
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
     * @return PdfVerify
     */
    public function setFlowId($flowId)
    {
        $this->flowId = $flowId;
        return $this;
    }

    function build()
    {
        $url="/v1/documents/".$this->fileId."/verify?";
        if($this->flowId!==null){
            $url=$url."&flowId=".$this->flowId;
        }
        $this->setUrl($url);
        $this->setReqType(\HttpEmun::GET);
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
            if($value===null||$key=='flowId'||$key=='fileId') {
                continue;
            }
            $json[$key] = $value;
        }
        return $json;
    }
}