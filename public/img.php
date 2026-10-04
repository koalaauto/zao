<?PHP
// gravatar 头像代理：只接受 gravatar 的 /avatar/{md5} 地址，
// 防止被当作开放代理去访问任意地址（含服务器内网）
if (empty($_GET['url'])) {
    output_404();
}
$urlInfo = parse_url(trim($_GET['url']));
if (empty($urlInfo['path'])
    || ! preg_match('#^/avatar/([0-9a-f]{32})$#i', $urlInfo['path'], $matches)) {
    output_404();
}
$url = 'https://cn.gravatar.com/avatar/' . strtolower($matches[1]) . '?d=404';

// Create a stream
$opts = array(
    'http'=>array(
        'method'          => 'GET',
        'timeout'         => 30,
        'follow_location' => 0,
        'header'          => 
            "referer:https://cn.gravatar.com/\r\n" .
            "User-Agent: Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/27.0.1453.110 Safari/537.36"
    )
);
$ctx = stream_context_create($opts);
// Open the file using the HTTP headers set above
$file = @file_get_contents($url, false, $ctx);
// output, fall back to the default avatar
if ( ! $file) {
    header('Content-type: image/png');
    readfile(__DIR__ . '/static/img/felix.png');
    exit;
}
output_image($file);

// output image header
function output_image($file) {
    header('Content-type: image/jpeg');
    exit($file);
}

// output not found header
function output_404() {
    header('HTTP/1.1 404 Not Found');
    header('Status: 404 Not Found');
    exit;
}
