<?php

require __DIR__ . '/../vendor/autoload.php';
$contracts = json_decode(file_get_contents(__DIR__ . '/fixtures/contract.json'), true);
$raw = json_decode(file_get_contents(__DIR__ . '/fixtures/files.json'), true);
$files = glob(__DIR__ . '/../data/*.php');
if (count($files) !== count($contracts)) {
    throw new RuntimeException('Data file inventory differs');
}
foreach ($contracts as $name => $contract) {
    $map = \Voku\PortableUtf8EmojiData\EmojiMap::load();
    if (count($map) !== $contract['entries'] || hash('sha256', serialize($map)) !== $contract['sha256']) {
        throw new RuntimeException('Key/value/order contract differs: ' . $name);
    }
    if (hash_file('sha256', __DIR__ . '/../data/' . $name) !== $raw[$name]) {
        throw new RuntimeException('Deterministic representation differs: ' . $name);
    }
    // Callers can mutate their returned copy without poisoning another load.
    $map['caller mutation'] = 'changed';
    if (hash('sha256', serialize(\Voku\PortableUtf8EmojiData\EmojiMap::load())) !== $contract['sha256']) {
        throw new RuntimeException('Provider leaked a caller mutation');
    }
}

// A damaged installed package must fail explicitly, not return partial/silent mappings.
$path = __DIR__ . '/../data/emoji.php';
$backup = $path . '.contract-backup';
if (!rename($path, $backup)) {
    throw new RuntimeException('Cannot prepare corruption probe');
}
try {
    try {
        \Voku\PortableUtf8EmojiData\EmojiMap::load();
        throw new LogicException('Missing known data did not throw');
    } catch (RuntimeException $error) {
        if (strpos($error->getMessage(), 'voku/portable-utf8-emoji-data') === false) {
            throw $error;
        }
    }
} finally {
    rename($backup, $path);
}
echo count($contracts) . ' exact mapping contracts, mutation isolation and missing-data guards passed.' . PHP_EOL;
