<?php

use factory\base\Account;
use factory\base\FileTemplate;
use factory\base\Seals;
use factory\base\SignFile;
use factory\bean\Doc;
use factory\bean\FlowInfo;
use factory\bean\PosBean;
use factory\bean\Signer;
use factory\bean\SignerAccount;
use factory\bean\Signfield;
use factory\Factory;

header("Content-type:text/html;charset=utf-8");
include("../eSignOpenAPI.php");
//此示例为企业和个人场景的签署示例代码，签署方式为一步发起签署，如果需要分步签署，签署部分代码示例可参考b2bDemo
var_dump("--------------------------初始化 start----------------------------");
$host = "https://smlopenapi.esign.cn";//请求网关host
$project_id = "7438857636";//应用id
$project_scert = "05aa332dbeb37e35f63f3eb7528b2f33";//密钥
Factory::init($host, $project_id, $project_scert);
Factory::setDebug(true);//是否开启日志记录，传true或false,日志存放在根目录的phplog.txt文件
//-----------------基础信息初始化 end--------------------------
var_dump("--------------------------初始化 end----------------------------");

/*$filePath="D:\\IDEAproject\\PdfFile\\dstPdf\\qianshu.pdf";//文件地址
if(!is_file($filePath)){
    echo '文件不存在';exit;
}*/
//-----------------------个人账号信息用于创建个人账号接口传入-----------------------------
$thirdPartyUserIdPsn = "3713241996010383551";//thirdPartyUserId参数，用户唯一标识，自定义保持唯一即可
$namePsn = "高洪超";//name参数，姓名
$idTypePsn = "CRED_PSN_CH_IDCARD";//idType参数，证件类型
$idNumberPsn = "371324199701038355";//idNumber参数，证件号
$mobilePsn = "15900509034";//mobile参数，手机号
$email = "conlecl@163.com";//mobile参数，手机号

//------------------------企业账号信息用于创建机构账号接口传入----------------
$thirdPartyUserIdOrg = "1212312312312";//thirdPartyUserId参数，用户唯一标识，自定义保持唯一即可
$nameOrg = "杭州天谷";//name参数，机构名称
$idTypeOrg = "CRED_ORG_USCC";//idType参数，证件类型
$idNumberOrg = "";//idNumber参数,机构证件号


var_dump("------------------ 创建个人账号 start -----------------");
$createPsn = Account::createPersonByThirdPartyUserId(
    $thirdPartyUserIdPsn,
    $namePsn,
    $idTypePsn,
    $idNumberPsn, $email);
$createPsn->setMobile($mobilePsn);
var_dump($createPsn);
exit(333);
$createPsnResp = $createPsn->execute();//execute方法发起请求
$createPsnJson = json_decode($createPsnResp->getBody());
$accountId = $createPsnJson->data->accountId;//生成的个人账号保存好，后续接口调用需要使用
var_dump("------------------ 创建个人账号 end ---------------");

/*
var_dump("------------------ 创建企业账号 start ---------------");
$createOrg = Account::createOrganizationsByThirdPartyUserId(
    $thirdPartyUserIdOrg,
    $accountId,
    $nameOrg,
    $idTypeOrg,
    $idNumberOrg
);
$createOrgResp = $createOrg->execute();
$createOrgJson=json_decode($createOrgResp->getBody());
$orgId=$createOrgJson->data->orgId;
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

var_dump("------------------ 一步发起签署 start -----------------");
$doc = new Doc();
$doc->setFileId($fileId);
$docs = array($doc);
$flowInfo = new FlowInfo();
$flowInfo->setBusinessScene("b2c合同签署测试")
    ->setAutoArchive(true)//自动归档
    ->setAutoInitiate(true);//自动开启流程
$psnSignfield = new Signfield();
$posBean = new PosBean();
$psnSignfield->setFileId($fileId)
    ->setPosBean($posBean->setPosPage(1)->setPosX(113)->setPosY(225));
$psnSignfields = array($psnSignfield);//构造个人signfields参数对象



$orgSignfield = new Signfield();
$posBean = new PosBean();
$orgSignfield->setFileId($fileId)
    ->setPosBean($posBean->setPosPage(1)->setPosX(224)->setPosY(334))
    ->setActorIndentityType(2);//机构签署必传
$orgSignfields = array($orgSignfield);//构造个人signfields参数对象




$signerpsn = new Signer();
$signerAccount1 = new SignerAccount();
$signerAccount1->setSignerAccountId($accountId);
$signerpsn->setSignerAccount($signerAccount1)
    ->setSignfields($psnSignfields);;//传入个人signer信息

$signerorg = new Signer();
$signerA1ccount2 = new SignerAccount();
$signerA1ccount2->setSignerAccountId($accountId)->setAuthorizedAccountId($orgId);
$signerorg->setSignerAccount($signerA1ccount2)
    ->setSignfields($orgSignfields);//传入企业signer信息

$signers = array($signerpsn, $signerorg);//


$createFlowOneStep = SignFile::createFlowOneStep($docs, $flowInfo, $signers);
$flowOneStepResp = $createFlowOneStep->execute();
$flowOneStepJson = json_decode($flowOneStepResp->getBody());
$flowId = $flowOneStepJson->data->flowId;//流程id保存好
var_dump("------------------ 一步发起签署 end -----------------");

var_dump("------------------ 获取签署地址 start -----------------");
$getFileSignUrl = SignFile::getFileSignUrl($flowId, $accountId);
$getFileSignUrl->setOrganizeId($orgId);
$getFileSignUrlResp = $getFileSignUrl->execute();
$getFileSignUrlJson = json_decode($getFileSignUrlResp->getBody());
$shortUrl = $getFileSignUrlJson->data -> shortUrl;
var_dump("签署短连接,复制到浏览器打开\n".$shortUrl);
var_dump("------------------ 获取签署地址 end -----------------");*/


