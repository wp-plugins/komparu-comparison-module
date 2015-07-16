<?php

class CodeController extends BaseController
{
    public function get($route)
    {
        $url  = sprintf(
            'http://code.komparu.%s/%s/?%s',
            $this->plugin->config['target'],
            $route,
            $_SERVER['QUERY_STRING']
        );
        $code = file_get_contents($url);


        $code = preg_replace_callback('/(Kmp\.json_[a-z0-9]*?)\((.*)\)\;/msi', function ($json) {
            $data = json_decode($json[2]);
            if (property_exists($data, 'documents')) {
                array_walk($data->documents, function ($document) {
                    $document->{'url'}           = HTMLProcessor::hashed($document->{'url'}, $this->plugin);
                    $document->{'company.image'} = HTMLProcessor::hashed(
                        MediaController::checkForSlashes($document->{'company.image'}),
                        $this->plugin
                    );
                });
            }
            if (property_exists($data, 'html')) {
                $html       = new HTMLProcessor($this->plugin, $data->html, '');
                $data->html = $html->process()->getText();
            }
            if (property_exists($data, 'url')) {
                $data->url = preg_replace(
                    '/http(s?)\:\/\/code\.komparu\.[a-z]{2,4}\//',
                    $this->plugin->siteUrl . $this->plugin->config['rewrite'] . 'code/',
                    $data->url
                );
            }

            return $json[1] . '(' . json_encode($data, JSON_UNESCAPED_UNICODE) . ');';
        }, $code);

        header('Content-Type: text/javascript; charset=UTF-8');
        header('Content-length: ' . strlen($code));
        echo $code;
        exit();
    }
}