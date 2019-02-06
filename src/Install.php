<?php
namespace TDE\Adeptus;

class Install
{
    private $stash_url         = 'https://treebeardusr:FCmJJA*6VzNdky!wSjR!MXsQHHN@treebeard.tde.agency:8081';
    private $stash_log_enabled = 1;
    private $file_log_enabled  = 0;
    private $curl_method       = 'shell';

    public function setDefaults()
    {
        if (null !== (get_option('adeptus_logstash_url'))) {
            add_option('adeptus_logstash_url', $this->stash_url);
        }

        if (null !== (get_option('adeptus_logstash_logger'))) {
            add_option('adeptus_logstash_logger', $this->stash_log_enabled);
        }

        if (null !== (get_option('adeptus_file_logger'))) {
            add_option('adeptus_file_logger', $this->file_log_enabled);
        }

        if (null !== (get_option('adeptus_curl_method'))) {
            add_option('adeptus_curl_method', $this->curl_method);
        }
    }
}
