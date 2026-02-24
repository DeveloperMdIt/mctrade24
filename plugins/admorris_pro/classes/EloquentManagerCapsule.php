<?php
/*   __________________________________________________
    |  Copyright by admorris.pro  |
    |__________________________________________________|
*/
 namespace Plugin\admorris_pro; use JTL\Shop; use AdmPro\Illuminate\Database\Capsule\Manager as Capsule; use AdmPro\Illuminate\Database\Connectors\ConnectionFactory; class EloquentManagerCapsule extends Capsule { protected function setupManager() { $pdo = Shop::Container()->getDB()->getPDO(); $customFactory = new class(Shop::Container()) extends ConnectionFactory { public function makeConnection(\PDO $pdo, $database, $prefix = '') { return $this->createConnection($pdo->getAttribute(\PDO::ATTR_DRIVER_NAME), $pdo, $database, $prefix); } }; $connection = $customFactory->makeConnection($pdo, DB_NAME); $resolver = new \AdmPro\Illuminate\Database\ConnectionResolver(); $resolver->addConnection("\x63\165\163\x74\157\155", $connection); $resolver->setDefaultConnection("\143\x75\x73\164\157\155"); $this->manager = $resolver; } }