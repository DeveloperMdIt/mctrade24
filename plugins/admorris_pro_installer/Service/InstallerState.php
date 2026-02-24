<?php
/*   __________________________________________________
    |  Copyright by admorris.pro  |
    |__________________________________________________|
*/
 namespace Plugin\admorris_pro_installer\Service; class InstallerState { public string $phase = "\x69\x64\x6c\145"; public ?string $downloadLink = null; public ?string $error = null; public ?string $success = null; public ?LicenseResult $license = null; public ?string $shopVersion = null; public ?int $downloadSize = null; public ?string $zipPath = null; public bool $extracted = false; public bool $installed = false; }