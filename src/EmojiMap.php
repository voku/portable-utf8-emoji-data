<?php

namespace Voku\PortableUtf8EmojiData;

/** Ordered mapping names and values; encoding policy remains in portable-utf8. */
final class EmojiMap
{
    /** @return array<string, string> */
    public static function load(): array
    {
        $path = __DIR__ . '/../data/emoji.php';
        if (!\is_file($path)) {
            throw new \RuntimeException('Emoji data is incomplete. Reinstall voku/portable-utf8-emoji-data.');
        }

        return include $path;
    }
}
