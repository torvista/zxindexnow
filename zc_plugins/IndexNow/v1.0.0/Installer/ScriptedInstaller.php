<?php

use Zencart\PluginSupport\ScriptedInstaller as ScriptedInstallBase;

class ScriptedInstaller extends ScriptedInstallBase
{
    protected function executeInstall()
    {
        $key = substr(bin2hex(random_bytes(20)), 0, 30);

        // Save the key to the file
        $filePath = DIR_FS_CATALOG . $key . '.txt';
        file_put_contents($filePath, $key);

        $this->addConfigurationKey('ZX_INDEXNOW_KEY', [
            'configuration_title' => 'IndexNow Key',
            'configuration_value' => $key,
            'configuration_description' => 'Auto-generated IndexNow Key. Matching txt file must exist in store root.',
            'configuration_group_id' => 0,
            'sort_order' => 100,
        ]);
    }

    protected function executeUninstall()
    {
        $filePath = DIR_FS_CATALOG . ZX_INDEXNOW_KEY . '.txt';
        @unlink($filePath);
        
        $this->deleteConfigurationKeys(['ZX_INDEXNOW_KEY']);
    }
}
