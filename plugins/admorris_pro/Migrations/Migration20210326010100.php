<?php
/*   __________________________________________________
    |  Copyright by admorris.pro  |
    |__________________________________________________|
*/
 namespace Plugin\admorris_pro\Migrations; use JTL\Plugin\Migration; use JTL\Update\IMigration; use JTL\Shop; class Migration20210326010100 extends Migration implements IMigration { protected $description = "\163\154\x69\x64\x65\x20\144\162\157\x70\x20\164\171\160\145"; public function up() { $this->execute("\101\x4c\124\x45\122\40\124\x41\102\114\105\40\170\x70\x6c\x75\147\x69\x6e\137\141\144\155\157\162\x72\151\163\x5f\160\162\157\x5f\163\154\x69\144\145\40\x44\x52\x4f\x50\40\164\x79\160\x65"); } public function down() { $this->execute("\x41\x4c\x54\105\x52\40\124\101\x42\114\105\40\170\x70\154\165\x67\x69\156\137\x61\144\155\157\162\162\x69\x73\137\160\162\x6f\137\163\154\x69\x64\x65\x20\101\104\x44\40\164\x79\160\x65\40\x76\141\x72\143\x68\141\162\x28\x32\65\x29\40\x41\106\x54\x45\x52\40\x74\x65\155\160\154\141\x74\x65"); } }