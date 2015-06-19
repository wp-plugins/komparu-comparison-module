<?php

/**
 * Class CSSProcessor
 */
class CSSProcessor extends BaseProcessor
{
    /**
     * holds the stylesheet's initial URL for relative paths
     * @var array
     */
    protected $url;

    /**
     * @param \Herbert\Framework\Plugin $plugin
     * @param $text
     * @param $url
     */
    public function __construct($plugin, $text, $token, $url)
    {
        $this->url = parse_url($url);

        return parent::__construct($plugin, $text, $token);
    }

    /**
     * @return $this
     */
    public function process()
    {
        return $this->url();
    }

    /**
     * @return $this
     */
    protected function url()
    {
        $this->text = preg_replace_callback('/url\s?\([\"]?(.*?)[\"]?\)/msi', function ($url) {
            $parsed = parse_url($url[1]);

            if (!isset($parsed['host'])) {
                // url had relative path
                $path    = preg_replace(
                    '#\.\.\/|([^\/]+?\/)\.\.\/#', '',
                    dirname($this->url['path']) . '/' . $parsed['path']
                );
                $newpath = str_replace('komparu', 'compmodule', $path);

                set_transient(
                    'cmp_md_' . trim($newpath, '/'),
                    $this->url['scheme'] . '://' . $this->url['host'] . $path,
                    DAY_IN_SECONDS
                );

                $url = wp_upload_dir()['baseurl'] . '/compmodule' . $newpath;
            } elseif (stristr($parsed['host'], 'komparu')) {
                // url had absolute komparu path
                $path    = trim($parsed['path'], '/');
                $newpath = str_replace('komparu', 'compmodule', $path);

                set_transient(
                    'cmp_md_' . $newpath,
                    (preg_match('/^\/\//', $url[1]) ? ($this->url['scheme'] . ':') : '') . $url[1],
                    DAY_IN_SECONDS
                );
                $url = wp_upload_dir()['baseurl'] . '/compmodule/' . $newpath;
            } else {
                // url is outside of komparu domain
                $url = $url[1];
            }

            return 'url("' . $url . '")';
        }, $this->text);

        $this->text = preg_replace('/([\#\.])komparu/', '\1compmodule', $this->text);

        return $this;
    }
}