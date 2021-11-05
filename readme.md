ESign Laravel MD5

## install

```
composer require conle/esign
```

## Use

```
use Conle\ESign\Factory\Factory;
use Conle\ESign\Factory\Base\Account;

$host = "https://smlopenapi.esign.cn";//请求网关host
$project_id = "";//应用id
$project_scert = "";//密钥
Factory::init($host, $project_id, $project_scert);


$thirdPartyUserIdPsn = "";//thirdPartyUserId参数，用户唯一标识，自定义保持唯一即可
$namePsn = "";//name参数，姓名
$idTypePsn = "CRED_PSN_CH_IDCARD";//idType参数，证件类型
$idNumberPsn = "";//idNumber参数，证件号
$mobilePsn = "";//mobile参数，手机号
$email = "";//email参数
$createPsn = Account::createPersonByThirdPartyUserId($thirdPartyUserIdPsn, $namePsn, $idTypePsn,  $idNumberPsn, $email);
$createPsn->setMobile($mobilePsn);
$createPsnResp = $createPsn->execute();//execute方法发起请求
$createPsnJson = json_decode($createPsnResp->getBody());
$accountId = $createPsnJson->data->accountId;//生成的个人账号保存好，后续接口调用需要使用
```

## License

MIT
