<?php

class MediaController extends BaseController
{
    public function get($media)
    {
        if (!($url = get_transient('cmpmd_' . $media))) {
            return;
        }

        $url = preg_replace('/^\/\//', 'http://', $url);

        $url_data = parse_url($url);
        if (stristr($url_data['host'], 'komparu')) {
            $client = new GuzzleHttp\Client();

            $url .= (parse_url($url, PHP_URL_QUERY) ? '&' : '?') . 'sid=' . SessionHelper::getSid($this->plugin);

            $response = $client->get($url, ['allow_redirects' => false]);

            if ($real_url = $response->getHeader('Location')) {
                wp_redirect($real_url);
                exit();
            }

            $filename = wp_upload_dir()['basedir'] . '/compmodule/' . $media;
            if (!is_dir(dirname($filename))) {
                mkdir(dirname($filename), 0777, true);
            }

            if (!file_put_contents($filename, $response->getBody())) {
                return;
            }

            $filetype = wp_check_filetype($filename);

            $attachment = [
                'guid'           => wp_upload_dir()['baseurl'] . '/compmodule/' . $media,
                'post_title'     => preg_replace('/\.[^.]+$/', '', basename($filename)),
                'post_mime_type' => $filetype['type'],
                'post_status'    => 'inherit'
            ];

            if (!($attach_id = wp_insert_attachment($attachment, $filename))) {
                return;
            }

            require_once(ABSPATH . 'wp-admin/includes/image.php');

            $attach_data = wp_generate_attachment_metadata($attach_id, $filename);
            wp_update_attachment_metadata($attach_id, $attach_data);

            wp_redirect(wp_upload_dir()['baseurl'] . '/compmodule/' . $media);
            exit();
        } else {
            wp_redirect($url);
            exit();
        }
    }

    public static function checkForSlashes($url)
    {
        return preg_replace('/^\/\//', 'http://', $url);
    }
}