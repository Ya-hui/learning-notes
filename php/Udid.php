<?php
/**
 * 获取IOS设备 唯一标识码UDID
 */
class Udid {

    /**
     * 用户成功安装描述文件后,苹果通知地址
     *
     * @var URL
     */
    protected $callbackUrl = '';

    /**
    * 重定向地址
    *
    * 在$callbackUrl 中重定向到该地址
    *
    * @var string
    */
    protected $redirectUrl = '';

    /**
    * 描述文件标题
    *
    * @var string
    */
    protected $title       = '查询设备UDID';

    /**
    * 安装描述文件时显示的描述信息
    *
    * @var string
    */
    protected $description = '本文件仅用来获取设备ID';

    /**
    * 苹果请求 $callbackUrl时 推送过来的参数数据(解析后)
    *
    * @var array
    */
    protected $params      = [
        'uid'            => '',
        'challenge'      => '',
        'device_udid'    => '',     //设备唯一标示 udid
        'device_product' => '',
        'device_name'    => '',     //设备名称
        'device_version' => ''      //设备版本
    ];
    public function __construct(array $config) {

        if (empty($config['callbackUrl']) || ! filter_var($config['callbackUrl'], FILTER_VALIDATE_URL)) {

            throw new Exception("Please set the apple notification address");
        }

        if (isset($config['redirectUrl']) && ! filter_var($config['redirectUrl'], FILTER_VALIDATE_URL)) {

            throw new Exception("Redirecting address error");

        } else {

            $this->redirectUrl = $config['redirectUrl'];
        }

        $this->callbackUrl = $config['callbackUrl'];

        $this->title       = $config['title'] ?? $this->title;

        $this->description = $config['description'] ?? $this->description;
    }

    /**
     * 创建描述文件
     *
     * @return string       XML
     */
    protected function make() {
        return
            '<?xml version="1.0" encoding="UTF-8"?>
            <!DOCTYPE plist PUBLIC "-//Apple//DTD PLIST 1.0//EN" "http://www.apple.com/DTDs/PropertyList-1.0.dtd">
            <plist version="1.0">
                <dict>
                    <key>PayloadContent</key>
                    <dict>
                        <key>URL</key>
                        <string>' . $this->callbackUrl .'</string>
                        <key>DeviceAttributes</key>
                        <array>
                            <string>UDID</string>
                            <string>IMEI</string>
                            <string>ICCID</string>
                            <string>VERSION</string>
                            <string>PRODUCT</string>
                        </array>
                    </dict>
                    <key>PayloadOrganization</key>
                    <string>dev.skyfox.org</string>
                    <key>PayloadDisplayName</key>
                    <string> ' . $this->title . ' </string>
                    <key>PayloadVersion</key>
                    <integer>1</integer>
                    <key>PayloadUUID</key>
                    <string>3C4DC7D2-E475-3375-489C-0BB8D737A653</string>
                    <key>PayloadIdentifier</key>
                    <string>dev.skyfox.profile-service</string>
                    <key>PayloadDescription</key>
                    <string>' . $this->description. '</string>
                    <key>PayloadType</key>
                    <string>Profile Service</string>
                </dict>
            </plist>';
    }

    public function download() {
        header("Content-type: application/force-download");
        header("Content-Disposition: attachment; filename=udid.mobileconfig");
        echo $this->make();
        exit;
    }

    /**
     * 用户下载描述文件后， 苹果回调通知的地址
     *
     * @return [type] [description]
     */
    public function parsing($func) {
        $data       = file_get_contents('php://input');
        $plistBegin = '<?xml version="1.0"';
        $plistEnd   = '</plist>';
        $pos1       = strpos($data, $plistBegin);
        $pos2       = strpos($data, $plistEnd);
        $data2      = substr($data, $pos1, $pos2 - $pos1);
        $xml        = xml_parser_create();
        xml_parse_into_struct($xml, $data2, $vs);
        xml_parser_free($xml);
        $arrayCleaned   = [];
        foreach ($vs as $v) {
            if ($v['level'] == 3 && $v['type'] == 'complete') {
                $arrayCleaned[]= $v;
            }
        }
        $iterator = 0;
        foreach ($arrayCleaned as $elem) {
            $value = $arrayCleaned[$iterator + 1]['value'];
            switch ($elem['value']) {
                case "CHALLENGE":
                    $this->params['challenge'] = $value;
                    break;
                case "DEVICE_NAME":
                    $this->params['device_name'] = $value;
                    break;
                case "PRODUCT":
                    $this->params['device_product'] = $value;
                    break;
                case "UDID":
                    $this->params['udid'] = $value;
                    break;
                case "VERSION":
                    $this->params['device_version'] = $value;
                    break;
            }
            $iterator++;
        }
        $this->params = $_GET ? array_merge($this->params, $_GET) : $this->params;
        if (is_callable($func)) {
            $func($this->params);
        }
        if ($this->redirectUrl) {
            header('HTTP/1.1 301 Moved Permanently');
            header('Location:' . $this->redirectUrl);
        }
    }
    public function setRedirectAddress($url) {
        $this->redirectUrl = $url;
    }
}
