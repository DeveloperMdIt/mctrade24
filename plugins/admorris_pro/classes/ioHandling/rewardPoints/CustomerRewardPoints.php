<?php
/*   __________________________________________________
    |  Copyright by admorris.pro  |
    |__________________________________________________|
*/
 namespace Plugin\admorris_pro\ioHandling\rewardPoints; use JTL\Customer\Customer; final class CustomerRewardPoints { public int $kKunde; public string $cVorname; public string $cNachname; public string $cMail; public string $cKundenNr; public function __construct(public float|int $valid_points = 0, public float|int $pending_points = 0, ?Customer $customer = null) { if (!($customer === null)) { goto DkdS6; } return; DkdS6: $this->kKunde = $customer->kKunde; $this->cVorname = $customer->cVorname; $this->cNachname = $customer->cNachname; $this->cMail = $customer->cMail; $this->cKundenNr = $customer->cKundenNr ?? ''; } }