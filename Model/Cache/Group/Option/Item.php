<?php

/**
 * Опшен для получения элементов для групп кеша
 *
 * @author neon
 */
class Cache_Group_Option_Item extends Model_Option
{

    public function after()
    {
        $groupIds = $this->collection->column('id');
        $locator = IcEngine::serviceLocator();
        $queryBuilder = $locator->getService('query');
        $dds = $locator->getService('dds');
        $cacheQuerySelect = $queryBuilder
            ->select('*')
            ->from('Cache')
            ->where('Cache_Group__id', $groupIds);
        $caches = $dds->execute($cacheQuerySelect)
            ->getResult()->asTable();
        $groupCache = array();
        foreach ($caches as $cache) {
            $groupId = $cache['Cache_Group__id'];
            if (!isset($groupCache[$groupId])) {
                $groupCache[$groupId] = array();
            }
            $groupCache[$groupId][] = $cache;
        }
        foreach ($this->collection as $item) {
            $items = array();
            if (isset($groupCache[$item['id']])) {
                $items = $groupCache[$item['id']];
            }
            $item['data']['items'] = $items;
        }
    }

}