<?php

namespace Tests\Concerns;

trait InteractsWithWechatPayKeys
{
    /**
     * @var array<int, string>
     */
    protected array $wechatKeyFiles = [];

    /**
     * @return array<string, string>
     */
    protected function createWechatKeyPair(string $prefix): array
    {
        $resource = openssl_pkey_new([
            'private_key_bits' => 2048,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ]);

        if ($resource === false) {
            self::fail('无法生成微信支付测试密钥');
        }

        openssl_pkey_export($resource, $privateKey);
        $details = openssl_pkey_get_details($resource);

        if (! is_array($details) || empty($details['key'])) {
            self::fail('无法导出微信支付测试公钥');
        }

        $privateKeyPath = tempnam(sys_get_temp_dir(), "{$prefix}_private_");
        $publicKeyPath = tempnam(sys_get_temp_dir(), "{$prefix}_public_");

        if ($privateKeyPath === false || $publicKeyPath === false) {
            self::fail('无法创建微信支付测试密钥文件');
        }

        file_put_contents($privateKeyPath, $privateKey);
        file_put_contents($publicKeyPath, $details['key']);

        $this->wechatKeyFiles[] = $privateKeyPath;
        $this->wechatKeyFiles[] = $publicKeyPath;

        return [
            'private_key' => $privateKey,
            'private_key_path' => $privateKeyPath,
            'public_key' => $details['key'],
            'public_key_path' => $publicKeyPath,
        ];
    }

    protected function cleanupWechatKeyFiles(): void
    {
        foreach ($this->wechatKeyFiles as $file) {
            if (is_file($file)) {
                @unlink($file);
            }
        }

        $this->wechatKeyFiles = [];
    }
}
