<?php

/**
 * Пагинатор для нескольких коллекий
 *
 * @author Ziht
 * @Service("multipaginator")
 */
class Multipaginator
{   
    public function build($collectionArray, $page = 1, $perPage = 10, $countArray = [], $withCache = false, 
        $limitCountFromCache = 150)
    {
        if (!$collectionArray) {
            return false;
        }
        $locator = IcEngine::getServiceLocator();
        $helperModelCollection = $locator->getService('helperModelCollection');
        $totalCount = 0;
        if ($countArray) {
            $totalCount = array_sum($countArray);
        } else {
            foreach ($collectionArray as $key => $collection) {
                $count = $helperModelCollection->getCount($collection);
                $countArray[$key] = $count;
                $totalCount += $count;
            }
        }
        $totalOffset = ($page - 1) * $perPage;
        $result = [];
        foreach ($countArray as $key => $count) {
            if ($totalOffset > $count) {
                $totalOffset -= $count;
                continue;
            }
            if ($withCache) {
                $pageFromCache = (int) ($totalOffset / $limitCountFromCache);
                $cacheOffset = $pageFromCache * $limitCountFromCache;
                $dataProviderManager = $locator->getService('dataProviderManager');
                $provider = $dataProviderManager->get('Multipaginator');
                $cacheKey = md5(serialize([$collectionArray[$key], $pageFromCache]));
                $cacheSerialize = $provider->get($cacheKey);
                if (!$cacheSerialize) {
                    $collectionArray[$key]->query()->limit($limitCountFromCache + $perPage, $cacheOffset);
                    $collectionArray[$key]->load();
                    $provider->set($cacheKey, serialize($collectionArray[$key]));
                } else {
                    $collectionArray[$key] = unserialize($cacheSerialize);
                }
                $collectionArray[$key]->slice($totalOffset - $cacheOffset, $perPage - count($result));
            } else {
                $collectionArray[$key]->query()
                    ->limit($perPage - count($result), $totalOffset);
                $collectionArray[$key]->load();
            }
            $result =  array_merge($result, $collectionArray[$key]->getItems());
            if (count($result) >= $perPage) {
                break;
            }
        }
        if (!$result) {
            return false;
        }
        $paginator = new Paginator($page, $perPage, $totalCount, false);
        return [
            'resultArray'   => $result,
            'paginator'     => $paginator,
            'counts'        => $countArray
        ];
    }
}