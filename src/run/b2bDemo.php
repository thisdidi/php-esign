<?php

use factory\base\Account;
use factory\base\FileTemplate;
use factory\base\Seals;
use factory\base\SignFile;
use factory\bean\Doc;
use factory\bean\PosBean;
use factory\bean\Signfield;
use factory\Factory;

header("Content-type:text/html;charset=utf-8");
include("../eSignOpenAPI.php");
//此示例为企业和企业签署场景的示例代码，签署方式为分步发起签署，如果需要一步发起签署，签署部分代码示例可参考b2bDemo
var_dump("--------------------------初始化 start----------------------------");
$host="https://smlopenapi.esign.cn";//请求网关host
$project_id="";//应用id
$project_scert="";//密钥
Factory::init($host,$project_id,$project_scert);
Factory::setDebug(true);//是否开启日志记录，传true或false,日志存放在根目录的phplog.txt文件
//-----------------基础信息初始化 end--------------------------
var_dump("--------------------------初始化 end----------------------------");

            $filePath="D:\\IDEAproject\\PdfFile\\dstPdf\\qianshu.pdf";//文件地址
            if(!is_file($filePath)){
                echo '文件不存在';exit;
            }
            //-----------------------个人账号信息用于创建个人账号接口传入-----------------------------
            $thirdPartyUserIdPsn="123213223";//thirdPartyUserId参数，用户唯一标识，自定义保持唯一即可
            $namePsn="";//name参数，姓名
            $idTypePsn="CRED_PSN_CH_IDCARD";//idType参数，证件类型
            $idNumberPsn="";//idNumber参数，证件号
            $mobilePsn="";//mobile参数，手机号

            //------------------------企业账号信息1用于创建机构账号接口传入----------------
            $thirdPartyUserIdOrg1="121232131212312312";//thirdPartyUserId参数，用户唯一标识，自定义保持唯一即可
            $nameOrg1="测试公司1";//name参数，机构名称
            $idTypeOrg1="CRED_ORG_USCC";//idType参数，证件类型
            $idNumberOrg1="";//idNumber参数,机构证件号

            //------------------------企业账号信息2用于创建机构账号接口传入----------------
            $thirdPartyUserIdOrg2="1212321231213312312";//thirdPartyUserId参数，用户唯一标识，自定义保持唯一即可
            $nameOrg2="测试公司2";//name参数，机构名称
            $idTypeOrg2="CRED_ORG_USCC";//idType参数，证件类型
            $idNumberOrg2="";//idNumber参数,机构证件号

var_dump("------------------ 创建个人账号 start ---------------");
$createPsn = Account::createPersonByThirdPartyUserId(
    $thirdPartyUserIdPsn,
    $namePsn,
    $idTypePsn,
    $idNumberPsn);
$createPsn->setMobile($mobilePsn);
$createPsnResp = $createPsn->execute();//execute方法发起请求
$createPsnJson = json_decode($createPsnResp->getBody());
$accountId = $createPsnJson->data->accountId;//生成的个人账号保存好，后续接口调用需要使用
var_dump("------------------ 创建个人账号 end ---------------");


var_dump("------------------ 创建企业账号 start ---------------");
$createOrg1 = Account::createOrganizationsByThirdPartyUserId(
    $thirdPartyUserIdOrg1,
    $accountId,
    $nameOrg1,
    $idTypeOrg1,
    $idNumberOrg1
);
$createOrg1Resp = $createOrg1->execute();
$createOrg1Json=json_decode($createOrg1Resp->getBody());
$orgId1=$createOrg1Json->data->orgId;

$createOrg2 = Account::createOrganizationsByThirdPartyUserId(
    $thirdPartyUserIdOrg2,
    $accountId,
    $nameOrg2,
    $idTypeOrg2,
    $idNumberOrg2
);
$createOrg2Resp = $createOrg2->execute();
$createOrg2Json=json_decode($createOrg2Resp->getBody());
$orgId2=$createOrg2Json->data->orgId;
var_dump("------------------ 创建企业账号 end ---------------");


var_dump("------------------ 通过上传方式创建文件 start -----------------");
$contentBase64Md5 = UtilHelper::getContentBase64Md5($filePath);
$filesize = filesize($filePath);
$fileContent = file_get_contents($filePath);
$getFileUploadUrl = FileTemplate::getFileUploadUrl($contentBase64Md5, "application/pdf", false, "测试合同.pdf", $filesize);
$getFileUploadUrlResp = $getFileUploadUrl->execute();
$getFileUploadUrlJson = json_decode($getFileUploadUrlResp->getBody());
$fileId = $getFileUploadUrlJson->data->fileId;//文件id保存好，后续使用
$uploadUrl = $getFileUploadUrlJson->data->uploadUrl;//上传url保存好，后续使用
var_dump("------------------ 通过上传方式创建文件 end -----------------");


var_dump("------------------ 文件流上传方法 start -----------------");
$uploadFile = FileTemplate::uploadFile($filePath, "application/pdf", $uploadUrl);
$uploadFileResp = $uploadFile->execute();
var_dump($uploadFileResp->getBody());
var_dump("------------------ 文件流上传方法 end -----------------");


var_dump("------------------ 分步发起签署 start -----------------");
var_dump("------ 签署流程创建 start ---------");

var_dump("------ 签署流程创建 end ---------");
$createSignFlow=SignFile::createSignFlow('b2b合同签署测试');
$createSignFlowResp = $createSignFlow->execute();
$createSignFlowJson = json_decode($createSignFlowResp->getBody());
$flowId = $createSignFlowJson->data->flowId;//流程id，保存好
var_dump("------ 流程文档添加 start ---------");
$doc = new Doc();
$doc->setFileId($fileId);
$createDocuments = SignFile::createDocuments($flowId, array($doc));
$createDocuments->execute();
var_dump("------ 流程文档添加 end ---------");

var_dump("------ 添加手动盖章签署区 start ---------");
$signfield1 = new Signfield();
$posBean1 = new PosBean();
$posBean1->setPosPage(1)->setPosX(222)->setPosY(333);
$signfield1->setFileId($fileId)->setSignerAccountId($accountId)->setAuthorizedAccountId($orgId1)->setPosBean($posBean1)->setActorIndentityType(2);

$signfield2 = new Signfield();
$posBean2 = new PosBean();
$posBean2->setPosPage(1)->setPosX(435)->setPosY(333);
$signfield2->setFileId($fileId)->setSignerAccountId($accountId)->setAuthorizedAccountId($orgId2)->setPosBean($posBean2)->setActorIndentityType(2);
$createHandSign = SignFile::createHandSign($flowId, array($signfield1, $signfield2));
$createHandSign->execute();
var_dump("------ 添加手动盖章签署区 end ---------");
var_dump("------------------ 分步发起签署 end -----------------");

var_dump("------------------ 签署流程开启 start -----------------");
$startSignFlow = SignFile::startSignFlow($flowId);
$startSignFlow->execute();
var_dump("------------------ 签署流程开启 end -----------------");


//开启流程后会向个人实名手机号发送签署信息，会向企业签署经办人发送信息，也可以调用获取签署地址接口获取签署链接
var_dump("------------------ 获取签署地址 start -----------------");
$fileSignUrl = SignFile::getFileSignUrl($flowId, $accountId);
$fileSignUrl->setOrganizeId($orgId1);
$fileSignUrlResp1 = $fileSignUrl->execute();
$fileSignUrlJson1 = json_decode($fileSignUrlResp1->getBody());
$shortUrl1 = $fileSignUrlJson1->data->shortUrl;//响应的签署链接，复制到浏览器访问即可打开签署页面
var_dump("企业1的签署短连接,复制到浏览器打开\n".$shortUrl1);

$fileSignUrl->setOrganizeId($orgId2);
$fileSignUrlResp2 = $fileSignUrl->execute();
$fileSignUrlJson2 = json_decode($fileSignUrlResp2->getBody());
$shortUrl2 = $fileSignUrlJson2->data->shortUrl;//响应的签署链接，复制到浏览器访问即可打开签署页面
var_dump("企业1的签署短连接,复制到浏览器打开\n".$shortUrl2);

var_dump("------------------ 获取签署地址 end -----------------");

//全部签署完成以后进行归档，归档以后签署就不能再修改了
//var_dump("------------------ 签署流程归档 start -----------------");
//$archiveSignFlow = SignFile::archiveSignFlow($flowId);
//$archiveSignFlow->execute();
//var_dump("------------------ 签署流程归档 end -----------------");