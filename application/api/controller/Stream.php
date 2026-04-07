<?php
namespace app\api\controller;

use think\Controller;
use think\Response;

class Stream extends Controller
{
    public function index()
    {
        $live_id = input('get.live_id', 0);
        $video_url = input('get.video_url', '');

        if (empty($live_id) || empty($video_url)) {
            return json(['code' => 0, 'msg' => '参数错误']);
        }

        $video_url = urldecode($video_url);
        
        $this->streamVideo($video_url);
    }

    protected function streamVideo($videoUrl)
    {
        if (!filter_var($videoUrl, FILTER_VALIDATE_URL)) {
            header('HTTP/1.1 404 Not Found');
            exit;
        }

        $ch = curl_init($videoUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        
        $response = curl_exec($ch);
        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $headers = substr($response, 0, $headerSize);
        $body = substr($response, $headerSize);
        
        curl_close($ch);

        if (empty($body)) {
            header('HTTP/1.1 404 Not Found');
            exit;
        }

        $this->sendHeaders($headers);
        echo $body;
        flush();
        ob_flush();
    }

    protected function sendHeaders($headers)
    {
        $headerLines = explode("\r\n", $headers);
        foreach ($headerLines as $header) {
            if (!empty($header) && strpos($header, 'HTTP/') !== 0) {
                header($header);
            }
        }
        
        header('Content-Type: video/mp4');
        header('Accept-Ranges: bytes');
        header('Cache-Control: no-cache');
        header('Connection: keep-alive');
    }

    public function hls()
    {
        $live_id = input('get.live_id', 0);
        $video_url = input('get.video_url', '');

        if (empty($live_id) || empty($video_url)) {
            return json(['code' => 0, 'msg' => '参数错误']);
        }

        $video_url = urldecode($video_url);
        
        $this->streamHLS($video_url);
    }

    protected function streamHLS($videoUrl)
    {
        $m3u8Content = $this->generateM3U8($videoUrl);
        
        header('Content-Type: application/vnd.apple.mpegurl');
        header('Cache-Control: no-cache');
        echo $m3u8Content;
    }

    protected function generateM3U8($videoUrl)
    {
        $m3u8 = "#EXTM3U\n";
        $m3u8 .= "#EXT-X-VERSION:3\n";
        $m3u8 .= "#EXT-X-TARGETDURATION:10\n";
        $m3u8 .= "#EXT-X-MEDIA-SEQUENCE:0\n";
        $m3u8 .= "#EXTINF:10.0,\n";
        $m3u8 .= $videoUrl . "\n";
        $m3u8 .= "#EXT-X-ENDLIST\n";
        
        return $m3u8;
    }

    public function dash()
    {
        $live_id = input('get.live_id', 0);
        $video_url = input('get.video_url', '');

        if (empty($live_id) || empty($video_url)) {
            return json(['code' => 0, 'msg' => '参数错误']);
        }

        $video_url = urldecode($video_url);
        
        $this->streamDASH($video_url);
    }

    protected function streamDASH($videoUrl)
    {
        $mpdContent = $this->generateMPD($videoUrl);
        
        header('Content-Type: application/dash+xml');
        header('Cache-Control: no-cache');
        echo $mpdContent;
    }

    protected function generateMPD($videoUrl)
    {
        $mpd = '<?xml version="1.0" encoding="UTF-8"?>';
        $mpd .= '<MPD xmlns="urn:mpeg:dash:schema:mpd:2011" minimumUpdatePeriod="PT1H" 
               mediaPresentationDuration="PT1H" 
               programInformation="urn:mpeg:dash:schema:mpd:programInformation:2011">';
        $mpd .= '<Period start="PT0S" duration="PT1H">';
        $mpd .= '<AdaptationSet mimeType="video/mp4" codecs="avc1.42E01E,mp4a.40.2">';
        $mpd .= '<Representation id="1" bandwidth="1000000" width="1280" height="720">';
        $mpd .= '<BaseURL>' . $videoUrl . '</BaseURL>';
        $mpd .= '</Representation>';
        $mpd .= '</AdaptationSet>';
        $mpd .= '</Period>';
        $mpd .= '</MPD>';
        
        return $mpd;
    }
}
