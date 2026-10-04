<?PHP
/**
 * 自定义函数
 */

/**
 * 秒数转时间
 *
 * @param int $seconds
 * @return string
 */
function seconds_to_time($seconds) {
    $hours = (int) ($seconds / 3600);
    if ($hours) {
        return gmstrftime('%H:%M:%S', $seconds);
    } else {
        return gmstrftime('%M:%S', $seconds);
    }
}

/**
 * 节目时段标题
 *
 * @param string $part
 * @return string
 */
function program_part_title($part) {
    $titles = [
        'a'   => '第一时段',
        'b'   => '第二时段',
        'c'   => '第三时段',
        'd'   => '第四时段',
        'all' => '完整时段'
    ];
    return isset($titles[$part]) ? $titles[$part] : '';
}

/**
 * 从节目URL中获取节目日期
 *
 * @param string $url
 * @return int
 */
function program_date_from_url($url) {
    preg_match('/program\/([0-9]+)/', $url, $matches);
    return (int) ($matches[1] ?? 0);
}

/**
 * 七牛URL
 *
 * @param string $path
 * @return string
 */
function qiniu_url($path) {
    return sprintf(
        '//%s%s',
        Config::get('filesystems.disks.qiniu.domain'),
        $path
    );
}

/**
 * 地图模式
 *
 * @param string $country
 * @return string
 */
function map_mode($country = '') {
    $modes = ['world', 'china'];
    $regions = ['中国', '香港', '澳门', '台湾', ''];

    return $modes[(int) in_array($country, $regions)];
}
