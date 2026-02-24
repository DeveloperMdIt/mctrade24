<?php
/*   __________________________________________________
    |  Copyright by admorris.pro  |
    |__________________________________________________|
*/
 namespace Plugin\admorris_pro\Models; use DateTimeInterface; use AdmPro\Illuminate\Database\Eloquent\Model; class RewardPointsTransactionModel extends Model { protected $table = "\x78\160\154\x75\x67\x69\x6e\137\x61\144\155\157\162\162\x69\x73\137\x70\162\x6f\137\162\x65\x77\141\162\x64\137\x70\x6f\151\156\x74\x73\x5f\x74\x72\141\156\163\141\143\164\151\x6f\x6e"; public $timestamps = false; protected $casts = ["\x63\165\x73\x74\157\x6d\x65\x72\137\151\144" => "\x69\156\164", "\x64\141\x74\x65" => "\x64\141\x74\x65\x74\x69\155\x65", "\164\x72\141\x6e\x73\x61\x63\x74\x69\157\x6e" => "\x66\x6c\157\x61\x74", "\x72\x65\144\x65\145\x6d\x65\144" => "\x62\157\x6f\154\x65\x61\x6e"]; }