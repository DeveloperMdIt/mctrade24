<?php
/*   __________________________________________________
    |  Copyright by admorris.pro  |
    |__________________________________________________|
*/
 namespace Plugin\admorris_pro\update; use Plugin\admorris_pro\Models\RewardPointsSettings; use Plugin\admorris_pro\update\AbstractUpdater; class RewardPointsUpdater extends AbstractUpdater { protected string $searchValue = "\x6d\x61\162\x6b\145\164\x69\156\x67\x5f\x72\x65\167\141\x72\x64\137\x70\x6f\151\156\164\x73\x5f"; protected string $model = RewardPointsSettings::class; protected ?string $wasActive = "\141\143\x74\151\x76\145"; protected array $excludeValues = ["\143\x72\145\x64\x69\164\x5f\x64\x65\x6c\x65\164\x65"]; protected array $renameValues = ["\163\x68\157\167\137\166\141\154\165\x65\x5f\157\x6e\x5f\160\x72\157\x64\165\x63\164\144\145\x74\141\151\x6c" => "\x73\x68\157\x77\137\x76\141\x6c\165\x65"]; }