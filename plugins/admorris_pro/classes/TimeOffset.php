<?php
/*   __________________________________________________
    |  Copyright by admorris.pro  |
    |__________________________________________________|
*/
 namespace Plugin\admorris_pro; class TimeOffset extends \DateTime { private $format; public function __construct($offset, $format) { parent::__construct(); $this->modify($offset); $this->format = $format; } public function __toString() { return $this->format($this->format); } }