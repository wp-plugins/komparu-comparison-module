<?php

/**
 * Class JavaScriptProcessor
 */
class JavaScriptProcessor extends BaseProcessor
{
    /**
     * @return $this
     */
    public function process()
    {
        return $this->code();
    }

    protected function code()
    {
        $this->text = str_replace(
            'http://code.komparu.' . $this->plugin->config['target'] ,
            $this->plugin->siteUrl . '/compmodule/code',
            $this->text
        );

        return $this;
    }
}