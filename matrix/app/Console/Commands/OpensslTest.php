<?php

namespace Matrix\Console\Commands;

use Illuminate\Console\Command;

class OpensslTest extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'openssl:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test the openssl library.';

    private $iv;
    private $key;
    private $cipher;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->iv = config('video.history.aes_iv');
        $this->key = config('video.history.aes_key');
        $this->cipher = config('video.history.aes_cipher');
        parent::__construct();
    }

    protected function encrypt(string $plaintext)
    {
        return base64_encode(openssl_encrypt($plaintext, $this->cipher, $this->key, OPENSSL_RAW_DATA, $this->iv));
    }

    protected function decrypt(string $encryptedText)
    {
        return openssl_decrypt(base64_decode($encryptedText), $this->cipher, $this->key, OPENSSL_RAW_DATA, $this->iv);
    }


    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        //
        $text = "[ \033[32mOK\033[0m ] The openssl looks like OK.\n";
        try {
            $encryptedText = $this->encrypt($text);
            $retText = $this->decrypt($encryptedText);
        } catch (\Exception $e) {
            $retText = "[ \033[31mFailed\033[0m ] The openssl has some mistakes.\n";
            \Log::error($text);
        }

        echo $retText;
    }
}
