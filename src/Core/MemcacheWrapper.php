<?php
/**
 * Ideal CMS (https://idealcms.ru/)
 *
 * @link      https://github.com/idealcms/idealcms репозиторий исходного кода
 * @copyright Copyright (c) 2012-2020 Ideal CMS (https://idealcms.ru)
 * @license   https://idealcms.ru/license.html LGPL v3
 */

namespace Ideal\Core;

/**
 * Обёртка над Memcache, добавляющая теггирование
 *
 */
class MemcacheWrapper extends \Memcache
{
    const FALSE_VALUE = '-s95VSn.zMbP(ph1-S6M]Q.c$e<9wV-h';

    /**
     * Добавляет значение $value по ключу $key в случае, если значение с $key не было установлено ранее
     *
     * @param string $key Ключ для записи $value
     * @param mixed $value Значение, помещаемое в кэш
     * @param bool $ttl Время жизни значения в кэше
     * @param string|array $tagsKeys Строка или массив с тегами для ключа $key
     * @return bool Возвращает true при успешном выполнении и false в случае ошибки
     */
    public function addWithTags($key, $value, $ttl = false, $tagsKeys = 'default'): bool
    {
        $value = $this->createTagsContainer($value, $tagsKeys);

        if (false === $value) {
            $value = self::FALSE_VALUE;
        }

        return $this->add($key, $value, false, (int)$ttl);
    }

    /**
     * Подготовка контейнера с тегами для кеширования
     *
     * Структура контейнера:
     *     $container = array(
     *         'tags' => array(
     *             'tag_1' => 'versionOfTag1',
     *             'tag_2' => 'versionOfTag2',
     *         ),
     *         'value' => 'value',
     *     );
     *
     * @param $value mixed Кэшируемое значение
     * @param $tags  string|array Строка или массив тегов
     * @return array Контейнер для помещения в кэш
     */
    private function createTagsContainer($value, $tags): array
    {
        if (!is_array($tags)) {
            $tags = [$tags];
        }

        $tagsValues = (array)$this->get($tags);

        foreach ($tags as $tagKey) {
            if (!isset($tagsValues[$tagKey]) || $tagsValues[$tagKey] === null) {
                $tagsValues[$tagKey] = 0;
                $this->add($tagKey, 0);
            }
        }

        return [
            'tags' => $tagsValues,
            'value' => $value
        ];
    }

    /**
     * Удаление значений в кеше по тегу или группе тегов
     *
     * @param $tag string|array Строка или массив тегов
     * @return bool Возвращает true при успешном выполнении и false в случае ошибки
     */
    public function deleteByTag($tag): bool
    {
        // Обновляем в кэше версию для тега

        return $this->safeIncrement($tag);
    }

    /**
     * Безопасное увеличение значения в memcache
     *
     * Если значения по ключу $key не было, то оно будет создано
     *
     * @param      $key
     * @param int $value
     * @param bool $ttl
     * @return bool Возвращает true при успешном выполнении и false в случае ошибки
     */
    public function safeIncrement($key, $value = 1, $ttl = false)
    {
        if ($result = $this->increment($key, $value)) {
            return $result;
        }

        $this->add($key, 0, $ttl);

        return $this->increment($key, $value);
    }

    /**
     * Получает значение по ключу $key
     *
     * @param string|array $key Ключ кэширования
     * @return mixed
     */
    public function getWithTags($key)
    {
        $value = $this->get($key);

        if (false === $value) {
            $value = null;
        }

        if (self::FALSE_VALUE === $value) {
            $value = false;
        }

        if (!$value) {
            return $value;
        }

        if (is_array($key)) {
            foreach ($key as $singleKey) {
                if (!isset($value[$singleKey])) {
                    $value[$singleKey] = null;
                }
            }
        }

        return $this->getFromTagsContainer($key, $value);
    }

    /**
     * Получение значения из контейнера с тегами
     *
     * @param $key       string Ключ кэше
     * @param $container array Контейнер с тегами и значением из кэша
     * @return mixed Значение по ключу $key или null
     */
    private function getFromTagsContainer($key, $container)
    {
        if ($this->isTagsValid($container['tags'])) {
            return $container['value'];
        }
        $this->delete($key);
        return null;
    }

    /**
     * Проверка валидности тегов контейнера
     *
     * @param $tags
     * @return bool
     */
    private function isTagsValid($tags): bool
    {
        // Версии тегов из кэша сравниваются с версиями, полученными из контейнера

        $tagsVersions = (array) parent::get(array_keys($tags));

        foreach ($tagsVersions as $tagKey => $tagVersion) {
            if ($tagVersion === null || $tags[$tagKey] != $tagVersion) {
                return false;
            }
        }

        return true;
    }

    /**
     * Безопасное уменьшение значения в memcache.
     *
     * Если значения по ключу $key не было, то оно будет создано
     *
     * @param      $key
     * @param int $value
     * @param bool $ttl
     * @return bool Возвращает true при успешном выполнении и false в случае ошибки
     */
    public function safeDecrement($key, $value = 1, $ttl = false)
    {
        if ($result = $this->decrement($key, $value)) {
            return $result;
        }

        $this->add($key, 0, $ttl);

        return $this->decrement($key, $value);
    }

    /**
     * Устанавливает значение $value по ключу $key в кэше
     *
     * @param string $key Ключ для записи $value
     * @param mixed $value Значение, помещаемое в кэш
     * @param bool $ttl Время жизни значения в кэше
     * @param string|array $tagsKeys Строка или массив с тегами для ключа $key
     * @return bool Возвращает true при успешном выполнении и false в случае ошибки
     */
    public function setWithTags($key, $value, $ttl = false, $tagsKeys = 'default'): bool
    {
        $value = $this->createTagsContainer($value, $tagsKeys);

        if (false === $value) {
            $value = self::FALSE_VALUE;
        }

        return $this->set($key, $value, false, (int)$ttl);
    }
}
