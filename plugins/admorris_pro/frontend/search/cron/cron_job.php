<?php
/*   __________________________________________________
    |  Copyright by admorris.pro  |
    |__________________________________________________|
*/
 namespace Plugin\admorris_pro; use JTL\Shop; use Plugin\admorris_pro\StatusRequest; use Plugin\admorris_pro\search\cron\SearchCronAPI; function executeSearchCron() { $statusRequest = Shop::Container()->get(StatusRequest::class); $searchConfig = $statusRequest->getSearchConfig(); $cronAPI = new SearchCronAPI($searchConfig); return $cronAPI->executeSearchCron(); }