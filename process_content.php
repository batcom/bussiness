<?php

/**
 * 发送 HTTP 请求 (GET 或 POST)
 *
 * @param string $url           目标 URL
 * @param string $method        请求方法 (GET 或 POST)
 * @param array  $data          发送的数据 (POST 时使用)
 * @param string $proxy         代理地址 (可选)
 * @param string $proxyUser     代理用户名 (可选)
 * @param string $proxyPassword 代理密码 (可选)
 * @return mixed                响应内容或 false
 */
function sendHttpRequest($url, $method = 'GET', $data = [], $proxy = null, $proxyUser = null, $proxyPassword = null)
{
    $ch = curl_init();

    // 设置请求的 URL
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

    // 根据请求方法设置 cURL 选项
    if (strtoupper($method) === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    } elseif (strtoupper($method) === 'GET' && !empty($data)) {
        $query = http_build_query($data);
        curl_setopt($ch, CURLOPT_URL, $url . '?' . $query); // 将查询参数附加到 URL
    }

    // 如果使用代理
    if (!empty($proxy)) {
        curl_setopt($ch, CURLOPT_PROXY, $proxy);
        if (!empty($proxyUser) && !empty($proxyPassword)) {
            curl_setopt($ch, CURLOPT_PROXYUSERPWD, "$proxyUser:$proxyPassword");
        }
    }

    // 执行请求并获取响应
    $response = curl_exec($ch);

    // 错误处理
    if ($response === false) {
        echo "请求失败: " . curl_error($ch) . "\n";
        curl_close($ch);
        return false;
    }

    curl_close($ch);

    return $response;
}
// 远程网址
$remoteUrl = 'https://fly0001.buzz/gateway/feitu?token=9e0e618ec60b3bca4407488764006ee6'; // 替换为真实的网址
$submitUrl = 'https://www.juliangtiaodong.cn/index/index/subm'; // 提交到远程的地址

// 读取远程网址的内容
$content = sendHttpRequest($remoteUrl);

if ($content === false) {
    echo "无法读取远程内容\n";
    exit(1);
}

// Base64 解码内容
$decodedContent = base64_decode($content);

if ($decodedContent === false) {
    echo "Base64 解码失败\n";
    exit(1);
}

// 按行拆分内容
$lines = explode("\n", $decodedContent);
// var_dump($lines);
// die;

// 需要删除包含此字符串的行
$excludeString = 'IEPL×5';

// 过滤掉包含特定字符串的行
$filteredLines = array_filter($lines, function ($line) use ($excludeString) {
    if (strlen($line) == 0) return false;
    return strpos($line, $excludeString) === false;
});
// var_dump($filteredLines);
// die;

// 将过滤后的内容重新拼接成字符串
$filteredContent = implode("\n", $filteredLines);

// Base64 编码回去
$encodedContent = base64_encode($filteredContent);

$response = sendHttpRequest($submitUrl, 'POST', ['content' => $encodedContent]);
file_put_contents('feitu.yaml', $encodedContent);
echo "提交成功: $response\n";
